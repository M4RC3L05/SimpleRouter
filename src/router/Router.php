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

    // private const NOT_FOUND_ROUTE = "NOT_FOUND_ROUTE";
    private const ALL_ROUTE = "ALL_ROUTE";
    private const GET_ROUTE = "GET";
    private const POST_ROUTE = "POST";
    private const PUT_ROUTE = "PUT";
    private const PATCH_ROUTE = "PATCH";
    private const DELETE_ROUTE = "DELETE";
    private const GLOBAL_MIDDLEWARES = "GLOBAL_MIDDLEWARES";
    private const GROUP_ROUTES = "GROUP_ROUTES";

    public function __construct(string $hostname = null, string $basePath = "/")
    {
        $this->_hostname = $hostname ?? $_SERVER["HTTP_HOST"] ?? $_SERVER["SERVER_NAME"] ?? "";
        $this->_routes = [];
        self::$_middlewares = [];
        $this->_viewsDir = "";
        self::$_sessionManager = new SessionManager();
        $this->_basePath = $basePath;

        $this->notFound("/notFound", function ($req, $res) {
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

            foreach ($handlers as $key => $value) {
                \array_push(self::$_middlewares, $value);
            }

            return;
        }

        if ($type == Router::GROUP_ROUTES) {
            if (!\array_key_exists($type, $this->_routes) || !isset($this->_routes[$type]))
                $this->_routes[$type] = [];

            $this->_routes[$type][$route] = $handlers;

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

        foreach ($handlers as $key => $value) {
            \array_push($this->_routes[$type][$finalPath], $value);
        }

    }

    private function _innerMath(string $hostname, string $method, string $path) : bool
    {

        $method = \strtoupper($method);

        if (!$this->_isRouterType($method) || !\array_key_exists($method, $this->_routes) || !isset($this->_routes[$method])) {
            $handlersForMatchRoute = $this->_routes[Router::GET_ROUTE][$this->_404Path];
            $handlersWithMiddlewares = \array_merge(self::$_middlewares, $handlersForMatchRoute);

            Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
            return true;
            die();
        }

        $matchesToRouteParams = [];

        $pathParams = \preg_match_all("/\:([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)/m", $this->_basePath, $matchesToRouteParams);
        $baseRegEx = "/^" . \preg_replace("/\//", "\/", $this->_basePath) . "/m";

        if (isset($matchesToRouteParams[0]) && \count($matchesToRouteParams[0]) > 0) {
            foreach ($matchesToRouteParams[0] as $keymatchesInRouteParams => $inRouteParamsMatches) {
                $baseRegEx = \str_replace($inRouteParamsMatches, "([0-9]+|[a-zA-z_@]+|[0-9a-zA-z_@]+)", $baseRegEx);
            }
        }

        if (!\preg_match_all($baseRegEx, $path)) return false;

        $routesForMethod = $this->_routes[$method];

        if ($routesForMethod === null || \count($routesForMethod) <= 0) {
            $handlersForMatchRoute = $this->_routes[Router::GET_ROUTE][$this->_404Path];
            $handlersWithMiddlewares = \array_merge(self::$_middlewares, $handlersForMatchRoute);

            Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
            return true;
            die();
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


            if (!\preg_match_all($regexPath, \explode("?", $hostname . $path)[0], $finalParamsMatches)) continue;

            $finalParamsMatches = Helpers::arrayFlat(\array_splice($finalParamsMatches, 1));

            if (\count($finalParamsMatches) > 0 && isset($matchesToRouteParams[1]) && \count($matchesToRouteParams) > 0) {
                $finalParamsMatches = \array_combine($matchesToRouteParams[1], $finalParamsMatches);
            }

            $handlersForMatchRoute = \array_values($pathwithhandler);
            $handlersWithMiddlewares = \array_merge(self::$_middlewares, $handlersForMatchRoute);
            Helpers::routerPipe($handlersWithMiddlewares, new Request($finalParamsMatches, self::$_sessionManager), new Response($this->_viewsDir));
            return true;
            die();
        }

        if (isset($this->_routes[Router::GROUP_ROUTES]) && \count($this->_routes[Router::GROUP_ROUTES]) > 0) {
            $tmp = false;
            foreach ($this->_routes[Router::GROUP_ROUTES] as $baseGroupPath => $goupRouter) {
                $tmp = $goupRouter->match($method, $path);

                if ($tmp) return true;
            }
            return $tmp;
        }

        return false;
    }

    public function use(...$middleware) : Router
    {
        $this->_innerRegisterRoute(Router::GLOBAL_MIDDLEWARES, "", $middleware);
        return $this;
    }

    public function on(string $basePath) : Router
    {
        $this->_basePath = $basePath;
        return $this;
    }

    public function group(string $basePath, Router $router) : Router
    {
        $this->_innerRegisterRoute(Router::GROUP_ROUTES, $basePath, $router);
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

    public function notFound(string $route, ...$handlers) : Router
    {
        if ($this->_basePath !== "/") return $this;
        unset($this->_routes[Router::GET_ROUTE][$this->_404Path]);
        $this->_innerRegisterRoute(Router::GET_ROUTE, $route, $handlers);
        $this->_404Path = $route;
        return $this;
    }

    public function match(string $method, string $path)
    {
        $matchResult = $this->_innerMath($this->_hostname, $method, $path);

        if (!$matchResult && $this->_basePath === "/") {
            $handlersForMatchRoute = $this->_routes[Router::GET_ROUTE][$this->_404Path];
            $handlersWithMiddlewares = \array_merge(self::$_middlewares, $handlersForMatchRoute);

            return Helpers::routerPipe($handlersWithMiddlewares, new Request([], self::$_sessionManager), new Response($this->_viewsDir));
            die();
        }

        return $matchResult;
    }

    public function registerView(string $viewsDir) : void
    {
        if (!\is_dir($viewsDir)) return;

        $this->_viewsDir = $viewsDir;
    }
}
