<!DOCTYPE html>
<html lang="en">
<head>    
    @section('head')    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield("title")</title>
    @show
</head>
<body>
    @yield('content')
    @section('scripts')
    <script>console.log("ola");</script>
    @show
</body>
</html>