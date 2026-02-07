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

/**
 * Response for ModalFormData. Holds an ordered set of form values.
 * Values may be boolean, number, string or null (if optional).
 */
readonly class ModalFormResponse extends FormResponse{

	/**
	 * @param array<int, bool|float|string|null>|null $formValues
	 */
	public function __construct(?FormCancelationReason $cancelationReason, public ?array $formValues){
		parent::__construct($cancelationReason, $cancelationReason !== null);
	}
}
