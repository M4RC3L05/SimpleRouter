<?php

namespace SimpleRouter\Session\Interfaces;

interface ISessionServiceProvider
{
    public function set(string $key, $value) : void;
    public function remove(string $key) : void;
    public function get(string $key);
    public function destroyAll() : void;
    public function getSession() : array;
}