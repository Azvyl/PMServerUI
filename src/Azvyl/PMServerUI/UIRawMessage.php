<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI;

use pocketmine\utils\Utils;
use function array_map;

/** A subset of the RawMessage type, and is used for UI messages. */
final class UIRawMessage{

	/**
	 * @param UIRawMessage[] $rawtext
	 * @param string[]|UIRawMessage $with
	 */
	public function __construct(public ?array $rawtext = null, public ?string $text = null, public ?string $translate = null, public null|array|UIRawMessage $with = null){
		if($this->rawtext !== null){
			Utils::validateArrayValueType($this->rawtext, fn(UIRawMessage $_) => null);
		}
		if(is_array($this->with)){
			Utils::validateArrayValueType($this->with, fn(string $_) => null);
		}
	}

	/** Encodes this value into the Bedrock RawMessage payload format for network packets. */
	public function encode() : array{
		$entries = [];

		if($this->translate !== null){
			$entry = ["translate" => $this->translate];

			if($this->with !== null){
				if($this->with instanceof UIRawMessage){
					$entry["with"] = $this->with->encode();
				}elseif(is_array($this->with)){
					$entry["with"] = ["rawtext" => array_map(function($v){
						if($v instanceof UIRawMessage){
							$enc = $v->encode();
							return $enc["rawtext"][0] ?? [];
						}
						return ["text" => (string)$v];
					}, $this->with)];
				}
			}

			$entries[] = $entry;
		}

		if($this->text !== null && $this->translate === null && $this->rawtext === null){
			$entries[] = ["text" => $this->text];
		}

		if($this->rawtext !== null){
			foreach($this->rawtext as $child){
				if(!($child instanceof UIRawMessage)) continue;
				$childEnc = $child->encode();
				$childEntries = $childEnc["rawtext"] ?? [];
				foreach($childEntries as $ce){
					$entries[] = $ce;
				}
			}
		}

		return ["rawtext" => $entries];
	}
}
