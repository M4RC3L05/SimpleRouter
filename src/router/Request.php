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

    public function __construct(array $params = [])
    {
        $this->params = isset($params) ? $params : [];
        $this->query = isset($_GET) ? $_GET : [];
        $this->body = isset($_POST) ? $_POST : [];
        $this->files = isset($_FILES) ? $_FILES : [];
        $this->session = isset($_SESSION) ? $_SESSION : [];
        $this->request = isset($_REQUEST) ? $_REQUEST : [];
        $this->server = isset($_SERVER) ? $_SERVER : [];
        $this->cookies = isset($_COOKIE) ? $_COOKIE : [];
    }

    public function setSession($key, $value)
    {
        if (!isset($_SESSION)) return;

        $_SESSION[$key] = $value;
        $this->session = $_SESSION;
    }

    public function removeFromSession($key)
    {
        if (!isset($_SESSION)) return;
        if (!\array_key_exists($key, $_SESSION) || !\array_key_exists($key, $this->session)) return;

        unset($_SESSION[$key]);
        $this->session = $_SESSION;
    }
}