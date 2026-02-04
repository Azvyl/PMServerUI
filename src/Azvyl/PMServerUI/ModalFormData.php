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

use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use function array_values;
use function count;
use function is_string;

/**
 * Used to create a fully customizable pop-up form for a player.
 */
class ModalFormData{

	private FormDataHandler $handler;
	private array $elements = [];

	public function __construct(){
		$this->handler = new FormDataHandler(["content" => null, "icon" => null, "title" => "", "type" => "custom_form"], ModalFormResponse::class, $this->elements);
	}

	public static function create(): ModalFormData{
		return new self();
	}

	/**
	 * Adds a section divider to the form.
	 */
	public function divider(): ModalFormData{
		$this->handler->arrAdd("content", ["text" => "", "type" => "divider"]);
		$this->elements[] = "divider";
		return $this;
	}

	/**
	 * Adds a dropdown with choices to the form.
	 *
	 * @param Translatable|string $label The label to display for the dropdown.
	 * @param (Translatable|string)[] $items The selectable items for the dropdown.
	 * @param int|null $default The default selected item index. It will be zero in case of not setting this value.
	 * @param Translatable|string|null $tooltip It will show an exclamation icon that will display a tooltip if it is hovered.
	 */
	public function dropdown(Translatable|string $label, array $items, ?int $default = null, Translatable|string|null $tooltip = null): ModalFormData{
		$items = array_values($items);
		foreach($items as $i => $item){
			if(!$item instanceof Translatable && !is_string($item)){
				throw new \InvalidArgumentException("Each item in the \$items array must be an instance of Translatable or a string");
			}
			$items[$i] = FormDataHandler::tryTl($item);
		}
		$val = ["options" => $items, "text" => FormDataHandler::tryTl($label), "type" => "dropdown"];
		if($default !== null){
			if(!isset($items[$default])){
				throw new \InvalidArgumentException("No option at index $default, cannot set as default");
			}
			$val["default"] = $default;
		}
		if($tooltip !== null){
			$val["tooltip"] = FormDataHandler::tryTl($tooltip);
		}
		$this->handler->arrAdd("content", $val);
		$this->elements[] = ["dropdown", count($items)];
		return $this;
	}

	/**
	 * Adds a header to the form.
	 *
	 * @param Translatable|string $text Text to display.
	 */
	public function header(Translatable|string $text): ModalFormData{
		$this->handler->arrAdd("content", ["text" => FormDataHandler::tryTl($text), "type" => "header"]);
		$this->elements[] = "header";
		return $this;
	}

	/**
	 * Adds a label to the form.
	 *
	 * @param Translatable|string $text Text to display.
	 */
	public function label(Translatable|string $text): ModalFormData{
		$this->handler->arrAdd("content", ["text" => FormDataHandler::tryTl($text), "type" => "label"]);
		$this->elements[] = "label";
		return $this;
	}

	/**
	 * Creates and shows this modal popup form. Returns asynchronously when the player confirms or cancels the dialog.
	 *
	 * @param Player $player Player to show this dialog to.
	 */
	public function show(Player $player): Promise{
		return $this->handler->show($player);
	}

	/**
	 * Adds a numeric slider to the form.
	 *
	 * @param Translatable|string $label The label to display for the slider.
	 * @param int $minimum The minimum selectable possible value.
	 * @param int $maximum The maximum selectable possible value.
	 * @param int|null $step Defines the increment of values that the slider generates when moved. It will be '1' in case of not providing this.
	 * @param int|null $default The default value for the slider.
	 * @param Translatable|string|null $tooltip It will show an exclamation icon that will display a tooltip if it is hovered.
	 */
	public function slider(Translatable|string $label, int $minimum, int $maximum, ?int $step = null, ?int $default = null, Translatable|string|null $tooltip = null): ModalFormData{
		if($minimum > $maximum){
			throw new \InvalidArgumentException("Slider min value should be less than max value");
		}
		$val = ["max" => $maximum, "min" => $minimum, "step" => 1, "text" => FormDataHandler::tryTl($label), "type" => "slider"];
		if($step !== null){
			if($step <= 0){
				throw new \InvalidArgumentException("Step must be greater than zero");
			}
			$val["step"] = $step;
		}
		if($default !== null){
			if($default > $maximum || $default < $minimum){
				throw new \InvalidArgumentException("Default must be in range $minimum ... $maximum");
			}
			$val["default"] = $default;
		}
		if($tooltip !== null){
			$val["tooltip"] = FormDataHandler::tryTl($tooltip);
		}
		$this->handler->arrAdd("content", $val);
		$this->elements[] = ["slider", $minimum, $maximum];
		return $this;
	}

	public function submitButton(Translatable|string $submitButtonText): ModalFormData{
		$this->handler->add("submit", FormDataHandler::tryTl($submitButtonText));
		return $this;
	}

	/**
	 * Adds a textbox to the form.
	 *
	 * @param Translatable|string $label The label to display for the textfield.
	 * @param Translatable|string|null $placeholder The place holder text to display.
	 * @param string|null $default The default value for the textfield.
	 * @param Translatable|string|null $tooltip It will show an exclamation icon that will display a tooltip if it is hovered.
	 */
	public function textField(Translatable|string $label, Translatable|string|null $placeholder = null, ?string $default = null, Translatable|string|null $tooltip = null): ModalFormData{
		$val = ["text" => FormDataHandler::tryTl($label), "type" => "input"];
		if($placeholder !== null){
			$val["placeholder"] = FormDataHandler::tryTl($placeholder);
		}
		if($default !== null){
			$val["default"] = $default;
		}
		if($tooltip !== null){
			$val["tooltip"] = FormDataHandler::tryTl($tooltip);
		}
		$this->handler->arrAdd("content", $val);
		$this->elements[] = "input";
		return $this;
	}

	/**
	 * This builder method sets the title for the modal dialog.
	 */
	public function title(Translatable|string $titleText): ModalFormData{
		$this->handler->add("title", FormDataHandler::tryTl($titleText));
		return $this;
	}

	/**
	 * Adds a toggle checkbox button to the form.
	 *
	 * @param Translatable|string $label The label to display for the toggle.
	 * @param bool|null $default The default value for the toggle.
	 * @param Translatable|string|null $tooltip It will show an exclamation icon that will display a tooltip if it is hovered.
	 */
	public function toggle(Translatable|string $label, ?bool $default = null, Translatable|string|null $tooltip = null): ModalFormData{
		$val = ["text" => FormDataHandler::tryTl($label), "type" => "toggle"];
		if($default !== null){
			$val["default"] = $default;
		}
		if($tooltip !== null){
			$val["tooltip"] = FormDataHandler::tryTl($tooltip);
		}
		$this->handler->arrAdd("content", $val);
		$this->elements[] = "toggle";
		return $this;
	}
}