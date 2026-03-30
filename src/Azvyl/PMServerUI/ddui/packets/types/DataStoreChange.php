<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\DataStore;
use pocketmine\network\mcpe\protocol\types\DataStoreType;

/** Represents a change to a data store property value. */
final class DataStoreChange extends DataStore{

	public const ID = DataStoreType::CHANGE;

	public function __construct(
		private readonly string $name,
		private readonly string $property,
		private readonly int $updateCount,
		private readonly DataStorePropertyValue $newValue
	){}

	public function getTypeId() : int{ return self::ID; }

	public function getName() : string{ return $this->name; }

	public function getProperty() : string{ return $this->property; }

	public function getUpdateCount() : int{ return $this->updateCount; }

	public function getNewValue() : DataStorePropertyValue{ return $this->newValue; }

	public static function read(ByteBufferReader $in) : self{
		$name = CommonTypes::getString($in);
		$property = CommonTypes::getString($in);
		$updateCount = LE::readUnsignedInt($in);

		$data = DataStorePropertyValue::read($in);

		return new self(
			$name,
			$property,
			$updateCount,
			$data,
		);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::putString($out, $this->name);
		CommonTypes::putString($out, $this->property);
		LE::writeUnsignedInt($out, $this->updateCount);
		$this->newValue->writeWithType($out);
	}
}