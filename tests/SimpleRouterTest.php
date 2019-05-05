<?php

namespace Tests\Http;


use PHPUnit\Framework\TestCase;
use SimpleRouter\SimpleRouter;
use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;


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
            ->get("", function ($req, $res) {
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
        $this->assertStringStartsWith("<h1>Anerrorocurr!</h1><p>Exception:err", \trim(\str_replace(["\r", "\n", " "], "", $res)));

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/next";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertStringStartsWith("<h1>Anerrorocurr!</h1><p>Exception:err2", \trim(\str_replace(["\r", "\n", " "], "", $res)));

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/next";

        $app->router()->use(function ($err, $req, $res, $next) {
            return $res->sendHtml("custom{$err}");
        });
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertStringStartsWith("customException:err2", \trim(\str_replace(["\r", "\n", " "], "", $res)));
    }

    public function test_it_should_handle_requests_without_handlers()
    {
        try {
            $app = new SimpleRouter();

            $_SERVER["REQUEST_METHOD"] = "GET";
            $_SERVER["REQUEST_URI"] = "/next";

            $app->handleRequest();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function test_it_should_append_the_data_to_the_views()
    {
        $app = new SimpleRouter();
        $app->registerViewEngine(
            new class implements IViewEngineServiceProvider
            {
                public function renderView(string $viewPath, array $data = []): string
                {
                    return \json_encode($data);
                }
            }
        );

        $app->router()
            ->use(function ($req, $res, $next) {
                $res->withViewData(["use" => "use"]);
                $next();
            })
            ->get("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("{\"use\":\"use\",\"\/\":\"\/\"}", $res);
    }

    public function test_it_should_allow_to_call_router_methods_directlly_from_SimpleRouter()
    {
        $app = new SimpleRouter();
        $app->registerViewEngine(
            new class implements IViewEngineServiceProvider
            {
                public function renderView(string $viewPath, array $data = []): string
                {
                    return \json_encode($data);
                }
            }
        );
        $app
            ->use(function ($req, $res, $next) {
                $res->withViewData(["use" => "use"]);
                $next();
            })
            ->get("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            })
            ->all("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            })
            ->post("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            })
            ->put("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            })
            ->delete("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            })
            ->patch("/", function ($req, $res) {
                return $res->withViewData(["/" => "/"])->view("");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";

        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("{\"use\":\"use\",\"\/\":\"\/\"}", $res);
    }

    public function test_it_should_match_on_groups()
    {
        $app = new SimpleRouter();
        $app->group("/", function ($r) {
            $r->post("/", function ($req, $res) {
                return $res->sendHtml("aaa");
            });
        });

        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("aaa", $res);
    }


    public function test_should_match_correct_path()
    {
        $app = new SimpleRouter();
        $app->group("/", function ($r) {
            $r
                ->use(function ($req, $res, callable $next) {
                    echo "middleware1";
                    $next();
                })
                ->to(["patch", "delete"], "", function ($req, $res) {
                    echo "oioioi";
                })
                ->get("", function ($req, $res, $next) {
                    echo "aaaa";
                    $next();
                }, function ($req, $res) use (&$tmp) {
                    echo "get /";
                })
                ->post("/", function ($req, $res) {
                    echo "post /";
                })
                ->all("/abc", function ($req, $res) {
                    echo "alll";
                })
                ->delete("/user/:id", function ($req, $res) use (&$tmp) {
                    \print_r("/user/:id");
                })
                ->put("/user/:id/a", function ($req, $res) use (&$tmp) {
                    \print_r("/user/:id/a");
                });
        })
            ->group(
                "/b/:aaa",
                function ($r) {
                    $r
                        ->get("/", function ($req, $res) use (&$tmp) {
                            \print_r("/b/:aaa");
                        })
                        ->put("/a", function ($req, $res) use (&$tmp) {
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
            )
            ->get("/throw", function () {
                throw new \Exception("error");
            })
            ->get("/throw-next", function ($req, $res, $next) {
                $next("next-error");
            })
            ->use(function ($req, $res) {
                return $res->status(404)->sendHTML("not found");
            })
            ->use(function ($error, $req, $res, $next) {
                return $res->status(500)->sendHtml("err: {$error->getMessage()}");
            });

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1aaaaget /", $res);

        $_SERVER["REQUEST_METHOD"] = "PATCH";
        $_SERVER["REQUEST_URI"] = "/";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1oioioi", $res);

        $_SERVER["REQUEST_METHOD"] = "DELETE";
        $_SERVER["REQUEST_URI"] = "/";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1oioioi", $res);

        $_SERVER["REQUEST_METHOD"] = "DELETE";
        $_SERVER["REQUEST_URI"] = "/user/12";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1/user/:id", $res);

        $_SERVER["REQUEST_METHOD"] = "PUT";
        $_SERVER["REQUEST_URI"] = "/user/232/a";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1/user/:id/a", $res);

        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/abc";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1alll", $res);

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/b/as4";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1/b/:aaa", $res);

        $_SERVER["REQUEST_METHOD"] = "PUT";
        $_SERVER["REQUEST_URI"] = "/b/as4/a";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1/b/:aaa/a", $res);

        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/b/sfrhds4g/aa";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1mid for /b/:aaa/aanot found", $res);

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/b/sfrhds4g/aa/vv";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1mid for /b/:aaa/aa/b/:aaa/aa/:vvv", $res);

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/throw";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1err: error", $res);

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/throw-next";
        \ob_start();
        $app->handleRequest();
        $res = \ob_get_clean();
        $this->assertEquals("middleware1err: next-error", $res);
    }
}
