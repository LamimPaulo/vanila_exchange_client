<?php

namespace Utils;

class Post {
    
    /**
     * Retorna o parametro solicitado ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function get($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            return trim(SQLInjection::clean($params["_POST"][$paramName]));
        }
        return $defaultValue;
    }
    
    /**
     * Retorna o parametro solicitado ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function getBase64($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            $value = $params["_POST"][$paramName];
            
            return trim($value);
        }
        return $defaultValue;
    }
    
    
    /**
     * Retorna o parametro solicitado ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function getJson($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            return trim(SQLInjection::clean($params["_POST"][$paramName], false));
        }
        return $defaultValue;
    }
    
    
    /**
     * Retorna o parametro solicitado ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function getDoc($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            return trim(SQLInjection::clean($params["_POST"][$paramName], false));
        }
        return $defaultValue;
    }
    
    /**
     * Retorna o parametro solicitado do tipo Data ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Data
     */
    public static function getData($params, $paramName, $defaultValue = null, $time = "00:00:00") {
        
        if (isset($params["_POST"][$paramName]) && strlen(trim($params["_POST"][$paramName])) == 10) {
            $date = trim($params["_POST"][$paramName]);
            return new Data($date. " " . trim($time) );
        }
        return $defaultValue;
    }
    
    /**
     * Retorna o parametro solicitado como numero ou o valor padrao
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function getNumeric($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            $numeric = $params["_POST"][$paramName];
            if (strpos($numeric, ".") != false && strpos($numeric, ",") != false) {
                $numeric = str_replace(".", "", $numeric);
            }
            //$numeric = SQLInjection::clean($numeric);
            return str_replace(",", ".", $numeric);
        }
        return $defaultValue;
        
    }
    
    /**
     * Retorna o parametro solicitado descriptografado ou o valor padrao se nao encontrado
     * @param Array $params
     * @param String $paramName
     * @param Mixed $defaultValue
     * @return Mixed
     */
    public static function getEncrypted($params, $paramName, $defaultValue = null) {
        
        if (isset($params["_POST"][$paramName])) {
            $value = trim($params["_POST"][$paramName]);
            if (!is_numeric($value) && empty($value)) {
                return null;
            }
            return Criptografia::decriptyPostId($params["_POST"][$paramName]);
        }
        return $defaultValue;
    }
    
    
    public static function getBoolean($params, $paramName, $defaultValue = false) {
        
        if (isset($params["_POST"][$paramName])) {
            if (is_numeric($params["_POST"][$paramName])) {
                return intval($params["_POST"][$paramName]) > 0;
            } else {
                return Conversao::stringToBoolean($params["_POST"][$paramName]);
            }
        }
        return $defaultValue;
    }
    
    
    public static function getBooleanAsStr($params, $paramName, $defaultValue = "f") {
        
        return (self::getBoolean($params, $paramName, ($defaultValue == "t")) ? "t" : "f");
    }
    
    
    public static function getBooleanAsInt($params, $paramName, $defaultValue = 0) {
        
        return (self::getBoolean($params, $paramName, ($defaultValue > 0)) ? 1 : 0);
    }
    
    
    public static function getArray($params, $paramName, $defaultValue = Array()) {
        
        if (isset($params["_POST"][$paramName]) && is_array($params["_POST"][$paramName])) {
            return $params["_POST"][$paramName];
        } 
        return $defaultValue;
    }
    
    
    public static function getHtml($params, $paramName, $defaultValue = "") {
        if (isset($params["_POST"][$paramName])) {
            return htmlentities(SQLInjection::clean($params["_POST"][$paramName], false));
        } 
        return $defaultValue;
    }
    
}

