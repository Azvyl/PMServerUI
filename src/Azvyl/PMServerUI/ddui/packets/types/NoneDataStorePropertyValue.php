<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;

final class NoneDataStorePropertyValue extends DataStorePropertyValue{
	public const ID = DataStorePropertyType::NONE;

	public function getTypeId() : int{ return self::ID; }

	protected function writePayload(ByteBufferWriter $out) : void{}

	public static function read(ByteBufferReader $in) : self{
		return new self();
	}
}
