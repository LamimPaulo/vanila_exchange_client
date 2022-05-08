<?php

namespace Email;

class EmailMain {
    
    /**
     * Função responsável por enviar o email de autenticação do site
     * @param \Models\Modules\Cadastro\Cliente $cliente
     * @throws \Exception
     */
    public static function send($cliente, $idEmail = null, $mensagem = null, $dados = null, $listaEnvio = null) {
        
        $idioma = new \Utils\PropertiesUtils("email_manager", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        
        /*if (empty($idEmail)) {
            throw new \Exception("O ID do e-mail deve ser informado.");
        }*/
        
        /*if (!$cliente instanceof \Models\Modules\Cadastro\Cliente) {
            throw new \Exception("Cliente inválido");
        }*/
        
        /*if (empty($cliente->nome)) {
            throw new \Exception("O nome deve ser informado");
        }*/
        /*if (empty($cliente->email)) {
            throw new \Exception("O email deve ser informado");
        }*/
           
        if(($idEmail != null) && ($idEmail > 0)){
            $emailManager = new \Models\Modules\Cadastro\EmailManager();
            $emailManagerRn = new \Models\Modules\Cadastro\EmailManagerRn();
            $emailManager->id = $idEmail;
            $emailManagerRn->conexao->carregar($emailManager);       

            $assunto = $idioma->getText($emailManager->assunto);

            if($emailManager->logAcesso == 1){ 
                $navegadoresRn = new \Models\Modules\Cadastro\NavegadorRn();
                $navegador = $navegadoresRn->ultimoAcessoCliente($cliente->id);
                $idClienteEncripty = \Utils\Criptografia::encriptyPostId($cliente->id);
                $idNavegadorEncripty = \Utils\Criptografia::encriptyPostId($navegador["id"]);
            }

            if(!empty($dados[1])){
                $mensagem = str_replace("{var1}", $dados[1], $idioma->getText($emailManager->mensagem)); 

                if(!empty($dados[2])){
                    $mensagem = str_replace("{var2}", $dados[2], $mensagem); 
                    
                        if(!empty($dados[3])){
                        $mensagem = str_replace("{var3}", $dados[3], $mensagem); 
                    }
                }
            }
            
            $params = Array("nome" => $cliente->nome, "idNavegador" => $idNavegadorEncripty, "idCliente" => $idClienteEncripty,
                        "navegador" => $navegador["navegador"], "localizacao" => $navegador["localizacao"],
                        "data" => $navegador["data_acesso"], "so" => $navegador["sistema_operacional"], "ip" => $navegador["ip_ultimo_acesso"],
                        "email_manager" => $emailManager, "mensagem" => $mensagem);
        } else {
            $assunto = "Notificação CointradeCX";
            $params = Array("nome" => $cliente->nome, "mensagem" => $mensagem); 
        }
        
       
        ob_start();
        \Utils\Layout::append("emails/email_manager", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();
        
        if (empty($listaEnvio)) {
            $listaEnvio = Array(
                Array("nome" => $cliente->nome, "email" => $cliente->email)
            );
        }

        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $assunto, $conteudo, $listaEnvio);
        $mail->send();

        
    }
    
}