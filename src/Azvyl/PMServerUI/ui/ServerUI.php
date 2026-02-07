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

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\Promise;
use pocketmine\player\Player;

abstract class ServerUI{

	public static function create() : static{
		return new static();
	}

	/** Show the form to a player. Returns a Promise that resolves with a FormResponse. */
	final public function show(Player $player) : Promise{
		return PMServerUI::getUIManager()->___send($player, $this);
	}

	abstract public function toPacketFormData() : array;

	abstract public function processResponse(string $rawData = null, FormCancelationReason $cancelReason = null) : FormResponse;

}