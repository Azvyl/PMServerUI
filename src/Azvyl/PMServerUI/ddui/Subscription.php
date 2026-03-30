<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\PMServerUI;

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
