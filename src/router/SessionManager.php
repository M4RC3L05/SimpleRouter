<?php

namespace SimpleRouter\Router;

class SessionManager
{
    private const PREFIX = "sr_session@@";

    public function __construct()
    {
        $this->init();
    }

    public function init() : void
    {
        if (!$this->is_session_started()) {
            session_start();
        }
    }

    public function set(string $key, $value, bool $prefix = true) : void
    {
        if (!$this->is_session_started()) return;
        if (!isset($_SESSION)) return;

        if ($prefix) $_SESSION[SessionManager::PREFIX . $key] = $value;
        else $_SESSION[$key] = $value;
    }

    public function remove(string $key, bool $prefix = true) : void
    {
        if (!$this->is_session_started()) return;
        if (!isset($_SESSION)) return;

        if ($prefix) {
            if (!\array_key_exists(SessionManager::PREFIX . $key, $_SESSION)) return;
            unset($_SESSION[SessionManager::PREFIX . $key]);
        } else {
            if (!\array_key_exists($key, $_SESSION)) return;
            unset($_SESSION[$key]);
        }
    }

    public function get(string $key, $prefix = true)
    {
        if (!$this->is_session_started()) return;
        if (!isset($_SESSION)) return;

        if ($prefix && \array_key_exists(SessionManager::PREFIX . $key, $_SESSION)) return $_SESSION[SessionManager::PREFIX . $key];
        else if (!$prefix && \array_key_exists($key, $_SESSION)) return $_SESSION[$key];
        else return null;
    }

    public function destroyByPrefix() : void
    {
        if (!$this->is_session_started()) return;
        if (!isset($_SESSION)) return;

        foreach ($_SESSION as $key => $value) {
            $explodekey = \explode("@@", $key);

            if (\count($explodekey) <= 1) continue;

            if ($explodekey[0] . "@@" !== SessionManager::PREFIX) continue;

            unset($_SESSION[$key]);
        }
    }

    public function destroyAll() : void
    {
        if (!$this->is_session_started()) return;
        if (!isset($_SESSION)) return;

        \session_unset();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        \session_destroy();
    }

    public function id() : string
    {
        return session_id();
    }

    public function regenerate() : string
    {
        session_regenerate_id(true);

        return session_id();
    }

    public function getSession() : array
    {
        return \array_replace([], $_SESSION);
    }

    private function is_session_started() : bool
    {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE;
        } else {
            return session_id() === '';
        }
    }

}
