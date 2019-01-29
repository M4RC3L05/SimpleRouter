<?php
use SimpleRouter\SimpleRouter;
use App\Controllers\HomeController;
use App\Providers\PlatesViewProvider;

require_once __DIR__ . "/../vendor/autoload.php";


$app = new SimpleRouter();
$app->registerViewEngine(new PlatesViewProvider());

$app->router()->get("/", [HomeController::class, "home"]);

$app->handleRequest();