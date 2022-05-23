<?php

namespace Modules\privado\Controllers;

class Methods {
    
    static $POST = "POST";
    static $GET = "GET";
    static $PUT = "PUT";
    static $DELETE = "DELETE";
    
    public static function isMethodAlowed($method, array $acceptedMethods) {
        if (!in_array(strtoupper($method), $acceptedMethods)) {
            throw new \Exception();
        }
    }
    
}