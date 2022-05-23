<?php

namespace Utils;

/**
 * Classe de Exceção
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Excecao {

    /**
     * mensagem() Recebe uma Exceção e retorna uma mensagem de acordo com o código
     *
     * @param Exception $exception Objeto do tipo exceção
     * @return string String com a mensagem da exceção
     */
    public static function mensagem(\Exception $exception) {
        
        $mensagem = '';
        try {
            $mensagem = $exception->getMessage();
            
            if ($exception instanceof \Zend\Mail\Exception\RuntimeException || is_numeric(strpos(strtolower($mensagem), "sqlstate")) || is_numeric(strpos(strtolower($mensagem), "statement")) ) {
                if (AMBIENTE == "producao") {
                    $idioma = new \Utils\PropertiesUtils("utils", 'IDIOMA');
                    $mensagem = $idioma->getText("falhaDesconhecida");
                }
            }
            
            $logCodigo = $exception->getCode();
            $logMessage = Excecao::setMessage($exception);
            
            $logMessage .= "<br><br>";
            $logMessage .= serialize($exception);
            
            \Models\Modules\Acesso\LogErroRn::registrar($logMessage, $logCodigo);
            
        } catch (\Exception $e) {
            if (AMBIENTE == "desenvolvimento") {
                print_r($e);
            }
        }
        return $mensagem;
    }

    /**
     * codigo() Recebe uma Exceção e retorna o código
     *
     * @param Exception $exception Objeto do tipo exceção
     * @return string String com a mensagem da exceção
     */
    public function codigo($exception) {
      
        try {
            if (!is_null($exception) && ($exception instanceof \Exception)) {
                $previousException = $exception->getPrevious();
                $codigo = $exception->getCode();
                if ($codigo != 99 && !(is_null($previousException))) {
                    $codigo = $previousException->getCode();
                }
                return $codigo;
            }
        } catch (Exception $e) {
            exit("erro");
            return 0;
        }
        return $mensagem;
    }
    
    public static function setMessage(\Exception $exception) {
        $mensagem = "";
        if ($exception->getMessage()!= null) {
            $mensagem .= $exception->getMessage() . ".";
        }
        if ($exception->getPrevious() != null) {
            $mensagem .= Excecao::setMessage($exception->getPrevious());
        }
        return $mensagem;
    }
    
    
    public static function throwException ($codigo) {
        Geral::redirect(URLBASE_CLIENT . "error/error/index/{$codigo}");
    }

}

?>