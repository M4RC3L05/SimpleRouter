<?php

namespace SimpleRouter;

class ViewEngine
{
    private $_loader;
    private $_twig;

    public function __construct(string $viewsDir, array $twigConfig = [])
    {
        $this->_loader = new \Twig_Loader_Filesystem($viewsDir);
        $this->_twig = new \Twig_Environment($this->_loader, $twigConfig);
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