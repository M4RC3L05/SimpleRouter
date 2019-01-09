<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Helpers\ArraysH;


class ArraysHTest extends TestCase
{
    public function test_it_shound_flattern_multi_array()
    {
        $arr = ArraysH::arrayFlat([1, [1], [[1], [[1]]], ["33"]]);

        $this->assertEquals([1, 1, 1, 1, "33"], $arr);
    }
}
