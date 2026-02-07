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

/** Response for MessageFormData. Contains selection index (0 or 1) or null. */
readonly class MessageFormResponse extends FormResponse{
	/**
	 * @param int|null $selection Returns the index of the button that was pushed.
	 */
	public function __construct(?FormCancelationReason $cancelationReason, public ?int $selection){
		parent::__construct($cancelationReason, $cancelationReason !== null);
	}
}
