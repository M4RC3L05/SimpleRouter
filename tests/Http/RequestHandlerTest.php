<?php
namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Handler;
use SimpleRouter\Http\RequestHandler;

class RequestHandlerTest extends TestCase
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

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        $re = new RequestHandler(\array_reverse($handlers));
        \ob_start();
        $re->pipeHandlers();
        $res = \ob_get_clean();
        $this->assertEquals("123", $res);
    }

    public function test_it_should_not_pipe_request_response_and_next_if_next_is_not_called()
    {
        $handlers = [
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "1";
                $next();
            }, ""),
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "2";
            }, ""),
            new Handler("get", "/", function ($req, $res, $next) use (&$a) {
                echo "3";
                $next();
            }, "")
        ];

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        $re = new RequestHandler(\array_reverse($handlers));
        \ob_start();
        $re->pipeHandlers();
        $res = \ob_get_clean();
        $this->assertEquals("12", $res);
    }
}
