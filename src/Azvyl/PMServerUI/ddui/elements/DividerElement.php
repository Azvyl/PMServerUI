<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;

final readonly class DividerElement implements CustomFormElement{
	public function __construct(private bool|Observable|null $visible = null){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		return [
			new DataStoreMapEntry('divider_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
		];
	}
}
