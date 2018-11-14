<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Request;
use SimpleRouter\Router\SessionManager;

class RequestTest extends TestCase
{

    public static function setUpBeforeClass()
    {
        // session_start();
    }

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
