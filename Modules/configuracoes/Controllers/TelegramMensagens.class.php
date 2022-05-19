<?php

namespace Modules\configuracoes\Controllers;

class TelegramMensagens {
    
    
    public function salvar($params) {
        try {
            
            $telegramMensagemAutomaticaRn = new \Models\Modules\Cadastro\TelegramMensagemAutomaticaRn();
            
            $telegramMensagemAutomatica = new \Models\Modules\Cadastro\TelegramMensagemAutomatica();
            $telegramMensagemAutomatica->id = \Utils\Post::get($params, "codigo", 0);
            $telegramMensagemAutomatica->conteudo = \Utils\Post::getHtml($params, "conteudo", NULL);
            $telegramMensagemAutomatica->idTelegramBot = \Utils\Post::get($params, "bot", NULL);
            $telegramMensagemAutomatica->idTelegramGrupo = \Utils\Post::get($params, "grupo", NULL);
            $telegramMensagemAutomatica->periodicidade = \Utils\Post::get($params, "periodicidade", NULL);
            $telegramMensagemAutomatica->slug = \Utils\Post::get($params, "slug", NULL);
            
            
            $telegramMensagemAutomaticaRn->salvar($telegramMensagemAutomatica);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Mensagem salva com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusAtivo($params) {
        try {
            $telegramMensagemAutomatica = new \Models\Modules\Cadastro\TelegramMensagemAutomatica();
            $telegramMensagemAutomatica->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $telegramMensagemAutomaticaRn = new \Models\Modules\Cadastro\TelegramMensagemAutomaticaRn();
            $telegramMensagemAutomaticaRn->alterarStatusAtivo($telegramMensagemAutomatica);
            
            
            $json["codigo"] = ($telegramMensagemAutomatica->id);
            $json["ativo"] = ($telegramMensagemAutomatica->ativo > 0);
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}