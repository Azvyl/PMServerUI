<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\ddui\packets\types\Int64DataStorePropertyValue;
use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\UIRawMessage;

final class ButtonElement implements CustomFormElement{
	private \Closure $onClick;

	public function __construct(
		private readonly Observable|string|UIRawMessage $label,
		callable $onClick,
		private readonly bool|Observable|null $disabled = null,
		private readonly Observable|string|UIRawMessage|null $tooltip = null,
		private readonly bool|Observable|null $visible = null,
	){
		$this->onClick = $onClick(...);
	}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$label = $context->resolveText($this->label, $context->path($index, 'label'));
		$tooltip = $context->resolveText($this->tooltip, $context->path($index, 'tooltip'));
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		$disabled = $context->resolveBool($this->disabled, $context->path($index, 'disabled'), false);

		$context->registerClickHandler($context->path($index, 'onClick'), function() : void{
			try{
				($this->onClick)();
			}catch(\Throwable $t){
				PMServerUI::getLogger()->logException($t);
			}
		});

		return [
			new DataStoreMapEntry('button_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('disabled', new BoolDataStorePropertyValue($disabled)),
			new DataStoreMapEntry('label', $context->toTextPropertyValue($label)),
			new DataStoreMapEntry('onClick', new Int64DataStorePropertyValue(0)),
			new DataStoreMapEntry('tooltip', $context->toTextPropertyValue($tooltip)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
		];
	}
}
