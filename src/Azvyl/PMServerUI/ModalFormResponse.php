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

namespace Azvyl\PMServerUI;

use pocketmine\form\FormValidationException;
use pocketmine\network\PacketHandlingException;
use pocketmine\utils\AssumptionFailedError;
use function array_fill;
use function count;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;

readonly class ModalFormResponse extends FormResponse{

	/**
	 * @param array|null $formValues An ordered set of values based on the order of controls specified by ModalFormData.
	 */
	public function __construct(?FormCancelationReason $cancelationReason, bool $canceled, public ?array $formValues){
		parent::__construct($cancelationReason, $canceled);
	}

	public static function validate(string $rawData, array $elements): array{
		try{
			$data = json_decode($rawData, true, 2, JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw PacketHandlingException::wrap($e, "Failed to decode form response data");
		}
		if(!is_array($data)){
			throw new FormValidationException("Expected array, got $rawData");
		}
		$actual = count($data);
		$expected = count($elements);
		if($actual > $expected){
			throw new FormValidationException("Too many result elements, expected $expected, got $actual");
		}elseif($actual < $expected){
			//In 1.21.70, the client doesn't send nulls for labels, so we need to polyfill them here to
			//maintain the old behaviour
			$noLabelsIndexMapping = [];
			foreach($elements as $index => $element){
				if($element === "label"){
					$noLabelsIndexMapping[] = $index;
				}
			}
			$expectedWithoutLabels = count($noLabelsIndexMapping);
			if($actual !== $expectedWithoutLabels){
				throw new FormValidationException("Wrong number of result elements, expected either $expected (with label values) or $expectedWithoutLabels (without label values, 1.21.70), got $actual");
			}

			//polyfill the missing nulls
			$mappedData = array_fill(0, $expected, null);
			foreach($data as $givenIndex => $value){
				$internalIndex = $noLabelsIndexMapping[$givenIndex] ?? null;
				if($internalIndex === null){
					throw new FormValidationException("Can't map given offset $givenIndex to an internal element offset (while correcting for labels)");
				}
				//set the appropriate values according to the given index
				//this could (?) still leave unexpected nulls, but the validation below will catch that
				$mappedData[$internalIndex] = $value;
			}
			if(count($mappedData) !== $expected){
				throw new AssumptionFailedError("This should always match");
			}
			$data = $mappedData;
		}

		foreach($data as $index => $value){
			if(!isset($elements[$index])){
				throw new FormValidationException("Element at offset $index does not exist");
			}
			$element = $elements[$index];
			if(is_array($element)){
				if($element[0] === "dropdown"){
					if(!is_int($value)){
						throw new FormValidationException("Dropdown: Expected int, got " . gettype($value));
					}
					if($value < 0 || $value >= $element[1]){
						throw new FormValidationException("Dropdown: Option $value does not exist");
					}
				}elseif($element[0] === "slider"){
					if(!is_float($value) && !is_int($value)){
						throw new FormValidationException("Slider: Expected float, got " . gettype($value));
					}
					if($value < $element[1] || $value > $element[2]){
						throw new FormValidationException("Slider: Value $value is out of bounds (min $element[1], max $element[2])");
					}
				}
			}elseif($element === "input"){
				if(!is_string($value)){
					throw new FormValidationException("Input: Expected string, got " . gettype($value));
				}
			}elseif($element === "toggle"){
				if(!is_bool($value)){
					throw new FormValidationException("Toggle: Expected bool, got " . gettype($value));
				}
			}elseif($value !== null){
				throw new FormValidationException("The value of $element must be null");
			}
		}
		return $data;
	}
}