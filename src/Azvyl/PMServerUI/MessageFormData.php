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
 * Builds a simple two-button modal dialog.
 */
class MessageFormData{

	private FormDataHandler $handler;

	public function __construct(){
		$this->handler = new FormDataHandler(["button1" => "", "button2" => "", "content" => "", "title" => "", "type" => "modal"], MessageFormResponse::class);
	}

	public static function create(): MessageFormData{
		return new self();
	}

	/**
	 * Method that sets the body text for the modal form.
	 */
	public function body(Translatable|string $bodyText): MessageFormData{
		$this->handler->add("content", FormDataHandler::tryTl($bodyText));
		return $this;
	}

	/**
	 * Method that sets the text for the first button of the dialog.
	 */
	public function button1(Translatable|string $text): MessageFormData{
		$this->handler->add("button1", FormDataHandler::tryTl($text));
		return $this;
	}

	/**
	 * This method sets the text for the second button on the dialog.
	 */
	public function button2(Translatable|string $text): MessageFormData{
		$this->handler->add("button2", FormDataHandler::tryTl($text));
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
	public function title(Translatable|string $titleText): MessageFormData{
		$this->handler->add("title", FormDataHandler::tryTl($titleText));
		return $this;
	}
}