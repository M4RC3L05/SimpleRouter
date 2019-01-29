<?php

namespace SimpleRouter\Http;

use SimpleRouter\Router\Handler;
use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;
use function FPPHP\Lists\filter;


class RequestHandler
{
    private $_handlers;
    private $_currPath;
    private $_request;
    private $_response;
    private $_errorOcurr;
    private $_error;

    public function __construct(array $handlers, string $currPath, IViewEngineServiceProvider $viewEngine = null)
    {

        $this->_handlers = $handlers;
        $this->_currPath = $currPath;
        $this->_request = new Request();
        $this->_response = new Response($viewEngine);
        $this->_errorOcurr = false;
        $this->_error = null;
    }

    private function _getProperHandler(Handler $handlerWrapper)
    {
        $handler = $handlerWrapper->getHandler();

        if (\is_callable($handler)) {
            return $handler;
        } else if (\is_array($handler) && \count($handler) === 2) {
            return $handler;
        } else {
            return null;
        }
    }

    private function _dispatch($properHandler)
    {

        $next = (function ($err = null) {
            if (isset($err) && !is_null($err)) {
                $this->onError($err);
                return $this->pipeHandlers();
            } else {
                return $this->pipeHandlers();
            }
        });

        if (\is_array($properHandler)) {
            $class = $properHandler[0];
            $method = $properHandler[1];

            if ($this->_errorOcurr)
                return (new $class)->$method($this->_error, $this->_request, $this->_response, $next);
            else
                return (new $class)->$method($this->_request, $this->_response, $next);
        } else {

            if ($this->_errorOcurr) {
                return $properHandler($this->_error, $this->_request, $this->_response, $next);
            } else
                return $properHandler($this->_request, $this->_response, $next);
        }
    }


    public function pipeHandlers()
    {

        if (\count($this->_handlers) <= 0) return;

        $now = array_pop($this->_handlers);

        $properHandler = $this->_getProperHandler($now);



        if (!isset($properHandler) || \is_null($properHandler)) return $this->pipeHandlers();

        if (!$this->_errorOcurr)
            $this->_request->params = $now->getPathParams();

        try {
            return $this->_dispatch($properHandler);
        } catch (\Exception $e) {
            $this->onError($e);
            $this->pipeHandlers();
        }
    }

    private function onError($error)
    {
        if ($this->_errorOcurr) return;

        $this->_handlers = filter(function ($h) {
            $properHandler = $this->_getProperHandler($h);

            if (!$properHandler) return false;

            if (\is_array($properHandler)) {
                $class = $properHandler[0];
                $method = $properHandler[1];

                $numArgs = (new \ReflectionFunction(\Closure::fromCallable([new $class, $method])))->getNumberOfRequiredParameters();
                if ($numArgs <= 3) return false;

            } else {
                $numArgs = (new \ReflectionFunction(\Closure::fromCallable($properHandler)))->getNumberOfRequiredParameters();
                if ($numArgs <= 3) return false;
            }

            return true;
        })($this->_handlers);

        $this->_errorOcurr = true;
        $this->_error = $error;
    }
}