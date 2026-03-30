<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\ddui\packets\ClientboundDataStorePacket;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreChange as ClientboundDataStoreChange;
use Azvyl\PMServerUI\ddui\packets\types\NoneDataStorePropertyValue;
use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\Promise;
use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUICloseScreenPacket;
use pocketmine\network\mcpe\protocol\ServerboundDataDrivenScreenClosedPacket;
use pocketmine\network\mcpe\protocol\ServerboundDataStorePacket;
use pocketmine\network\mcpe\protocol\types\BoolDataStoreValue;
use pocketmine\network\mcpe\protocol\types\DataStore;
use pocketmine\network\mcpe\protocol\types\DataStoreUpdate;
use pocketmine\network\mcpe\protocol\types\DataStoreValue;
use pocketmine\network\mcpe\protocol\types\DoubleDataStoreValue;
use pocketmine\network\mcpe\protocol\types\StringDataStoreValue;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

/**
 * @internal
 * DDUIManager
 *
 * Handles lifecycle and packet-level interactions for Data-Driven UI.
 */
final class DDUIManager{
	private int $formIdCounter = 1;

	/** @var array<string, int> */
	private array $updateCounts = [];

	/** @var array<string, int> */
	private array $activeFormIds = [];

	/**
	 * Per-player promises for open DDUI forms.
	 * Structure: [playerUuid => [formId => Promise]]
	 *
	 * @var array<string, array<int, Promise<bool>>>
	 */
	private array $formPromises = [];

	/**
	 * Bindings of observables for shown forms.
	 * Structure: [playerUuid => [formId => [path => Observable]]]
	 *
	 * @var array<string, array<int, array<string, Observable>>>
	 */
	private array $bindings = [];

	/**
	 * Reverse index of bindings by observable object id.
	 * Structure: [spl_object_id => [['player' => playerUuid, 'form' => formId, 'path' => path], ...]]
	 *
	 * @var array<int, array<array<string,mixed>>>
	 */
	private array $bindingsByObservable = [];

	/** Click handlers for buttons: [playerUuid => [formId => [path => callable]]] */
	private array $clickHandlers = [];

	/** Path update counters per player and path to produce pathUpdateCount */
	private array $pathUpdateCounts = [];

	/**
	 * Per-element interaction state used to validate inbound updates.
	 * Structure: [playerUuid => [formId => [elementIndex => ['visible' => bool, 'disabled' => bool]]]]
	 *
	 * @var array<string, array<int, array<int, array{visible:bool, disabled:bool}>>>
	 */
	private array $elementInteractionState = [];

