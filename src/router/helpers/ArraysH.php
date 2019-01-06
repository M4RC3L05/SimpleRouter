<?php

namespace SimpleRouter\Router\Helpers;

class ArraysH
{
    public static function arrayFlat(array $array) : array
    {
        $tmp = [];
        array_walk_recursive($array, function ($v) use (&$tmp) {
            $tmp[] = $v;
        });

        return $tmp;
    }
}
