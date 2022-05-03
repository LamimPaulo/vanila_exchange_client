<?php

namespace Modules\apiv2\Controllers;

class Account {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function balance($params) {
        $httpResponse = new HttpResult();
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "GET") {
                throw new \Exception("Método inválido", 403);
            }
            
            $symbol = \Utils\Get::get($params, 0, null);
            
            if (!empty($symbol)) {
                $moeda = \Models\Modules\Cadastro\MoedaRn::find($symbol);
                if ($moeda == null) {
                    throw new \Exception("Moeda inválida", 401);
                }
            }
            
            $cliente = Auth::auth(apache_request_headers());
            
            $moedas = Array();
            if (isset($moeda) && $moeda != null) {
                $moedas[] = $moeda;
            } else {
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moedas = $moedaRn->listar("ativo = 1 AND id > 1", "principal desc, simbolo", null, null);
            }
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
                $httpResponse->addBody("BRL", number_format(($saldo["saldo"] + $saldo["bloqueado"]), 4, ".", ""));
                $httpResponse->addBody("BRL_locked", number_format(($saldo["bloqueado"]), 4, ".", ""));
                $httpResponse->addBody("BRL_available", number_format(($saldo["saldo"]), 4, ".", ""));
            
            foreach ($moedas as $moeda) {
                
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true);
                
                $httpResponse->addBody($moeda->simbolo, number_format(($saldo["saldo"] + $saldo["bloqueado"]), $moeda->casasDecimais, ".", ""));
                $httpResponse->addBody("{$moeda->simbolo}_locked", number_format(($saldo["bloqueado"]), $moeda->casasDecimais, ".", ""));
                $httpResponse->addBody("{$moeda->simbolo}_available", number_format(($saldo["saldo"]), $moeda->casasDecimais, ".", ""));
                
            }
            
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    
}