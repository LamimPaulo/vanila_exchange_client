<?php

namespace CoreBtc;

class Credentials {
    const SERVER_HOST_HOMOLOG = "http://35.190.184.242:8333/";
    const SERVER_HOST_PROD = "http://127.0.0.1:8333/";
    const SERVER_PORT = "8333";
    const USER = "newcash";
    const PASSWORD = "jknfkldjnfnd38239473829knflksdKJKLJLKJKJyyei9";
    
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