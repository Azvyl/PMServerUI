<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\ddui\packets\types\DataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\Int64DataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\ListDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\MapDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\NoneDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\StringDataStorePropertyValue;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class CustomFormRenderContext{
	public function __construct(
		private DDUIManager $manager,
		private string $playerUuid,
		private int $formId,
	){}

	public function entry(string $key, DataStorePropertyValue $value) : DataStoreMapEntry{
		return new DataStoreMapEntry($key, $value);
	}

	public function path(int $index, string $property) : string{
		return "layout[$index].$property";
	}

	public function registerBinding(string $path, Observable $observable) : void{
		$this->manager->registerObservableBinding($this->playerUuid, $this->formId, $path, $observable);
	}

	public function registerClickHandler(string $path, callable $handler) : void{
		$this->manager->registerClickHandler($this->playerUuid, $this->formId, $path, $handler);
	}

	public function resolveBool(bool|Observable|null $value, string $path, bool $default = false) : bool{
		if($value instanceof Observable){
			$this->registerBinding($path, $value);
			return (bool)$value->getData();
		}
		if($value === null){
			return $default;
		}
		return (bool)$value;
	}

	public function resolveInt(int|float|Observable|null $value, string $path, int $default = 0) : int{
		if($value instanceof Observable){
			$this->registerBinding($path, $value);
			return (int)$value->getData();
		}
		if($value === null){
			return $default;
		}
		return (int)$value;
	}

	public function resolveText(Observable|string|UIRawMessage|null $value, string $path, string|UIRawMessage $default = '') : string|UIRawMessage{
		if($value instanceof Observable){
			$this->registerBinding($path, $value);
			$value = $value->getData();
		}

		if($value instanceof UIRawMessage){
			return $value;
		}

		if($value === null){
			return $default;
		}

		return (string)$value;
	}

	public function toTextPropertyValue(mixed $value) : DataStorePropertyValue{
		if($value instanceof UIRawMessage){
			return $this->toPropertyValue($value->encode());
		}
		return new StringDataStorePropertyValue((string)($value ?? ''));
	}

	public function toPropertyValue(mixed $value) : DataStorePropertyValue{
		if($value === null){
			return new NoneDataStorePropertyValue();
		}

		if(is_array($value)){
			$isList = array_keys($value) === range(0, count($value) - 1);
			if($isList){
				$entries = [];
				foreach($value as $item){
					$entries[] = $this->toPropertyValue($item);
				}
				return new ListDataStorePropertyValue($entries);
			}

			$mapEntries = [];
			foreach($value as $key => $entryValue){
				if($key === 'with' && is_array($entryValue) && array_keys($entryValue) === range(0, count($entryValue) - 1)){
					$rawTextEntries = [];
					foreach($entryValue as $item){
						$rawTextEntries[] = $this->toPropertyValue($item);
					}
					$mapEntries[] = new DataStoreMapEntry('with', new MapDataStorePropertyValue([
						new DataStoreMapEntry('rawtext', new ListDataStorePropertyValue($rawTextEntries)),
					]));
					continue;
				}

				$mapEntries[] = new DataStoreMapEntry((string)$key, $this->toPropertyValue($entryValue));
			}

			return new MapDataStorePropertyValue($mapEntries);
		}

		if(is_string($value)){
			return new StringDataStorePropertyValue($value);
		}
		if(is_int($value) || is_float($value)){
			return new Int64DataStorePropertyValue((int)$value);
		}
		if(is_bool($value)){
			return new BoolDataStorePropertyValue($value);
		}

		return new NoneDataStorePropertyValue();
	}
}
