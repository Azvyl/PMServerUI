<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\PacketDecodeException;

abstract class DataStorePropertyValue{

	abstract public function getTypeId() : int;

	abstract protected function writePayload(ByteBufferWriter $out) : void;

	public function writeWithType(ByteBufferWriter $out) : void{
		LE::writeSignedInt($out, $this->getTypeId());
		$this->writePayload($out);
	}

	public static function read(ByteBufferReader $in) : self{
		$type = LE::readSignedInt($in);
		return match($type){
			DataStorePropertyType::NONE => NoneDataStorePropertyValue::read($in),
			DataStorePropertyType::BOOL => BoolDataStorePropertyValue::read($in),
			DataStorePropertyType::INT64 => Int64DataStorePropertyValue::read($in),
			DataStorePropertyType::STRING => StringDataStorePropertyValue::read($in),
			DataStorePropertyType::LIST => ListDataStorePropertyValue::read($in),
			DataStorePropertyType::MAP => MapDataStorePropertyValue::read($in),
			default => throw new PacketDecodeException("Unknown DataStorePropertyType"),
		};
	}
}
