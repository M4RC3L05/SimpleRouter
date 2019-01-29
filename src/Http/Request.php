<?php

namespace SimpleRouter\Http;


class Request
{
    public $params;
    public $query;
    public $body;
    public $files;
    public $request;
    public $cookies;
    public $headers;
    public $method;
    public $server;
    public $custom;


    public function __construct()
    {
        $this->params = [];
        $this->query = isset($_GET) ? $_GET : [];
        $this->body = isset($_POST) ? $_POST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->cookies = isset($_COOKIE) ? $_COOKIE : [];
        $this->headers = \function_exists("getallheaders") ? \getallheaders() : [];
        $this->method = $_SERVER["REQUEST_METHOD"] ?? "";
        $this->server = isset($_SERVER) ? $_SERVER : [];
        $this->custom = [];
        $this->session = $_SESSION ?? [];
    }
}
