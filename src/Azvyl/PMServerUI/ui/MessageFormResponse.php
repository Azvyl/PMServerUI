<?php

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
