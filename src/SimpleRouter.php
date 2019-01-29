<?php

namespace SimpleRouter;

use SimpleRouter\Interfaces\IViewEngine;
use SimpleRouter\Router\Router;
use SimpleRouter\Session\SessionManager;
use function FPPHP\Lists\reverse;
use SimpleRouter\Http\RequestHandler;


class SimpleRouter
{
    private $_viewEngine;
    private $_router;
    private $_sessionManager;

    public function __construct()
    {
        $this->_router = new Router();
        $this->_viewEngine = null;
        $this->_sessionManager = new SessionManager();
    }

    public function router()
    {
        return $this->_router;
    }

    public function handleRequest()
    {
        $handlers = $this->_router->match($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);
        $pathOnly = \parse_url($_SERVER["REQUEST_URI"])["path"];
        return (new RequestHandler(reverse($handlers), $pathOnly, $this->_sessionManager, $this->_viewEngine))->pipeHandlers();
    }

    public function registerViewEngine(IViewEngine $engine)
    {
        $this->_viewEngine = $engine;
    }
}