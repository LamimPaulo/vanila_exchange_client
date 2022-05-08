<?php

namespace Modules\configuracoes\Controllers;

class PainelSite {
    
    private  $codigoModulo = "configuracoes";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            
            $configuracaoSite = \Models\Modules\Cadastro\ConfiguracaoSiteRn::get();
            
            $params["configuracao"] = $configuracaoSite;
            
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("configuracoes_site", $params);
    }
    
    public function salvar($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PAINELSITE, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar as configurações");
            }
            
            $configuracaoSite = new \Models\Modules\Cadastro\ConfiguracaoSite(Array("id" => 1));
            
            $configuracaoSite->imagemCabase = \Utils\File::get($params, "imagemCabase", null);
            $configuracaoSite->imagemCacentro = \Utils\File::get($params, "imagemCacentro", null);
            $configuracaoSite->labelBotaoCabase = \Utils\Post::get($params, "labelBotaoCabase", null);
            $configuracaoSite->labelBotaoCacentro = \Utils\Post::get($params, "labelBotaoCacentro", null);
            $configuracaoSite->showBotaoCabase = \Utils\Post::getBooleanAsInt($params, "showBotaoCabase", 0);
            $configuracaoSite->showBotaoCacentro = \Utils\Post::getBooleanAsInt($params, "showBotaoCacentro", 0);
            $configuracaoSite->showCabase = \Utils\Post::getBooleanAsInt($params, "showCabase", 0);
            $configuracaoSite->showCacentro = \Utils\Post::getBooleanAsInt($params, "showCacentro", 0);
            $configuracaoSite->showCelularCabase = \Utils\Post::getBooleanAsInt($params, "showCelularCabase", 0);
            $configuracaoSite->showCelularCacentro = \Utils\Post::getBooleanAsInt($params, "showCelularCacentro", 0);
            $configuracaoSite->textoCabase = \Utils\Post::get($params, "textoCabase", null);
            $configuracaoSite->textoCacentro = \Utils\Post::get($params, "textoCacentro", null);
            $configuracaoSite->tituloCabase = \Utils\Post::get($params, "tituloCabase", null);
            $configuracaoSite->tituloCacentro = \Utils\Post::get($params, "tituloCacentro", null);
            $configuracaoSite->urlBotaoCabase = \Utils\Post::get($params, "urlBotaoCabase", null);
            $configuracaoSite->urlBotaoCacentro = \Utils\Post::get($params, "urlBotaoCacentro", null);
           
            $configuracaoSiteRn = new \Models\Modules\Cadastro\ConfiguracaoSiteRn();
            
            $configuracaoSiteRn->salvar($configuracaoSite);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
}