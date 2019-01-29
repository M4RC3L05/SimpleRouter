<?php

namespace SimpleRouter;

use SimpleRouter\Router\Router;
use SimpleRouter\Http\RequestHandler;
use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;
use function FPPHP\Lists\reverse;


class SimpleRouter
{
    private $_viewEngine;
    private $_router;
    private $_sessionManager;

    public function __construct()
    {
        $this->_router = new Router();
        $this->_viewEngine = null;
    }

    public function router()
    {
        return $this->_router;
    }

    public function handleRequest()
    {
        $handlers = $this->_router->match($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
        $pathOnly = \parse_url($_SERVER["REQUEST_URI"])["path"];
        return (new RequestHandler(reverse($handlers), $pathOnly, $this->_viewEngine))->pipeHandlers();
    }

    public function registerViewEngine(IViewEngineServiceProvider $engine)
    {
        $this->_viewEngine = $engine;
    }
}