<?php

namespace JanSuchanek\DataGrid\Components\VisualPaginator;

interface VisualPaginatorControlFactoryInterface
{
	/** @return VisualPaginatorControl */
	public function create();
}