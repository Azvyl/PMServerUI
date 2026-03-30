<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\elements;

use Azvyl\PMServerUI\ddui\CustomFormRenderContext;
use Azvyl\PMServerUI\ddui\DropdownItem;
use Azvyl\PMServerUI\ddui\Observable;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\ddui\packets\types\Int64DataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\MapDataStorePropertyValue;
use Azvyl\PMServerUI\UIRawMessage;

final readonly class DropdownElement implements CustomFormElement{
	/**
	 * @param DropdownItem[] $items
	 * @phpstan-param list<DropdownItem> $items
	 */
	public function __construct(
		private Observable|string|UIRawMessage $label,
		private Observable $value,
		private array $items,
		private Observable|string|UIRawMessage|null $description = null,
		private bool|Observable|null $disabled = null,
		private bool|Observable|null $visible = null,
	){}

	public function buildEntries(CustomFormRenderContext $context, int $index) : array{
		$description = $context->resolveText($this->description, $context->path($index, 'description'));
		$label = $context->resolveText($this->label, $context->path($index, 'label'));
		$visible = $context->resolveBool($this->visible, $context->path($index, 'visible'), true);
		$disabled = $context->resolveBool($this->disabled, $context->path($index, 'disabled'), false);
		$value = $context->resolveInt($this->value, $context->path($index, 'value'));

		$itemEntries = [];
		foreach($this->items as $itemIndex => $item){
			$itemMap = [
				new DataStoreMapEntry('label', $context->toTextPropertyValue($item->label)),
				new DataStoreMapEntry('value', new Int64DataStorePropertyValue((int)$item->value)),
			];
			$itemEntries[] = new DataStoreMapEntry((string)$itemIndex, new MapDataStorePropertyValue($itemMap));
		}
		$itemEntries[] = new DataStoreMapEntry('length', new Int64DataStorePropertyValue(count($this->items)));

		return [
			new DataStoreMapEntry('description', $context->toTextPropertyValue($description)),
			new DataStoreMapEntry('disabled', new BoolDataStorePropertyValue($disabled)),
			new DataStoreMapEntry('dropdown_visible', new BoolDataStorePropertyValue(true)),
			new DataStoreMapEntry('visible', new BoolDataStorePropertyValue($visible)),
			new DataStoreMapEntry('label', $context->toTextPropertyValue($label)),
			new DataStoreMapEntry('items', new MapDataStorePropertyValue($itemEntries)),
			new DataStoreMapEntry('value', new Int64DataStorePropertyValue($value)),
		];
	}
}

