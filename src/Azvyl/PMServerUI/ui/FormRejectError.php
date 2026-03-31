<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

/** Error thrown when a form response is rejected for a reason. */
class FormRejectError extends \RuntimeException{
	public function __construct(public FormRejectReason $reason, string $message = "Form rejected", ?\Throwable $previous = null){
		parent::__construct($message, previous: $previous);
	}

	public static function create(FormRejectReason $reason, string $message = 'Form rejected', ?\Throwable $previous = null) : static{
		return new static($reason, $message, $previous);
	}
}
