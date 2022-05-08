<?php

namespace Modules\apiv2\Controllers;

class Book {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    
    public function index($params) {
        
        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            $cliente = Auth::auth(apache_request_headers());
            switch (strtoupper($method)) {
                case "GET":
                    $param = \Utils\Get::get($params, 0, NULL);
                    
                    if (empty($param) || is_numeric(strpos($param, ":"))) {
                        $this->getOpenOrders($httpResponse, $cliente, $param);
                    } else {
                        $this->getOrder($httpResponse, $cliente, $param);
                    }
                    
                    break;
                case "DELETE":
                    $param = \Utils\Get::get($params, 0, NULL);

                    if (empty($param)) {
                        throw new \Exception("Parâmetro inválido", 403);
                    }
                    
                    if (strtoupper($param) == "ALL") {
                        $this->deleteAllOrders($cliente);
                    } else {
                        $this->deleteOrder($cliente, $param);
                    }
                    
                    break;

                default:
                    throw new \Exception("Método inválido", 403);
            }
            
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
        
    }
    
    public function buy($params) {
        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Método inválido", 403);
            }
            
            $cliente = Auth::auth(apache_request_headers());
            
            $object = json_decode($json);
            
            if (is_array($object)) {
                foreach ($object as $o) {
                    $this->plotarOrdem($httpResponse, $cliente, $o, \Utils\Constantes::ORDEM_COMPRA);
                }
            } else {
                $this->plotarOrdem($httpResponse, $cliente, $object, \Utils\Constantes::ORDEM_COMPRA);
            }
            
            
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    public function sell($params) {
        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');
        
        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Método inválido", 403);
            }
            
            $cliente = Auth::auth(apache_request_headers());
            
            $object = json_decode($json);
            
            if (is_array($object)) {
                foreach ($object as $o) {
                    $this->plotarOrdem($httpResponse, $cliente, $o, \Utils\Constantes::ORDEM_VENDA);
                }
            } else {
                $this->plotarOrdem($httpResponse, $cliente, $object, \Utils\Constantes::ORDEM_VENDA);
            }
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
    private function getOpenOrders(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $market) {
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        if (!empty($market)) {
            $paridade = $paridadeRn->getBySymbol($market);

            if ($paridade == null) {
                throw new \Exception("Mercado inválido", 403);
            }
        }
        
        $where = new \Zend\Db\Sql\Where();
        $where->equalTo("cancelada", "0");
        $where->equalTo("executada", "0");
        $where->equalTo("id_cliente", $cliente->id);
        
        if (isset($paridade) && $paridade != null) {
            $where->equalTo("id_paridade", $paridade->id);
        }
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        $ordens = $orderBookRn->conexao->listar($where, "id DESC");
        
        $lista = Array();
        foreach ($ordens as $orderBook) {
            //$orderBook = new \Models\Modules\Cadastro\OrderBook();
            $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => $orderBook->idParidade));
            $paridadeRn->carregar($paridade, true, true, true);
            
