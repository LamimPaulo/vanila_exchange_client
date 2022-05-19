<?php

namespace Email;

class EmailAutenticacao {
    
    /**
     * Função responsável por enviar o email de autenticação do  site
     * @param String $codigo 
     * @param String $nome
     * @param String $email
     * @throws \Exception
     */
    public static function send($codigo, $nome, $email, $id = null) {
        $idioma = new \Utils\PropertiesUtils("email_autent_segunda_etapa", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
            
        if (empty($nome)) {
            throw new \Exception("O nome deve ser informado");
        }
        if (empty($email)) {
            throw new \Exception("O email deve ser informado");
        }
        if (empty($codigo)) {
            throw new \Exception("É necessário informar o código de autenticação");
        }
        
        $idNavegadorEncripty = null;
        $idClienteEncripty = null;
        $navegadorNome = null;
        $localizacao = null;
        $data = null;
        $ip = null;
        $so = null;
                
        if ($id != null) {
            $navegadoresRn = new \Models\Modules\Cadastro\NavegadorRn();
            $navegador = $navegadoresRn->ultimoAcessoCliente($id);
           
            if($navegador != null ){
               $ip = $navegador["ip_ultimo_acesso"];
               $so = $navegador["sistema_operacional"]; 
               $data = $navegador["data_acesso"];
               $localizacao = $navegador["localizacao"];
               $navegadorNome = $navegador["navegador"];
               $idClienteEncripty = \Utils\Criptografia::encriptyPostId($id);
               $idNavegadorEncripty = \Utils\Criptografia::encriptyPostId($navegador["id"]); 
            }
            
        }
        
        $params = Array("codigo" => $codigo, "nome" => $nome, "idNavegador" => $idNavegadorEncripty, "idCliente" => $idClienteEncripty,
                        "navegador" => $navegadorNome, "localizacao" => $localizacao,
                        "data" => $data, "so" => $so, "ip" => $ip);
            
        ob_start();
        \Utils\Layout::append("emails/autenticacao_segunda_etapa", $params);
        
        $conteudo = ob_get_contents();

        ob_end_clean();

        $listaEnvio = Array(
            Array("nome" => $nome, "email" => $email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("assunto"), $conteudo, $listaEnvio);
        $mail->send();
        

    }
    
}