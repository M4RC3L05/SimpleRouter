<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Helpers\FPH;


class FPHTest extends TestCase
{
    public function test_it_should_memoise()
    {

        $times = 0;
        $a = new class
        {
            public function sum($a, $b)
            {
                return $a + $b;
            }
        };


        $sumMem = FPH::memoise(function (...$params) use ($a, &$times) {
            $times += 1;
            return $a->sum(...$params);
        });

        $sumMem(1, 2);
        $sumMem(1, 2);

        $this->assertEquals(1, $times);
    }
}
