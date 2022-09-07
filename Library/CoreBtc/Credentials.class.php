<?php

namespace CoreBtc;

class Credentials {
    const SERVER_HOST_HOMOLOG = "http://177.38.215.99:8333/";
    const SERVER_HOST_PROD = "http://177.38.215.99:8333/";
    const SERVER_PORT = "8333";
    const USER = "navi";
    const PASSWORD = "8250ED7965E9AE0A33AB230828FE30BE05DE1FF565F0E240FBF9299FFF6301A0";
    
    public static function getAuthorization() {
        return base64_encode(self::USER.":".self::PASSWORD);
    }
    
    
    public static function getHost() {
        if (AMBIENTE == "producao") {
            return self::SERVER_HOST_PROD;
        } else {
            return self::SERVER_HOST_HOMOLOG;
        }
    }
}