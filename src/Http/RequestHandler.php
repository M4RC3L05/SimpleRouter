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

    private function _dispatch(Handler $handler)
    {
        $next = (function (string $err = null) {
            if (isset($err) && !is_null($err)) {
                $this->onError(new \Exception($err));
                return $this->pipeHandlers();
            } else {
                return $this->pipeHandlers();
            }
        });

        return $handler->callHandler($this->_errorOcurr, $this->_error, $this->_request, $this->_response, $next);
    }

    private function _matchCurrentRequestState(Handler $h)
    {
        if ($this->_errorOcurr)
            if ($h->getNumOfHandlerParams() <= 3) return false;
            else return true;

        if ($h->getNumOfHandlerParams() > 3) return false;

        $currPath = \parse_url($this->_request->server["REQUEST_URI"])["path"];

        if (!$h->match($currPath)) return false;

        if ($h->getVerb() !== Router::MIDDLEWARE && $h->getVerb() !== Router::ALL_ROUTE && $h->getVerb() !== strtoupper($this->_request->server["REQUEST_METHOD"])) return false;

        return true;
    }


    public function pipeHandlers()
    {
        if (\count($this->_handlers) <= 0) return;

        $now = array_pop($this->_handlers);

        if (!$this->_matchCurrentRequestState($now)) return $this->pipeHandlers();

        $now->populatePathParams(\parse_url($this->_request->server["REQUEST_URI"])["path"]);

        $this->_request->params = $now->getPathParams();

        try {
            return $this->_dispatch($now);
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
