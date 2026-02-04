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

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function trim;

final class UIManager{

	/** @var (array{FormDataHandler, Promise})[][] */
	private array $playerForms = [];

	public function __construct(Plugin $plugin){
		$plManager = Server::getInstance()->getPluginManager();
		$plManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event): void{
			$player = $event->getPlayer();
			if(!isset($this->playerForms[$player->getId()])){
				return;
			}
			/**
			 * @var FormDataHandler $form
			 * @var Promise $promise
			 */
			foreach($this->playerForms[$player->getId()] as [$form, $promise]){
				$form->informQuit($player, $promise);
			}
			unset($this->playerForms[$player->getId()]);
		}, EventPriority::LOWEST, $plugin);
		$plManager->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event): void{
			$packet = $event->getPacket();
			if($packet instanceof ModalFormResponsePacket){
				$player = $event->getOrigin()->getPlayer();
				/**
				 * @var FormDataHandler $form
				 * @var Promise $promise
				 */
				[$form, $promise] = $this->playerForms[$player->getId()][$packet->formId] ?? [null, null];
				if($form !== null){
					unset($this->playerForms[$player->getId()][$packet->formId]);
					$event->cancel();

					try{
						if($packet->cancelReason !== null){
							$form->handleCancel($player, $packet->cancelReason, $promise);
						}elseif($packet->formData !== null){
							$form->handleResponse($player, trim($packet->formData), $promise);
						}else{
							throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
						}
					}catch(\Throwable $e){
						PMServerUI::getLogger()->logException($e);
						try{
							$form->informCrash($e, $promise);
						}catch(\Throwable $t){
							PMServerUI::getLogger()->logException($t);
						}
					}
				}
			}
		}, EventPriority::LOW, $plugin);
	}

	public function trackForm(Player $player, int $formId, FormDataHandler $handler, Promise $promise): void{
		$this->playerForms[$player->getId()][$formId] = [$handler, $promise];
	}
}