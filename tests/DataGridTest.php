<?php

declare(strict_types=1);

namespace NDataGrid\Tests;

use NDataGrid\Column\Column;
use NDataGrid\DataGridControl;
use NDataGrid\DataGridControlFactoryInterface;
use NDataGrid\DataGridExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../vendor/autoload.php';

\Tester\Environment::setup();


class DataGridTest extends TestCase
{
	// =====================
	// Column tests
	// =====================

	public function testColumnCreation(): void
	{
		$col = new Column('name');
		Assert::same('name', $col->name);
		Assert::same('text', $col->getType());
		Assert::same('LIKE ?', $col->operator);
	}


	public function testColumnSetType(): void
	{
		$col = new Column('price');
		$col->setType('int');
		Assert::same('int', $col->getType());
		Assert::same('= ?', $col->operator);
	}


	public function testColumnLabelAndClass(): void
	{
		$col = new Column('status');
		$col->setLabel('Stav')->setClass('text-bold')->addClass('text-red');
		Assert::same('Stav', $col->getLabel());
		Assert::contains('text-bold', $col->getClass());
		Assert::contains('text-red', $col->getClass());
	}


	public function testColumnPrefixSuffix(): void
	{
		$col = new Column('price');
		$col->setPrefix('$')->setSuffix(' Kč');
		Assert::same('$ ', $col->getPrefix());
		Assert::same(' Kč', $col->getSuffix());
	}


	public function testColumnSelection(): void
	{
		$col = new Column('type');
		$col->setType('select');
		$col->setSelection([1 => 'Admin', 2 => 'User']);
		Assert::same('Admin', $col->getSelect(1));
		Assert::same('', $col->getSelect(99));
	}


	public function testColumnRender(): void
	{
		$col = new Column('name');
		$col->setPrefix('>>');

		$item = new \stdClass();
		$item->name = 'Test';

		$html = $col->render($item);
		Assert::type(\Nette\Utils\Html::class, $html);
		Assert::contains('Test', (string) $html);
		Assert::contains('>>', (string) $html);
	}


	public function testColumnRenderUnknown(): void
	{
		$col = new Column('missing');
		$col->setUnknown('N/A');

		$item = new \stdClass();
		$html = $col->render($item);
		Assert::contains('N/A', (string) $html);
	}


	// =====================
	// DataGridControl tests
	// =====================

	public function testGridPrimary(): void
	{
		$grid = new DataGridControl();
		Assert::same('id', $grid->getPrimary());

		$grid->setPrimary('code');
		Assert::same('code', $grid->getPrimary());
	}


	public function testGridAddColumn(): void
	{
		$grid = new DataGridControl();
		$col = $grid->addColumn('name', 'Název', 'text');

		Assert::type(Column::class, $col);
		Assert::same('Název', $col->getLabel());
		Assert::same('text', $col->getType());
		Assert::count(1, $grid->getColumns());
	}


	public function testGridItems(): void
	{
		$grid = new DataGridControl();
		$grid->setPrimary('id');

		$items = [
			(object) ['id' => 1, 'name' => 'A'],
			(object) ['id' => 2, 'name' => 'B'],
		];
		$grid->setItems($items);

		Assert::count(2, $grid->getItems());
	}


	public function testGridMassActions(): void
	{
		$grid = new DataGridControl();
		$grid->setMassActions(['delete' => 'Smazat', 'export' => 'Export']);

		Assert::count(2, $grid->getMassActions());
	}


	public function testGridPaginator(): void
	{
		$grid = new DataGridControl();
		$grid->setItemsPerPage(25);
		$grid->setItemCount(100);

		$paginator = $grid->getPaginator();
		Assert::same(100, $paginator->getItemCount());
		Assert::same(25, $paginator->getItemsPerPage());
		Assert::same(4, $paginator->getPageCount());
	}


	public function testGridSorting(): void
	{
		$grid = new DataGridControl();
		$grid->addColumn('name', 'Název');

		Assert::same('id', $grid->getOrder()); // default = primary
		Assert::null($grid->getSort());
	}


	public function testGridWhere(): void
	{
		$grid = new DataGridControl();
		$grid->addColumn('name', 'Název', 'text');
		$grid->addColumn('price', 'Cena', 'int');
		$grid->query = ['name' => 'Test', 'price' => '100'];

		$where = $grid->getWhere();
		Assert::count(2, $where);
	}


	public function testGridWhereDateRange(): void
	{
		$grid = new DataGridControl();
		$grid->addColumn('created', 'Vytvořeno', 'date');
		$grid->query = ['created' => '2024-01-15'];

		$where = $grid->getWhere();
		Assert::count(2, $where); // date generates >= and < conditions
	}


	// =====================
	// Extension tests
	// =====================

	public function testExtensionRegistersFactory(): void
	{
		$loader = new ContainerLoader(sys_get_temp_dir() . '/ndatagrid-test', true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('datagrid', new DataGridExtension());
		}, 'ndatagrid-ext-test');

		$container = new $class();
		$factory = $container->getByType(DataGridControlFactoryInterface::class);
		Assert::type(DataGridControlFactoryInterface::class, $factory);

		$grid = $factory->create();
		Assert::type(DataGridControl::class, $grid);
	}
}

(new DataGridTest())->run();
