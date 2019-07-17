<?php

namespace SimpleRouter\Http;


class Request
{
    private $_extras;
    public $params;
    public $query;
    public $body;
    public $files;
    public $request;
    public $cookies;
    public $headers;
    public $server;


    public function __construct()
    {
        $this->params = [];
        $this->query = isset($_GET) ? $_GET : [];
        $this->body = isset($_POST) ? $_POST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->cookies = isset($_COOKIE) ? $_COOKIE : [];
        $this->headers = \function_exists("getallheaders") ? getallheaders() : [];
        $this->server = isset($_SERVER) ? $_SERVER : [];
        $this->_extras = [];
    }

    public function __get($name)
    {
        if (!\array_key_exists($name, $this->_extras)) return null;

        return $this->_extras[$name];
    }

    public function __set($name, $value)
    {
        $this->_extras[$name] = $value;
    }
}
