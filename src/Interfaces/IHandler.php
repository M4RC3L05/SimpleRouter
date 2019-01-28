<?php

namespace SimpleRouter\Interfaces;

interface IHandler
{
    public function match(string $regex) : bool;
    public function getHandler();
    public function populatePathParams(string $path);
    public function getPathParams() : array;
    public function getPath() : string;
    public function getPathRegex() : string;
    public function getVerb() : string;
}