<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\UIRawMessage;

/**
 * A class that represents data that can be Observed.
 *
 * @template T of string|int|float|bool|UIRawMessage
 */
final class Observable{
	/** @var array<int, callable> */
	private array $listeners = [];

	private int $nextListenerId = 1;

	/**
	 * @param T $data
	 * @param bool $clientWritable
	 */
	private function __construct(private bool|float|int|string|UIRawMessage $data, private readonly bool $clientWritable = false){}

	/**
	 * Create an observable.
	 *
	 * @param T $data
	 * @param bool $clientWritable
	 *
	 * @return Observable<T>
	 */
	public static function create(bool|float|int|string|UIRawMessage $data, bool $clientWritable = false) : self{
		return new self($data, $clientWritable);
	}

	/**
	 * Get the data.
	 *
	 * @return T
	 */
	public function getData() : bool|float|int|string|UIRawMessage{
		return $this->data;
	}

	// TODO: getFilteredText()

	/**
	 * Set the data and notify subscribers.
	 *
	 * @param T $data
	 */
	public function setData(bool|float|int|string|UIRawMessage $data) : void{
		if($data === $this->data){
			return;
		}
		$this->data = $data;
		foreach($this->listeners as $listener){
			try{
				$listener($data);
			}catch(\Throwable $t){
				PMServerUI::getLogger()->logException($t);
			}
		}

		try{
			PMServerUI::getDDUIManager()->notifyObservableChanged($this);
		}catch(\Throwable $e){
			PMServerUI::getLogger()->logException($e);
		}
	}

	/**
	 * @internal
	 * Apply a value received from the client. This updates the observable and notifies listeners
	 * but DOES NOT echo the value back to the client (to avoid feedback loops).
	 *
	 * @param T $data
	 */
	public function applyClientUpdate(bool|float|int|string|UIRawMessage $data) : void{
		$this->data = $data;
		foreach($this->listeners as $listener){
			$listener($data);
		}
	}

	/**
	 * Subscribe to changes. Returns a Subscription which can be used to unsubscribe.
	 *
	 * @param callable(T): void $listener
	 *
	 * @return Subscription
	 */
	public function subscribe(callable $listener) : Subscription{
		$id = $this->nextListenerId++;
		$this->listeners[$id] = $listener;
		return new Subscription(function() use ($id) : void{
			unset($this->listeners[$id]);
		});
	}

	public function isClientWritable() : bool{
		return $this->clientWritable;
	}
}
