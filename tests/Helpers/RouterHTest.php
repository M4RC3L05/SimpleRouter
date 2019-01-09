<?php
namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Helpers\RouterH;
use SimpleRouter\Router\Request;
use SimpleRouter\Router\Response;
use SimpleRouter\Router\SessionManager;

class RouterHTest extends TestCase
{
    public function test_it_should_pipe_request_response_and_next_into_handlers()
    {
        $a = "";

        $handlers = [
            function ($req, $res, $next) use (&$a) {
                $a .= "1";
                $next();
            },
            function ($req, $res, $next) use (&$a) {
                $a .= "2";
                $next();
            },
            function ($req, $res, $next) use (&$a) {
                $a .= "3";
                $next();
            }
        ];

        $sessionHandlerSpy = $this->createMock(SessionManager::class);
        RouterH::routerPipe($handlers, new Request([], $sessionHandlerSpy), new Response(""));
        $this->assertEquals("123", $a);
    }

    public function test_it_should_not_pipe_request_response_and_next_if_next_is_not_called()
    {

        $a = "";

        $handlers = [
            function ($req, $res, $next) use (&$a) {
                $a .= "1";
                $next();
            },
            function ($req, $res, $next) use (&$a) {
                $a .= "2";
            },
            function ($req, $res, $next) use (&$a) {
                $a .= "3";
                $next();
            }
        ];

        $sessionHandlerSpy = $this->createMock(SessionManager::class);
        RouterH::routerPipe($handlers, new Request([], $sessionHandlerSpy), new Response(""));
        $this->assertEquals("12", $a);
    }
}
