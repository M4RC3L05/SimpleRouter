<?php

namespace SimpleRouter\Router;

class Request
{
    public $params;
    public $query;
    public $body;
    public $files;
    public $request;
    public $session;
    public $server;
    public $cookies;

    public function __construct(array $params = [], SessionManager $session)
    {
        $this->params = isset($params) ? $params : [];
        $this->query = isset($_GET) ? $_GET : [];
        $this->body = isset($_POST) ? $_POST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->session = $session;
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->server = isset($_SERVER) ? $_SERVER : [];
        $this->cookies = isset($_COOKIE) ? $_COOKIE : [];
    }
}
