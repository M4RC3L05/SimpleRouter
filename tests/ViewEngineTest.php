<?php

namespace Tests;


use PHPUnit\Framework\TestCase;
use SimpleRouter\ViewEngine;

class ViewEngineTest extends TestCase
{
    public function test_it_should_create_a_new_view_engine_object()
    {
        try {
            new ViewEngine(__DIR__ . "/views");
            $this->assertEquals(1, 1);
        } catch (\Exception $e) {
            $this->assertEquals(1, 2);
        }
    }

    public function test_it_should_render_a_view()
    {
        $ve = new ViewEngine(__DIR__ . "/views");

        $res = $ve->renderView("home/index.twig", ["users" => ["João", "Ana"]]);
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
