<?php

namespace SimpleRouter\Interfaces;

interface IViewEngine
{
    public function renderView(string $viewPath, array $data = []);
}