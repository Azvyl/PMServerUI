<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

/** Dropdown data for use in CustomForm. */
final class DropdownItem{ // TODO: support UIRawMessage
	/**
	 * @param string $label The label of the dropdown item in the dropdown.
	 * @param int|float $value The value the dropdown will be set to when this item is selected.
	 * @param string|null $description The description of the dropdown item shown when it is selected.
	 */
	public function __construct(public string $label, public int|float $value, public ?string $description = null){}
}
