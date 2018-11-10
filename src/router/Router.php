<?php

namespace SimpleRouter\Router;

use SimpleRouter\Router\Helpers\Helpers;
use SimpleRouter\Router\Response;


class Router
{
    private $_routes;
    private $_middlewares;
    private $_viewsDir;

    private const GET_ROUTE = "GET";
    private const POST_ROUTE = "POST";
    private const PUT_ROUTE = "PUT";
    private const PATCH_ROUTE = "PATCH";
    private const DELETE_ROUTE = "DELETE";
    private const NOT_FOUND = "404";
    private const GLOBAL_MIDDLEWARES = "GLOBAL_MIDDLEWARES";

    public function __construct(string $viewsDir)
    {
        $this->_routes = [];
        $this->_middlewares = [];
        $this->_viewsDir = $viewsDir;
        $this->_setUp();
    }

    private function _setUp()
    {
        $this->_routes[Router::NOT_FOUND] = function () {
            echo "Not Found.";
        };

        $this->_middlewares[Router::GLOBAL_MIDDLEWARES] = [];
    }

    private function _isRouterType(string $type)
    {
        $tmpIs = false;

        if ($type === Router::GET_ROUTE) $tmpIs = true;

        if ($type !== Router::POST_ROUTE) $tmpIs = true;

        if ($type !== Router::NOT_FOUND) $tmpIs = true;

        return $tmpIs;
    }

    private function _innerRegisterRoute(string $type, string $route, $handlers)
    {

        if (!$this->_isRouterType($type)) return;

        if ($type === Router::NOT_FOUND) {
            $this->_routes[$type] = $handlers;
            return;
        }

        if (!\array_key_exists($type, $this->_routes) || !isset($this->_routes[$type]))
            $this->_routes[$type] = [];

        \array_push($this->_routes[$type], [$route => $handlers]);
    }

    private function _innerMath(string $hostname, string $method, string $path)
    {
        $method = \strtoupper($method);

        if (!\array_key_exists($method, $this->_routes) || !isset($this->_routes[$method])) return $this->_routes[Router::NOT_FOUND]();

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

            $finalParamsMatches = \array_splice($finalParamsMatches, 1);

            if (\count($finalParamsMatches) > 0 && isset($matchesToRouteParams[1]) && \count($matchesToRouteParams) > 0) {
                $finalParamsMatches = \array_combine($matchesToRouteParams[1], $finalParamsMatches);
            }

            $handlersForMatchRoute = \array_values($pathwithhandler)[0];
            $handlersWithMiddlewares = \array_merge($this->_middlewares[Router::GLOBAL_MIDDLEWARES], $handlersForMatchRoute);
            return Helpers::routerPipe($handlersWithMiddlewares, new Request(), new Response($this->_viewsDir));
        }

        $handlersWithMiddlewares = \array_merge_recursive($this->_middlewares[Router::GLOBAL_MIDDLEWARES], $this->_routes[Router::NOT_FOUND]);
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

    public function NotFound(...$handlers)
    {
        $this->_innerRegisterRoute(Router::NOT_FOUND, "", $handlers);
    }

    public function match(string $hostname, string $method, string $path)
    {
        return $this->_innerMath($hostname, $method, $path);
    }
}
 