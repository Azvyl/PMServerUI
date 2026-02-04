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

/**
 * Builds a simple player form with buttons that let the player take action.
 */
class ActionFormData{

	private FormDataHandler $handler;
	private int $buttonCount = 0;

	public function __construct(){
		$this->handler = new FormDataHandler(["content" => "", "elements" => null, "title" => "", "type" => "form"], ActionFormResponse::class, $this->buttonCount);
	}

	public static function create(): ActionFormData{
		return new self();
	}

	/**
	 * Method that sets the body text for the modal form.
	 */
	public function body(Translatable|string $bodyText): ActionFormData{
		$this->handler->add("content", FormDataHandler::tryTl($bodyText));
		return $this;
	}

	/**
	 * Adds a button to this form with an icon from a resource pack.
	 */
	public function button(Translatable|string $text, ?string $iconPath = null, string $iconType = "path"): ActionFormData{
		$this->handler->arrAdd("elements", ["image" => $iconPath !== null ? ["data" => $iconPath, "type" => $iconType] : null, "text" => FormDataHandler::tryTl($text), "type" => "button"]);
		$this->buttonCount++;
		return $this;
	}

	/**
	 * Adds a section divider to the form.
	 */
	public function divider(): ActionFormData{
		$this->handler->arrAdd("elements", ["text" => "", "type" => "divider"]);
		return $this;
	}

	/**
	 * Adds a header to the form.
	 *
	 * @param Translatable|string $text Text to display.
	 */
	public function header(Translatable|string $text): ActionFormData{
		$this->handler->arrAdd("elements", ["text" => FormDataHandler::tryTl($text), "type" => "header"]);
		return $this;
	}

	/**
	 * Adds a label to the form.
	 *
	 * @param Translatable|string $text Text to display.
	 */
	public function label(Translatable|string $text): ActionFormData{
		$this->handler->arrAdd("elements", ["text" => FormDataHandler::tryTl($text), "type" => "label"]);
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
	 * This builder method sets the title for the modal dialog.
	 */
	public function title(Translatable|string $titleText): ActionFormData{
		$this->handler->add("title", FormDataHandler::tryTl($titleText));
		return $this;
	}
}