<?php

namespace SimpleRouter\Router;

use SimpleRouter\Router\Response;
use SimpleRouter\Router\Helpers\Helpers;
use SimpleRouter\Router\Types\IResponse;

class Router
{
    private $_routes;
    private $_viewsDir;
    private $_404Path = "/notfound";
    private static $_sessionManager;
    private $_hostname;
    private static $_middlewares;
    private $_basePath;
    private $_memoiseAllMiddlewaresForRoute;

    private const ALL_ROUTE = "ALL_ROUTE";
    private const GET_ROUTE = "GET";
    private const POST_ROUTE = "POST";
    private const PUT_ROUTE = "PUT";
    private const PATCH_ROUTE = "PATCH";
    private const DELETE_ROUTE = "DELETE";
    private const GLOBAL_MIDDLEWARES = "GLOBAL_MIDDLEWARES";
    private const NOT_FOUND_ROUTE = "404_ROUTE";

    public function __construct(string $hostname = null, string $basePath = "/")
    {
        $this->_hostname = $hostname ?? $_SERVER["HTTP_HOST"] ?? $_SERVER["SERVER_NAME"] ?? "";
        $this->_routes = [];
        self::$_middlewares = [];
        $this->_viewsDir = "";
        self::$_sessionManager = new SessionManager();
        $this->_basePath = $basePath;
        $this->_memoiseAllMiddlewaresForRoute = Helpers::memoise(function (...$params) {
            return $this->_allMiddlewaresForRoute(...$params);
        });

        $this->notFound(function ($req, $res) {
            return $res->status(404)->sendHtml("<p>Not found</p>");
        });
    }

