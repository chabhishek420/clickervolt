<?php

namespace ClickerVolt;

class Session
{
    const URL_SESSION_KEY = 'session';
    const COOKIE_NAME = 'clickervolt-sid';

    /**
     * 
     */
    function get($key)
    {
        return empty($_SESSION[$key]) ? null : $_SESSION[$key];
    }

    /**
     * 
     */
    function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 
     */
    function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            $sessionLifetime = 7 * 24 * 60 * 60;
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path' => '/',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();

            $cookieLifetime = 365 * 24 * 60 * 60;
            setcookie(self::COOKIE_NAME, session_id(), [
                'expires' => time() + $cookieLifetime,
                'path' => '/',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
}
