<?php

namespace Modules\emails\Controllers;

class EmailAutenticacao {
    
    public function emailCodigo($codigo, $nome, $email) {
        //teste
        try {
            $params = Array("codigo" => $codigo);
            
            ob_start();
            \Utils\Layout::append("emails/autenticacao_segunda_etapa", $params);
            $conteudo = ob_get_contents();
            ob_end_clean();
            
            $listaEnvio = Array(
                Array(
                    "nome" => $nome,
                    "email" => $email
                )
            );
            
            $mail = new \Utils\Mail("Recarga de Cartão", $conteudo, $listaEnvio);
            $mail->send();
            
            
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        
    }
    
}