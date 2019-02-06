<?php

namespace SimpleRouter\Router;

use function FPPHP\Lists\slice;
use function FPPHP\Lists\reduce;
use function FPPHP\Lists\append;
use SimpleRouter\Http\RequestHandler;
use function FPPHP\Lists\reverse;

class Router
{
    private $_handlers;
    private $_basePath;
    private $_viewEngine;

    public const ALL_ROUTE = "ALL_ROUTE";
    public const MIDDLEWARE = "MIDDLEWARE";
    public const GET_ROUTE = "GET";
    public const POST_ROUTE = "POST";
    public const PUT_ROUTE = "PUT";
    public const PATCH_ROUTE = "PATCH";
    public const DELETE_ROUTE = "DELETE";


    public function __construct()
    {
        $this->_handlers = [];
        $this->_basePath = "/";
    }

    private function _isRouterType(string $type) : bool
    {
        switch ($type) {
            case Router::ALL_ROUTE:
            case Router::MIDDLEWARE:
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

    public function getHandlers()
    {
        $finalHandlers = append(new Handler(Router::MIDDLEWARE, "/*", function ($error, $req, $res, $next) {
            return $res->status(500)->sendHtml("
            <h1>An error ocurr!</h1>
            <p>{$error}</p>
            ");
        }, "/"))($this->_handlers);
        return $finalHandlers;
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

        $this->_innerRegisterHandlers(Router::MIDDLEWARE, $path, $handlers);

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

    public function to(array $methods, string $path, ...$handlers) : Router
    {
        foreach ($methods as $method) {
            $this->_innerRegisterHandlers(\strtoupper($method), $path, $handlers);
        }

        return $this;
    }
}
