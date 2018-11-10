<?php

namespace SimpleRouter\Router;

class Response
{
    private $_viewsDir;

    public function __construct(string $viewsDir)
    {
        $this->_viewsDir = $viewsDir;
    }
    public function status($code)
    {
        http_response_code($code);
        return $this;
    }

    public function sendFile($path, bool $forceDownlod = false)
    {
        if (!\file_exists($path)) die();

        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = \finfo_file($finfo, $path);
        \finfo_close($finfo);
        $contentDisposition = $forceDownlod ? 'attachment' : 'inline';
        $basename = basename($path);
        $filesize = filesize($path);
        header("Content-Type: {$mimetype}");
        header("Content-Disposition: {$contentDisposition}; filename={$basename}");
        header("Content-Length: {$filesize}");
        header("Expires: 0");
        header('Cache-Control: must-revalidate ');
        header('Pragma: public');
        ob_clean();
        flush();
        \readfile($path);
        die();
    }

    public function redirect($to, $permanent = true)
    {
        header('Location: ' . $to, true, $permanent ? 301 : 302);
        die();
    }

    public function json($data)
    {
        \header('Content-Type: application/json;charset=UTF-8');
        echo json_encode($data);
        die();
    }

    public function sendString(string $data)
    {
        \header('Content-Type: plain/text;charset=UTF-8');
        echo $data;
        die();
    }

    public function sendHtml(string $data)
    {
        \header('Content-Type: text/html; charset=UTF-8');
        echo $data;
        die();
    }

    public function view(string $viewName, $data = [])
    {
        $splitViewName = \explode(".", $viewName);

        if (!isset($this->_viewsDir)) throw new Exception("No views directory provided", 1);

        if (!\is_dir($this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0])) throw new Exception("No view found.", 1);

        if (!\is_file($this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0] . \DIRECTORY_SEPARATOR . $splitViewName[1] . ".view.php")) throw new Exception("No view found.", 1);

        require_once $this->_viewsDir . \DIRECTORY_SEPARATOR . $splitViewName[0] . \DIRECTORY_SEPARATOR . $splitViewName[1] . ".view.php";
        die();
    }
}