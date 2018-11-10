<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Router;


class RouterTest extends TestCase
{
    public function test_it_should_create_a_router()
    {
        try {
            $router = new Router();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function test_it_should_match_routes()
    {
        $tmp = "";

        $router = new Router();
        $router->get("/get", function ($req, $res) use (&$tmp) {
            $tmp .= "get";
        });

        $router->post("/post", function ($req, $res) use (&$tmp) {
            $tmp .= "post";
        });

        $router->put("/put", function ($req, $res) use (&$tmp) {
            $tmp .= "put";
        });

        $router->patch("/patch", function ($req, $res) use (&$tmp) {
            $tmp .= "patch";
        });

        $router->delete("/delete", function ($req, $res) use (&$tmp) {
            $tmp .= "delete";
        });

        $router->match("app.com", "get", "app.com/get");
        $router->match("app.com", "post", "app.com/post");
        $router->match("app.com", "put", "app.com/put");
        $router->match("app.com", "patch", "app.com/patch");
        $router->match("app.com", "DELETE", "app.com/delete");

        $this->assertEquals("getpostputpatchdelete", $tmp);
    }

    public function test_it_should_display_not_found_path_if_path_not_exists()
    {
        $tmp = "";

        $router = new Router();
        $router->get("/ola", function ($req, $res) use (&$tmp) {
            $tmp .= "get";
        });

        \ob_start();
        $router->match("app.com", "get", "app.com/sds");
        $notfound = \ob_get_clean();

        $this->assertEquals("Not found", $notfound);

        $router = new Router();
        $router->get("/ola", function ($req, $res) use (&$tmp) {
            $tmp .= "get";
        });

        $router->notFound("/404", function ($req, $res) use (&$tmp) {
            $tmp .= "notfound";
        });

        $router->match("app.com", "get", "app.com/sds");
        $this->assertEquals("notfound", $tmp);
    }

    public function test_it_should_pass_middlewares_before_route_handler()
    {
        $tmp = "";

        $router = new Router();
        $router->get("/ola", function ($req, $res, $next) use (&$tmp) {
            $tmp .= "middleware";

            $next();
        }, function ($req, $res) use (&$tmp) {
            $tmp .= "get";
        });

        $router->use(function ($req, $res, $next) use (&$tmp) {
            $tmp .= "start";
            $next();
        });

        $router->match("app.com", "get", "app.com/ola");

        $this->assertEquals("startmiddlewareget", $tmp);
    }

    public function test_it_should_match_routes_with_params_and_return_them_as_response_params()
    {
        $tmp = "";
        $router = new Router();

        $router->get("/user/:id", function ($req, $res) use (&$tmp) {
            $tmp .= $req->params["id"];
        });

        $router->get("/user/:id/prodile/:profid", function ($req, $res) use (&$tmp) {
            $tmp .= $req->params["id"];
            $tmp .= $req->params["profid"];
        });

        $router->get("/user/:id/prodile/:profid/comment/:comid", function ($req, $res) use (&$tmp) {
            $tmp .= $req->params["id"];
            $tmp .= $req->params["profid"];
            $tmp .= $req->params["comid"];
        });

        $router->match("", "get", "/user/123");
        $this->assertEquals("123", $tmp);
        $tmp = "";

        $router->match("", "get", "/user/123/prodile/111");
        $this->assertEquals("123111", $tmp);
        $tmp = "";

        $router->match("", "get", "/user/123/prodile/111/comment/321");
        $this->assertEquals("123111321", $tmp);
        $tmp = "";
    }
}
