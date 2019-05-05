<?php

namespace Tests\Http;


use PHPUnit\Framework\TestCase;
use SimpleRouter\Router\Handler;


class HandlerTest extends TestCase
{
    public function test_it_should_create_a_new_request_object()
    {
        try {
            $obj = new Handler("get", "/", function () { }, "/");
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }

    public function test_it_should_match_path_correctly()
    {
        $h = new Handler("get", "/", function () { }, "/");
        $this->assertFalse($h->match("/abc"));
        $this->assertFalse($h->match(""));
        $this->assertTrue($h->match("/"));

        $h = new Handler("get", "/a/:b", function () { }, "/");
        $this->assertFalse($h->match("/abc"));
        $this->assertFalse($h->match(""));
        $this->assertFalse($h->match("/a"));
        $this->assertFalse($h->match("/a/"));
        $this->assertFalse($h->match("/a/12/12"));
        $this->assertTrue($h->match("/a/12"));
        $this->assertTrue($h->match("/a/12/"));
    }

    public function test_it_should_populate_path_params_correctly()
    {
        $h = new Handler("get", "/", function () { }, "/");
        $h->populatePathParams("/");
        $this->assertEquals([], $h->getPathParams());

        $h = new Handler("get", "/a/:b", function () { }, "/");
        $h->populatePathParams("/");
        $this->assertEquals(["b"], $h->getPathParams());
        $h->populatePathParams("/a/12");
        $this->assertEquals(["b" => "12"], $h->getPathParams());

        $h = new Handler("get", "/a/:b/c/:d", function () { }, "/");
        $h->populatePathParams("/");
        $this->assertEquals(["b", "d"], $h->getPathParams());
        $h->populatePathParams("/a");
        $this->assertEquals(["b", "d"], $h->getPathParams());
        $h->populatePathParams("/a/12");
        $this->assertEquals(["b", "d"], $h->getPathParams());
        $h->populatePathParams("/a/12/c");
        $this->assertEquals(["b", "d"], $h->getPathParams());
        $h->populatePathParams("/a/12/c/13");
        $this->assertEquals(["b" => "12", "d" => "13"], $h->getPathParams());
    }
}
