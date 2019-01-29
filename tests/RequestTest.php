<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use SimpleRouter\Request;
use SimpleRouter\SessionManager;

class RequestTest extends TestCase
{
    public function test_it_should_create_a_new_request_object()
    {
        try {
            $sessionHandlerSpy = $this->createMock(SessionManager::class);
            $obj = new Request([], $sessionHandlerSpy);
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }
}
