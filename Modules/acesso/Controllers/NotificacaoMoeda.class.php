<?php

namespace Modules\acesso\Controllers;

class NotificacaoMoeda {
    
    private $idioma = null;
    
    public function __construct($params) {
        
        $this->idioma = new \Utils\PropertiesUtils("docs_aceitacao", IDIOMA);
        if (!\Utils\Geral::isLogado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
        }
        
        if (!\Utils\Geral::isAutenticado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_TWOFACTORAUTH);
        }
        
    }
    
    
    
    public function index($params) {
        
        \Utils\Layout::view("notificacao_moeda", $params);
    }
    
    
    public function listarCliente($params) {
        
        try {
            $cliente = \Utils\Geral::getCliente();
            $notificacaoMoedaHasLidoRn = new \Models\Modules\Cadastro\NotificacaoMoedaHasLidoRn();
            $notificacoes = $notificacaoMoedaHasLidoRn->notificacoesNaoLidas($cliente);           
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();

            $id = "";
            $tituloPor = "";
            $tituloIng = "";
            $descPort = "";
            $descIng = "";
            $imagemMoeda = "";
            $ativa = false;

            if (sizeof($notificacoes) > 0) {
                foreach ($notificacoes as $notificacao) {
                 $ativa = true;
                 $id = $notificacao["id"];
                 $imagemMoeda = $notificacao["id_moeda"];
                 $tituloPor = $notificacao["titulo_portugues"];
                 $tituloIng = $notificacao["titulo_ingles"];
                 $descPort = $notificacao["descricao_portugues"];
                 $descIng  = $notificacao["descricao_ingles"];
                }
                if($imagemMoeda != null){
                    $moeda->id = $imagemMoeda;
                    $moedaRn->carregar($moeda);
                }
                
            } else {
                $ativa = false;
            }

            $json["ativa"] = $ativa;
            $json["id"] = \Utils\Criptografia::encriptyPostId($id);
            $json["imagemMoeda"] = IMAGES."/currencies/".$moeda->simbolo.".png";
            $json["nomeMoeda"] = $moeda->nome;
            $json["tituloIng"] = $tituloIng;
            $json["descIng"] = $descIng;
            $json["tituloPort"] = $tituloPor;
            $json["descPort"] = $descPort;            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
        
    public function marcarComoLido($params) {
        try{
            $cliente = \Utils\Geral::getCliente();
            $idNotificacaoMoeda = \Utils\Post::getEncrypted($params, "idNotificacao", null);
            $notificacaoMoedaHasLido = new \Models\Modules\Cadastro\NotificacaoMoedaHasLido();
            $notificacaoMoedaHasLidoRn = new \Models\Modules\Cadastro\NotificacaoMoedaHasLidoRn();
            
            $notificacaoMoedaHasLido->idCliente = $cliente->id;
            $notificacaoMoedaHasLido->idNotificacao = $idNotificacaoMoeda;
            $notificacaoMoedaHasLido->dataLeitura =  \date("Y-m-d H:i:s");
            $notificacaoMoedaHasLidoRn->salvar($notificacaoMoedaHasLido);

            $json["sucesso"] = true;
            $json["mensagem"] = "Mensagem marcada como lida.";
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}
