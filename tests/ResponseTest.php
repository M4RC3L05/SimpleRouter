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
            $viewEgMock = $this->createMock(ViewEngine::class);
            $response = new Response($viewEgMock);
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }

    public function test_it_should_make_a_json_response()
    {
        $viewEgMock = $this->createMock(ViewEngine::class);
        $response = new Response($viewEgMock);
        ob_start();
        $response->json(["a" => 1]);
        $json = ob_get_clean();
        $this->assertEquals("{\"a\":1}", $json);
    }

    public function test_it_should_make_a_string_html_response()
    {
        $viewEgMock = $this->createMock(ViewEngine::class);
        $response = new Response($viewEgMock);
        ob_start();
        $response->sendString("ola");
        $string = ob_get_clean();
        ob_start();
        $response->sendHtml("<h1>ola</h1>");
        $html = ob_get_clean();
        $this->assertEquals("ola", $string);
        $this->assertEquals("<h1>ola</h1>", $html);
    }

    public function test_it_should_render_a_view()
    {

        $response = new Response(new ViewEngine(__DIR__ . "/views"));
        \ob_start();
        $response->withViewData(["users" => ["João", "Ana"]])->view("home/index.twig");
        $res = \ob_get_clean();
        $this->assertEquals(\str_replace(["\n", "\r", " "], "", "
        <!DOCTYPE html>
        <html lang=\"en\">
        <head>
                        <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                <title>Home</title>
            
            <style type=\"text/css\">
                .important { color: #336699; }
            </style>
        </head>
        <body>
                <h1>Index</h1>
            <p>Users</p>
                    <ul>
                            <li>João</li>
                            <li>Ana</li>
                        </ul>
            
            </body>
        </html>"), \str_replace(["\n", " "], "", $res));
    }
}
