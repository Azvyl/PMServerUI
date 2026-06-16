<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\ddui\elements\CustomFormElement;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreMapEntry;
use Azvyl\PMServerUI\ddui\packets\types\Int64DataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\MapDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\StringDataStorePropertyValue;
use Azvyl\PMServerUI\UIRawMessage;

final class CustomFormPayloadComposer{
	/**
	 * @param CustomFormElement[] $elements
	 * @phpstan-param list<CustomFormElement> $elements
	 */
	public function compose(
		DDUIManager $manager,
		string $playerUuid,
		int $formId,
		Observable|string|UIRawMessage $title,
		bool $closeButtonEnabled,
		array $elements,
	) : MapDataStorePropertyValue{
		$context = new CustomFormRenderContext($manager, $playerUuid, $formId);

		$closeButtonEntries = [
			new DataStoreMapEntry('button_visible', new BoolDataStorePropertyValue($closeButtonEnabled)),
			new DataStoreMapEntry('label', new StringDataStorePropertyValue('Close')),
			new DataStoreMapEntry('onClick', new Int64DataStorePropertyValue(0)),
		];

		$layoutEntries = [];
		foreach($elements as $index => $element){
			$elementEntries = $element->buildEntries($context, $index);
			$visible = true;
			$disabled = false;
			foreach($elementEntries as $entry){
				$key = $entry->getKey();
				$value = $entry->getValue();
				if(!$value instanceof BoolDataStorePropertyValue){
					continue;
				}
				if($key === 'visible'){
					$visible = $value->getValue();
				}elseif($key === 'disabled'){
					$disabled = $value->getValue();
				}
			}
			$manager->registerElementInteractionState($playerUuid, $formId, $index, $visible, $disabled);
			$layoutEntries[] = new DataStoreMapEntry((string)$index, new MapDataStorePropertyValue($elementEntries));
		}
		$layoutEntries[] = new DataStoreMapEntry('length', new Int64DataStorePropertyValue(count($elements)));

		$titleText = $context->resolveText($title, 'title');

		return new MapDataStorePropertyValue([
			new DataStoreMapEntry('closeButton', new MapDataStorePropertyValue($closeButtonEntries)),
			new DataStoreMapEntry('layout', new MapDataStorePropertyValue($layoutEntries)),
			new DataStoreMapEntry('title', $context->toTextPropertyValue($titleText)),
		]);
	}
}
