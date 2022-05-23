<?php

namespace Modules\emails\Controllers;

class EmailBoasVindas {
    
    public function bemvindo($cliente) {
        
        try {
            $params = Array("cliente" => $cliente);
            
            ob_start();
            \Utils\Layout::append("emails/bemvindo", $params);
            $conteudo = ob_get_contents();
            ob_end_clean();
            
            $listaEnvio = Array(
                Array(
                    "nome" => $cliente->nome,
                    "email" => $cliente->email
                )
            );
            
            $mail = new \Utils\Mail("Recarga de Cartão", $conteudo, $listaEnvio);
            $mail->send();
            
            
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        
    }
    
}