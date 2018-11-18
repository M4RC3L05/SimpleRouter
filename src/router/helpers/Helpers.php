<?php

namespace SimpleRouter\Router\Helpers;

use SimpleRouter\Router\Response;
use SimpleRouter\Router\Request;


class Helpers
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
            $properHandler = Helpers::_getProperHandler($handlers[0]);
            if (isset($properHandler) && !\is_null($properHandler)) {
                return $properHandler($request, $response, function () {
                });
            } else {
                return;
            }
        }

        $now = array_shift($handlers);

        $properHandler = Helpers::_getProperHandler($now);

        if (!isset($properHandler) || \is_null($properHandler)) return;

        $next = function ($err = null) use ($handlers, $request, $response) {
            if (isset($err) && !is_null($err)) {
                throw new \Exception($err, 1);
            } else {
                return Helpers::routerPipe($handlers, $request, $response);
            }
        };

        return $properHandler($request, $response, $next);
    }

    public static function arrayFlat(array $array) : array
    {
        $tmp = [];
        array_walk_recursive($array, function ($v) use (&$tmp) {
            $tmp[] = $v;
        });

        return $tmp;
    }

    public static function memoise($function)
    {
        $memo = [];

        return function () use (&$memo, $function) {
            $args = \implode("", func_get_args());

            if (\array_key_exists($args, $memo)) {
                echo "incach";
                return $memo[$args];
            }

            $memo[$args] = $function(...func_get_args());
            return $memo[$args];
        };
    }
}
