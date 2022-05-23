<?php

namespace Modules\privado\Controllers;

class Account {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Cache-Control:max-age=0");
    }
    
    public function balance($params) {        
     
        $httpResponse = new HttpResult();
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "GET") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $cliente = Auth::logar(apache_request_headers());
            
            $symbol = \Utils\Get::get($params, "asset", null);
                 
            
            if ($symbol == "ALL" || $symbol == null) {
                
            } else {
                if (!empty($symbol) && strlen($symbol) <= 5) {
                    $moeda = \Models\Modules\Cadastro\MoedaRn::find($symbol);
                    if (empty($moeda)) {
                        throw new \Exception("Invalid Asset", 401);
                    }
                } else {
                    throw new \Exception("Invalid Asset", 401);
                }
            }

            $moedas = Array();
            if (isset($moeda) && $moeda != null) {
                $moedas[] = $moeda;
            } else {
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moedas = $moedaRn->listar("ativo = 1 OR id = 1", "principal desc, simbolo", null, null);
            }
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $carteiraCliente = "";
            $saldos = Array();
            
            foreach ($moedas as $moeda) {
                if ($moeda->id == 1) { //Real - BRL
                    $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
                } else {
                    $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true);
                    $carteiraCliente = $carteiraRn->getPrincipalCarteira($cliente, $moeda);
                }
                
                if ($symbol == "ALL" || $symbol == null) {
                    if (($saldo["saldo"] + $saldo["bloqueado"]) > 0) {
                        $balance = Array(
                            "timestamp" => strtotime(date("Y-m-d H:i:s")),
                            "asset" => $moeda->simbolo,
                            "assetName" => $moeda->nome,
                            "balanceTotal" => number_format(($saldo["saldo"] + $saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                            "balanceAvailable" => number_format((($saldo["saldo"] + $saldo["bloqueado"]) - $saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                            "inOrder" => number_format(($saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                            "isActive" => $moeda->ativo == 1 ? true : false,
                            "allowDeposit" => $moeda->statusDeposito == 1 ? true : false,
                            "allowWithdraw" => $moeda->statusSaque == 1 ? true : false,
                            "depositAddress" => !empty($carteiraCliente->endereco) ? $carteiraCliente->endereco : "",
                        );

                        $saldos[] = $balance;
                    }
                } else if(!empty($symbol)){
                    
                    //Criar carteira da moeda
                    if(($moeda->id > 1) && empty($carteiraCliente)){                        
                        $carteiraCliente = new \Models\Modules\Cadastro\Carteira();
                        $carteiraCliente->idMoeda = $moeda->id;
                        $carteiraCliente->nome = $moeda->nome . " Wallet";
                        $carteiraCliente->idCliente = $cliente->id;
                        $carteiraCliente->principal = 1;
                        
                        $carteiraRn->salvar($carteiraCliente);
                    }
                    
                    $balance = Array(
                        "timestamp" => strtotime(date("Y-m-d H:i:s")),
                        "asset" => $moeda->simbolo,
                        "assetName" => $moeda->nome,
                        "balanceTotal" => number_format(($saldo["saldo"] + $saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                        "balanceAvailable" => number_format((($saldo["saldo"] + $saldo["bloqueado"]) - $saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                        "inOrder" => number_format(($saldo["bloqueado"]), $moeda->casasDecimais, ".", ""),
                        "isActive" => $moeda->ativo == 1 ? true : false,
                        "allowDeposit" => $moeda->statusDeposito == 1 ? true : false,
                        "allowWithdraw" => $moeda->statusSaque == 1 ? true : false,
                        "depositAddress" => !empty($carteiraCliente->endereco) ? $carteiraCliente->endereco : "",
                    );
                    
                    $saldos[] = $balance;
                }
                
                $carteiraCliente = null;
                $balance = null;
                
            }

                $httpResponse->addBody(null, $saldos);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    public function fees($params) {
        $httpResponse = new HttpResult();
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "GET") {
                throw new \Exception("Método inválido", 403);
            }
            
            $symbol = \Utils\Get::get($params, "moeda", null);  
            
            $cliente = Auth::logar(apache_request_headers());
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn(); 
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();            
           
            if (!empty($symbol)  && strlen($symbol) <= 5) { 
                $where = " AND simbolo = '{$symbol}' ";
            }

            $moedas = $moedaRn->listar("ativo = 1 AND id > 1 {$where} ", "principal desc, simbolo", null, null);
            
            if (sizeof($moedas) > 0) {
                foreach ($moedas as $moeda) {
                    $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $moeda->id);
                    $taxasDireta = $clienteHasTaxaRn->getTaxaCliente($cliente, $moeda->id, TRUE);

                    if ($taxas["compra"] == 0) {
                        $taxas["compra"] = $configuracao->percentualCompraPassiva;
                    }
                    if ($taxas["venda"] == 0) {
                        $taxas["venda"] = $configuracao->percentualVendaPassiva;
                    }

                    if ($taxasDireta["venda"] == 0) {
                        $taxasDireta["venda"] = $configuracao->percentualVenda;
                    }

                    if ($taxasDireta["compra"] == 0) {
                        $taxasDireta["compra"] = $configuracao->percentualCompra;
                    }

                    $httpResponse->addBody("maker_buy_" . $moeda->simbolo, $taxas["compra"]);
                    $httpResponse->addBody("taker_buy_" . $moeda->simbolo, $taxasDireta["compra"]);
                    $httpResponse->addBody("maker_sell_" . $moeda->simbolo, $taxas["venda"]);
                    $httpResponse->addBody("taker_sell_" . $moeda->simbolo, $taxasDireta["venda"]);
                }
            }

            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
            
    /*public function withdraw($params) {
        $httpResponse = new HttpResult();
        
        try {
            $json = $entityBody = file_get_contents('php://input');
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            $cliente = Auth::logar(apache_request_headers());
            
            if (!in_array($method, Array(\Utils\Constantes::POST))) {
                throw new \Exception("Método inválido.", 400);
            }
            
            if (empty($json)) {
                throw new \Exception("Dados inválidos.", 400);
            }
            $object = json_decode($json);

            $conta = $this->incluirSaque($object, $cliente);

            $httpResponse->addBody("withdraw", $this->getJsonAccount($conta));
            
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
        
    }
    
        
    public function transaction($params) {
        $httpResponse = new HttpResult();
        
        try {
            $json = $entityBody = file_get_contents('php://input');
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            $cliente = Auth::logar(apache_request_headers());
            
            if (!in_array($method, Array(\Utils\Constantes::GET))) {
                throw new \Exception("Método inválido.", 400);
            }
            
            $codigo = \Utils\Get::get($params, 0, 0);
                    
            if ($codigo > 0) {
                $httpResponse->addBody("transaction", $this->getAccount($codigo));
            } else {
                if (empty($json)) {
                    throw new \Exception("Dados inválidos.", 400);
                }
                $object = json_decode($json);
                $httpResponse->addBody("transactions", $this->listarTransacoes($cliente, $object));
            }
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
        
    }
    
    private function incluirSaque($object, $cliente) {
        
        $valor = \Utils\JSON::getNumeric($object, "valor", 0);
            
        if (!$valor > 0) {
            throw new \Exception("O valor precisa ser meior que zero.");
        }

        $symbol = \Utils\JSON::get($object, "moeda", NULL);

        $moeda = \Models\Modules\Cadastro\MoedaRn::find($symbol);
        if ($moeda == null) {
            throw new \Exception("Moeda inválida.");
        }

        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

        $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id);

        if ($saldo < $valor) {
            throw new \Exception("Saldo insuficiente.");
        }

        $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
        $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);

        $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
        $contaCorrenteBtc->autorizada = 0;
        $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteBtc->descricao = \Utils\JSON::get($object, "descricao", "");
        $contaCorrenteBtc->enderecoBitcoin = \Utils\JSON::get($object, "endereco", "");
        $contaCorrenteBtc->idCliente = $cliente->id;
        $contaCorrenteBtc->idMoeda = $moeda->id;
        $contaCorrenteBtc->origem = 13;
        $contaCorrenteBtc->tipo = \Utils\Constantes::SAIDA;
        $contaCorrenteBtc->transferencia = 1;
        $contaCorrenteBtc->valor = $valor;
        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
        $contaCorrenteBtc->valorTaxa = $taxaMoeda->taxaTransferencia;

        //exit(print_r($contaCorrenteBtc));
        $contaCorrenteBtcRn->salvar($contaCorrenteBtc);
        $contaCorrenteBtcRn->carregar($contaCorrenteBtc, true, FALSE, true);
        return $contaCorrenteBtc;
    }
    
    private function getJsonAccount(\Models\Modules\Cadastro\ContaCorrenteBtc $conta) {
        return Array(
            "codigo" => $conta->id,
            "autorizada" => ($conta->autorizada > 0),
            "status" => $conta->getStatusCode(),
            "dataTransacao" => $conta->data->formatar(\Utils\Data::FORMATO_PT_BR),
            "dataCadastro" => $conta->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
            "descricao" => $conta->descricao,
            "wallet" => $conta->enderecoBitcoin,
            "txid" => $conta->hash,
            "moeda" => $conta->moeda->symbol,
            "movimento" => ($conta->tipo == \Utils\Constantes::SAIDA ? "SAIDA" : "ENTRADA"),
            "valor" => number_format(($conta->valor - $conta->valorTaxa), 2, ".", "")
        );
    }
    
    private function getAccount($codigo) {
        
        $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
        $contaCorrenteBtc->id = $codigo;
        
        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
        $contaCorrenteBtcRn->carregar($contaCorrenteBtc, true, false, true);
        
        return $this->getJsonAccount($contaCorrenteBtc);
    }
    
    
    private function listarTransacoes(\Models\Modules\Cadastro\Cliente $cliente, $object) {
        
        $symbol = \Utils\JSON::get($object, "moeda", null);        
        $pagina = \Utils\JSON::get($object, "pagina", 1);        
        $registros = \Utils\JSON::get($object, "registros", 50);
        
        if (!is_numeric($pagina) || $pagina < 1) {
            $pagina = 1;
        }
        
        if (!is_numeric($registros) || $registros < 1) {
            $registros = 50;
        }
        $pagina--;
        
        $moeda = \Models\Modules\Cadastro\MoedaRn::find($symbol);
        
        $where = " id_cliente = {$cliente->id} ";
        if ($moeda != null) {
            $where .= " AND id_moeda = {$moeda->id} ";
        }
        
        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
        $result = $contaCorrenteBtcRn->lista($where, "id DESC", ($registros * $pagina), $registros, false, true);
        
        $lista = Array();
        foreach ($result as $c) {
            $lista[] = $this->getJsonAccount($c);
        }
        
        return $lista;
    }*/
    
}
