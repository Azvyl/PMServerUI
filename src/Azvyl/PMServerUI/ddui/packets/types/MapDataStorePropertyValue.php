<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;
use function count;

final class MapDataStorePropertyValue extends DataStorePropertyValue{
	public const ID = DataStorePropertyType::MAP;

	/**
	 * @param DataStoreMapEntry[] $entries
	 * @phpstan-param list<DataStoreMapEntry> $entries
	 */
	public function __construct(
		private readonly array $entries,
	){}

	/**
	 * @return DataStoreMapEntry[]
	 * @phpstan-return list<DataStoreMapEntry>
	 */
	public function getEntries() : array{ return $this->entries; }

	public function getTypeId() : int{ return self::ID; }

	protected function writePayload(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, count($this->entries));
		foreach($this->entries as $entry){
			$entry->write($out);
		}
	}

	public static function read(ByteBufferReader $in) : self{
		$entries = [];
		for($i = 0, $len = VarInt::readUnsignedInt($in); $i < $len; ++$i){
			$entries[] = DataStoreMapEntry::read($in);
		}
		return new self($entries);
	}
}
