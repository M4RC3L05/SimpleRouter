<?php

namespace SimpleRouter\Router\Interfaces;

interface IHandler
{
    public function match(string $regex) : bool;
}