<?php

namespace SimpleRouter\Router;

use function FPPHP\Lists\map;
use function FPPHP\Lists\zipAssoc;
use function FPPHP\Lists\flatten;


class Handler
{

    private $_verb;
    private $_pathRegex;
    private $_pathOriginal;
    private $_handler;
    private $_pathParams;
    private $_populatedPathParams;
    private $_basePath;

    public function __construct(string $_verb, string $pathOriginal, $handler, string $basePath)
    {
        $this->_verb = \strtoupper($_verb);
        $this->_handler = $handler;
        $this->_basePath = $basePath;
        $this->_pathOriginal = $this->_formatPath($pathOriginal);
        $this->_pathToRegex();
    }

    public function match(string $path) : bool
    {
        return \preg_match_all($this->_pathRegex, $path);
    }

    public function getHandler()
    {
        return $this->_handler;
    }

    public function populatePathParams(string $path)
    {
        if (\count($this->_pathParams) <= 0) return $this->_populatedPathParams;

        $paramData = [];

        \preg_match_all($this->_pathRegex, $path, $paramData);

        \array_shift($paramData);
        $paramData = flatten($paramData);

        if (\count($paramData) <= 0) return $this->_populatedPathParams;

        $this->_populatedPathParams = zipAssoc($this->_pathParams)($paramData);
    }

    public function getPathParams() : array
    {
        return $this->_populatedPathParams;
    }

    public function getPath() : string
    {
        return $this->_pathOriginal;
    }

    public function getPathRegex() : string
    {
        return $this->_pathRegex;
    }

    public function getVerb() : string
    {
        return $this->_verb;
    }

    private function _formatPath(string $path) : string
    {
        $finalPath = $path;

        if ($finalPath === "") $finalPath = $this->_basePath;

        if ($this->_basePath === "/")
            $finalPath = $finalPath = $path;

        else if ($this->_basePath !== "/" && $path === "/")
            $finalPath = $finalPath = $this->_basePath;
        else
            $finalPath = $this->_basePath . $path;

        if (!\substr($finalPath, -1) !== "/")
            $finalPath .= "/";

        return $finalPath;
    }

    private function _pathToRegex()
    {

        if ($this->_pathOriginal === "*") {
            $this->_pathParams = [];
            $this->_pathRegex = "/^" . \preg_replace("/\//", "\/", $this->_basePath) . ".*" . "\/" . "?$/";
            return;
        }

        $matchesToRouteParams = [];
        $pathParams = \preg_match_all("/\/\:[A-Za-z0-9_]+/", $this->_pathOriginal, $matchesToRouteParams);

        if (\array_key_exists(0, $matchesToRouteParams) && \count($matchesToRouteParams[0])) {
            $this->_pathParams = map(function ($x) {
                return \str_replace("/:", "", $x);
            })($matchesToRouteParams[0]);
        } else {
            $this->_pathParams = [];
        }

        $this->_pathRegex = "/^" . \preg_replace(["/\//", "/\/\:[A-Za-z0-9_]+/", "/\/\*/"], ["\/", "/([^\/]+?)", "/*.*"], $this->_pathOriginal) . "?$/";
        $this->_populatedPathParams = $this->_pathParams;
    }
}
    