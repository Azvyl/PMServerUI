<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\form\FormValidationException;
use function is_numeric;

/** Builder for a simple action form with a list of buttons. */
class ActionFormData extends ServerUI{
	private array|null|string $title = null;
	private array|null|string $body = null;
	private array $elements = [];
	private int $buttonCount = 0;

	public function title(string|UIRawMessage $title) : self{
		$this->title = $title instanceof UIRawMessage ? $title->encode() : $title;
		return $this;
	}

	public function body(string|UIRawMessage $body) : self{
		$this->body = $body instanceof UIRawMessage ? $body->encode() : $body;
		return $this;
	}

	public function button(string|UIRawMessage $text, ?string $iconPath = null, ?string $iconUrl = null) : self{
		$this->elements[] = [
			'type' => 'button',
			'text' => $text instanceof UIRawMessage ? $text->encode() : $text,
			'image' => $iconPath !== null || $iconUrl !== null ? [
				'data' => $iconPath ?? $iconUrl,
				'type' => $iconPath !== null ? 'path' : 'url',
			] : null,
		];
		$this->buttonCount++;
		return $this;
	}

	public function divider() : self{
		$this->elements[] = ['type' => 'divider', 'text' => ''];
		return $this;
	}

	public function header(string|UIRawMessage $text) : self{
		$this->elements[] = ['type' => 'header', 'text' => $text instanceof UIRawMessage ? $text->encode() : $text];
		return $this;
	}

	public function label(string|UIRawMessage $text) : self{
		$this->elements[] = ['type' => 'label', 'text' => $text instanceof UIRawMessage ? $text->encode() : $text];
		return $this;
	}

	/** @internal */
	public function toPacketFormData() : array{
		return [
			'type' => 'form',
			'title' => $this->title ?? '',
			'content' => $this->body ?? '',
			'elements' => $this->elements,
		];
	}

	/** @internal */
	public function processResponse(?string $rawData = null, ?FormCancelationReason $cancelReason = null) : ActionFormResponse{
		if($cancelReason !== null){
			return new ActionFormResponse($cancelReason, null);
		}
		if($rawData !== null){
			if(!is_numeric($rawData)){
				throw new FormValidationException("Expected int, got $rawData");
			}
			$data = (int)$rawData;
			if($data < 0 || $data >= $this->buttonCount){
				throw new FormValidationException("Button $data does not exist");
			}
			return new ActionFormResponse(null, $data);
		}
		throw new \InvalidArgumentException("Expected rawData to be non-null");
	}
}
