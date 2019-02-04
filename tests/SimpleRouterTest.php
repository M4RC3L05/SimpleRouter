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
        $app->registerViewEngine(new class implements IViewEngineServiceProvider
        {
            public function renderView(string $viewPath, array $data = [])
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
        $app->registerViewEngine(new class implements IViewEngineServiceProvider
        {
            public function renderView(string $viewPath, array $data = [])
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
}
