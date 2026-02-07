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

/** Response for ActionFormData. Contains the selected button index. */
readonly class ActionFormResponse extends FormResponse{
	/**
	 * @param int|null $selection Returns the index of the button that was pushed.
	 */
	public function __construct(?FormCancelationReason $cancelationReason, public ?int $selection){
		parent::__construct($cancelationReason, $cancelationReason !== null);
	}
}
