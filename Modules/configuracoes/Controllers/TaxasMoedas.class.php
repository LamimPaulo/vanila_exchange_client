<?php

namespace Modules\configuracoes\Controllers;

class TaxasMoedas {
    
    private  $codigoModulo = "configuracoes";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    

    public function index($params) {
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->conexao->listar("id > 2", "nome");
            
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            
            $lista = Array();
            foreach ($moedas as $moeda) {
                $taxa = $taxaMoedaRn->getByMoeda($moeda->id);
                
                $lista[] = Array(
                    "moeda" => $moeda,
                    "taxa" => $taxa
                );
            }
            
            $params["moedas"] = $lista;
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("taxas_moedas", $params);
    }
    
    public function salvar($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CRIPTOCURRENCIES_FEES, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para editar as configurações");
            }
            
            $taxas = \Utils\Post::getArray($params, "taxas", Array());
            
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $taxaMoedaRn->salvarTaxas($taxas);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Taxas Atualizadas com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}