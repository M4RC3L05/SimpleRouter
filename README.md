# SimpleRouter

[![Build Status](https://travis-ci.org/M4RC3L05/SimpleRouter.svg?branch=master)](https://travis-ci.org/M4RC3L05/SimpleRouter)

A simple router for php

## How to use

1. Instal via composer

    ```bash
        $ composer require m4rc3l05/simplerouter
    ```

2. Import to you code and have fun

    ```php
        // require autoload file
        require_once __DIR__ . '/../vendor/autoload.php';

        // Import simple router
        use SimpleRouter;
        // Optional (just for intellicence)
        use SimpleRouter\Http\Request;
        use SimpleRouter\Http\Response;

        // Create a new Router
        $app = new SimpleRouter();

        // Register a view engine (see examples folder, optional)
        $app->registerViewEngine(new ViewEngine())

        // especify you routes
        // $app->router() ou jus call the method on the $app
        $app
        // Used for middlewares, you have to call
        // next to move to the next middleware
        ->use(function (Request $req, Response $res, callable $next) {
            echo ("<b>gloabl</b>");

            // must call the $next callable, otherwise
            // no more handlers will be called
            $next();
        })

        // GET method
        ->get("/", function (Request $req, Response $res, callable $next) {
            return $res->sendHtml("<h1>hi</h1>");
        })

        //Create group of routes
        ->group("/g", function (Router $r) {
            $r
                ->post("/", function (Request $req, Response $res, callable $next) {
                    // Send a file
                    return $res->sendFile("./a.txt");
                });
        })
        // Used to bind the handler to goups of methods
        ->to(["get", "post"], function (Request $req, Response $res, callable $next) {
            return $res->sendHtml("hello");
        })
        // 404 handler (must be the last)
        ->use(function (Request $request, Response $response) {
            return $response->status(404)->sendHtml("404, Not found!");
        })
        // Custom error handler, must provide the 4 arguments
        // (must be the last, if not present, the default error handler will be used)
        ->use(function ($error, Request $request, Response $response, $next) {
            return $response->status(500)->sendHtml("<h1>Error!!!</h1>{$error}");
        })
        // ...
        // To handle incomming requests
        $app->handleRequest();
    ```

## API

-   SimpleRouter

    -   Used to handle incoming requests

    ```php
        public function handleRequest()
    ```

    -   Used to register a view template engine (must implement IViewServiceProvider)

    ```php
        public function registerViewEngine(IViewEngineServiceProvider $engine)
    ```

    -   Gets the router instance

    ```php
        public function router(): Router
    ```

    -   All the methods from router are also available here

-   Router

    -   Used to register one or many middlewares (optionally the path as the first argument)

    ```php
        public function use() : Router
    ```

    -   Used to group routes

    ```php
        public function group(string $basePath, callable $function) : Router
    ```

    -   Used to register a get route and one or many handlers

    ```php
        public function get(string $route, ...$handlers) : Router
    ```

    -   Used to register a post route and one or many handlers

    ```php
        public function post(string $route, ...$handlers) : Router
    ```

    -   Used to register a put route and one or many handlers

    ```php
        public function put(string $route, ...$handlers) : Router
    ```

    -   Used to register a patch route and one or many handlers

    ```php
        public function patch(string $route, ...$handlers) : Router
    ```

    -   Used to register a delete route and one or many handlers

    ```php
        public function delete(string $route, ...$handlers) : Router
    ```

    -   Used to match the same handler to more than one methods

    ```php
        public function to(array $methods, string $path, ...$handlers) : Router
    ```

    -   Used to retreave all the request handlers

    ```php
        public function getHandlers()
    ```

-   Request

    -   Paramas - Store the params of the request
    -   Query - Store the query params of the request
    -   Body - Store de body of the request
    -   Files - Store the files sended
    -   Request - Store the request information
    -   Headers - Store de headers of the request
    -   Server - Store server related information
    -   \* - Can set data to any property

-   Response

    -   Set the status code of the response

    ```php
        public function status(int $code) : Response
    ```

    -   Send a file has the response

    ```php
        public function sendFile(string $path, bool $forceDownlod = false) : void
    ```

    -   Redirect to other page

    ```php
        public function redirect(string $to, bool $permanent = true) : void
    ```

    -   Sends json data as response

    ```php
        public function json($data) : void
    ```

    -   Sends a string has data of the response

    ```php
        public function sendString(string $data) : void
    ```

    -   Sends html as the data of the response

    ```php
        public function sendHtml(string $data) : void
    ```

    -   Render especific view

    ```php
        public function view(string $viewName)
    ```

    -   Set the response headers

    ```php
        public function withHeaders(array $headers) : Response
    ```

    -   Pass data to views

    ```php
        public function withViewData(array $data) : Response
    ```

    -   Sets the response cookies

    ```php
        public function withCookies(array $data) : Response
    ```
