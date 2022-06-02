<?php

namespace TWWSms;

class Credenciais {
    
    private static $numusu = "";
    private static $senha = "";
    private static $url = "https://webservices2.twwwireless.com.br/reluzcap/";
    private static $soapAction = "https://www.twwwireless.com.br/reluzcap/wsreluzcap/";
    
    
    public static function getCredenciais() {
        return Array(
            "numusu" => self::$numusu,
            "senha" => self::$senha,
            "url" => self::$url,
            "SOAPAction" => self::$soapAction
        );
    }
    
}