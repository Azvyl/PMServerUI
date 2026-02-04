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
use function is_numeric;

/**
 * Returns data about the player results from a modal action form.
 */
readonly class ActionFormResponse extends FormResponse{

	/**
	 * @param int|null $selection Returns the index of the button that was pushed.
	 */
	public function __construct(?FormCancelationReason $cancelationReason, bool $canceled, public ?int $selection){
		parent::__construct($cancelationReason, $canceled);
	}

	public static function validate(string $rawData, int $buttonCount): int{
		if(!is_numeric($rawData)){
			throw new FormValidationException("Expected int, got $rawData");
		}
		$data = (int)$rawData;
		if($data < 0 || $data >= $buttonCount){
			throw new FormValidationException("Button $data does not exist");
		}
		return $data;
	}
}