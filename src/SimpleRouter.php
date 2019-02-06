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
        $handlers = $this->_router->getHandlers();
        return (new RequestHandler(reverse($handlers), $this->_viewEngine))->pipeHandlers();
    }

    public function registerViewEngine(IViewEngineServiceProvider $engine)
    {
        $this->_viewEngine = $engine;
    }

    public function __call($name, $arguments)
    {
        if (!\method_exists($this->_router, $name)) throw new \Exception("Could not call method {$name}");

        return \call_user_func_array([$this->_router, $name], $arguments);
    }
}