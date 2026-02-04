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

use pocketmine\form\FormValidationException;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use function is_array;
use function json_encode;

final class FormDataHandler implements \JsonSerializable{

	public function __construct(private array $data, private readonly string $responseClass, private mixed &$sharedInfo = null){}

	public function add(string $key, mixed $value): void{
		$this->data[$key] = $value;
	}

	public function arrAdd(string $key, mixed $value): void{
		if(!isset($this->data[$key]) || !is_array($this->data[$key])){
			$this->data[$key] = [];
		}
		$this->data[$key][] = $value;
	}

	public static function tryTl(Translatable|string $value): array|string{
		return PMServerUI::$tl && $value instanceof Translatable ? ["rawtext" => [self::getTl($value)]] : (string)$value;
	}

	private static function getTl(Translatable $translatable): array{
		$params = [];
		foreach($translatable->getParameters() as $param){
			$params[] = $param instanceof Translatable ? self::getTl($param) : ["text" => $param];
		}
		$ret = ["translate" => $translatable->getText()];
		if($params !== []){
			$ret["with"] = ["rawtext" => $params];
		}
		return $ret;
	}

	public function show(Player $player): Promise{
		$promise = new Promise();
		(function(FormDataHandler $form, Promise $promise): void{
			/** @noinspection PhpUndefinedFieldInspection */
			$id = $this->formIdCounter++;
			/** @noinspection PhpUndefinedMethodInspection */
			if($this->getNetworkSession()->sendDataPacket(ModalFormRequestPacket::create($id, json_encode($form, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)))){
				/** @noinspection PhpParamsInspection */
				PMServerUI::getUIManager()->trackForm($this, $id, $form, $promise);
			}
		})->call($player, $this, $promise);
		return $promise;
	}

	public function handleCancel(Player $player, int $cancelReason, Promise $promise): void{
		$reason = FormCancelationReason::cases()[$cancelReason] ?? null;
		if($reason !== null){
			$promise->resolve($player, (new \ReflectionClass($this->responseClass))->newInstance($reason, true, null));
		}else{
			throw new FormValidationException("Player {$player->getName()} sent unknown cancel reason");
		}
	}

	public function handleResponse(Player $player, ?string $rawData, Promise $promise): void{
		if($rawData === null || $rawData === "null" || $rawData === ""){
			throw new FormValidationException("Form response can't be null without cancel reason");
		}
		$promise->resolve($player, (new \ReflectionClass($this->responseClass))->newInstance(null, false, [$this->responseClass, "validate"]($rawData, $this->sharedInfo)));
	}

	public function informCrash(\Throwable $t, Promise $promise): void{
		if($t instanceof FormValidationException){
			$promise->reject(FormRejectError::create(FormRejectReason::MalformedResponse, "Failed to process form: {$t->getMessage()}", previous: $t));
			return;
		}
		$promise->reject(FormRejectError::create(FormRejectReason::ServerShutdown, "Crashed when handling packet: {$t->getMessage()}", previous: $t));
	}

	public function informQuit(Player $player, Promise $promise): void{
		$promise->reject(FormRejectError::create(FormRejectReason::PlayerQuit, "Player {$player->getName()} quit before responding"));
	}

	public function jsonSerialize(): array{
		return $this->data;
	}
}