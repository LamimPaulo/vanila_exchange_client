<?php

namespace Email;

class ContatoSite {
    
    /**
     * 
     * @throws \Exception
     */
    public static function send(\Models\Modules\Cadastro\ContatoSite $contato) {
        $idioma = new \Utils\PropertiesUtils("email_contato_site", IDIOMA);
        $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
        $empresa = \Models\Modules\Cadastro\EmpresaRn::getEmpresa();
        
        $params = Array("contato" => $contato);
            
        ob_start();
        \Utils\Layout::append("emails/contato_site", $params);
        $conteudo = ob_get_contents();
        ob_end_clean();
        
        $listaEnvio = Array(
            Array("nome" => $empresa->nomeFantasia, "email" => $empresa->emailSac)
        );
        // Param 1 = Nome do remetente da mensagem
        // Param 2 = Assunto da mensagem
        // Param 3 = Conteudo da mensagem em HTML
        // Param 4 = Array de destinatários
        $mail = new \Utils\Mail($brand->nome, $idioma->getText("assunto"), "SAC " . $brand->nome, $conteudo, $listaEnvio);
        $mail->send();
        
    }
    
}