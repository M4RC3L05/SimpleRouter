<?php

namespace SimpleRouter\Router;

use SimpleRouter\Router\Response;
use SimpleRouter\Router\Helpers\Helpers;
use SimpleRouter\Router\Types\IResponse;

class Router
{
    private $_routes;
    private $_middlewares;
    private $_viewsDir;
    private $_404Path = "/notfound";

    private const NOT_FOUND_ROUTE = "NOT_FOUND_ROUTE";
    private const GET_ROUTE = "GET";
    private const POST_ROUTE = "POST";
    private const PUT_ROUTE = "PUT";
    private const PATCH_ROUTE = "PATCH";
    private const DELETE_ROUTE = "DELETE";
    private const GLOBAL_MIDDLEWARES = "GLOBAL_MIDDLEWARES";

    public function __construct()
    {
        $this->_routes = [];
        $this->_middlewares = [];
        $this->_viewsDir = "";
        $this->_setUp();
    }

    private function _setUp()
    {
        $this->notFound($this->_404Path, function ($req, Response $res) {
            return $res->status(404)->sendHtml("Not found");
        });

        $this->_middlewares[Router::GLOBAL_MIDDLEWARES] = [];
    }

    private function _isRouterType(string $type)
    {
        switch ($type) {
            case Router::GET_ROUTE:
            case Router::POST_ROUTE:
            case Router::GET_ROUTE:
            case Router::PUT_ROUTE:
            case Router::PATCH_ROUTE:
            case Router::DELETE_ROUTE:
            case Router::NOT_FOUND_ROUTE:
                return true;

            default:
                return false;
        }
    }

    private function _innerRegisterRoute(string $type, string $route, $handlers)
    {

        if (!$this->_isRouterType($type)) return;

        if ($type === Router::NOT_FOUND_ROUTE) {
            unset($this->_routes[Router::GET_ROUTE][$this->_404Path]);
            $this->_404Path = $route;
            $this->_routes[Router::GET_ROUTE][$this->_404Path] = $handlers;
            return;
        }

        if (!\array_key_exists($type, $this->_routes) || !isset($this->_routes[$type]))
            $this->_routes[$type] = [];

        \array_push($this->_routes[$type], [$route => $handlers]);
    }

    private function _innerMath(string $hostname, string $method, string $path)
    {
        $method = \strtoupper($method);

        if (!$this->_isRouterType($method) || !\array_key_exists($method, $this->_routes) || !isset($this->_routes[$method])) return $this->_routes[Router::NOT_FOUND]();

        $routesForMethod = $this->_routes[$method];

        if ($routesForMethod == null || \count($routesForMethod) <= 0) return $this->_routes[Router::NOT_FOUND]();

        foreach ($routesForMethod as $keyIndexRoute => $pathwithhandler) {
            $innerPath = \array_keys($pathwithhandler)[0];
            $matchesToRouteParams = [];
            $pathParams = \preg_match_all("/\:([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)/m", $innerPath, $matchesToRouteParams);

            $regexPath = "/^" . $hostname . \preg_replace("/\//", "\/", $innerPath) . "$/m";

            if (isset($matchesToRouteParams[0]) && \count($matchesToRouteParams[0]) > 0) {
                foreach ($matchesToRouteParams[0] as $keymatchesInRouteParams => $inRouteParamsMatches) {
                    $regexPath = \str_replace($inRouteParamsMatches, "([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)", $regexPath);
                }
            }

            $finalParamsMatches = [];

            if (!\preg_match_all($regexPath, \explode("?", $path)[0], $finalParamsMatches)) continue;

            $finalParamsMatches = Helpers::arrayFlat(\array_splice($finalParamsMatches, 1));

            if (\count($finalParamsMatches) > 0 && isset($matchesToRouteParams[1]) && \count($matchesToRouteParams) > 0) {
                $finalParamsMatches = \array_combine($matchesToRouteParams[1], $finalParamsMatches);
            }

            $handlersForMatchRoute = \array_values($pathwithhandler)[0];
            $handlersWithMiddlewares = \array_merge($this->_middlewares[Router::GLOBAL_MIDDLEWARES], $handlersForMatchRoute);

            return Helpers::routerPipe($handlersWithMiddlewares, new Request($finalParamsMatches), new Response($this->_viewsDir));
        }

        $handlersForMatchRoute = $this->_routes[Router::GET_ROUTE][$this->_404Path];
        $handlersWithMiddlewares = \array_merge($this->_middlewares[Router::GLOBAL_MIDDLEWARES], $handlersForMatchRoute);

        return Helpers::routerPipe($handlersWithMiddlewares, new Request(), new Response($this->_viewsDir));
    }

    public function use($middleware)
    {
        \array_push($this->_middlewares[Router::GLOBAL_MIDDLEWARES], $middleware);
    }

    public function get(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::GET_ROUTE, $route, $handlers);
    }

    public function post(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::POST_ROUTE, $route, $handlers);
    }

    public function put(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::PUT_ROUTE, $route, $handlers);
    }

    public function patch(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::PATCH_ROUTE, $route, $handlers);
    }

    public function delete(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::DELETE_ROUTE, $route, $handlers);
    }

    public function notFound(string $route, ...$handlers)
    {
        $this->_innerRegisterRoute(Router::NOT_FOUND_ROUTE, $route, $handlers);
    }

    public function match(string $hostname, string $method, string $path)
    {
        return $this->_innerMath($hostname, $method, $path);
    }

    public function registerView(string $viewsDir) : void
    {
        if (!\is_dir($viewsDir)) return;

        $this->_viewsDir = $viewsDir;
    }
}
