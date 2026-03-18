# NDataGrid — Lightweight DataGrid for Nette 3.2

Minimalistický, přehledný DataGrid pro Nette Framework. Žádné zbytečné závislosti.

## Features

- **Sorting** — kliknutí na hlavičku sloupce
- **Filtering** — textový filtr na každý sloupec
- **Inline edit** — editace přímo v tabulce
- **Mass actions** — hromadné akce s checkboxy
- **Pagination** — vestavěný paginátor (Nette\Utils\Paginator)
- **Column types** — text, int, date, select, stav

## Installation

```bash
composer require jansuchanek/datagrid
```

## Configuration

```neon
extensions:
    datagrid: NDataGrid\DataGridExtension
```

## Usage

```php
protected function createComponentProductGrid(): DataGridControl
{
    $grid = $this->dataGridFactory->create();
    $grid->setPrimary('id');
    $grid->setItemsPerPage(20);

    $grid->addColumn('name', 'Název', 'text');
    $grid->addColumn('price', 'Cena', 'int')->setSuffix(' Kč');
    $grid->addColumn('status', 'Stav', 'select', [1 => 'Aktivní', 0 => 'Neaktivní']);
    $grid->addColumn('created', 'Vytvořeno', 'date');

    $grid->setMassActions(['delete' => 'Smazat vybrané', 'export' => 'Exportovat']);

    $grid->onFindItems[] = function (DataGridControl $grid) {
        $where = $grid->getWhere();
        $order = $grid->getOrder() . ' ' . ($grid->getSort() ?? 'ASC');
        $paginator = $grid->getPaginator();

        $grid->setItemCount($this->productRepo->count($where));
        $grid->setItems($this->productRepo->findAll($where, $order, $paginator));
    };

    $grid->onSubmitProcess[] = function ($values) {
        // Mass action handler
        foreach ($values->items as $id => $checked) {
            if ($checked) { /* ... */ }
        }
    };

    return $grid;
}
```

```latte
{control productGrid}
```

## Requirements

- PHP >= 8.2
- Nette 3.2
- Latte 3
