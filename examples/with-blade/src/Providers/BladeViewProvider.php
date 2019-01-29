<?php

namespace App\Providers;

use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;
use duncan3dc\Laravel\BladeInstance;


class BladeViewProvider implements IViewEngineServiceProvider
{
    private $_blade;

    public function __construct()
    {
        $this->_blade = new BladeInstance(__DIR__ . "/../../resources/views", __DIR__ . "/../../.cache/views");
    }

    public function renderView(string $viewPath, array $viewData = []) : string
    {
        return $this->_blade->render($viewPath, $viewData);
    }

    public function getInstance()
    {
        return $this->_blade;
    }
}