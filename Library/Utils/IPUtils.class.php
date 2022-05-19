<?php

namespace Utils;

class IPUtils {
    
    public static function getCountryByIp($ip) {
        $client_country = "";
        try {
            $db = new \GeoIp2\Database\Reader('./security/GeoLite2-City.mmdb');
            $client_ip= $db->city($ip);   
            $client_country=$client_ip->country->isoCode;

        } catch (\Exception $e) {

        }
        
        return $client_country;
    }
    
    
    
    public static function getDispositivo($webkit) {
        
        $iphone = strpos($webkit,"iPhone");
        $ipad = strpos($webkit,"iPad");
        $android = strpos($webkit,"Android");
        $palmpre = strpos($webkit,"webOS");
        $berry = strpos($webkit,"BlackBerry");
        $ipod = strpos($webkit,"iPod");
        $symbian =  strpos($webkit,"Symbian");
        
        if ($iphone) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($ipad) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($android) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($palmpre) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($berry) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($ipod) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else if ($symbian) {
            return Array("dispositivo" => "Desconhecido", "icone" => "");
        } else {
            
        }
        
        return Array("dispositivo" => "Desconhecido", "icone" => "");
    }
}