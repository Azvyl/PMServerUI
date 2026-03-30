<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class HeaderElement implements CustomFormElement{
	public function __construct(
		private Observable|string|UIRawMessage $text,
		private bool|Observable|null $visible = null,
	){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$text = $context->resolveText($this->text, $context->path($index, 'text'));
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);

		return [
			new DataStoreMapEntry('header_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
			new DataStoreMapEntry('text', $context->toTextPropertyValue($text)),
		];
	}
}

