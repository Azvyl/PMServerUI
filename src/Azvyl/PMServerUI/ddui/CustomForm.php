<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

use Azvyl\PMServerUI\ddui\elements\ButtonElement;
use Azvyl\PMServerUI\ddui\elements\CustomFormElement;
use Azvyl\PMServerUI\ddui\elements\DividerElement;
use Azvyl\PMServerUI\ddui\elements\DropdownElement;
use Azvyl\PMServerUI\ddui\elements\HeaderElement;
use Azvyl\PMServerUI\ddui\elements\LabelElement;
use Azvyl\PMServerUI\ddui\elements\SliderElement;
use Azvyl\PMServerUI\ddui\elements\SpacerElement;
use Azvyl\PMServerUI\ddui\elements\TextFieldElement;
use Azvyl\PMServerUI\ddui\elements\ToggleElement;
use Azvyl\PMServerUI\ddui\packets\ClientboundDataStorePacket;
use Azvyl\PMServerUI\ddui\packets\types\BoolDataStorePropertyValue;
use Azvyl\PMServerUI\ddui\packets\types\DataStoreChange as ClientboundDataStoreChange;
use Azvyl\PMServerUI\PMServerUI;
use Azvyl\PMServerUI\Promise;
use Azvyl\PMServerUI\UIRawMessage;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUICloseScreenPacket;
use pocketmine\network\mcpe\protocol\ClientboundDataDrivenUIShowScreenPacket;
use pocketmine\player\Player;

/** Builder for Data-Driven UI (CustomForm) shown to a single player. */
final class CustomForm extends DDUI{
	/** @var \WeakReference<Player> */
	private \WeakReference $playerRef;
	/** @var Observable<string>|Observable<UIRawMessage>|string|UIRawMessage */
	private mixed $title;
	/** @var int|null */
	private ?int $formId = null;
	/** @var array<int, CustomFormElement> */
	private array $elements = [];
	private bool $showing = false;
	private bool $closeButtonEnabled = false;
	private CustomFormPayloadComposer $payloadComposer;

	/**
	 * Create a CustomForm for a specific player.
	 *
	 * @param Player $player The player to show the form to.
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $title The title of the form.
	 */
	public static function create(Player $player, Observable|string|UIRawMessage $title) : self{
		$instance = new self();
		$instance->playerRef = \WeakReference::create($player);
		$instance->title = $title;
		$instance->elements = [];
		$instance->showing = false;
		$instance->closeButtonEnabled = false;
		$instance->payloadComposer = new CustomFormPayloadComposer();
		return $instance;
	}

	/**
	 * Inserts a button into the Custom form. onClick is called when the button is pressed.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label The text to display on the button.
	 * @param callable() : void $onClick The function to call when the button is clicked.
	 * @param bool|Observable<bool> $disabled
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $tooltip The tooltip to display when hovering over the button.
	 * @param bool|Observable<bool> $visible
	 */
	public function button(Observable|string|UIRawMessage $label, callable $onClick, bool|Observable $disabled = null, Observable|string|UIRawMessage $tooltip = null, bool|Observable $visible = null) : self{
		$this->elements[] = new ButtonElement($label, $onClick, $disabled, $tooltip, $visible);
		return $this;
	}

