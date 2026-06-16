<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\form\FormValidationException;

/** Builder for a simple message form with two buttons. */
class MessageFormData extends ServerUI{
	private array|null|string $title = null;
	private array|null|string $body = null;
	private array|null|string $button1 = null;
	private array|null|string $button2 = null;

	public function title(string|UIRawMessage $title) : self{
		$this->title = $title instanceof UIRawMessage ? $title->encode() : $title;
		return $this;
	}

	public function body(string|UIRawMessage $body) : self{
		$this->body = $body instanceof UIRawMessage ? $body->encode() : $body;
		return $this;
	}

	public function button1(string|UIRawMessage $text) : self{
		$this->button1 = $text instanceof UIRawMessage ? $text->encode() : $text;
		return $this;
	}

	public function button2(string|UIRawMessage $text) : self{
		$this->button2 = $text instanceof UIRawMessage ? $text->encode() : $text;
		return $this;
	}

	/** @internal */
	public function toPacketFormData() : array{
		return [
			'type' => 'modal',
			'title' => $this->title ?? '',
			'content' => $this->body ?? '',
			'button1' => $this->button1 ?? '',
			'button2' => $this->button2 ?? '',
		];
	}

	/** @internal */
	public function processResponse(?string $rawData = null, ?FormCancelationReason $cancelReason = null) : MessageFormResponse{
		if($cancelReason !== null){
			return new MessageFormResponse($cancelReason, null);
		}
		if($rawData !== null){
			$trimmed = trim($rawData);
			if($trimmed === "true" || $trimmed === "false"){
				return new MessageFormResponse(null, $trimmed === "true" ? 0 : 1);
			}
			throw new FormValidationException("Expected bool, got $trimmed");
		}
		throw new \InvalidArgumentException("Expected rawData to be non-null");
	}
}
