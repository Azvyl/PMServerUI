<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class ToggleElement implements CustomFormElement{
	public function __construct(
		private Observable|string|UIRawMessage $label,
		private Observable $toggled,
		private Observable|string|UIRawMessage|null $description = null,
		private bool|Observable|null $disabled = null,
		private bool|Observable|null $visible = null,
	){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$description = $context->resolveText($this->description, $context->path($index, 'description'));
		$label = $context->resolveText($this->label, $context->path($index, 'label'));
		$toggled = $context->resolveBool($this->toggled, $context->path($index, 'toggled'), false);
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		$disabled = $context->resolveBool($this->disabled, $context->path($index, 'disabled'), false);

		return [
			new DataStoreMapEntry('description', $context->toTextPropertyValue($description)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
			new DataStoreMapEntry('disabled', new BoolDataStorePropertyValue($disabled)),
			new DataStoreMapEntry('label', $context->toTextPropertyValue($label)),
			new DataStoreMapEntry('toggle_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('toggled', new BoolDataStorePropertyValue($toggled)),
		];
	}
}