	/**
	 * Close the form programmatically.
	 *
	 * Sends a close packet to the client. This method expects the form to be currently shown; it will throw if not.
	 */
	public function close() : void{
		if(!$this->showing){
			throw new \RuntimeException("Form is not open");
		}
		$player = $this->playerRef->get();
		if($player === null){
			throw new \RuntimeException("Player reference is gone");
		}
		$fid = $this->formId;
		$player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUICloseScreenPacket::create($fid));
		$this->showing = false;
	}

	/** Enable the standard close (X) control shown by the client. */
	public function closeButton() : self{
		$this->closeButtonEnabled = true;
		return $this;
	}

	/**
	 * Inserts a divider (i.e. a line) into the Custom form.
	 *
	 * @param bool|Observable<bool> $visible Whether the divider is visible.
	 */
	public function divider(bool|Observable $visible = null) : self{
		$this->elements[] = new DividerElement($visible);
		return $this;
	}

	/**
	 * Inserts a dropdown into the Custom form with the provided items. The value is based on the items value that
	 * selected.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label The text to display above the dropdown.
	 * @param Observable<int>|Observable<float> $value The currently selected index in the dropdown.
	 * @param DropdownItem[] $items An array of DropdownItem to show in the dropdown.
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $description
	 * @param bool|Observable<bool> $disabled
	 * @param bool|Observable<bool> $visible
	 */
	public function dropdown(Observable|string|UIRawMessage $label, Observable $value, array $items, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{ // TODO: verify if $description can be Observable<UIRawMessage>
		$this->elements[] = new DropdownElement($label, $value, $items, $description, $disabled, $visible);
		return $this;
	}

	/**
	 * Inserts a label (i.e. medium-sized text) into the Custom form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $text The text to display in the label.
	 * @param bool|Observable<bool> $visible
	 */
	public function label(Observable|string|UIRawMessage $text, bool|Observable $visible = null) : self{
		$this->elements[] = new LabelElement($text, $visible);
		return $this;
	}

	/**
	 * Inserts a header (i.e. large-sized text) into the Custom form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $text The text to display in the header.
	 * @param bool|Observable<bool> $visible Whether the header is visible.
	 */
	public function header(Observable|string|UIRawMessage $text, bool|Observable $visible = null) : self{
		$this->elements[] = new HeaderElement($text, $visible);
		return $this;
	}

	/** Returns true if this CustomForm has been shown and not yet acknowledged/closed. */
	public function isShowing() : bool{
		return $this->showing;
	}

	/**
	 * Shows the form to the player. Will return false if the client was busy (i.e. in another menu or this one is open).
	 * Will throw if the user disconnects.
	 *
	 * @return Promise<bool>
	 */
	public function show() : Promise{ // TODO: return Promise<DataDrivenScreenClosedReason>
		/** @var Promise<bool> $promise */
		$promise = new Promise(); // TODO: reject instead off throwing exception
		if($this->showing){
			throw new \RuntimeException("Form is already open");
		}
		$player = $this->playerRef->get();
		if($player === null){
			throw new \RuntimeException("Player reference is gone");
		}
		$manager = PMServerUI::getDDUIManager();
		$playerUuid = $player->getUniqueId()->getBytes();
		if($manager->hasActiveForm($playerUuid)){
			throw new \RuntimeException("Another DDUI form is already active for this player");
		}
		$formId = $manager->nextFormId();

		$this->formId = $formId;
		$player->getNetworkSession()->sendDataPacket(ClientboundDataDrivenUIShowScreenPacket::create("minecraft:custom_form", $formId, null));

		$mapValue = $this->payloadComposer->compose($manager, $playerUuid, $formId, $this->title, $this->closeButtonEnabled, $this->elements);
		$updateCount = $manager->nextUpdateCountFor($playerUuid);

		$player->getNetworkSession()->sendDataPacket(ClientboundDataStorePacket::create([
			new ClientboundDataStoreChange('minecraft', 'custom_form_data', $updateCount, $mapValue),
			new ClientboundDataStoreChange('minecraft', 'ddui_form_active', $updateCount, new BoolDataStorePropertyValue(true))
		]));

		$this->showing = true;

		$manager->registerFormPromise($playerUuid, $formId, $promise);

		return $promise;
	}

	/**
	 * Creates a slider that lets players pick a number between minValue and maxValue.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label The text to display above the slider.
	 * @param Observable<int>|Observable<float> $value The current value observable (client-writable).
	 * @param Observable<int>|Observable<float>|int|float $minValue The minimum selectable value.
	 * @param Observable<int>|Observable<float>|int|float $maxValue The maximum selectable value.
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null $disabled
	 * @param Observable<int>|Observable<float>|int|float|null $step The step size (increment) for the slider.
	 * @param bool|Observable<bool>|null $visible
	 */
	public function slider(Observable|string|UIRawMessage $label, Observable $value, int|float|Observable $minValue, int|float|Observable $maxValue, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, int|float|Observable $step = null, bool|Observable $visible = null) : self{
		$this->elements[] = new SliderElement($label, $value, $minValue, $maxValue, $description, $disabled, $step, $visible);
		return $this;
	}

	/**
	 * Inserts a space into the Custom form.
	 *
	 * @param bool|Observable<bool>|null $visible Whether the spacer is visible.
	 */
	public function spacer(bool|Observable $visible = null) : self{
		$this->elements[] = new SpacerElement($visible);
		return $this;
	}

	/**
	 * Inserts a text field into the Custom for that players can enter text into.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label The label shown above the text field.
	 * @param Observable<string> $text The text observable bound to the field (client-writable).
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null $disabled
	 * @param bool|Observable<bool>|null $visible
	 */
	public function textField(Observable|string|UIRawMessage $label, Observable $text, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{
		$this->elements[] = new TextFieldElement($label, $text, $description, $disabled, $visible);
		return $this;
	}

	/**
	 * Inserts an on/off toggle that players can interact with into the Custom form.
	 *
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage $label The label shown beside the toggle.
	 * @param Observable<bool> $toggled The boolean observable bound to the toggle (client-writable).
	 * @param Observable<string>|Observable<UIRawMessage>|string|UIRawMessage|null $description Optional description shown in the UI.
	 * @param bool|Observable<bool>|null $disabled
	 * @param bool|Observable<bool>|null $visible
	 */
	public function toggle(Observable|string|UIRawMessage $label, Observable $toggled, Observable|string|UIRawMessage $description = null, bool|Observable $disabled = null, bool|Observable $visible = null) : self{
		$this->elements[] = new ToggleElement($label, $toggled, $description, $disabled, $visible);
		return $this;
	}
}
