<?php

/*
 * PMServerUI
 * https://github.com/Azvyl/PMServerUI
 *
 * Copyright (c) 2026 Azvyl
 *
 * Licensed under the MIT License.
 * See LICENSE file in the project root for details.
 */

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\form\FormValidationException;
use pocketmine\network\PacketHandlingException;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_values;
use function count;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;

/** Builder for a customizable modal form. */
final class ModalFormData extends ServerUI{
	private array|null|string $title = null;
	private array|null|string $submit = null;
	/** @var array<int, array<string, mixed>> */
	private array $controls = [];
	/** @var array<int, callable|null> */
	private array $validators = [];

	public function title(string|UIRawMessage $title) : self{
		$this->title = $title instanceof UIRawMessage ? $title->encode() : $title;
		return $this;
	}

	public function divider() : self{
		$this->controls[] = ['type' => 'divider', 'text' => ''];
		$this->validators[] = null;
		return $this;
	}

	/**
	 * @param string[]|UIRawMessage[] $items
	 */
	public function dropdown(string|UIRawMessage $label, array $items, int $defaultValueIndex = null, string|UIRawMessage $tooltip = null) : self{
		$dropdownElement = [
			'type' => 'dropdown',
			'text' => $label instanceof UIRawMessage ? $label->encode() : $label,
			'options' => array_map(fn(string|UIRawMessage $item) => $item instanceof UIRawMessage ? $item->encode() : $item, $items)
		];
		if($defaultValueIndex !== null){
			$dropdownElement['default'] = $defaultValueIndex;
		}
		if($tooltip !== null){
			$dropdownElement['tooltip'] = $tooltip instanceof UIRawMessage ? $tooltip->encode() : $tooltip;
		}

		$this->controls[] = $dropdownElement;
		$this->validators[] = static function($value) use ($items) : int{
			if(!is_int($value)){
				throw new FormValidationException("Expected integer index for dropdown response, got " . gettype($value));
			}
			$maxIndex = count($items) - 1;
			if($value < 0 || $value > $maxIndex){
				throw new FormValidationException("Dropdown response index out of range (0..$maxIndex), got $value");
			}
			return $value;
		};

		return $this;
	}

	public function header(string|UIRawMessage $text) : self{
		$this->controls[] = ['type' => 'header', 'text' => $text instanceof UIRawMessage ? $text->encode() : $text];
		$this->validators[] = null;
		return $this;
	}

	public function label(string|UIRawMessage $text) : self{
		$this->controls[] = ['type' => 'label', 'text' => $text instanceof UIRawMessage ? $text->encode() : $text];
		$this->validators[] = null;
		return $this;
	}

	public function slider(string|UIRawMessage $label, int $minValue, int $maxValue, int $defaultValue = null, string|UIRawMessage $tooltip = null, int $valueStep = null) : self{
		$sliderElement = [
			'type' => 'slider',
			'text' => $label instanceof UIRawMessage ? $label->encode() : $label,
			'min' => (float) $minValue,
			'max' => (float) $maxValue,
			'step' => (float) ($valueStep ?? 1.0),
			'timeout' => 100.0,//TODO: Find out what this does (1.26.0.29)
		];
		if($defaultValue !== null){
			$sliderElement['default'] = $defaultValue;
		}
		if($tooltip !== null){
			$sliderElement['tooltip'] = $tooltip instanceof UIRawMessage ? $tooltip->encode() : $tooltip;
		}
		$this->controls[] = $sliderElement;
		$this->validators[] = static function($value) use ($minValue, $maxValue) : float{
			if(!is_int($value) && !is_float($value)){
				throw new FormValidationException("Expected numeric value for slider response, got " . gettype($value));
			}
			$numeric = (float) $value;
			if($numeric < $minValue || $numeric > $maxValue){
				throw new FormValidationException("Slider response out of range ($minValue..$maxValue), got $numeric");
			}
			return $numeric;
		};

		return $this;
	}

	public function submitButton(string|UIRawMessage $text) : self{
		$this->submit = $text instanceof UIRawMessage ? $text->encode() : $text;
		return $this;
	}

