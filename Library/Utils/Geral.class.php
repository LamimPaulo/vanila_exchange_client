<?php
namespace Utils;
class Geral {
    public static function printVar($var, $_active = false) {
        if ($_active === true) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
            echo '--------------------------------';
        }
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
    public static function redirect($_url, $_time = -1) {
        
        try {
            
            $url = $_url;
            if ($_time != -1)
                header("Refresh: {$_time}; {$url}");
            else
                header("Location: {$url}");
        } catch (\Exception $e) {
            return Excecao::mensagem($e);
        }
    }
    
    public static function setLogado($usuario = null, $cliente = null) {
       
        if ($usuario != null) {
            $_SESSION["login"]["usuario"] = serialize($usuario);
        }
        if ($cliente != null) {
            $_SESSION["login"]["cliente"] = serialize($cliente);
        }
    }
    
    
    public static function setCliente($cliente = null) {
        
        $_SESSION["login"]["cliente"] = serialize($cliente);
        
    }
    
    public static function getLogado() {
        
        if (isset($_SESSION["login"]["usuario"])) {
            return unserialize($_SESSION["login"]["usuario"]);
        } else if (isset($_SESSION["login"]["cliente"])) {
            return unserialize($_SESSION["login"]["cliente"]);
        }
        return null;
    }
    
    public static function getCliente() {
        if (isset($_SESSION["login"]["cliente"])) {
            return unserialize($_SESSION["login"]["cliente"]);
        }
        return null;
    }
    
    public static function isCliente() {
        return (isset($_SESSION["login"]["cliente"]) && $_SESSION["login"]["cliente"] != null);
    }
    
    public static function isUsuario() {
        
        return (isset($_SESSION["login"]["usuario"]) && $_SESSION["login"]["usuario"] != null);
    }
    
    public static function isLogado() {
        return (isset($_SESSION["login"]["usuario"]) || isset($_SESSION["login"]["cliente"]));
    }
    
    public static function setAutenticado($autenticado = false) {
        
        $_SESSION["login"]["autenticado"] = $autenticado;
        
    }
    
    public static function isAutenticado() {
        return isset($_SESSION["login"]["autenticado"]) ? $_SESSION["login"]["autenticado"] : false;
    }
    
    public static function isAjax(){
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    
    public static function setUsuario($usuario) {
        
        $_SESSION["login"]["usuario"] = serialize($usuario);
        
    }
    
    public static function setMenu($modulos, $rotinas) {
        $_SESSION["login"]["menu"] = Array("modulos" => $modulos, "rotinas" => $rotinas);
    }
    
    public static function getMenu() {
        # 04/07/2019 - Caique - fix
        return isset($_SESSION["login"]["menu"]);
    }
    
}

    
