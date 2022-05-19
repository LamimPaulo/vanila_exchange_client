<?php

namespace EmailIco;

class ConfirmacaoNewsletter {
    
    /**
     * 
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\NewsLetter $newsletter) {
        
        $idioma = new \Utils\PropertiesUtils("email_confirmacao_newsletter", IDIOMA);
        
        if (empty($newsletter->email)) {
            throw new \Exception("Email inválido");
        }
        
        $params = Array("newsletter" => $newsletter);
            
        ob_start();
        \Utils\Layout::append("emails_ico/confirmacao_newsletter", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();
        //exit();
        
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Nome do destinatário da mensagem
        // Param 4 = Email do destinatário da mensagem
        // Param 5 = Content Type =  se for somente texto pode ser usado text/plain. Como estou enviando conteúdo em html estou usando text/html
        // Param 6 = Corpo do email
        \Email\SendGridUtil::send("Cointrade", $idioma->getText("assunto"), "Cliente NEWC Token", $newsletter->email, "text/html", $conteudo);
        
    }
    
}