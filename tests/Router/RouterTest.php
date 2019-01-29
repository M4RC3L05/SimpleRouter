<?php
namespace Tests\Router;

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

        $router = new Router();
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

        $res = $router->match("get", "/get");
        $this->assertEquals(1, \count($res));
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("get", $res);

        $res = $router->match("post", "/post");
        $this->assertEquals(1, \count($res));
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("post", $res);

        $res = $router->match("put", "/put");
        $this->assertEquals(1, \count($res));
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("put", $res);

        $res = $router->match("patch", "/patch");
        $this->assertEquals(1, \count($res));
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("patch", $res);

        $res = $router->match("DELETE", "/delete");
        $this->assertEquals(1, \count($res));
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("delete", $res);
    }

    public function test_it_should_display_not_found_path_if_path_not_exists()
    {

        $router = new Router();
        $router->get("/ola", function ($req, $res) use (&$tmp) {
            echo "get";
        });

        $router->use(function ($req, $res) use (&$tmp) {
            echo "notfound";
        });

        $res = $router->match("get", "/sds");
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("notfound", $res);
    }

    public function test_it_should_create_groups_routes()
    {

        $router = new Router();


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




        $res = $router->match("get", "/user/111");
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("/user/:id", $res);

        $res = $router->match("get", "/b/abc");
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("/b/:aaa", $res);

        $res = $router->match("get", "/b/abc/a");
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn("", "");
        $res = \ob_get_clean();
        $this->assertEquals("/b/:aaa/a", $res);

        $res = $router->match("get", "/b/ccc/aa/ggg");
        \ob_start();
        $fn = $res[0]->getHandler();
        $fn2 = $res[1]->getHandler();
        $fn("", "", function () {
        });
        $fn2("", "", function () {
        });
        $res = \ob_get_clean();
        $this->assertEquals("mid for /b/:aaa/aa/b/:aaa/aa/:vvv", $res);
    }
}
