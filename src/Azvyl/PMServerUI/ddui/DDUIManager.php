<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

/** Manager responsible for Data-Driven UI (DDUI) lifecycle and packet handling. */
final class DDUIManager{

	public function __construct(Plugin $plugin){
		$plManager = Server::getInstance()->getPluginManager();

		$plManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{

		}, EventPriority::LOWEST, $plugin);

		$plManager->registerEvent(DataPacketDecodeEvent::class, function(DataPacketDecodeEvent $event) : void{

		}, EventPriority::LOWEST, $plugin);

		$plManager->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{

		}, EventPriority::LOW, $plugin);
	}
}