            $lista[] = $this->montarObjectOrdem($orderBook, $paridade);
            
        }
        
        $httpResponse->addBody("orders", $lista) ;
    }
    
    private function getOrder(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $idOrdem) {
        $idOrdem = \Utils\Criptografia::decriptyPostId($idOrdem);
        if (!is_numeric($idOrdem)) {
            throw new \Exception("Identificação da ordem inválida", 403);
        }
        
        $where = new \Zend\Db\Sql\Where();
        $where->equalTo("id_cliente", $cliente->id);
        $where->equalTo("id", $idOrdem);
        
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        $ordens = $orderBookRn->conexao->listar($where, "id DESC");
        
        if (sizeof($ordens) > 0) {
            $orderBook = $ordens->current();
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => $orderBook->idParidade));
            $paridadeRn->carregar($paridade, true, true, true);
            
            $httpResponse->addBody("order", $this->montarObjectOrdem($orderBook, $paridade)) ;        
        } else {
            $httpResponse->addBody("order", null) ; 
        }
    }
    
    
    private function montarObjectOrdem(\Models\Modules\Cadastro\OrderBook $orderBook, \Models\Modules\Cadastro\Paridade $paridade) {
        $dados = Array(
                "id" => \Utils\Criptografia::encriptyPostId($orderBook->id),
                "market" => $paridade->symbol,
                "type" => ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? "BUY" : "SELL"),
                "status" => ($orderBook->cancelada > 0 ?  "CANCELED" : ($orderBook->executada > 0 ? "FILLED" : ($orderBook->volumeExecutado > 0 ? "PARTIAL" : "EMPTY"))),
                "amount" => number_format($orderBook->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", ""),
                "price" => number_format($orderBook->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                "exec_amount" => number_format($orderBook->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ""),
                "cost" => number_format(($orderBook->volumeExecutado * $orderBook->valorCotacao), $paridade->moedaTrade->casasDecimais, ".", ""),
                "limited" => ($orderBook->direta > 0),
            );
        
        $where = "";
        if ($orderBook->tipo == \Utils\Constantes::ORDEM_VENDA) {
            $where = "id_ordem_book_venda = {$orderBook->id}";
        } else {
            $where = "id_ordem_book_compra = {$orderBook->id}";
        }
        
        $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
        $executadas = $ordemExecutadaRn->conexao->listar($where, "data_execucao DESC, id ASC");
        
        $negociacoes = Array();
        
        foreach ($executadas as $ordemExecutada) {
            //$ordemExecutada = new \Models\Modules\Cadastro\OrdemExecutada();
            
            $negociacoes[] = Array(
                "id" => \Utils\Criptografia::encriptyPostId($ordemExecutada->id),
                "cotacao" => number_format($ordemExecutada->valorCotacao, 8, ".", ""),
                "data_execucao" => $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                "tipo" => ($ordemExecutada->tipo == \Utils\Constantes::ORDEM_COMPRA ? "BUY" : "SELL"),
                "volume" => number_format($ordemExecutada->volumeExecutado, 8, ".", "")
            );
        }
        
        $dados["negociacoes"] = $negociacoes;
        
        return $dados;
    }
    
    
    private function plotarOrdem(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $object, $tipo) {
        
        $mercado = (isset($object->market) ? \Utils\SQLInjection::clean($object->market) : null);
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        $paridade = $paridadeRn->getBySymbol($mercado);
        
        if ($paridade == null) {
            throw new \Exception("Mercado inválido", 401);
        }
        
        //$paridade = new \Models\Modules\Cadastro\Paridade();
        
        $preco = (isset($object->price) ? number_format($object->price, $paridade->moedaTrade->casasDecimais, ".", "") : null);
        if (is_null($preco)) {
            throw new \Exception("É necessário informar o preço da ordem", 401);
        }
        $amount = (isset($object->amount) ? number_format($object->amount, $paridade->moedaBook->casasDecimais, ".", "") : null);
        if (is_null($amount)) {
            throw new \Exception("É necessário informar o volume da ordem", 401);
        }
        
        $limited = (isset($object->limited) ? $object->limited : NULL);
        if (is_null($limited)) {
            throw new \Exception("É necessário informar o tipo da ordem", 401);
        }
        
        $direta = 0;
        $precoReferencia = null;
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        
        if (!$limited) {
            $dadosPreco = $orderBookRn->calcularPreco($amount, $tipo, $paridade->id);
            $p = number_format($dadosPreco["preco"], $paridade->moedaTrade->casasDecimais, ".", "");
            
            $base = number_format(($tipo == \Utils\Constantes::ORDEM_COMPRA ? $dadosPreco["maior"] : $dadosPreco["menor"]), $paridade->moedaTrade->casasDecimais, ",", "");
            if (!$base > 0) {
                $base = $dadosPreco["preco"];
            }
            
            $direta = 1;
            $preco = $p;
            $precoReferencia = $base;
        } else {
            $direta = 0;
        }
        
        //exit("{$amount}    {$preco}    {$paridade->symbol}    {$direta}      {$precoReferencia}     {$cliente->nome}"   );
        
        $orderBook = null;
        if ($tipo == \Utils\Constantes::ORDEM_COMPRA) {
           $orderBook = $orderBookRn->registrarOrdemCompra($amount, $preco, $paridade, $direta, $precoReferencia, $cliente);
        } else {
            $orderBook = $orderBookRn->registrarOrdemVenda($amount, $preco, $paridade, $direta, $cliente);
        }
        
        $httpResponse->addBody("order_id", \Utils\Criptografia::encriptyPostId($orderBook->id));
    }
    
    private function deleteOrder(\Models\Modules\Cadastro\Cliente $cliente, $idOrdem) {
        
        $idOrdem = \Utils\Criptografia::decriptyPostId($idOrdem);
        if (!is_numeric($idOrdem)) {
            throw new \Exception("Identificação da ordem inválida", 403);
        }
        
        $where = new \Zend\Db\Sql\Where();
        $where->equalTo("id_cliente", $cliente->id);
        $where->equalTo("id", $idOrdem);
        
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        $orderBookRn->conexao->update(Array("cancelada" => 1), $where);
        
    }
    
    private function deleteAllOrders(\Models\Modules\Cadastro\Cliente $cliente) {
        
        $where = new \Zend\Db\Sql\Where();
        $where->equalTo("id_cliente", $cliente->id);
        
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        $orderBookRn->conexao->update(Array("cancelada" => 1), $where);
        
    }
    
}