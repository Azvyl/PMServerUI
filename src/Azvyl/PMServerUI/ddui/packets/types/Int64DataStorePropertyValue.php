<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;

final class Int64DataStorePropertyValue extends DataStorePropertyValue{
	public const ID = DataStorePropertyType::INT64;

	public function __construct(
		private readonly int $value
	){}

	public function getValue() : int{ return $this->value; }

	public function getTypeId() : int{ return self::ID; }

	protected function writePayload(ByteBufferWriter $out) : void{
		LE::writeSignedLong($out, $this->value);
	}

	public static function read(ByteBufferReader $in) : self{
		return new self(LE::readSignedLong($in));
	}
}
