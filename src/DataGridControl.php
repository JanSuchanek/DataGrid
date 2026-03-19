<?php

declare(strict_types=1);

namespace NDataGrid;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Paginator;
use NDataGrid\Column\Column;

/**
 * Lightweight DataGrid component for Nette 3.2.
 *
 * Features: sorting, filtering, inline editing, mass actions, pagination.
 *
 * Usage in presenter:
 *   protected function createComponentGrid(): DataGridControl
 *   {
 *       $grid = $this->dataGridFactory->create();
 *       $grid->setPrimary('id');
 *       $grid->addColumn('name', 'Název', 'text');
 *       $grid->addColumn('price', 'Cena', 'int')->setSuffix(' Kč');
 *       $grid->setItemsPerPage(20);
 *
 *       $grid->onFindItems[] = function (DataGridControl $grid) {
 *           $grid->setItems($this->repository->findAll());
 *       };
 *
 *       return $grid;
 *   }
 */
class DataGridControl extends Control
{
	private const DEFAULT_PRIMARY = 'id';
	private const DEFAULT_ITEMS_PER_PAGE = 20;

	protected string $primary = self::DEFAULT_PRIMARY;
	protected ?string $view = null;

	/** @var array<string, Column> */
	protected array $columns = [];

	/** @var array<int|string, object> */
	protected array $items = [];

	/** @var array<string, string> */
	protected array $massActions = [];

	protected int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE;
	protected Paginator $paginator;

	// Events
	/** @var list<callable(self): void> */
	public array $onFindItems = [];

	/** @var list<callable(self): void> */
	public array $onQueryProcess = [];

	/** @var list<callable(self): void> */
	public array $onCancelProcess = [];

	/** @var list<callable(mixed): void> */
	public array $onSaveProcess = [];

	/** @var list<callable(mixed): void> */
	public array $onSubmitProcess = [];

	/** @var list<callable(Form, array<string, Column>, array<int|string, object>, ?int): void> */
	public array $onInlineEdit = [];

	// Persistent parameters
	/** @persistent */
	public ?string $order = null;

	/** @persistent */
	public ?string $sort = null;

	/** @persistent */
	/** @var array<string, string> */
	public array $query = [];

	/** @persistent */
	public ?int $editId = null;

	/** @persistent */
	public int $page = 1;


	public function __construct()
	{
		$this->paginator = new Paginator();
	}


	// =====================
	// Column Management
	// =====================

	/**
	 * @param array<string|int, string> $selection
	 */
	public function addColumn(string $name, string $label, string $type = 'text', array $selection = []): Column
	{
		$col = $this->getColumn($name);
		$col->setType($type);
		$col->setLabel($label);

		if ($type === 'select') {
			$col->setSelection($selection);
		}

		return $col;
	}


	public function getColumn(string $name): Column
	{
		if (!isset($this->columns[$name])) {
			$this->columns[$name] = new Column($name);
		}
		return $this->columns[$name];
	}


	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	// =====================
	// Data
	// =====================

	/**
	 * @param iterable<object> $items
	 */
	public function setItems(iterable $items): void
	{
		$primary = $this->primary;
		foreach ($items as $item) {
			/** @var int|string $key */
			$key = $item->$primary;
			$this->items[$key] = $item;
		}
	}


	/**
	 * @return array<int|string, object>
	 */
	public function getItems(): array
	{
		return $this->items;
	}


	public function getItemCount(): int
	{
		return $this->paginator->getItemCount() ?? 0;
	}


	/**
	 * Set total item count for pagination.
	 */
	public function setItemCount(int $count): void
	{
		$this->paginator->setItemCount($count);
	}


	// =====================
	// Pagination
	// =====================

	public function getPaginator(): Paginator
	{
		$this->paginator->setItemsPerPage($this->itemsPerPage);
		$this->paginator->setPage($this->page);
		return $this->paginator;
	}


	public function setItemsPerPage(int $perPage): void
	{
		$this->itemsPerPage = $perPage;
	}


	// =====================
	// Primary / View / Sort
	// =====================

	public function setPrimary(string $col): void
	{
		$this->primary = $col;
	}


	public function getPrimary(): string
	{
		return $this->primary;
	}


	public function setView(string $view): void
	{
		$path = __DIR__ . '/templates/' . $view . '.latte';
		if (is_file($path)) {
			$this->view = $view;
		}
	}


	public function getOrder(): string
	{
		if ($this->order && isset($this->columns[$this->order])) {
			return $this->order;
		}
		return $this->primary;
	}


