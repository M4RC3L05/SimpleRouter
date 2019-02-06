<?php

namespace SimpleRouter\Http;

use SimpleRouter\Router\Handler;
use SimpleRouter\Views\Interfaces\IViewEngineServiceProvider;
use SimpleRouter\Router\Router;


class RequestHandler
{
    private $_handlers;
    private $_request;
    private $_response;
    private $_errorOcurr;
    private $_error;

    public function __construct(array $handlers, IViewEngineServiceProvider $viewEngine = null)
    {

        $this->_handlers = $handlers;
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
        $next = (function (string $err = null) {
            if (isset($err) && !is_null($err)) {
                $this->onError(new \Exception($err));
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
            else {
                $numArgs = (new \ReflectionFunction(\Closure::fromCallable([new $class, $method])))->getNumberOfRequiredParameters();

                if ($numArgs > 3) return $next();

                return (new $class)->$method($this->_request, $this->_response, $next);
            }
        } else {



            if ($this->_errorOcurr) {
                return $properHandler($this->_error, $this->_request, $this->_response, $next);
            } else {
                $numArgs = (new \ReflectionFunction(\Closure::fromCallable($properHandler)))->getNumberOfRequiredParameters();

                if ($numArgs > 3) return $next();

                return $properHandler($this->_request, $this->_response, $next);
            }
        }
    }

    private function _matchCurrentRequestState(Handler $h)
    {
        if ($this->_errorOcurr) {
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
        }

        $currPath = \parse_url($this->_request->server["REQUEST_URI"])["path"];
        if (!$h->match($currPath)) return false;

        if ($h->getVerb() !== Router::MIDDLEWARE && $h->getVerb() !== Router::ALL_ROUTE && $h->getVerb() !== $this->_request->server["REQUEST_METHOD"]) return false;

        return true;
    }


    public function pipeHandlers()
    {
        if (\count($this->_handlers) <= 0) return;

        $now = array_pop($this->_handlers);

        if (!$this->_matchCurrentRequestState($now)) return $this->pipeHandlers();

        $now->populatePathParams(\parse_url($this->_request->server["REQUEST_URI"])["path"]);

        $properHandler = $this->_getProperHandler($now);

        if (!isset($properHandler) || \is_null($properHandler)) return $this->pipeHandlers();

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
        $this->_errorOcurr = true;
        $this->_error = $error;
    }
}