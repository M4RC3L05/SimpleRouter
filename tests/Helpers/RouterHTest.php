<?php
namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Helpers\RouterH;
use SimpleRouter\Router\Request;
use SimpleRouter\Router\Response;
use SimpleRouter\Router\SessionManager;
use SimpleRouter\Router\Handler;

class RouterHTest extends TestCase
{
    public function test_it_should_pipe_request_response_and_next_into_handlers()
    {

        $handlers = [
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "1";
                $next();
            }, ""),
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "2";
                $next();
            }, ""),
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "3";
                $next();
            }, "")
        ];

        $sessionHandlerSpy = $this->createMock(SessionManager::class);
        \ob_start();
        RouterH::routerPipe(\array_reverse($handlers), new Request([], $sessionHandlerSpy), new Response(""), "");
        $res = \ob_get_clean();
        $this->assertEquals("123", $res);
    }

    public function test_it_should_not_pipe_request_response_and_next_if_next_is_not_called()
    {
        $a = "";

        $handlers = [
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                $a .= "1";
                $next();
            }, ""),
            new Handler("", "", function ($req, $res, $next) use (&$a) {
                $a .= "2";
            }, ""),
            new Handler("", "", function ($req, $res, $next) use (&$a) {
                $a .= "3";
                $next();
            }, "")
        ];

        $sessionHandlerSpy = $this->createMock(SessionManager::class);
        RouterH::routerPipe(\array_reverse($handlers), new Request([], $sessionHandlerSpy), new Response(""), "");
        $this->assertEquals("12", $a);
    }
}
