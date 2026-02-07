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

/** A simple 2-button modal message box. */
final class MessageBox extends DDUI{

	/** Creates a message form for a certain player. */
	public static function create(Player $player) : self{
		throw new \RuntimeException("Not implemented");
	}

	/**
	 * Sets the body text of the message box.
	 *
	 * @param Observable<string>|string|UIRawMessage $text
	 */
	public function body(Observable|string|UIRawMessage $text) : self{
		throw new \RuntimeException("Not implemented");
	}

	/**
	 * Sets the data for the top button in the form.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label
	 * @param Observable<string>|string|UIRawMessage|null $tooltip
	 */
	public function button1(Observable|string|UIRawMessage $label, Observable|string|UIRawMessage $tooltip = null) : self{
		throw new \RuntimeException("Not implemented");
	}

	/**
	 * Sets the data for the bottom button in the form.
	 *
	 * @param Observable<string>|string|UIRawMessage      $label
	 * @param Observable<string>|string|UIRawMessage|null $tooltip
	 */
	public function button2(Observable|string|UIRawMessage $label, Observable|string|UIRawMessage $tooltip = null) : self{
		throw new \RuntimeException("Not implemented");
	}

	/** Closes the form. Will throw an error if the form is not open. */
	public function close() : void{
		throw new \RuntimeException("Not implemented");
	}

	/** Show this modal to the player. Will throw an error if the modal is already showing. */
	public function show(Player $player) : Promise{
		throw new \RuntimeException("Not implemented");
	}

	/**
	 * Sets the title of the form.
	 *
	 * @param Observable<string>|string|UIRawMessage $text
	 */
	public function title(Observable|string|UIRawMessage $text) : self{
		throw new \RuntimeException("Not implemented");
	}
}
