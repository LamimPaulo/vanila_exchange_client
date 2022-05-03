<?php

namespace Utils;

class SessionHandler  {
    
    private static $_WRITE_METHODS = Array(
        "acesso->logar",
        "acesso->index",
        "acesso->logarapi",
        "acesso->logout",
        "sms->validate"
    );
    
    public static function initializeSession($controller, $method) {
        
        $writable = (in_array("{$controller}->{$method}", self::$_WRITE_METHODS));
        Session::start(!$writable);
    }

}