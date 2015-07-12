<?php

namespace JanSuchanek\DataGrid\Components\DataGrid;

interface DataGridControlFactoryInterface
{
    /** @return DataGridControl */
    function create();
}