	public function __construct(Plugin $plugin){
		$plManager = Server::getInstance()->getPluginManager();

		$plManager->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			$this->clearPlayerState($event->getPlayer()->getUniqueId()->getBytes(), "Player disconnected");
		}, EventPriority::LOWEST, $plugin);

		$plManager->registerEvent(DataPacketDecodeEvent::class, function(DataPacketDecodeEvent $event) : void{
			if($event->getPacketId() === ServerboundDataStorePacket::NETWORK_ID || $event->getPacketId() === ServerboundDataDrivenScreenClosedPacket::NETWORK_ID){
				$event->uncancel();
			}
		}, EventPriority::LOWEST, $plugin, true);

		$plManager->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			$packet = $event->getPacket();
			$player = $event->getOrigin()->getPlayer();
			if($player === null){
				return;
			}

			if($packet instanceof ServerboundDataStorePacket){
				$this->handleServerboundDataStore($event, $player, $packet);
			}elseif($packet instanceof ServerboundDataDrivenScreenClosedPacket){
				$this->handleServerboundScreenClosed($event, $player, $packet);
			}
		}, EventPriority::LOW, $plugin);
	}

	public function hasActiveForm(string $playerUuid) : bool{
		return isset($this->activeFormIds[$playerUuid]);
	}

	/**
	 * @param DataStore[] $entries
	 */
	public function sendDataStoreEntries(Player $player, array $entries) : void{
		$player->getNetworkSession()->sendDataPacket(ClientboundDataStorePacket::create($entries));
	}

	/**
	 * Register a promise for a shown form so DDUIManager can resolve/reject it.
	 *
	 * @param string $playerUuid raw uuid bytes
	 * @param int $formId
	 * @param Promise<bool> $promise
	 */
	public function registerFormPromise(string $playerUuid, int $formId, Promise $promise) : void{
		if(isset($this->activeFormIds[$playerUuid]) && $this->activeFormIds[$playerUuid] !== $formId){
			throw new \RuntimeException("A DDUI form is already active for this player");
		}
		$this->formPromises[$playerUuid][$formId] = $promise;
		$this->activeFormIds[$playerUuid] = $formId;
	}

	/**
	 * Register an observable binding for a shown form path.
	 * The path is the DataStore path, e.g. "title" or "layout[3].visible".
	 */
	public function registerObservableBinding(string $playerUuid, int $formId, string $path, Observable $observable) : void{
		$this->bindings[$playerUuid][$formId][$path] = $observable;
		$id = spl_object_id($observable);
		$this->bindingsByObservable[$id][] = ['player' => $playerUuid, 'form' => $formId, 'path' => $path];
	}

	/**
	 * Unregister bindings for a form (called when form closes).
	 */
	public function unregisterBindingsForForm(string $playerUuid, int $formId) : void{
		if(!isset($this->bindings[$playerUuid][$formId])) return;
		foreach($this->bindings[$playerUuid][$formId] as $path => $obs){
			$id = spl_object_id($obs);
			if(isset($this->bindingsByObservable[$id])){
				$this->bindingsByObservable[$id] = array_filter($this->bindingsByObservable[$id], function($e) use ($playerUuid, $formId, $path){
					return !($e['player'] === $playerUuid && $e['form'] === $formId && $e['path'] === $path);
				});
				if($this->bindingsByObservable[$id] === []){
					unset($this->bindingsByObservable[$id]);
				}
			}
		}
		unset($this->bindings[$playerUuid][$formId]);
	}

	/**
	 * Register a click handler for a specific button path in a shown form.
	 */
	public function registerClickHandler(string $playerUuid, int $formId, string $path, callable $handler) : void{
		$this->clickHandlers[$playerUuid][$formId][$path] = $handler;
	}

	public function registerElementInteractionState(string $playerUuid, int $formId, int $elementIndex, bool $visible, bool $disabled) : void{
		$this->elementInteractionState[$playerUuid][$formId][$elementIndex] = [
			'visible' => $visible,
			'disabled' => $disabled,
		];
	}

	private function getDataStoreValue(DataStoreValue $val) : mixed{
		if(method_exists($val, 'getValue')){
			return $val->getValue();
		}
		return null;
	}

	/**
	 * Called by Observable when server code sets an observable value. Sends DataStore updates to all bound clients.
	 */
	public function notifyObservableChanged(Observable $observable) : void{
		$id = spl_object_id($observable);
		if(!isset($this->bindingsByObservable[$id])) return;

		foreach($this->bindingsByObservable[$id] as $entry){
			$playerUuid = $entry['player'];
			$formId = $entry['form'];
			$path = $entry['path'];

			try{
				$this->sendObservableUpdateToBinding($playerUuid, $formId, $path, $observable->getData());
			}catch(\Throwable $t){
				PMServerUI::getLogger()->logException($t);
			}
		}
	}

	private function handleServerboundDataStore(DataPacketReceiveEvent $event, Player $player, ServerboundDataStorePacket $packet) : void{
		try{
			$update = $packet->getUpdate();
			if($update->getName() !== "minecraft" || $update->getProperty() !== "custom_form_data"){
				return;
			}

			$path = $update->getPath();
			$playerUuid = $player->getUniqueId()->getBytes();
			$formId = $this->activeFormIds[$playerUuid] ?? null;
			if($formId === null){
				return;
			}

			if($this->isInteractionBlockedByState($playerUuid, $formId, $path)){
				$event->cancel();
				return;
			}

			if($path === "closeButton.onClick"){
				$event->cancel();
				$player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUICloseScreenPacket::create($formId));
				return;
			}

			if(isset($this->clickHandlers[$playerUuid][$formId][$path])){
				$event->cancel();
				($this->clickHandlers[$playerUuid][$formId][$path])($update->getData());
				return;
			}

			if(isset($this->bindings[$playerUuid][$formId][$path])){
				$event->cancel();
				$observable = $this->bindings[$playerUuid][$formId][$path];
				if(!$observable->isClientWritable()){
					return;
				}
				$newValue = $this->getDataStoreValue($update->getData());
				$this->trackElementInteractionPathUpdate($playerUuid, $formId, $path, $newValue);
				$observable->applyClientUpdate($newValue);
				$this->notifyObservableChangedFromClient($observable, $playerUuid, $formId, $path);
			}
		}catch(\Throwable $t){
			PMServerUI::getLogger()->logException($t);
		}
	}

	private function notifyObservableChangedFromClient(Observable $observable, string $sourcePlayerUuid, int $sourceFormId, string $sourcePath) : void{
		$id = spl_object_id($observable);
		if(!isset($this->bindingsByObservable[$id])){
			return;
		}

		$value = $observable->getData();
		foreach($this->bindingsByObservable[$id] as $entry){
			$playerUuid = $entry['player'];
			$formId = $entry['form'];
			$path = $entry['path'];

			if($playerUuid === $sourcePlayerUuid && $formId === $sourceFormId && $path === $sourcePath){
				continue;
			}

			try{
				$this->sendObservableUpdateToBinding($playerUuid, $formId, $path, $value);
			}catch(\Throwable $t){
				PMServerUI::getLogger()->logException($t);
			}
		}
	}

	private function sendObservableUpdateToBinding(string $playerUuid, int $formId, string $path, bool|float|int|string|UIRawMessage $value) : void{
		if(($this->activeFormIds[$playerUuid] ?? null) !== $formId){
			return;
		}

		$player = Server::getInstance()->getPlayerByRawUUID($playerUuid);
		if($player === null){
			return;
		}

		$data = $this->toDataStoreValue($value);
		if($data === null){
			PMServerUI::getLogger()->warning("Unimplemented data type for DataStoreValue conversion: " . get_debug_type($value));
			return;
		}

		$updateCount = $this->updateCounts[$playerUuid];// use the same value as previously sent to open the form
		$pathUpdate = ($this->pathUpdateCounts[$playerUuid][$path] ?? 0) + 1;
		$this->pathUpdateCounts[$playerUuid][$path] = $pathUpdate;
		$this->trackElementInteractionPathUpdate($playerUuid, $formId, $path, $value);

		$this->sendDataStoreEntries($player, [new DataStoreUpdate(
			"minecraft",
			"custom_form_data",
			$path,
			$data,
			$updateCount,
			$pathUpdate
		)]);
	}

	private function toDataStoreValue(bool|float|int|string|UIRawMessage $value) : ?DataStoreValue{
		if(is_bool($value)){
			return new BoolDataStoreValue($value);
		}
		if(is_int($value) || is_float($value)){
			return new DoubleDataStoreValue((float) $value);
		}
		if(is_string($value)){
			return new StringDataStoreValue($value);
		}
		if($value instanceof UIRawMessage){
			return new StringDataStoreValue(json_encode($value->encode(), JSON_UNESCAPED_SLASHES));
		}

		return null;
	}

	private function handleServerboundScreenClosed(DataPacketReceiveEvent $event, Player $player, ServerboundDataDrivenScreenClosedPacket $packet) : void{
		$event->cancel();
		$playerUuid = $player->getUniqueId()->getBytes();
		$updateCount = $this->nextUpdateCountFor($playerUuid);

		try{
			$this->sendDataStoreEntries($player, [
				new ClientboundDataStoreChange("minecraft", "ddui_form_active", $updateCount, new BoolDataStorePropertyValue(false)),
				new ClientboundDataStoreChange("minecraft", "custom_form_data", $updateCount, new NoneDataStorePropertyValue()),
			]);

			$formId = $this->getScreenClosedFormId($packet);
			if(isset($this->formPromises[$playerUuid][$formId])){
				$this->formPromises[$playerUuid][$formId]->resolve(true);
				unset($this->formPromises[$playerUuid][$formId]);
				if($this->formPromises[$playerUuid] === []){
					unset($this->formPromises[$playerUuid]);
				}
			}

			$this->unregisterBindingsForForm($playerUuid, $formId);
			unset($this->clickHandlers[$playerUuid][$formId]);

			if(($this->activeFormIds[$playerUuid] ?? null) === $formId){
				unset($this->activeFormIds[$playerUuid]);
			}
			unset($this->elementInteractionState[$playerUuid][$formId]);
			if(($this->elementInteractionState[$playerUuid] ?? []) === []){
				unset($this->elementInteractionState[$playerUuid]);
			}
			unset($this->pathUpdateCounts[$playerUuid]);
		}catch(\Throwable $t){
			PMServerUI::getLogger()->logException($t);
		}
	}

	private function getScreenClosedFormId(ServerboundDataDrivenScreenClosedPacket $packet) : int{
		if(method_exists($packet, 'getFormId')){
			/** @var int */
			return $packet->getFormId();
		}
		return (new \ReflectionProperty($packet, 'formId'))->getValue($packet);
	}

	private function clearPlayerState(string $playerUuid, string $reason) : void{
		unset($this->updateCounts[$playerUuid], $this->activeFormIds[$playerUuid], $this->pathUpdateCounts[$playerUuid], $this->clickHandlers[$playerUuid], $this->bindings[$playerUuid], $this->elementInteractionState[$playerUuid]);
		if(!isset($this->formPromises[$playerUuid])){
			return;
		}
		foreach($this->formPromises[$playerUuid] as $promise){
			try{
				$promise->reject(new \RuntimeException($reason));
			}catch(\Throwable $t){
				PMServerUI::getLogger()->logException($t);
			}
		}
		unset($this->formPromises[$playerUuid]);
	}

	/** Generate and return a new unique form id. */
	public function nextFormId() : int{
		return $this->formIdCounter++;
	}

	/** Increment and return the next update count for a given player UUID (raw bytes string). */
	public function nextUpdateCountFor(string $playerUuid) : int{
		$this->updateCounts[$playerUuid] = ($this->updateCounts[$playerUuid] ?? 0) + 1;
		return $this->updateCounts[$playerUuid];
	}

	private function isInteractionBlockedByState(string $playerUuid, int $formId, string $path) : bool{
		if(!preg_match('/^layout\[(\d+)]\.(.+)$/', $path, $matches)){
			return false;
		}

		$property = $matches[2];
		if($property === 'visible' || $property === 'disabled'){
			return false;
		}

		$elementIndex = (int) $matches[1];
		$state = $this->elementInteractionState[$playerUuid][$formId][$elementIndex] ?? null;
		if($state === null){
			return false;
		}

		return $state['disabled'] || !$state['visible'];
	}

	private function trackElementInteractionPathUpdate(string $playerUuid, int $formId, string $path, mixed $value) : void{
		if(!preg_match('/^layout\[(\d+)]\.(visible|disabled)$/', $path, $matches)){
			return;
		}

		$elementIndex = (int) $matches[1];
		$property = $matches[2];
		$state = $this->elementInteractionState[$playerUuid][$formId][$elementIndex] ?? ['visible' => true, 'disabled' => false];
		$state[$property] = (bool) $value;
		$this->elementInteractionState[$playerUuid][$formId][$elementIndex] = $state;
	}
}
