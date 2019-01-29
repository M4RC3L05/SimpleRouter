<?php

namespace Tests\Http;


use PHPUnit\Framework\TestCase;
use SimpleRouter\Http\Request;


class RequestTest extends TestCase
{
    public function test_it_should_create_a_new_request_object()
    {
        try {
            $obj = new Request();
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }
}
