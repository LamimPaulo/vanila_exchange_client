<?php

namespace Modules\configuracoes\Controllers;

class NotificacaoMoedaOnlyView {
    
    private $codigoModulo = "configuracoes";
    
    public function __construct($params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {

        \Utils\Layout::view("notificacao_moeda_only_view", $params);        
    }
    
    public function listar($params) {
        
        try {
            $id = \Utils\Post::get($params, "codigo", NULL);
            $notificacaoMoeda = new \Models\Modules\Cadastro\NotificacaoMoeda();
            $notificacaoMoedaRn = new \Models\Modules\Cadastro\NotificacaoMoedaRn();                    
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            
            $notificacaoMoeda->id = $id;
            $notificacaoMoedaRn->conexao->carregar($notificacaoMoeda);
            $moeda->id = $notificacaoMoeda->idMoeda;
            $moedaRn->carregar($moeda);

            $json["id"] = \Utils\Criptografia::encriptyPostId($id);
            $json["imagemMoeda"] = IMAGES."/currencies/".$moeda->simbolo.".png";
            $json["nomeMoeda"] = $moeda->nome;
            $json["tituloIng"] = $notificacaoMoeda->tituloIngles;
            $json["descIng"] = $notificacaoMoeda->descricaoIngles;
            $json["tituloPort"] = $notificacaoMoeda->tituloPortugues;
            $json["descPort"] = $notificacaoMoeda->descricaoPortugues;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }    
    
    
    
}