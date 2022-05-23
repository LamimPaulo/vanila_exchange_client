<?php

namespace Modules\privado\Controllers;

class Fee {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }   
    
    public function listarTaxas($params) {
        
        $httpResponse = new HttpResult();

        try {

            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }

            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            
            if (!empty($cliente)) {

                $json = file_get_contents('php://input');            
                $object = json_decode($json);
               
                $asset = \Utils\SQLInjection::clean($object->asset);
                          
                if(!empty($asset)){
                    $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                    $asset = $moedaRn->getBySimbolo($asset);
                }
                
                $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
                $taxasMoedas = $taxaMoedaRn->taxasMoedasAtivas();

                $clienteTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();

                $taxasFinal = Array();
                
                foreach ($taxasMoedas as $taxaMoeda){                
                    $result = $clienteTaxaRn->getTaxaCliente($cliente, $taxaMoeda["moedaId"], true);
                    $taxasFinal["cryptocurrency"][] = $this->montarObjeto($taxaMoeda + $result);                
                }
                
                
                $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

                $taxasFinal["fiat"]["asset"] = Array(
                    /* Transferencias */
                    "asset" => "BRL"
                );
                $taxasFinal["fiat"]["withdraw"] = Array(
                    /* Saque */
                    "taxTed" => number_format($configuracao->tarifaTed, 2, ".", ""),
                    "feeWithdraw" => number_format($configuracao->taxaSaque, 2, ".", "") . "%",
                    "minimumValue" => number_format($configuracao->valorMinSaqueReais, 2, ".", ""),
                );

                $taxasFinal["fiat"]["deposit"] = Array(
                    // Guia Dinheiro do Painel de Controle
                    "feeDeposit" => number_format($configuracao->taxaDeposito, 2, ".", "") . "%",
                    "depositReversal" => number_format($configuracao->percentualEstornoDeposito, 2, ".", ""),
                );

                $taxasFinal["fiat"]["transfer"] = Array(
                    /* Transferencias */
                    "taxInternalTransfer" => number_format($configuracao->taxaTransferenciaInternaReais, 2, ".", ""),
                );




                $httpResponse->addBody("fee", $taxasFinal);


                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function montarObjeto($objeto) {

        $dados = Array(
            "asset" => $objeto["sigla"],
            "transferFee" => $objeto["taxaTrans"],
            "buyFee" => $objeto["compra"],
            "sellFee" => $objeto["venda"]
        );

        return $dados;
    }

}