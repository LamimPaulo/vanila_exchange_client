<?php

namespace Utils;

class File {
    
    private static $maxSize = (10 * 1024 * 1024);
    
    public static function get($params, $paramName, $default = null, array $extencoesValidas = Array(), $cliente = null, $subdir = "", $publico = false) {
        $idioma = new \Utils\PropertiesUtils("utils", 'IDIOMA');
        $file = $params["_FILE"];
        if (isset($file[$paramName]) && $file[$paramName]["error"] < 1) {
            $arquivo = new \Utils\Arquivo($file[$paramName]);
            $arquivo->setPublicDirectory($publico);
            
            if ($arquivo->getTamanho() > self::$maxSize) {
                throw new \Exception($idioma->getText("tamanhoMaximoArq"));
            }
            
            if (sizeof($extencoesValidas) > 0 && !in_array(strtoupper($arquivo->getExtensao()), $extencoesValidas)) {
                throw new \Exception($idioma->getText("formatoInvalido"));
            }
            
            $arquivo->uploadArquivo($cliente, $subdir);

            return $arquivo->nome_saida;
        }
        
        return $default;
    }
    
    public static function getPublic() {
        
    }
    
}