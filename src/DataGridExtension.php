<?php

declare(strict_types=1);

namespace NDataGrid;

use Nette\DI\CompilerExtension;

/**
 * DI extension — registers DataGridControlFactoryInterface.
 *
 * extensions:
 *     datagrid: NDataGrid\DataGridExtension
 */
class DataGridExtension extends CompilerExtension
{
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('gridFactory'))
			->setImplement(DataGridControlFactoryInterface::class);
	}
}
