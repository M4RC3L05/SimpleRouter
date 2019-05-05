<?php
namespace Tests\Http;


use PHPUnit\Framework\TestCase;
use SimpleRouter\Http\Response;
use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;


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
        $res = new Response();

        \ob_start();
        $res->json(["a" => 1]);

        $r = \ob_get_clean();
        $this->assertEquals("{\"a\":1}", $r);
    }

    public function test_it_should_make_a_string_html_response()
    {
        $res = new Response();

        ob_start();
        $res->sendString("ola");
        $string = ob_get_clean();
        ob_start();
        $res->sendHtml("<h1>ola</h1>");
        $html = ob_get_clean();
        $this->assertEquals("ola", $string);
        $this->assertEquals("<h1>ola</h1>", $html);
    }

    public function test_it_should_throw_if_not_view_engine()
    {
        $res = new Response();

        $this->expectException(\Exception::class);
        $res->view("a");
    }

    public function test_it_should_render_a_view()
    {
        $resMock = $this->getMockBuilder(Response::class)
            ->setConstructorArgs([
                new class implements IViewEngineServiceProvider
                {
                    public function renderView(string $viewPath, array $data = []): string
                    {
                        return "abc";
                    }
                }
            ])
            ->setMethodsExcept(["view"])
            ->setMethods(["end"])
            ->getMock();

        \ob_start();
        $resMock->view("popo");
        $res = \ob_get_clean();
        $this->assertEquals("abc", $res);
    }

    public function test_it_should_set_the_status_code_of_the_response()
    {
        $res = new Response();
        $result = $res->status(203);

        $this->assertEquals($res, $result);
        $this->assertEquals(203, \http_response_code());
    }

    public function test_it_should_thow_if_path_is_not_a_file()
    {
        $resMock = $this->getMockBuilder(Response::class)
            ->setMethodsExcept(["sendFile"])
            ->setMethods(["end"])
            ->getMock();

        $this->expectException(\Exception::class);
        $resMock->sendFile(__DIR__ . "/../files/as.txt");
    }
}
