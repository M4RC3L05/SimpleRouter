<?php

namespace SimpleRouter\Router\Interfaces;

interface IHandler
{
    public function match(string $regex) : bool;
    public function getHandler() : callable;
    public function getPathParams(string $path) : array;
    public function getPath() : string;
    public function getPathRegex() : string;
    public function getVerb() : string;
}