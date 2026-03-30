<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\ddui\packets\types\Int64DataStorePropertyValue;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class SliderElement implements CustomFormElement{
	public function __construct(
		private Observable|string|UIRawMessage $label,
		private Observable $value,
		private int|float|Observable $min,
		private int|float|Observable $max,
		private Observable|string|UIRawMessage|null $description = null,
		private bool|Observable|null $disabled = null,
		private int|float|Observable|null $step = null,
		private bool|Observable|null $visible = null,
	){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$description = $context->resolveText($this->description, $context->path($index, 'description'));
		$label = $context->resolveText($this->label, $context->path($index, 'label'));
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		$disabled = $context->resolveBool($this->disabled, $context->path($index, 'disabled'), false);
		$min = $context->resolveInt($this->min, $context->path($index, 'minValue'), 0);
		$max = $context->resolveInt($this->max, $context->path($index, 'maxValue'), 100);
		$step = $context->resolveInt($this->step, $context->path($index, 'step'), 1);
		$value = $context->resolveInt($this->value, $context->path($index, 'value'));

		return [
			new DataStoreMapEntry('description', $context->toTextPropertyValue($description)),
			new DataStoreMapEntry('slider_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('disabled', new BoolDataStorePropertyValue($disabled)),
			new DataStoreMapEntry('label', $context->toTextPropertyValue($label)),
			new DataStoreMapEntry('maxValue', new Int64DataStorePropertyValue($max)),
			new DataStoreMapEntry('minValue', new Int64DataStorePropertyValue($min)),
			new DataStoreMapEntry('step', new Int64DataStorePropertyValue($step)),
			new DataStoreMapEntry('value', new Int64DataStorePropertyValue($value)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
		];
	}
}

