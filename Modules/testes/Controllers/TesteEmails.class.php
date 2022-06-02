<?php

namespace Modules\testes\Controllers;

class TesteEmails {
    
    public function index($params) {
        try {
        $contato = new \Models\Modules\Cadastro\ContatoSite();
        $contato->id = 0;
        $contato->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
        $contato->departamento = "Nome do Departamento";
        $contato->email = "vagnercarvalho.vfc@gmail.com";
        $contato->mensagem = "mensagem mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem ."
                . " mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem ."
                . " mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem  mensagem "
                . "";
        $contato->nome = "Vagner Carvalho";
        
        //\EmailIco\RespostaAutomaticaContato::send($contato);
        //\EmailIco\ContatoSite::send($contato);
        
        $newsletter = new \Models\Modules\Cadastro\NewsLetter();
        $newsletter->email = "vagnercarvalho.vfc@gmail.com";
        
        \EmailIco\ConfirmacaoNewsletter::send($newsletter);
        
        exit("ok");
        } catch (\Exception $e) {
            print_r($e);
        }
    }
    
}