<?php

declare(strict_types=1);

namespace NDataGrid\Column;

/**
 * Base column definition with label, icon, CSS class, and rendering options.
 */
class Column
{
	public string $operator = 'LIKE ?';

	/** @var array{0: ?string, 1: ?string} */
	public array $pattern = [null, '%'];

	private string $type = 'text';
	private string $label = '';
	private string $icon = '';
	private string $class = '';
	private string $prefix = '';
	private string $suffix = '';
	private string $unknown = '';

	/** @var array<string|int, string> */
	private array $selection = [];

	/** @var list<callable> */
	public array $onClick = [];


	public function __construct(
		public readonly string $name,
	) {}


	// — Type —

	public function getType(): string
	{
		return $this->type;
	}


	public function setType(string $type): static
	{
		$this->type = $type;

		// Set default operator/pattern per type
		match ($type) {
			'int', 'date', 'select', 'stav' => $this->setOperatorPattern('= ?', null, null),
			default => $this->setOperatorPattern('LIKE ?', null, '%'),
		};

		return $this;
	}


	private function setOperatorPattern(string $operator, ?string $left, ?string $right): void
	{
		$this->operator = $operator;
		$this->pattern = [$left, $right];
	}


	// — Label / Icon / Class —

	public function getLabel(): string
	{
		return $this->label;
	}


	public function setLabel(string $label): static
	{
		$this->label = $label;
		return $this;
	}


	public function getIcon(): string
	{
		return $this->icon;
	}


	public function setIcon(string $icon): static
	{
		$this->icon = $icon;
		return $this;
	}


	public function getClass(): string
	{
		return $this->class;
	}


	public function setClass(string $class): static
	{
		$this->class = $class;
		return $this;
	}


	public function addClass(string $class): static
	{
		$this->class .= ' ' . $class;
		return $this;
	}


	// — Prefix / Suffix / Unknown —

	public function getPrefix(): string
	{
		return $this->prefix !== '' ? $this->prefix . ' ' : '';
	}


	public function setPrefix(string $s): static
	{
		$this->prefix = $s;
		return $this;
	}


	public function getSuffix(): string
	{
		return $this->suffix;
	}


	public function setSuffix(string $s): static
	{
		$this->suffix = $s;
		return $this;
	}


	public function getUnknown(): string
	{
		return $this->unknown;
	}


	public function setUnknown(string $s): static
	{
		$this->unknown = $s;
		return $this;
	}


	// — Selection (for select type) —

	/**
	 * @param array<string|int, string> $selection
	 */
	public function setSelection(array $selection): static
	{
		$this->selection = $selection;
		return $this;
	}


	public function getSelect(string|int $key): string
	{
		return $this->selection[$key] ?? '';
	}


	// — Render —

	public function render(object $data): \Nette\Utils\Html
	{
		$name = $this->name;
		$el = \Nette\Utils\Html::el('span');
		$el->addText($this->getPrefix());
		$el->addText(isset($data->$name) ? strval($data->$name) : $this->unknown); // @phpstan-ignore argument.type
		$el->addText($this->getSuffix());
		return $el;
	}
}
