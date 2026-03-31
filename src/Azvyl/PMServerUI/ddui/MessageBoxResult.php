<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

final class MessageBoxResult{
	/**
	 * @param DataDrivenScreenClosedReason $closeReason The reason the message box was closed.
	 * @param int|null $selection The button that was selected, undefined if it was closed without pressing a button.
	 */
	public function __construct(DataDrivenScreenClosedReason $closeReason, int $selection = null){}
}