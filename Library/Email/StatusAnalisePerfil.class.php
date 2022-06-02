<?php

namespace Email;

class StatusAnalisePerfil {
    
    /**
     * 
     * @param \Models\Modules\Cadastro\Cliente $cliente
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\Cliente $cliente) {
        $idioma = new \Utils\PropertiesUtils("email_analise_documento", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        try {
            $clienteRn->conexao->carregar($cliente);
        } catch (\Exception $ex) {
            throw new \Exception("Cliente inválido");
        }
            
        $params = Array("cliente" => $cliente);
        
        ob_start();
        
        \Utils\Layout::append("emails/analise_documentos", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();

        $listaEnvio = Array(
            Array("nome" => $cliente->nome, "email" => $cliente->email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("assunto"), $conteudo, $listaEnvio);
        $mail->send();
        
    }
    
}