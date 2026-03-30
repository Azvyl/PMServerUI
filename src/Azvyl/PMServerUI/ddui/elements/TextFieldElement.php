<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class TextFieldElement implements CustomFormElement{
	public function __construct(
		private Observable|string|UIRawMessage $label,
		private Observable $text,
		private Observable|string|UIRawMessage|null $description = null,
		private bool|Observable|null $disabled = null,
		private bool|Observable|null $visible = null,
	){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$description = $context->resolveText($this->description, $context->path($index, 'description'));
		$label = $context->resolveText($this->label, $context->path($index, 'label'));
		$text = $context->resolveText($this->text, $context->path($index, 'text'));
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		$disabled = $context->resolveBool($this->disabled, $context->path($index, 'disabled'), false);

		return [
			new DataStoreMapEntry('description', $context->toTextPropertyValue($description)),
			new DataStoreMapEntry('disabled', new BoolDataStorePropertyValue($disabled)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
			new DataStoreMapEntry('label', $context->toTextPropertyValue($label)),
			new DataStoreMapEntry('text', $context->toTextPropertyValue($text)),
			new DataStoreMapEntry('textfield_visible', new BoolDataStorePropertyValue(true)),
		];
	}
}

