<?php

namespace SimpleRouter\Http;

use SimpleRouter\Interfaces\IViewEngine;

class Response
{

    private $_viewEngine;
    private $_withData;

    public function __construct(IViewEngine $viewEngine = null)
    {
        $this->_viewEngine = $viewEngine;
    }

    public function status(int $code) : Response
    {
        http_response_code($code);
        return $this;
    }

    public function sendFile(string $path, bool $forceDownlod = false) : void
    {
        if (!\file_exists($path)) die();

        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = \finfo_file($finfo, $path);
        \finfo_close($finfo);
        $contentDisposition = $forceDownlod ? 'attachment' : 'inline';
        $basename = basename($path);
        $filesize = filesize($path);
        \header("Content-Type: {$mimetype}");
        \header("Content-Disposition: {$contentDisposition}; filename={$basename}");
        \header("Content-Length: {$filesize}");
        \header("Expires: 0");
        \header('Cache-Control: must-revalidate ');
        \header('Pragma: public');
        ob_clean();
        flush();
        \readfile($path);
    }

    public function redirect(string $to, bool $permanent = true) : void
    {
        \header('Location: ' . $to, true, $permanent ? 301 : 302);
        $this->end();
    }

    public function json($data) : void
    {
        \header('Content-Type: application/json;charset=UTF-8');
        echo json_encode($data);
    }

    public function sendString(string $data) : void
    {
        \header('Content-Type: plain/text;charset=UTF-8');
        echo $data;
    }

    public function sendHtml(string $data) : void
    {
        \header('Content-Type: text/html;charset=UTF-8');
        echo $data;
    }

    public function view(string $viewName)
    {

        if (!$this->_viewEngine || !isset($this->_viewEngine)) throw new Exception("No view engine resgistered");


        return $this->sendHtml($this->_viewEngine->renderView($viewName, $this->_withData ?? []));
    }

    public function withHeaders(array $headers) : Response
    {
        foreach ($headers as $key => $value) {
            \header("{$key}:{$value}");
        }

        return $this;
    }

    public function withViewData(array $data) : Response
    {
        $this->_withData = $data;
        return $this;
    }

    public function withCookies(array $data) : Response
    {
        foreach ($data as $key => $cookie) {
            \setcookie($cookie["name"], $cookie["value"] ?? null, $cookie["expires"] ?? null, $cookie["path"] ?? null, $cookie["domain"] ?? null, $cookie["secure"] ?? null, $cookie["httponly"] ?? null);
        }

        return $this;
    }

    public function end() : void
    {
        die();
    }
}
