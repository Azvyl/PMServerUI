<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

readonly class FormResponse{
	/**
	 * @param FormCancelationReason|null $cancelationReason Contains additional details as to why a form was canceled.
	 * @param bool $canceled If true, the form was canceled by the player (e.g., they selected the pop-up X close button).
	 */
	protected function __construct(public ?FormCancelationReason $cancelationReason, public bool $canceled){}
}
