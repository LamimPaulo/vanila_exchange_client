<?php

namespace Modules\acesso\Controllers;

class MarketingImagem {

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

        $id = \Utils\Get::get($params, 0, null);
        $params["code"] = $id;
        \Utils\Layout::view("marketing_imagem", $params);
    }

    public function listarCliente($params) {
        try {
            $id = \Utils\Post::getEncrypted($params, "code", null);
            $marketingImagemRn = new \Models\Modules\Cadastro\MarketingImagemRn();

            $imagemMoeda = "";
            $ativa = false;

            if(!empty($id)){
                $marketingImagem = new \Models\Modules\Cadastro\MarketingImagem(Array("id" => $id));
                $marketingImagemRn->conexao->carregar($marketingImagem);

                if(!empty($marketingImagem)){
                    $ativa = true;
                    $id = $marketingImagem->id;
                    $imagemMoeda = $marketingImagem->url;
                } else {
                    $ativa = false;
                }                
            } else {
                $ativa = false;
            }

            $json["ativa"] = $ativa;
            $json["id"] = \Utils\Criptografia::encriptyPostId($id);
            
            if(AMBIENTE == "producao"){
                $json["imagemMoeda"] = URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/" . \Utils\Criptografia::encriptyPostId($imagemMoeda);
            } else {
                $json["imagemMoeda"] = URLBASE_CLIENT . $imagemMoeda;
            }
           
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
            $notificacaoMoedaHasLido = new \Models\Modules\Cadastro\MarketingImagemHasLido();
            $notificacaoMoedaHasLidoRn = new \Models\Modules\Cadastro\MarketingImagemHasLidoRn();
            
            $notificacaoMoedaHasLido->idCliente = $cliente->id;
            $notificacaoMoedaHasLido->idNotificacao = $idNotificacaoMoeda;
            $notificacaoMoedaHasLido->dataLeitura = new \Utils\Data(date("Y-m-d H:i:s"));
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
