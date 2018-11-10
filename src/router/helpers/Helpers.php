<?php

namespace SimpleRouter\Router\Helpers;

class Helpers
{
    private static function getProperHandler($handler)
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
            return null;
        }
    }


    public static function routerPipe($handlers, $request, $response)
    {

        if (\count($handlers) <= 0) return;

        if (\count($handlers) === 1) {
            $properHandler = Helpers::getProperHandler($handlers[0]);
            if (isset($properHandler) && !\is_null($properHandler)) {
                return $properHandler($request, $response, function () {
                });
            } else {
                return;
            }
        }

        $now = array_shift($handlers);

        $properHandler = Helpers::getProperHandler($now);

        if (!isset($properHandler) || \is_null($properHandler)) return;

        $next = function ($err = null) use ($handlers, $request, $response) {
            if (isset($err) && !is_null($err)) {
                throw new Exception($err, 1);
            } else {
                return Helpers::routerPipe($handlers, $request, $response);
            }
        };

        return $properHandler($request, $response, $next);
    }
}