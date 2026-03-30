<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;

interface CustomFormElement{
	/**
	 * @return DataStoreMapEntry[]
	 * @phpstan-return list<DataStoreMapEntry>
	 */
	public function buildEntries(CustomFormRenderContext $context, int $index) : array;
}

