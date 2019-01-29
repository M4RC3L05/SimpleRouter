<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use SimpleRouter\Response;
use SimpleRouter\ViewEngine;


class ResponseTest extends TestCase
{

    public function test_it_should_create_a_response_object()
    {
        try {
            $response = new Response();
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }

    public function test_it_should_make_a_json_response()
    {
        $response = new Response();
        ob_start();
        $response->json(["a" => 1]);
        $json = ob_get_clean();
        $this->assertEquals("{\"a\":1}", $json);
    }

    public function test_it_should_make_a_string_html_response()
    {
        $response = new Response();
        ob_start();
        $response->sendString("ola");
        $string = ob_get_clean();
        ob_start();
        $response->sendHtml("<h1>ola</h1>");
        $html = ob_get_clean();
        $this->assertEquals("ola", $string);
        $this->assertEquals("<h1>ola</h1>", $html);
    }
}
