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

/**
 * A class that represents data that can be Observed.
 *
 * @template T of string|int|float|bool
 */
final class Observable{
	/** @var array<int, callable> */
	private array $listeners = [];

	/**
	 * @param T    $data
	 * @param bool $clientWritable
	 */
	private function __construct(private bool|float|int|string $data, private readonly bool $clientWritable = false){ }

	/**
	 * Create an observable.
	 *
	 * @param T    $data
	 * @param bool $clientWritable
	 *
	 * @return Observable<T>
	 */
	public static function create(bool|float|int|string $data, bool $clientWritable = false) : self{
		return new self($data, $clientWritable);
	}

	/**
	 * Get the data.
	 * @return T
	 */
	public function getData() : bool|float|int|string{
		return $this->data;
	}

	/**
	 * Set the data and notify subscribers.
	 *
	 * @param T $data
	 */
	public function setData(bool|float|int|string $data) : void{
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
		$id = count($this->listeners) + 1;
		$this->listeners[$id] = $listener;
		return new Subscription(function() use ($id) : void{
			unset($this->listeners[$id]);
		});
	}

	public function isClientWritable() : bool{
		return $this->clientWritable;
	}
}