    private function _isRouterType(string $type) : bool
    {
        switch ($type) {
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

    private function _innerRegisterRoute(string $type, string $route, $handlers) : void
    {

        if ($type === Router::GLOBAL_MIDDLEWARES) {


            if (!isset(self::$_middlewares))
                self::$_middlewares = [];

            if (!isset(self::$_middlewares[$this->_basePath]))
                self::$_middlewares[$this->_basePath] = [];

            foreach ($handlers as $key => $value) {
                \array_push(self::$_middlewares[$this->_basePath], $value);
            }

            return;
        }

        if ($type === Router::NOT_FOUND_ROUTE) {
            if (!\array_key_exists($type, $this->_routes) || !isset($this->_routes[$type]))
                $this->_routes[$type] = [];

            $this->_routes[$type] = $handlers;
            return;
        }

        if (!$this->_isRouterType($type)) return;

        if (!\array_key_exists($type, $this->_routes) || !isset($this->_routes[$type]))
            $this->_routes[$type] = [];


        $finalPath = "";

        if ($this->_basePath === "/") {
            $finalPath = $route;
        } else if ($this->_basePath !== "/" && $route === "/") {
            $finalPath = $this->_basePath;
        } else {
            $finalPath = $this->_basePath . $route;
        }

        if (!\array_key_exists($finalPath, $this->_routes[$type]) || !isset($this->_routes[$type][$finalPath]))
            $this->_routes[$type][$finalPath] = [];

        $this->_routes[$type][$finalPath] = $handlers;

    }


    private function _allMiddlewaresForRoute(string $path)
    {
        $allMiddlewares = [];

        foreach (self::$_middlewares as $p => $handlers) {

            $matchesToRouteParams = [];
            $pathParams = \preg_match_all("/\:([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)/m", $p, $matchesToRouteParams);
            $regexPath = "/^" . \preg_replace("/\//", "\/", $p) . ($p === "/" ? "" : "\/") . "/m";

            if (isset($matchesToRouteParams[0]) && \count($matchesToRouteParams[0]) > 0) {

                foreach ($matchesToRouteParams[0] as $keymatchesInRouteParams => $inRouteParamsMatches) {
                    $regexPath = \str_replace($inRouteParamsMatches, "([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)", $regexPath);
                }
            }

            if (!\preg_match_all($regexPath, $path . "/")) continue;

            for ($i = 0; $i < count($handlers); $i++) {
                \array_push($allMiddlewares, $handlers[$i]);
            }
        }

        return $allMiddlewares;
    }

    private function _innerMath(string $hostname, string $method, string $path)
    {

        $method = \strtoupper($method);

        if (!$this->_isRouterType($method) || !\array_key_exists($method, $this->_routes) || !isset($this->_routes[$method])) {
            $allMiddlewares = $this->_memoiseAllMiddlewaresForRoute->call(new class
            {
            }, $path);
            $handlersForMatchRoute = $this->_routes[Router::NOT_FOUND_ROUTE];
            $handlersWithMiddlewares = \array_merge($allMiddlewares ?? [], $handlersForMatchRoute);

            return Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
        }

        $routesForMethod = $this->_routes[$method];

        if ($routesForMethod === null || \count($routesForMethod) <= 0) {
            $allMiddlewares = $this->_memoiseAllMiddlewaresForRoute->call(new class
            {
            }, $path);
            $handlersForMatchRoute = $this->_routes[Router::NOT_FOUND_ROUTE];
            $handlersWithMiddlewares = \array_merge($allMiddlewares ?? [], $handlersForMatchRoute);

            return Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
        }

        foreach ($routesForMethod as $keyIndexRoute => $pathwithhandler) {
            $innerPath = $keyIndexRoute;
            $matchesToRouteParams = [];

            $pathParams = \preg_match_all("/\:([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)/m", $innerPath, $matchesToRouteParams);
            $regexPath = "/^" . $hostname . \preg_replace("/\//", "\/", $innerPath) . "$/m";

            if (isset($matchesToRouteParams[0]) && \count($matchesToRouteParams[0]) > 0) {

                foreach ($matchesToRouteParams[0] as $keymatchesInRouteParams => $inRouteParamsMatches) {
                    $regexPath = \str_replace($inRouteParamsMatches, "([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)", $regexPath);
                }
            }

            $finalParamsMatches = [];

            $matchPath = \explode("?", $path)[0];

            if (!\preg_match_all($regexPath, $hostname . $matchPath, $finalParamsMatches)) continue;

            $finalParamsMatches = Helpers::arrayFlat(\array_splice($finalParamsMatches, 1));

            if (\count($finalParamsMatches) > 0 && isset($matchesToRouteParams[1]) && \count($matchesToRouteParams) > 0) {
                $finalParamsMatches = \array_combine($matchesToRouteParams[1], $finalParamsMatches);
            }

            $allMiddlewares = $this->_memoiseAllMiddlewaresForRoute->call(new class
            {
            }, $matchPath);
            $handlersForMatchRoute = \array_values($pathwithhandler);
            $handlersWithMiddlewares = \array_merge($allMiddlewares ?? [], $handlersForMatchRoute);
            return Helpers::routerPipe($handlersWithMiddlewares, new Request($finalParamsMatches, self::$_sessionManager), new Response($this->_viewsDir));
        }

        $matchPath = \explode("?", $path)[0];
        $allMiddlewares = $this->_memoiseAllMiddlewaresForRoute->call(new class
        {
        }, $matchPath);
        $handlersForMatchRoute = $this->_routes[Router::NOT_FOUND_ROUTE];
        $handlersWithMiddlewares = \array_merge($allMiddlewares ?? [], $handlersForMatchRoute);
        return Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
    }

    public function use(...$middleware) : Router
    {
        $this->_innerRegisterRoute(Router::GLOBAL_MIDDLEWARES, "", $middleware);
        return $this;
    }

    public function group(string $basePath, \Closure $function) : Router
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

    public function get(string $route, ...$handlers) : Router
    {
        $this->_innerRegisterRoute(Router::GET_ROUTE, $route, $handlers);
        return $this;
    }

    public function post(string $route, ...$handlers) : Router
    {
        $this->_innerRegisterRoute(Router::POST_ROUTE, $route, $handlers);
        return $this;
    }

    public function put(string $route, ...$handlers) : Router
    {
        $this->_innerRegisterRoute(Router::PUT_ROUTE, $route, $handlers);
        return $this;
    }

    public function patch(string $route, ...$handlers) : Router
    {
        $this->_innerRegisterRoute(Router::PATCH_ROUTE, $route, $handlers);
        return $this;
    }

    public function delete(string $route, ...$handlers) : Router
    {
        $this->_innerRegisterRoute(Router::DELETE_ROUTE, $route, $handlers);
        return $this;
    }

    public function notFound(...$handlers) : Router
    {
        if ($this->_basePath !== "/") return $this;
        $this->_innerRegisterRoute(Router::NOT_FOUND_ROUTE, "", $handlers);
        return $this;
    }

    public function match(string $method, string $path)
    {
        $this->_innerMath($this->_hostname, $method, $path);
    }

    public function registerViews(string $viewsDir) : void
    {
        if (!\is_dir($viewsDir)) return;

        $this->_viewsDir = $viewsDir;
    }
}
