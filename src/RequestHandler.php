<?php

namespace SimpleRouter;

use SimpleRouter\Interfaces\IHandler;
use SimpleRouter\Interfaces\IViewEngine;


class RequestHandler
{
    private $_handlers;
    private $_currPath;
    private $_request;
    private $_response;

    public function __construct(array $handlers, string $currPath, SessionManager $sessionManager, IViewEngine $viewEngine = null)
    {

        $this->_handlers = $handlers;
        $this->_currPath = $currPath;
        $this->_request = new Request([], $sessionManager);
        $this->_response = new Response($viewEngine);
    }

    private function _getProperHandler(IHandler $handlerWrapper)
    {
        $handler = $handlerWrapper->getHandler();

        if (\is_callable($handler)) {
            return $handler;
        } else if (\is_array($handler) || ($handler instanceof Traversable)) {
            $class = $handler[0];
            $method = $handler[1];
            return function ($request, $response, $next) use ($class, $method) {
                (new $class)->$method($request, $response, $next);
            };
        } else {
            return null;
        }
    }


    public function pipeHandlers()
    {

        if (\count($this->_handlers) <= 0) return;

        $now = array_pop($this->_handlers);

        $properHandler = $this->_getProperHandler($now);

        if (!isset($properHandler) || \is_null($properHandler)) return $this->pipeHandlers();

        $next = \count($this->_handlers) <= 0 ? (function ($err = null) {
            if (isset($err) && !is_null($err))
                throw new \Exception($err, 1);
        }) : \Closure::fromCallable(function ($err = null) {
            if (isset($err) && !is_null($err)) {
                throw new \Exception($err, 1);
            } else {
                return $this->pipeHandlers();
            }
        })->bindTo($this);

        $this->_request->params = $now->getPathParams();
        return $properHandler($this->_request, $this->_response, $next);
    }
}