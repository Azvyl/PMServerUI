<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets;

use Azvyl\PMServerUI\ddui\packets\types\DataStoreChange;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\types\DataStore;
use pocketmine\network\mcpe\protocol\types\DataStoreRemoval;
use pocketmine\network\mcpe\protocol\types\DataStoreType;
use pocketmine\network\mcpe\protocol\types\DataStoreUpdate;

final class ClientboundDataStorePacket extends \pocketmine\network\mcpe\protocol\ClientboundDataStorePacket implements ClientboundPacket{
	/**
	 * @param DataStore[] $values
	 * @phpstan-param list<DataStore> $values
	 */
	public static function create(array $values) : self{
		$result = new self;
		$result->values = $values;
		return $result;
	}

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->values = [];
		for($i = 0, $len = VarInt::readUnsignedInt($in); $i < $len; ++$i){
			$this->values[] = match(VarInt::readUnsignedInt($in)){
				DataStoreType::UPDATE => DataStoreUpdate::read($in),
				DataStoreType::CHANGE => DataStoreChange::read($in),
				DataStoreType::REMOVAL => DataStoreRemoval::read($in),
				default => throw new PacketDecodeException("Unknown DataStore type"),
			};
		}
	}
}