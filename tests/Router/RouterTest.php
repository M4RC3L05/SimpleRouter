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

    public function test_it_should_register_route_handlers()
    {

        $router = new Router();
        $router->get("/get", function ($req, $res) {
            echo "get";
        });

        $router->post("/post", function ($req, $res) {
            echo "post";
        });

        $router->put("/put", function ($req, $res) {
            echo "put";
        });

        $router->patch("/patch", function ($req, $res) {
            echo "patch";
        });

        $router->delete("/delete", function ($req, $res) {
            echo "delete";
        });

        $router->to(["get", "post"], "/", function ($req, $res) {
            echo "delete";
        });

        $this->assertEquals(8, \count($router->getHandlers()));
    }

    public function test_it_should_register_group_route_handlers()
    {
        $router = new Router();
        $router->group("/", function ($r) {
            $r->get("/get", function ($req, $res) {
                echo "get";
            });
        });

        $this->assertEquals(2, \count($router->getHandlers()));
    }
}
