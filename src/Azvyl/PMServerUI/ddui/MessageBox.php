<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\Promise;
use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\player\Player;

/** A simple 2-button modal message box. */
final class MessageBox extends DDUI{

	/** Creates a message form for a certain player. */
	public static function create(Player $player, Observable|string|UIRawMessage $title) : self{
		$instance = new self();
		// TODO: to be implemented
		return $instance;
	}

	/**
	 * Sets the body text of the message box.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $text
	 */
	public function body(Observable|string|UIRawMessage $text) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Sets the data for the top button in the form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $tooltip
	 */
	public function button1(Observable|string|UIRawMessage $label, Observable|string|UIRawMessage $tooltip = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/**
	 * Sets the data for the bottom button in the form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $tooltip
	 */
	public function button2(Observable|string|UIRawMessage $label, Observable|string|UIRawMessage $tooltip = null) : self{
		// TODO: to be implemented
		return $this;
	}

	/** Closes the form. Will throw an error if the form is not open. */
	public function close() : void{
		// TODO: to be implemented
	}

	/** Returns true if the message box is currently being shown to the player. */
	public function isShowing() : bool{
		// TODO: to be implemented
		return false;
	}

	/**
	 * Show this modal to the player. Will throw an error if the modal is already showing.
	 *
	 * @return Promise<mixed>
	 */
	public function show() : Promise{
		/** @var Promise<mixed> $promise */
		$promise = new Promise();
		//TODO: to be implemented
		return $promise;
	}

	/**
	 * Sets the title of the form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $text
	 */
	public function title(Observable|string|UIRawMessage $text) : self{
		// TODO: to be implemented
		return $this;
	}
}
