<?php

namespace Email;

class ConfirmacaoNewsletter {
    
    /**
     * 
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\NewsLetter $newsletter) {
        
        $idioma = new \Utils\PropertiesUtils("email_confirmacao_newsletter", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        
        if (empty($newsletter->nome)) {
            throw new \Exception("Nome inválido");
        }
        
        if (empty($newsletter->email)) {
            throw new \Exception("Email inválido");
        }
        
        $params = Array("newsletter" => $newsletter);
            
        ob_start();
        \Utils\Layout::append("emails/confirmacao_newsletter", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();
        //exit();
        
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Nome do destinatário da mensagem
        // Param 4 = Email do destinatário da mensagem
        // Param 5 = Content Type =  se for somente texto pode ser usado text/plain. Como estou enviando conteúdo em html estou usando text/html
        // Param 6 = Corpo do email
        SendGridUtil::send($brand->nome, $idioma->getText("assunto"), $newsletter->nome, $newsletter->email, "text/html", $conteudo);
        
        $listaEnvio = Array(
            Array("nome" => $newsletter->nome, "email" => $newsletter->email)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("assunto"), $conteudo, $listaEnvio);
        $mail->send();
        
    }
    
}