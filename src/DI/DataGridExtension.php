<?php

namespace JanSuchanek\DataGrid\DI;

use Nette\DI\CompilerExtension;

class DataGridExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $containerBuilder = $this->getContainerBuilder();
        $services = $this->loadFromFile(__DIR__ . '/services.neon');
        $this->compiler->parseServices($containerBuilder, $services);
    }
}
