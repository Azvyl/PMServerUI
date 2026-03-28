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

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\Promise;
use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\player\Player;

/** A customizable form that lets you put buttons, labels, toggles, dropdowns, sliders, and more into a form. */
final class CustomForm extends DDUI{

	/**
	 * Create a CustomForm for a specific player.
	 *
	 * @param Player                                 $player The player to show the form to.
	 * @param Observable<string>|string|UIRawMessage $title The title of the form.
	 */
	public static function create(Player $player, Observable|string|UIRawMessage $title) : self{
		$instance = new self();
		// TODO: to be implemented
		return $instance;
	}

	/**
	 * Inserts a button into the Custom form. onClick is called when the button is pressed.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label The text to display on the button.
	 * @param callable() : void                           $onClick The function to call when the button is clicked.
	 * @param bool|Observable<bool>                       $disabled
	 * @param Observable<string>|string|UIRawMessage|null $tooltip The tooltip to display when hovering over the button.
	 * @param bool|Observable<bool>                       $visible
	 */
	public function button(Observable|string|UIRawMessage $label, callable $onClick, bool|Observable $disabled = null, Observable|string|UIRawMessage $tooltip = null, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/** Closes the form. Throws an error if the form is not open. */
	public function close() : void{
		// TODO: to be implemented
	}

	/** Adds a close "X" button to the form. */
	public function closeButton() : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a divider (i.e. a line) into the Custom form.
	 *
	 * @param bool|Observable<bool> $visible Whether the divider is visible.
	 */
	public function divider(bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a dropdown into the Custom form with the provided items. The value is based on the items value that
	 * selected.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label The text to display above the dropdown.
	 * @param Observable<int|float>                       $value The currently selected index in the dropdown.
	 * @param array                                       $items An array of DropdownItem to show in the dropdown.
	 * @param Observable<string>|string|UIRawMessage|null $description
	 * @param bool|Observable<bool>                       $disabled
	 * @param bool|Observable<bool>                       $visible
	 */
	public function dropdown(Observable|string|UIRawMessage $label, Observable $value, array $items, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a label (i.e. medium-sized text) into the Custom form.
	 *
	 * @param Observable<string>|string|UIRawMessage $text The text to display in the label.
	 * @param bool|Observable<bool>                  $visible
	 */
	public function label(Observable|string|UIRawMessage $text, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a header (i.e. large-sized text) into the Custom form.
	 *
	 * @param Observable<string>|string|UIRawMessage $text The text to display in the header.
	 * @param bool|Observable<bool>                  $visible Whether the header is visible.
	 */
	public function header(Observable|string|UIRawMessage $text, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/** Returns true if the form is currently being shown to the player. */
	public function isShowing() : bool{
		// TODO: to be implemented
		return false;
	}

	/**
	 * Shows the form to the player. Will throw errors if the form is currently being shown or if another behavior pack
	 * is showing a form.
	 */
	public function show() : Promise{
		$promise = new Promise();
		// TODO: to be implemented
		return $promise;
	}

	/**
	 * Creates a slider that lets players pick a number between minValue and maxValue.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label The text to display above the slider.
	 * @param Observable<int|float>                       $value The current value observable (client-writable).
	 * @param int|float                                   $minValue The minimum selectable value.
	 * @param int|float                                   $maxValue The maximum selectable value.
	 * @param Observable<string>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null                  $disabled
	 * @param Observable<int|float>|int|float|null        $step The step size (increment) for the slider.
	 * @param bool|Observable<bool>|null                  $visible
	 */
	public function slider(Observable|string|UIRawMessage $label, Observable $value, int|float $minValue, int|float $maxValue, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, int|float|Observable $step = null, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a space into the Custom form.
	 *
	 * @param bool|Observable<bool>|null $visible Whether the spacer is visible.
	 */
	public function spacer(bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts a text field into the Custom for that players can enter text into.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label The label shown above the text field.
	 * @param Observable<string>                          $text The text observable bound to the field (client-writable).
	 * @param Observable<string>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null                  $disabled
	 * @param bool|Observable<bool>|null                  $visible
	 */
	public function textField(Observable|string|UIRawMessage $label, Observable $text, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Inserts an on/off toggle that players can interact with into the Custom form.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label The label shown beside the toggle.
	 * @param Observable<bool>                            $toggled The boolean observable bound to the toggle (client-writable).
	 * @param Observable<string>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null                  $disabled
	 * @param bool|Observable<bool>|null                  $visible
	 */
	public function toggle(Observable|string|UIRawMessage $label, Observable $toggled, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{
		// TODO: to be implemented
		return $this;
	}
}
