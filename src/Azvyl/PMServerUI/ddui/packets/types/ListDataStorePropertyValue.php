<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;

final class ListDataStorePropertyValue extends DataStorePropertyValue{
	public const ID = DataStorePropertyType::LIST;

	/**
	 * @param DataStorePropertyValue[] $entries
	 * @phpstan-param list<DataStorePropertyValue> $entries
	 */
	public function __construct(
		private readonly array $entries,
	){}

	/**
	 * @return DataStorePropertyValue[]
	 * @phpstan-return list<DataStorePropertyValue>
	 */
	public function getEntries() : array{ return $this->entries; }

	public function getTypeId() : int{ return self::ID; }

	protected function writePayload(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, count($this->entries));
		foreach($this->entries as $entry){
			$entry->writeWithType($out);
		}
	}

	public static function read(ByteBufferReader $in) : self{
		$entries = [];
		for($i = 0, $len = VarInt::readUnsignedInt($in); $i < $len; ++$i){
			$entries[] = DataStorePropertyValue::read($in);
		}
		return new self($entries);
	}
}
