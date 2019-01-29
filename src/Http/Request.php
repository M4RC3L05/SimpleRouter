<?php

namespace SimpleRouter\Http;

use SimpleRouter\Session\Interfaces\ISessionServiceProvider;


class Request
{
    public $params;
    public $query;
    public $body;
    public $files;
    public $request;
    public $session;
    public $cookies;
    public $headers;
    public $method;
    public $server;

    public function __construct(array $params = [], ISessionServiceProvider $session)
    {
        $this->params = isset($params) ? $params : [];
        $this->query = isset($_GET) ? $_GET : [];
        $this->body = isset($_POST) ? $_POST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->session = $session;
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->cookies = isset($_COOKIE) ? $_COOKIE : [];
        $this->headers = \function_exists("getallheaders") ? \getallheaders() : [];
        $this->method = $_SERVER["REQUEST_METHOD"] ?? "";
        $this->server = isset($_SERVER) ? $_SERVER : [];
    }
}
