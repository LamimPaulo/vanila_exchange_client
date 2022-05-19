<?php

namespace Email;

class ContaEmAnalise {
    
    /**
     * Função responsável por enviar o email de autenticação do site
     * @param \Models\Modules\Cadastro\Cliente $cliente
     * @throws \Exception
     */
    public static function send($cliente) {
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        
        if (!$cliente instanceof \Models\Modules\Cadastro\Cliente) {
            throw new \Exception("Cliente inválido");
        }
        
        if (empty($cliente->nome)) {
            throw new \Exception("O nome deve ser informado");
        }
        if (empty($cliente->email)) {
            throw new \Exception("O email deve ser informado");
        }
            
        $params = Array("cliente" => $cliente);
            
        ob_start();
        \Utils\Layout::append("emails/contaemanalise", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();

        
        $listaEnvio = Array(
            Array("nome" => $cliente->nome, "email" => $cliente->email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, "Registro em análise " . $cliente->nome . "!", $conteudo, $listaEnvio);
        $mail->send();
        
    }
    
}