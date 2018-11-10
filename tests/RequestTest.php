<?php

namespace Tests;



use \PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Request;

class RequestTest extends TestCase
{

    public static function setUpBeforeClass()
    {
        // session_start();
    }

    public function test_it_should_create_a_new_response_object()
    {
        try {
            $obj = new Request();
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }

    }


    public function test_it_should_set_data_to_session()
    {
        $prevSession = $_SESSION ?? [];
        $_SESSION = [];
        // $setOnSessionMock = $this
        //     ->getMockBuilder(Request::class)
        //     ->getMock();

        // $setOnSessionMock
        //     ->expects($this->once())
        //     ->method("setSession")
        //     ->with("a", "b");

        $request = new Request();
        $request->setSession("a", "b");
        $this->assertEquals(["a" => "b"], $request->session);
        $_SESSION = $prevSession;
    }

    public function test_it_should_clear_data_from_session()
    {
        $prevSession = $_SESSION ?? [];

        $_SESSION = [];

        $request = new Request();
        $request->setSession("a", "b");
        $this->assertEquals(["a" => "b"], $request->session);
        $request->removeFromSession("a", "b");
        $this->assertEquals([], $request->session);
        $_SESSION = $prevSession;
    }
}
