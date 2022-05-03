<?php

namespace Email;

class AcessoCliente {
    
    /**
     * Função responsável por enviar o email de autenticação do site
     * @param \Models\Modules\Cadastro\Cliente $cliente
     * @throws \Exception
     */
    public static function send($cliente) {
        
        $idioma = new \Utils\PropertiesUtils("email_autent_segunda_etapa", IDIOMA);
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
            
        $navegadoresRn = new \Models\Modules\Cadastro\NavegadorRn();
        $navegador = $navegadoresRn->ultimoAcessoCliente($cliente->id);
        

        $idClienteEncripty = \Utils\Criptografia::encriptyPostId($cliente->id);
        $idNavegadorEncripty = \Utils\Criptografia::encriptyPostId($navegador["id"]);

        
        $params = Array("nome" => $cliente->nome, "idNavegador" => $idNavegadorEncripty, "idCliente" => $idClienteEncripty,
                        "navegador" => $navegador["navegador"], "localizacao" => $navegador["localizacao"],
                        "data" => $navegador["data_acesso"], "so" => $navegador["sistema_operacional"], "ip" => $navegador["ip_ultimo_acesso"]);
          
        ob_start();
        \Utils\Layout::append("emails/acesso_cliente", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();

        
        $listaEnvio = Array(
            Array("nome" => $cliente->nome, "email" => $cliente->email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("informAcesso") , $conteudo, $listaEnvio);
        $mail->send();
        
    }
    
}