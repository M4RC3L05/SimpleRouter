<?php

namespace SimpleRouter\Views\Interfaces;

interface IViewEngineServiceProvider
{
    public function renderView(string $viewPath, array $data = []): string;
}
