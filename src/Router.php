<?php

namespace SimpleRouter;

use SimpleRouter\Response;
use SimpleRouter\Types\IResponse;
use SimpleRouter\Interfaces\IHandler;
use SimpleRouter\Interfaces\IRouter;
use SimpleRouter\Handler;
use function FPPHP\Lists\slice;
use function FPPHP\Lists\reduce;
use function FPPHP\Lists\reverse;

class Router implements IRouter
{
    private $_handlers;
    private $_viewsDir;
    private $_sessionManager;
    private $_basePath;
    private $_hostname;

    private const ALL_ROUTE = "ALL_ROUTE";
    private const GET_ROUTE = "GET";
    private const POST_ROUTE = "POST";
    private const PUT_ROUTE = "PUT";
    private const PATCH_ROUTE = "PATCH";
    private const DELETE_ROUTE = "DELETE";

    public function __construct(string $hostname = null, string $basePath = "/")
    {
        $this->_handlers = [];
        $this->_viewsDir = "";
        $this->_sessionManager = new SessionManager();
        if ($hostname) {
            $this->_hostname = $hostname;
        } else {
            $this->_hostname = $_SERVER["HTTP_HOST"] ?? $_SERVER["SERVER_NAME"] ?? "";
        }
        $this->_basePath = $basePath;
    }

    private function _isRouterType(string $type) : bool
    {
        switch ($type) {
            case Router::ALL_ROUTE:
            case Router::GET_ROUTE:
            case Router::POST_ROUTE:
            case Router::GET_ROUTE:
            case Router::PUT_ROUTE:
            case Router::PATCH_ROUTE:
            case Router::DELETE_ROUTE:
                return true;

            default:
                return false;
        }
    }

    private function _innerRegisterHandlers(string $type, string $path, array $handlers) : void
    {

        if (!$this->_isRouterType($type)) return;

        foreach ($handlers as $key => $handler) {

            \array_push($this->_handlers, new Handler($type, $path, $handler, $this->_basePath));
        }

    }

    private function _innerMath(string $hostname, string $method, string $path)
    {

        $verb = \strtoupper($method);

        $handlers = reduce(function ($acc, IHandler $curr) use ($verb, $path) {
            if ($curr->getVerb() !== Router::ALL_ROUTE && $curr->getVerb() !== $verb) return $acc;

            if (!$curr->match($path)) return $acc;

            \array_push($acc, $curr);

            return $acc;
        })([])($this->_handlers);

        return (new RequestHandler(reverse($handlers), $path, $this->_viewsDir, $this->_sessionManager))->pipeHandlers();
    }

    public function use() : Router
    {
        $args = \func_get_args();
        $path = "/*";
        $handlers = [];

        if (\is_string($args[0])) {
            $path = $args[0] . "/*";
            $handlers = slice(1)(\count($args))($args);
        } else {
            $handlers = $args;
        }

        $this->_innerRegisterHandlers(Router::ALL_ROUTE, $path, $handlers);

        return $this;
    }

    public function group(string $basePath, callable $function) : Router
    {
        $tmpPrevBase = $this->_basePath;

        if ($this->_basePath === "/") {
            $this->_basePath = $basePath;
        } else {
            $this->_basePath = $this->_basePath . $basePath;
        }

        $function($this);

        $this->_basePath = $tmpPrevBase;
        return $this;
    }

    public function get(string $path, ...$handlers) : Router
    {

        $this->_innerRegisterHandlers(Router::GET_ROUTE, $path, $handlers);
        return $this;
    }

    public function post(string $path, ...$handlers) : Router
    {
        $this->_innerRegisterHandlers(Router::POST_ROUTE, $path, $handlers);
        return $this;
    }

    public function put(string $path, ...$handlers) : Router
    {
        $this->_innerRegisterHandlers(Router::PUT_ROUTE, $path, $handlers);
        return $this;
    }

    public function patch(string $path, ...$handlers) : Router
    {
        $this->_innerRegisterHandlers(Router::PATCH_ROUTE, $path, $handlers);
        return $this;
    }

    public function delete(string $path, ...$handlers) : Router
    {
        $this->_innerRegisterHandlers(Router::DELETE_ROUTE, $path, $handlers);
        return $this;
    }

    public function all(string $path, ...$handlers) : Router
    {
        $this->_innerRegisterHandlers(Router::ALL_ROUTE, $path, $handlers);
        return $this;
    }

    public function match(string $method, string $path)
    {
        return $this->_innerMath($this->_basePath, $method, $path);
    }

    public function registerViews(string $viewsDir) : void
    {
        if (!\is_dir($viewsDir)) return;

        $this->_viewsDir = $viewsDir;
    }
}