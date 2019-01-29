<?php
use SimpleRouter\SimpleRouter;
use App\Controllers\HomeController;
use App\Providers\MustacheViewProvider;

require_once __DIR__ . "/../vendor/autoload.php";


$app = new SimpleRouter();
$app->registerViewEngine(new MustacheViewProvider());

$app->router()->get("/", [HomeController::class, "home"]);

$app->handleRequest();