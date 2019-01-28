<?php

namespace SimpleRouter\Interfaces;

use SimpleRouter\Router;

interface IRouter
{
    public function use() : Router;
    public function group(string $basePath, callable $function) : Router;
    public function get(string $path, ...$handlers) : Router;
    public function post(string $path, ...$handlers) : Router;
    public function put(string $path, ...$handlers) : Router;
    public function patch(string $path, ...$handlers) : Router;
    public function delete(string $path, ...$handlers) : Router;
    public function all(string $path, ...$handlers) : Router;
    public function match(string $method, string $path);
    public function registerViews(string $viewsDir) : void;
}