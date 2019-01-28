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

        $router = new Router("app.com");
        $router->get("/get", function ($req, $res) use (&$tmp) {
            echo "get";
        });

        $router->post("/post", function ($req, $res) use (&$tmp) {
            echo "post";
        });

        $router->put("/put", function ($req, $res) use (&$tmp) {
            echo "put";
        });

        $router->patch("/patch", function ($req, $res) use (&$tmp) {
            echo "patch";
        });

        $router->delete("/delete", function ($req, $res) use (&$tmp) {
            echo "delete";
        });

        \ob_start();
        $router->match("get", "/get");
        $router->match("post", "/post");
        $router->match("put", "/put");
        $router->match("patch", "/patch");
        $router->match("DELETE", "/delete");
        $res = \ob_get_clean();
        $this->assertEquals("getpostputpatchdelete", $res);
    }

    public function test_it_should_display_not_found_path_if_path_not_exists()
    {

        $router = new Router("app.com");
        $router->get("/ola", function ($req, $res) use (&$tmp) {
            echo "get";
        });

        $router->use(function ($req, $res) use (&$tmp) {
            echo "notfound";
        });

        \ob_start();
        $router->match("get", "/sds");
        $res = \ob_get_clean();
        $this->assertEquals("notfound", $res);
    }

    public function test_it_should_match_routes_with_params_and_return_them_as_response_params()
    {
        $router = new Router("app.com");

        $router->get("/user/:id", function ($req, $res) use (&$tmp) {
            echo $req->params["id"];
        });

        $router->get("/user/:id/prodile/:profid", function ($req, $res) use (&$tmp) {

            echo $req->params["id"];
            echo $req->params["profid"];
        });

        $router->get("/user/:id/prodile/:profid/comment/:comid", function ($req, $res) use (&$tmp) {
            echo $req->params["id"];
            echo $req->params["profid"];
            echo $req->params["comid"];
        });

        \ob_start();
        $router->match("get", "/user/123");
        $res = \ob_get_clean();
        $this->assertEquals("123", $res);

        \ob_start();
        $router->match("get", "/user/123/prodile/111");
        $res = \ob_get_clean();
        $this->assertEquals("123111", $res);

        \ob_start();
        $router->match("get", "/user/123/prodile/111/comment/321");
        $res = \ob_get_clean();
        $this->assertEquals("123111321", $res);
    }

    public function test_it_should_create_groups_routes()
    {

        $router = new Router("app.com");


        $router
            ->get("/user/:id", function ($req, $res) use (&$tmp) {
                \print_r("/user/:id");
            })
            ->group(
                "/b/:aaa",
                function ($r) {
                    $r
                        ->get("/", function ($req, $res) use (&$tmp) {
                            \print_r("/b/:aaa");
                        })
                        ->get("/a", function ($req, $res) use (&$tmp) {
                            \print_r("/b/:aaa/a");
                        })
                        ->group(
                            "/aa",
                            function ($r) {
                                $r
                                    ->use(function ($req, $res, $next) {
                                        \print_r("mid for /b/:aaa/aa");
                                        $next();
                                    })
                                    ->get(
                                        "/:vvv",
                                        function ($req, $res) use (&$tmp) {
                                            \print_r("/b/:aaa/aa/:vvv");
                                        }
                                    );
                            }

                        );
                }
            );




        \ob_start();
        $router->match("get", "/user/111");
        $router->match("get", "/b/abc");
        $router->match("get", "/b/abc/a");
        $router->match("get", "/b/ccc/aa/ggg");
        $tmp = \ob_get_clean();
        $this->assertEquals("/user/:id/b/:aaa/b/:aaa/amid for /b/:aaa/aa/b/:aaa/aa/:vvv", $tmp);
    }
}
