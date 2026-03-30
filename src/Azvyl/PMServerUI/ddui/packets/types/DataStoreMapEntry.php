<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final readonly class DataStoreMapEntry{
	public function __construct(
		private string $key,
		private DataStorePropertyValue $value,
	){}

	public function getKey() : string{ return $this->key; }

	public function getValue() : DataStorePropertyValue{ return $this->value; }

	public static function read(ByteBufferReader $in) : self{
		$key = CommonTypes::getString($in);
		$value = DataStorePropertyValue::read($in);
		return new self($key, $value);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::putString($out, $this->key);
		$this->value->writeWithType($out);
	}
}

