<?php

declare(strict_types=1);

namespace NDataGrid;

interface DataGridControlFactoryInterface
{
	public function create(): DataGridControl;
}
