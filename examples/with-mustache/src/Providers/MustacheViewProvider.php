<?php

namespace App\Providers;

use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;


class MustacheViewProvider implements IViewEngineServiceProvider
{
    private $_m;

    public function __construct()
    {
        $this->_m = new \Mustache_Engine([
            "loader" => new \Mustache_Loader_FilesystemLoader(__DIR__ . "/../../resources/views")
        ]);
    }

    public function renderView(string $viewPath, array $viewData = []) : string
    {
        return $this->_m->render($viewPath, $viewData);
    }

    public function getInstance()
    {
        return $this->_m;
    }
}