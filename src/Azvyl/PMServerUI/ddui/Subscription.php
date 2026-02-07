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

/** Represents a subscription returned from Observable::subscribe. */
final class Subscription{
	private bool $active = true;
	/** @var callable|null */
	private $onUnsubscribe;

	public function __construct(?callable $onUnsubscribe = null){
		$this->onUnsubscribe = $onUnsubscribe;
	}

	public function unsubscribe() : void{
		if(!$this->active){
			return;
		}
		$this->active = false;
		if(is_callable($this->onUnsubscribe)){
			($this->onUnsubscribe)();
		}
	}

	public function isActive() : bool{
		return $this->active;
	}
}
