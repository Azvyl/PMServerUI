<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class StringDataStorePropertyValue extends DataStorePropertyValue{
	public const ID = DataStorePropertyType::STRING;

	public function __construct(
		private readonly string $value
	){}

	public function getValue() : string{ return $this->value; }

	public function getTypeId() : int{ return self::ID; }

	protected function writePayload(ByteBufferWriter $out) : void{
		CommonTypes::putString($out, $this->value);
	}

	public static function read(ByteBufferReader $in) : self{
		return new self(CommonTypes::getString($in));
	}
}

