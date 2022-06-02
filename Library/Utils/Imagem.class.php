<?php
/**
 * Contém os métodos de tratamento de imagens.
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
namespace Utils;
/**
 * Classe responsável pelo tratamento e upload de imagens
 */
class Imagem {

    

    /**
     * Aplica os tratamentos na imagem caso sejam necessÃ¡rios redimensionamento e fez o upload do arquivo para a pasta do cliente no servidor.
     * @param Arquivo $imagem
     * @throws Exception
     */
    public static function enviaImagem(Arquivo $imagem, $cliente = null, $subdir = null, $public = false) {
        $idioma = new \Utils\PropertiesUtils("utils", IDIOMA);
        Imagem::validaImagem($imagem);
        
        try {

            $canvas = new \Canvas();
            if (!$public) {
                $imagem->diretorio_saida = UPLOADS;
            } else {
                $imagem->diretorio_saida = PUBLIC_IMAGES;
            }
            
            
            if (!$public) {
                if ($cliente != null && $cliente->id > 0) {
                    $imagem->diretorio_saida .= "{$cliente->id}/";
                    if (!file_exists($imagem->diretorio_saida)) {
                        mkdir($imagem->diretorio_saida);
                    }
                }
            }  
            
            if ($subdir != null && !empty($subdir)) {
                $imagem->diretorio_saida .= "{$subdir}/";
                if (!file_exists($imagem->diretorio_saida)) {
                    mkdir($imagem->diretorio_saida);
                }
            }

            
            if (file_exists($imagem->diretorio_saida . $imagem->nome_saida)) {
                $valida = false;
                $i = 1;
                while (!$valida) {
                    
                    $novoNome = substr($imagem->nome_saida, 0, strpos($imagem->nome_saida, ".")) . "_" . $i . "." . $imagem->getExtensao();
                    if (!file_exists($imagem->diretorio_saida . $novoNome)) {
                        $valida = true;
                        $imagem->nome_saida = $novoNome;
                    }

                    $i++;
                }
            }
            
            $canvas->carrega($imagem->getDiretorioTemp());
            
            if ($imagem->largura != null && $imagem->altura != null) {
                $canvas->redimensiona($imagem->largura, $imagem->altura, "preenchimento");
            }
            
            $canvas->grava($imagem->diretorio_saida . $imagem->nome_saida); 
            if (file_exists($imagem->diretorio_saida . $imagem->nome_saida)) {
                $imagem->upado = true;
            } else {
                throw new \Exception ($idioma->getText("ocorreramFalhas"));
            }

            
            if ($public) { 
                $imagem->nome_saida = $imagem->diretorio_saida . $imagem->nome_saida;
            } else {
                $imagem->nome_saida = str_replace(UPLOADS, "", $imagem->diretorio_saida) . $imagem->nome_saida;
            }
        } catch (\Exception $erro) {
            throw new \Exception (Excecao::mensagem($erro));
        }
    }

    /**
     * Valida os dados da imagem.
     * @param Array $imagem array contendo os dados da imagem
     * @throws \Exception
     */
    public static function validaImagem(Arquivo $imagem) {
        $idioma = new \Utils\PropertiesUtils("utils", IDIOMA);
        // Valida a extensÃ£o da imagem
        $formatosAceitos = Array("gif", "jpg", "jpeg", "png", "bmp");
        
        $formatoValido = false;
        foreach ($formatosAceitos as $formato) {
            if (strtolower($imagem->getExtensao()) == $formato) {
                $formatoValido = true;
                break;
            }
        }
        
        if (!$formatoValido)
            throw new \Exception ($idioma->getText("extensaoImagem") . $imagem->getExtensao());
                
        if (!file_exists($imagem->getDiretorioTemp())) {
            throw new \Exception ($idioma->getText("arquivoTemporario"));
        }
        
        if (!strlen($imagem->getNome()) > 0) {
            throw new \Exception ($idioma->getText("nomeImagemNaoInformado"));
        }
        
    }

    public static function base64ToJpeg( $base64String, $outputFile ) {
        $ifp = fopen( $outputFile, "wb" ); 
        fwrite( $ifp, base64_decode( $base64String) ); 
        fclose( $ifp ); 
        return( $outputFile ); 
    }
    
}

?>
