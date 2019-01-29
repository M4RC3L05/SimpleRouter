<?php

namespace App\Providers;

use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;


class TwigViewProvider implements IViewEngineServiceProvider
{
    private $_loader;
    private $_twig;

    public function __construct()
    {
        $this->_loader = new \Twig_Loader_Filesystem(__DIR__ . "/../../resources/views");
        $this->_twig = new \Twig_Environment($this->_loader, []);
    }

    public function renderView(string $viewPath, array $viewData = []) : string
    {
        return $this->_twig->render($viewPath, $viewData);
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