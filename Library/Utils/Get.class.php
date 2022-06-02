<?php

namespace Utils;

class Get {
    
    
    public static function getEncrypted($params, $key, $default = null) {
        if (is_numeric($key)) {
            if (isset($params["_parameters"][$key])) {
                return Criptografia::decriptyPostId($params["_parameters"][$key]);
            }
        } else {
            if (isset($params["_GET"][$key])) {
                return Criptografia::decriptyPostId($params["_GET"][$key]);
            }
        }
        return $default;
    }
    
    
    public static function get($params, $key, $default = null) {
        if (is_numeric($key)) {
            if (isset($params["_parameters"][$key])) {
                return SQLInjection::clean($params["_parameters"][$key]);
            }
        } else {
            if (isset($params["_GET"][$key])) {
                return SQLInjection::clean($params["_GET"][$key]);
            }
        }
        return $default;
    }
    
}