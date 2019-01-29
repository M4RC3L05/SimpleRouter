<?php

namespace Tests\Http;


use PHPUnit\Framework\TestCase;
use SimpleRouter\SimpleRouter;


class SimpleRouterTest extends TestCase
{
    public function test_it_should_create_a_new_simple_router_object()
    {
        try {
            $obj = new SimpleRouter();
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }

    public function test_it_should_handle_errors_on_route_handlers()
    {
        $app = new SimpleRouter();
        $app->router()
            ->get("/", function ($req, $res) {
                throw new \Exception("err");
            })
            ->get("/next", function ($req, $res, $next) {
                return $next("err2");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertStringStartsWith("<h1>An error ocurr!</h1>", \trim(\str_replace(["\r", "\n"], "", $res)));

        $app = new SimpleRouter();
        $app->router()
            ->get("/", function ($req, $res) {
                throw new \Exception("err");
            })
            ->get("/next", function ($req, $res, $next) {
                return $next("err2");
            })
            ->use(function ($err, $req, $res, $next) {
                return $res->status(500)->sendHtml("custom error");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/next";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("custom error", \trim(\str_replace(["\r", "\n"], "", $res)));

        $app = new SimpleRouter();
        $app->router()
            ->get("/", function ($req, $res) {
                throw new \Exception("err");
            })
            ->get("/next", function ($req, $res, $next) {
                return $next("err2");
            })
            ->use(function ($err, $req, $res, $next) {
                throw new \Exception("Error Processing Request", 1);

                return $res->status(500)->sendHtml("custom error");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/next";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertStringStartsWith("<h1>An error ocurr!</h1>", \trim(\str_replace(["\r", "\n"], "", $res)));
    }
}
