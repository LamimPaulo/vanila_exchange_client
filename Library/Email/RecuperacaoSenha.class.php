<?php



namespace Email;

class RecuperacaoSenha {
    
    /**
     * Função responsável por enviar o email de contato do site
     * @param String $nome 
     * @param String $email
     * @param String $idade
     * @param String $cidade
     * @param String $estado
     * @param String $mensagem
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\Usuario $usuario, $fraseSeguranca = null, $hash = null) {
        $idioma = new \Utils\PropertiesUtils("email_recuperacao_senha", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        $params = Array("usuario" => $usuario, "frase" => $fraseSeguranca, "hash" => $hash);
        
        ob_start();
        \Utils\Layout::append("emails/recuperacao_senha",$params);
         
        $conteudo = ob_get_contents();

        ob_end_clean();

        
        $listaEnvio = Array(
            Array("nome" => $usuario->nome, "email" => $usuario->email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("assunto"), $conteudo, $listaEnvio);
        $mail->send();
        
        
        
    }
    
}