<?php

namespace Modules\apiv2\Controllers;

class Coins {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function info($params) {
        $httpResponse = new HttpResult();
        try {
            
            $sigla = \Utils\Get::get($params, 0, null);
            $lang = \Utils\Get::get($params, 1, "pt-BR");
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda = $moedaRn->getBySimbolo($sigla);
            if ($moeda == null) {
                throw new \Exception("Moeda inválida", 400);
            }
            
            $idioma = new \Utils\PropertiesUtils("moedas", $lang);
            
            $httpResponse->addBody("sigla", $moeda->simbolo);
            $httpResponse->addBody("nome", $moeda->nome);
            $httpResponse->addBody("descricao", $idioma->getText($moeda->simbolo));
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
}