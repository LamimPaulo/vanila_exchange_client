<?php

namespace EmailIco;

class RespostaAutomaticaContato {
    
    /**
     * 
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\ContatoSite $contato) {
        $idioma = new \Utils\PropertiesUtils("email_resposta_automatica_contato_site", IDIOMA);
        
        $params = Array("contato" => $contato);
            
        ob_start();
        \Utils\Layout::append("emails_ico/resposta_automatica_contato_site", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();
        //exit();
        
        //$contato->email = "sac@newc.com.br";
        //$contato->email = "vagnercarvalho.vfc@gmail.com";
        
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Nome do destinatário da mensagem
        // Param 4 = Email do destinatário da mensagem
        // Param 5 = Content Type =  se for somente texto pode ser usado text/plain. Como estou enviando conteúdo em html estou usando text/html
        // Param 6 = Corpo do email
        \Email\SendGridUtil::send("Cointrade", $idioma->getText("assunto"), "SAC Cointrade", $contato->email, "text/html", $conteudo);
        
    }
    
}