<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\Promise;
use pocketmine\player\Player;

abstract class ServerUI{

	public static function create() : static{
		return new static();
	}

	/**
	 * Show the form to a player. Returns a Promise that resolves with a FormResponse.
	 *
	 * @return Promise<FormResponse>
	 */
	final public function show(Player $player) : Promise{
		return PMServerUI::getUIManager()->___send($player, $this);
	}

	abstract public function toPacketFormData() : array;

	abstract public function processResponse(string $rawData = null, FormCancelationReason $cancelReason = null) : FormResponse;

}