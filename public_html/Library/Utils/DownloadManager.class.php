<?php

namespace Utils;

class DownloadManager {
    
    private static $filesPath = "../../../../efs/arquivosclientes/";
    //private static $filesPathPublic = "../../../../efs/img-public/";
    
    const PRIVADO = "p";
    const PUBLICO = "o";
    
    /**
     * 
     * @param String $tipo p para arquivo privado (somente o dono pode ver) ou o para arquivo publico (é necessário apenas estar logado para ver)
     * @param String $arquivo caminho relativo para o arquivo
     * @param String $disposition W para ser exibido na página ou D para download
     */ 
    public static function getFile($tipo, $arquivo, $disposition = "W") {        
        $extension = "";
        $fullPath = $arquivo;
        
        if($tipo == self::PRIVADO){
            $fullPath = self::$filesPath . $arquivo;
        }
        
        if (is_file($fullPath)) {
            
            $liberado = false;
            
            if (Geral::isLogado()) {
              // obrigatoriamente tem de estar logado
            
                switch ($tipo) {
                    case self::PRIVADO:

                        if (Geral::isUsuario()) {
                            $liberado = true;
                        } else if (Geral::isCliente()){
                            
                            $caminho = explode("/", $arquivo);
                            $ex = explode(".", $arquivo);
                            $extension = $ex[sizeof($ex) - 1];
                            
                            $cliente = Geral::getCliente();
                            
                            if ($cliente->id == $caminho[0]) {
                                
                                $liberado = true;
                            } else {
                                http_response_code(403);
                            }
                            
                        } else {
                            http_response_code(403);
                        }
                        
                        break;
                    case self::PUBLICO:

                        $liberado = true;
                        
                        break;

                    default:

                        http_response_code(404);
                        break;
                }
            } else {
                http_response_code(403);
            }
            
            if ($liberado) {
                
                switch ($disposition) {
                    case "D":
                        $disposition = "attachment";
                        break;

                    default:
                        $disposition = "inline";
                }
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $type = finfo_file($finfo, $fullPath);
                
                $name = "". time().".{$extension}";
                $size = filesize($fullPath);
                
                header('Content-type: '.$type);
                //header('Content-Disposition: attachment; filename="'.$name.'"');
                header("Content-Length: " . $size);
                
                ob_start();
                readfile($fullPath);
                $bytes = ob_get_contents();
                ob_end_clean();
                
                echo $bytes;
                exit();
            }
        } else {
            if(AMBIENTE == "desenvolvimento"){
                echo "Não existe: {$fullPath}";
            }
            http_response_code(404);
        }
    }
    
}