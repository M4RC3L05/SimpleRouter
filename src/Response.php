<?php

namespace SimpleRouter;


class Response
{
    private $_viewsDir;
    private $_widthData;

    public function __construct(string $viewsDir = null)
    {
        $this->_viewsDir = $viewsDir;
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
        $splitViewName = \explode(".", $viewName);

        if (!isset($this->_viewsDir)) throw new \Exception("No views directory provided", 1);

        if (!\is_dir($this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0])) throw new \Exception("No view found.", 1);

        if (!\is_file($this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0] . \DIRECTORY_SEPARATOR . $splitViewName[1] . ".view.php")) throw new \Exception("No view found.", 1);
        $strRequire = $this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0] . \DIRECTORY_SEPARATOR . $splitViewName[1] . ".view.php";

        \extract($this->_widthData);
        \ob_start();
        require($strRequire);
        $content = \ob_get_clean();
        return $this->sendHtml($content);
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
        $this->_widthData = $data;
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
