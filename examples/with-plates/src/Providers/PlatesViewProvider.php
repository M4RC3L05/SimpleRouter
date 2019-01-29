<?php

namespace App\Providers;


use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;
use League\Plates\Engine;


class PlatesViewProvider implements IViewEngineServiceProvider
{
    private $_plates;

    public function __construct()
    {
        $this->_plates = new Engine(__DIR__ . "/../../resources/views", "view.php");
    }

    public function renderView(string $viewPath, array $viewData = []) : string
    {
        return $this->_plates->render($viewPath, $viewData);
    }

    public function getInstance()
    {
        return $this->_twig;
    }

    public function getLoader()
    {
        return $this->_loader;
    }
}