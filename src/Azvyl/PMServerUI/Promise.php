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

class Promise{
	private mixed $result = null;
	private ?\Throwable $error = null;
	private bool $isResolved = false;
	private bool $isRejected = false;

	private array $onFulfilledCallbacks = [];
	private array $onRejectedCallbacks = [];

	public function __construct(?callable $executor = null){
		if($executor !== null){
			$this->run($executor);
		}
	}

	public function run(callable $executor): void{
		try{
			$executor(fn(mixed ...$value) => $this->resolve(...$value), fn(\Throwable $t) => $this->reject($t));
		}catch(\Throwable $e){
			$this->reject($e);
		}
	}

	public function resolve(mixed ...$value): void{
		if($this->isResolved || $this->isRejected) return;

		$this->isResolved = true;
		$this->result = $value;

		foreach($this->onFulfilledCallbacks as $cb){
			$cb(...$value);
		}

		$this->onFulfilledCallbacks = [];
	}

	public function reject(\Throwable $reason): void{
		if($this->isResolved || $this->isRejected) return;

		$this->isRejected = true;
		$this->error = $reason;

		foreach($this->onRejectedCallbacks as $cb){
			$cb($reason);
		}

		$this->onRejectedCallbacks = [];
	}

	public function then(?callable $onFulfilled = null, ?callable $onRejected = null): Promise{
		return new Promise(function($resolve, $reject) use ($onFulfilled, $onRejected){
			$handleFulfilled = function(...$value) use ($onFulfilled, $resolve, $reject){
				if($onFulfilled){
					try{
						$result = $onFulfilled(...$value);
						$resolve($result);
					}catch(\Throwable $e){
						$reject($e);
					}
				}else{
					$resolve($value);
				}
			};

			$handleRejected = function($reason) use ($onRejected, $resolve, $reject){
				if($onRejected){
					try{
						$result = $onRejected($reason);
						$resolve($result);
					}catch(\Throwable $e){
						$reject($e);
					}
				}else{
					$reject($reason);
				}
			};

			if($this->isResolved){
				$handleFulfilled($this->result);
			}elseif($this->isRejected){
				$handleRejected($this->error);
			}else{
				$this->onFulfilledCallbacks[] = $handleFulfilled;
				$this->onRejectedCallbacks[] = $handleRejected;
			}
		});
	}

	public function catch(?callable $onRejected = null): Promise{
		return $this->then(null, $onRejected);
	}
}