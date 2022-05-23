<?php

namespace Modules\privado\Controllers;

class BotCliente {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function information($params) {        
     
        $httpResponse = new HttpResult();
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "GET") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $cliente = Auth::logar(apache_request_headers());
            
            $botsClientes = Array();
            $botClienteRn = new \Models\Modules\Cadastro\BotClienteRn();
            
            $bots = $botClienteRn->listar( " id_cliente = {$cliente->id} ", null, null, null, true, true);
            
            foreach ($bots as $bot) {
                
                    $dados = Array(
                        "timestamp"                     => strtotime(date("Y-m-d H:i:s")),
                        "descricao"                     => $bot->descricao,
                        "status"                        => $bot->status == 1 ? true : false,
                        "paridade"                      => str_replace(":", "_", $bot->paridade->symbol),
                        "exchangeCotacaoCompra"         => $bot->exchangeCotacaoCompra,
                        "exchangeCotacaoVenda"          => $bot->exchangeCotacaoVenda,
                        "qtdOrdensCompra"               => number_format($bot->qtdOrdensCompra, 0, ".", ""),
                        "qtdOrdensVenda"                => number_format($bot->qtdOrdensVenda, 0, ".", ""),
                        "percentualSaldoMoedaBook"      => number_format($bot->percentualSaldoMoedaBook, 2, ".", ""),
                        "percentualSaldoMoedaTrade"     => number_format($bot->percentualSaldoMoedaTrade, 2, ".", ""),
                        "percentualMinCompra"           => number_format($bot->percentualMinCompra, 2, ".", ""),
                        "percentualMaxCompra"           => number_format($bot->percentualMaxCompra, 2, ".", ""),
                        "percentualMinVenda"            => number_format($bot->percentualMinVenda, 2, ".", ""),
                        "percentualMaxVenda"            => number_format($bot->percentualMaxVenda, 2, ".", ""),
                    );
                    
                    $botsClientes[] = $dados;
                }
            

            $httpResponse->addBody(null, $botsClientes);
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
}