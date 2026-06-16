<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\Promise;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\form\FormValidationException;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function json_encode;
use function strlen;
use function trim;

/** Manager responsible for opening and closing UI forms. */
final class UIManager{

	/** @var array<int, array<int, array{0:ServerUI,1:Promise<FormResponse>}>> */
	private array $playerForms = [];

	public function __construct(Plugin $plugin){
		$plManager = Server::getInstance()->getPluginManager();
		$plManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			$player = $event->getPlayer();
			$playerId = $player->getId();
			if(isset($this->playerForms[$playerId])){
				foreach($this->playerForms[$playerId] as [, $promise]){
					try{
						$promise->reject(FormRejectError::create(FormRejectReason::PlayerQuit, "Player disconnected"));
					}catch(\Throwable $t){
						PMServerUI::getLogger()->logException($t);
					}
				}
				unset($this->playerForms[$playerId]);
			}
		}, EventPriority::LOWEST, $plugin);
		$plManager->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			$packet = $event->getPacket();
			if($packet instanceof ModalFormResponsePacket){
				$player = $event->getOrigin()->getPlayer();
				if($player === null){
					return;
				}
				$playerId = $player->getId();
				if(isset($this->playerForms[$playerId][$packet->formId])){
					[$ui, $promise] = $this->playerForms[$playerId][$packet->formId];
					unset($this->playerForms[$playerId][$packet->formId]);
					$event->cancel();

					try{
						if($packet->cancelReason !== null){
							$reason = FormCancelationReason::cases()[$packet->cancelReason] ?? null;
							if($reason !== null){
								$promise->resolve($ui->processResponse(cancelReason: $reason));
							}else{
								throw new FormValidationException("Player {$player->getName()} sent unknown cancel reason");
							}
						}elseif($packet->formData !== null){
							$maxFormResponseSize = 10 * 1024;
							if(strlen($packet->formData) > $maxFormResponseSize){
								throw new PacketHandlingException("Form response data too large, refusing to decode (received" . strlen($packet->formData) . " bytes, max $maxFormResponseSize bytes)");
							}
							$trimmedData = trim($packet->formData);
							if($trimmedData === "null" || $trimmedData === ""){
								throw new FormValidationException("Form response can't be null without cancel reason");
							}
							$promise->resolve($ui->processResponse(rawData: $packet->formData));
						}else{
							throw new PacketHandlingException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
						}
					}catch(\Throwable $t){
						PMServerUI::getLogger()->logException($t);
						try{
							if($t instanceof FormValidationException){
								$promise->reject(FormRejectError::create(FormRejectReason::MalformedResponse, "Failed to process form: {$t->getMessage()}", previous: $t));
								return;
							}
							$promise->reject(FormRejectError::create(FormRejectReason::ServerShutdown, "Crashed when handling packet: {$t->getMessage()}", previous: $t));
						}catch(\Throwable $t){
							PMServerUI::getLogger()->logException($t);
						}
					}
				}
			}
		}, EventPriority::LOW, $plugin);
	}

	/** Close all forms for a player. */
	public function closeAllForms(Player $player) : void{
		throw new \RuntimeException("Not implemented yet");
	}

	/**
	 * @internal
	 * @param Promise<FormResponse> $promise
	 */
	public function ___track(Player $player, int $formId, ServerUI $ui, Promise $promise) : void{
		// Note: A vanilla client should only have one form open at a time
		if(($this->playerForms[$player->getId()] ?? []) !== []){
			PMServerUI::getLogger()->debug("Player {$player->getName()} ({$player->getId()}) had multiple open forms. This may indicate a suspicious client or a bug in a UI plugin.");
		}
		$this->playerForms[$player->getId()][$formId] = [$ui, $promise];
	}

	/**
	 * @internal
	 * @return Promise<FormResponse>
	 */
	public function ___send(Player $player, ServerUI $ui) : Promise{
		/** @var Promise<FormResponse> $promise */
		$promise = new Promise();
		(function(UIManager $UIManager, ServerUI $ui, Promise $promise) : void{
			/** @noinspection PhpUndefinedFieldInspection */
			$id = $this->formIdCounter++;//$this is Player instance due to closure binding
			/** @noinspection PhpUndefinedMethodInspection */
			if($this->getNetworkSession()->sendDataPacket(ModalFormRequestPacket::create($id, json_encode($ui->toPacketFormData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)))){
				/** @noinspection PhpParamsInspection */
				$UIManager->___track($this, $id, $ui, $promise);
			}
		})->call($player, $this, $ui, $promise);
		return $promise;
	}
}
