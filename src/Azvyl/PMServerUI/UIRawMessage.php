<?php

/*
 * PMServerUI
 * https://github.com/Azvyl/PMServerUI
 *
 * Copyright (c) 2026 Azvyl
 *
 * Licensed under the MIT License.
 * See LICENSE file in the project root for details.
 */

declare(strict_types=1);

namespace Azvyl\PMServerUI;

use pocketmine\utils\Utils;
use function array_map;

/** A subset of the RawMessage type, and is used for UI messages. */
final class UIRawMessage{

	/**
	 * @param UIRawMessage[]        $rawtext
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

	public function encode() : array{
		$entries = [];

		if($this->translate !== null){
			$entry = ["translate" => $this->translate];

			if($this->with !== null){
				if($this->with instanceof UIRawMessage){
					$encoded = $this->with->encode();
					$entry["with"] = $encoded["rawtext"] ?? [];
				}elseif(is_array($this->with)){
					$entry["with"] = array_map(fn(string $s) => ["text" => $s], $this->with);
				}
			}

			$entries[] = $entry;
		}

		if($this->text !== null && $this->translate === null && $this->rawtext === null){
			$entries[] = ["text" => $this->text];
		}

		if($this->rawtext !== null){
			// Todo: Verify if this is correct
			$entries[] = ["rawtext" => array_map(fn(UIRawMessage $child) => $child->encode(), $this->rawtext)];
		}

		return ["rawtext" => $entries];
	}
}