	public function getSort(): ?string
	{
		return $this->sort === 'DESC' ? 'DESC' : null;
	}


	/**
	 * @param array<string, string> $actions
	 */
	public function setMassActions(array $actions): void
	{
		$this->massActions = $actions;
	}


	/**
	 * @return array<string, string>
	 */
	public function getMassActions(): array
	{
		return $this->massActions;
	}


	// =====================
	// WHERE builder
	// =====================

	/**
	 * Build WHERE conditions from query filters.
	 *
	 * @return list<array<string, mixed>>
	 */
	public function getWhere(): array
	{
		$where = [];

		foreach ($this->query as $key => $value) {
			if (!$value || !isset($this->columns[$key])) {
				continue;
			}

			$col = $this->columns[$key];
			$type = $col->getType();

			if ($type === 'date') {
				$date = new \DateTime($value);
				$where[] = [$key . ' >= ?' => $date->format('Y-m-d')];
				$nextDay = (clone $date)->modify('+1 day');
				$where[] = [$key . ' < ?' => $nextDay->format('Y-m-d')];
			} elseif (in_array($type, ['int', 'stav'], true)) {
				$where[] = [$key . ' ' . $col->operator => (int) $value];
			} else {
				$where[] = [$key . ' ' . $col->operator => $col->pattern[0] . $value . $col->pattern[1]];
			}
		}

		return $where;
	}


	// =====================
	// Signals
	// =====================

	public function handleEdit(?int $id = null): void
	{
		$this->editId = $id;
		$this->redirect('this');
	}


	// =====================
	// Form
	// =====================

	protected function createComponentListForm(): Form
	{
		$form = new Form();
		$form->addSelect('select', 'Výběr akce', $this->massActions);

		// Trigger data loading
		foreach ($this->onFindItems as $cb) {
			$cb($this);
		}

		// Inline edit callback
		foreach ($this->onInlineEdit as $cb) {
			$cb($form, $this->columns, $this->items, $this->editId);
		}

		// Checkboxes for items
		$container = $form->addContainer('items');
		foreach ($this->items as $item) {
			$primary = $this->primary;
			$container->addCheckbox(strval($item->$primary)); // @phpstan-ignore argument.type
		}

		// Query filters
		$queryContainer = $form->addContainer('query');
		foreach ($this->columns as $key => $col) {
			$input = $queryContainer->addText($key, $col->getLabel());
			if (isset($this->query[$key])) {
				$input->setDefaultValue($this->query[$key]);
			}
		}

		// Buttons
		$form->addSubmit('cancel', 'Zrušit')->onClick[] = function (SubmitButton $button): void {
			$this->query = [];
			$this->editId = null;
			foreach ($this->onCancelProcess as $cb) {
				$cb($this);
			}
			$this->redirect('this');
		};

		$form->addSubmit('search', 'Hledej')->onClick[] = function (SubmitButton $button): void {
			/** @var array<string, array<string, string>> $values */
			$values = $button->getForm()->getValues('array');
			/** @var array<string, string> $queryValues */
			$queryValues = $values['query'] ?? [];
			$this->query = $queryValues;
			foreach ($this->onQueryProcess as $cb) {
				$cb($this);
			}
			$this->redirect('this');
		};

		$form->addSubmit('save', 'Uložit')->onClick[] = function (SubmitButton $button): void {
			$values = $button->getForm()->getHttpData();
			foreach ($this->onSaveProcess as $cb) {
				$cb($values);
			}
			$this->redirect('this');
		};

		$form->addSubmit('submit', 'Proveď')->onClick[] = function (SubmitButton $button): void {
			$values = $button->getForm()->getValues();
			foreach ($this->onSubmitProcess as $cb) {
				$cb($values);
			}
			$this->redirect('this');
		};

		return $form;
	}


	// =====================
	// Render
	// =====================

	public function render(): void
	{
		// Trigger data loading
		foreach ($this->onFindItems as $cb) {
			$cb($this);
		}

		$paginator = $this->getPaginator();

		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate $template */
		$template = $this->template;
		$template->setParameters([
			'itemsCount' => $paginator->getItemCount(),
			'cols' => $this->columns,
			'items' => $this->items,
			'primary' => $this->primary,
			'order' => $this->getOrder(),
			'sort' => $this->getSort(),
			'editId' => $this->editId,
			'paginator' => $paginator,
			'page' => $this->page,
		]);

		$view = $this->view ?? 'default';
		$template->setFile(__DIR__ . '/templates/' . $view . '.latte');
		$template->render();
	}
}
