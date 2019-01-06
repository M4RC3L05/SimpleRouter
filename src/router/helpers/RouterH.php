<?php

namespace SimpleRouter\Router\Helpers;

use SimpleRouter\Router\Response;
use SimpleRouter\Router\Request;


class RouterH
{
    private static function _getProperHandler($handler)
    {
        if (\is_array($handler) or ($handler instanceof Traversable)) {
            $class = $handler[0];
            $method = $handler[1];
            return function ($request, $response, $next) use ($class, $method) {
                (new $class)->$method($request, $response, $next);
            };
        } else if ($handler instanceof \Closure || \function_exists($handler)) {
            return $handler;
        } else {
            return;
        }
    }


    public static function routerPipe(array $handlers, Request $request, Response $response)
    {

        if (\count($handlers) <= 0) return;

        if (\count($handlers) === 1) {
            $properHandler = RouterH::_getProperHandler($handlers[0]);
            if (isset($properHandler) && !\is_null($properHandler)) {
                return $properHandler($request, $response, function () {
                });
            } else {
                return;
            }
        }

        $now = array_shift($handlers);

        $properHandler = RouterH::_getProperHandler($now);

        if (!isset($properHandler) || \is_null($properHandler)) return;

        $next = function ($err = null) use ($handlers, $request, $response) {
            if (isset($err) && !is_null($err)) {
                throw new \Exception($err, 1);
            } else {
                return RouterH::routerPipe($handlers, $request, $response);
            }
        };

        return $properHandler($request, $response, $next);
    }

}