	public function textField(string|UIRawMessage $label, string|UIRawMessage $placeholderText, string|UIRawMessage $defaultValue = null, string|UIRawMessage $tooltip = null) : self{
		$textFieldElement = [
			'type' => 'input',
			'text' => $label instanceof UIRawMessage ? $label->encode() : $label,
			'placeholder' => $placeholderText instanceof UIRawMessage ? $placeholderText->encode() : $placeholderText,
		];
		if($defaultValue !== null){
			$textFieldElement['default'] = $defaultValue instanceof UIRawMessage ? $defaultValue->encode() : $defaultValue;
		}
		if($tooltip !== null){
			$textFieldElement['tooltip'] = $tooltip instanceof UIRawMessage ? $tooltip->encode() : $tooltip;
		}
		$this->controls[] = $textFieldElement;
		$this->validators[] = static function($value) : string{
			if(!is_string($value)){
				throw new FormValidationException("Expected string for text field response, got " . gettype($value));
			}
			return $value;
		};

		return $this;
	}

	public function toggle(string|UIRawMessage $label, bool $defaultValue = null, string|UIRawMessage $tooltip = null) : self{
		$toggleElement = [
			'type' => 'toggle',
			'text' => $label instanceof UIRawMessage ? $label->encode() : $label,
		];
		if($defaultValue !== null){
			$toggleElement['default'] = $defaultValue;
		}
		if($tooltip !== null){
			$toggleElement['tooltip'] = $tooltip instanceof UIRawMessage ? $tooltip->encode() : $tooltip;
		}
		$this->controls[] = $toggleElement;
		$this->validators[] = static function($value) : bool{
			if(!is_bool($value)){
				throw new FormValidationException("Expected boolean for toggle response, got " . gettype($value));
			}
			return $value;
		};

		return $this;
	}

	/** @internal */
	public function toPacketFormData() : array{
		$data = [
			'type' => 'custom_form',
			'icon' => null,
			'title' => $this->title ?? '',
			'content' => $this->controls,
		];
		if($this->submit !== null){
			$data['submit'] = $this->submit;
		}
		return $data;
	}

	/** @internal */
	public function processResponse(string $rawData = null, FormCancelationReason $cancelReason = null) : ModalFormResponse{
		if($cancelReason !== null){
			return new ModalFormResponse($cancelReason, null);
		}
		if($rawData !== null){
			try{
				$data = json_decode($rawData, true, 2, JSON_THROW_ON_ERROR);
			}catch(\JsonException $e){
				throw PacketHandlingException::wrap($e, "Failed to decode form response data");
			}
			if(!is_array($data)){
				throw new FormValidationException("Expected array, got $rawData");
			}

			$controlCount = count($this->controls);
			$interactiveCount = 0;
			foreach($this->validators as $v){
				if($v !== null) $interactiveCount++;
			}
			$actual = count($data);

			if($actual === $controlCount){
				$results = [];
				foreach($this->validators as $index => $validator){
					if($validator === null){
						$results[] = null;
						continue;
					}
					if(!array_key_exists($index, $data)){
						throw new FormValidationException("Missing expected result element at index $index");
					}
					$results[] = $validator($data[$index]);
				}
				return new ModalFormResponse(null, $results);
			}elseif($actual < $controlCount){
				// TODO: Verify this with 1.21.70 clients
				$filteredData = array_values(array_filter($data, static fn($value) => $value !== null));
				if(count($filteredData) === $interactiveCount){
					$results = [];
					$interactiveIndex = 0;
					foreach($this->validators as $validator){
						if($validator === null){
							$results[] = null;
							continue;
						}
						$results[] = $validator($filteredData[$interactiveIndex]);
						$interactiveIndex++;
					}
					return new ModalFormResponse(null, $results);
				}
			}

			throw new FormValidationException("Unexpected number of result elements: expected either $controlCount or $interactiveCount (compact), got $actual");
		}
		throw new \InvalidArgumentException("Expected rawData to be non-null");
	}
}
