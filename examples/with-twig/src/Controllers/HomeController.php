<?php

namespace App\Controllers;

class HomeController
{
    public function home($req, $res)
    {
        return $res
            ->status(200)
            ->withViewData([
                "title" => "home",
                "users" => [
                    "Rob",
                    "Anna",
                    "John"
                ]
            ])
            ->view("home/index");
    }
}