<?php

namespace SimpleRouter\Router\Helpers;

class FPH
{
    public static function memoise($function)
    {
        $memo = [];

        return function () use (&$memo, $function) {
            $args = \implode("", func_get_args());

            if (\array_key_exists($args, $memo)) {
                return $memo[$args];
            }

            $memo[$args] = $function(...func_get_args());
            return $memo[$args];
        };
    }
}
