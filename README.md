# SimpleRouter

[![Build Status](https://travis-ci.org/M4RC3L05/SimpleRouter.svg?branch=master)](https://travis-ci.org/M4RC3L05/SimpleRouter)

A simple router for php

## How to use

1. Instal via composer

    ```bash
        $ composer require m4rc3l05/simplerouter
    ```

2. Import to you code and use

    ```php
        // to use sessions
        session_start();

        // require autoload file
        require_once __DIR__ . '/../vendor/autoload.php';

        // Import simple router
        use SimpleRouter\Router;
        use SimpleRouter\Response;
        use SimpleRouter\Request;

        // Create a new Router
        $router = new Router();

        // especify you routes
        $router
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
        // 404 handler (must be the last)
        ->use(function (Request $request, Response $response) {
            return $response->status(404)->sendHtml("404, Not found!");
        })
        // ...
        // To match incomming requests
        ->match($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"])
    ```

## API

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

    -   Used to match the incomming request

    ```php
        public function match(string $method, string $path)
    ```

    -   Used to register the folder of the views (only php files are suported)

    ```php
        public function registerViews(string $viewsDir) : void
    ```

-   Request

    -   Paramas - Store the params of the request
    -   Query - Store the query params of the request
    -   Body - Store de body of the request
    -   Files - Store the files sended
    -   Session - Store session related information

        -   Initializes a new session if not already created

        ```php
            public function init() : void
        ```

        -   Set a data to the session

        ```php
            public function set(string $key, $value, bool $prefix = true) : void
        ```

        -   Removes data from session

        ```php
            public function remove(string $key, bool $prefix = true) : void
        ```

        -   Gets the data from the session

        ```php
            public function get(string $key, $prefix = true)
        ```

        -   Destroy all data from session tha has the session prefix

        ```php
            public function destroyByPrefix() : void
        ```

        -   Destroy all data from the session

        ```php
            public function destroyAll() : void
        ```

        -   Get the session id

        ```php
            public function id() : string
        ```

        -   Regenerate a new session

        ```php
            public function regenerate() : string
        ```

        -   Get all data from session

        ```php
            public function getSession() : array
        ```

        -   Check if the session has already started

        ```php
            public function is_session_started() : bool
        ```

    -   Request - Store the request information
    -   Headers - Store de headers of the request
    -   Method - Store the type of the method
    -   Server - Store server related information

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
        public function withCookies(array \$data) : Response
    ```

    -   Ends the response

    ```php
        public function end() : void
    ```
