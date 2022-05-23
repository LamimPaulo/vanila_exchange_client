<?php

namespace Modules\privado\Controllers;

class Book {
    private $method = null;
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("exception");
        header('Access-Control-Allow-Origin: *');
    }
    
    
    public function myOrders($params) {

        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');

        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "GET") {
                throw new \Exception("Invalid Method", 400);
            }
            
            $cliente = Auth::logar(apache_request_headers());

            $market = \Utils\Get::get($params, "market", NULL);
            $order = \Utils\Get::get($params, "idOrder", NULL);
            $type = \Utils\Get::get($params, "type", NULL);
            $dateStart = \Utils\Get::get($params, "dateStart", NULL);
            $dateEnd = \Utils\Get::get($params, "dateEnd", NULL);
            $status = \Utils\Get::get($params, "status", NULL);
            $registro = \Utils\Get::get($params, "depth", 30);
            
            if(empty($type)){
                $type = "ALL";
            }
            
            
            if(empty($market)){
                throw new \Exception("Request parameter error", 400);
            }   
           
            $market = str_replace("_", ":", $market);
            
            if ((strtoupper($order) == "ALL" || $order == null) && !empty($market)) {
                $this->getOpenOrders($httpResponse, $cliente, $market, $type, $dateStart, $dateEnd, $status, $registro);
            } else {
                $this->getOrder($httpResponse, $cliente, $order);
            }

            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }

    public function buy($params) {
        
        $httpResponse = new HttpResult();
        $json = file_get_contents('php://input');
        
        sleep(2);
        
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 400);
            }
            
            $cliente = Auth::logar(apache_request_headers());
            
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
        
        sleep(2);
        
        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 400);
            }
            
            $cliente = Auth::logar(apache_request_headers());
            
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
    
    private function getOpenOrders(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $market, $type = null, $dateStart = null,
            $dateEnd = null, $status = null, $registro = 30) {
        $bd = new \Io\BancoDados(BDBOOK);
        
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);
        if (!empty($market)) {
            $paridade = $paridadeRn->getBySymbol($market);

            if ($paridade == null) {
                throw new \Exception("Invalid Market", 400);
            }
            
            if ($paridade->ativoApi != 1) {
                throw new \Exception("Market is not available.", 400);
            }
        }
        
        $where = new \Zend\Db\Sql\Where();
        switch (strtoupper($type)) {                
                case "SELL":
                    $where->equalTo("tipo", "V");
                    break;
                case "BUY":
                    $where->equalTo("tipo", "C");
                    break;
                case "ALL":
                    break;
                default:
                    throw new \Exception("Request parameter error", 400);
                    break;
            }
            
        if (!empty($dateStart) && !empty($dateEnd)) {
            if (is_numeric($dateStart) && is_numeric($dateEnd)) {
                if ($dateStart < $dateEnd) {
                    $where->between("data_cadastro", date("Y-m-d H:i:s", $dateStart), date("Y-m-d H:i:s", $dateEnd));
                } else {
                    throw new \Exception("Invalid date.", 400);
                }
            } else {
                throw new \Exception("Invalid date.", 400);
            }
        }
        
        switch (strtoupper($status)) {                
            case \Utils\Constantes::STATUS_ORDEM_PARTIAL:
                $where->equalTo("cancelada", "0");
                $where->equalTo("executada", "0");
                $where->greaterThan("volume_executado", "0");
                break;
            case \Utils\Constantes::STATUS_ORDEM_CANCELED:
                $where->equalTo("cancelada", "1");
                break;
            case \Utils\Constantes::STATUS_ORDEM_FILLED:
                $where->equalTo("executada", "1");
                break;
            case \Utils\Constantes::STATUS_ORDEM_EMPTY:
                $where->equalTo("cancelada", "0");
                $where->equalTo("executada", "0");
                $where->equalTo("volume_executado", "0");
            case \Utils\Constantes::STATUS_ORDEM_ALL:
                break;
            default:
                $where->equalTo("cancelada", "0");
                $where->equalTo("executada", "0");
                break;
        }
        
        $where->equalTo("id_cliente", $cliente->id);
        
        if (isset($paridade) && $paridade != null) {
            $where->equalTo("id_paridade", $paridade->id);
        }
        
        
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn($bd);
        $ordens = $orderBookRn->conexao->listar($where, "id DESC", null, $registro);
        
        
        $lista = Array();
        foreach ($ordens as $orderBook) {
            $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => $orderBook->idParidade));
            $paridadeRn->carregar($paridade, true, true, true);
            
            $lista[] = $this->montarObjectOrdem($orderBook, $paridade);
            
        }
        
        $httpResponse->addBody("orders", $lista) ;
    }
    
    private function getOrder(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $idOrdem) {
        $bd = new \Io\BancoDados(BDBOOK);        
        $ordemFinal = Array();
        
        $order = \Utils\Criptografia::decriptyPostId($idOrdem);
        
        if (!is_numeric($order)) {
            throw new \Exception("Request parameter error", 400);
        }
        
        $where = new \Zend\Db\Sql\Where();
        $where->equalTo("id_cliente", $cliente->id);
        $where->equalTo("id", $order);
        
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn($bd);
        $ordens = $orderBookRn->conexao->listar($where, "id DESC");
        
        if (sizeof($ordens) > 0) {
            $orderBook = $ordens->current();
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);
            $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => $orderBook->idParidade));
            $paridadeRn->carregar($paridade, true, true, true);
            
            $ordemFinal[] = $this->montarObjectOrdem($orderBook, $paridade);
            $httpResponse->addBody("order", $ordemFinal) ;        
        } else {
            $httpResponse->addBody("order", null) ; 
        }
    }
    
    
    private function montarObjectOrdem(\Models\Modules\Cadastro\OrderBook $orderBook, \Models\Modules\Cadastro\Paridade $paridade) {
        
        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
        }

        $dados = Array(
                "timestamp" => strtotime(date("Y-m-d H:i:s")),
                "id" => \Utils\Criptografia::encriptyPostId($orderBook->id),
                "createdTimestamp" => strtotime($orderBook->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                "market" => str_replace(":", "_", $paridade->symbol),
                "type" => ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? "BUY" : "SELL"),
                "status" => ($orderBook->cancelada > 0 ?  "CANCELED" : ($orderBook->executada > 0 ? "FILLED" : ($orderBook->volumeExecutado > 0 ? "PARTIAL" : "EMPTY"))),
                "amount" => number_format($orderBook->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", ""),
                "price" => number_format($orderBook->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                "amountBaseTraded" => number_format($orderBook->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ""),
                "limited" => ($orderBook->direta > 0 ? "false" : "true"),
            );
        
        return $dados;
    }
    
    
    private function plotarOrdem(HttpResult $httpResponse, \Models\Modules\Cadastro\Cliente $cliente, $object, $tipo) {
        
        $mercado = (isset($object->market) ? \Utils\SQLInjection::clean($object->market) : null);
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        $mercado = str_replace("_", ":", $mercado);
        $paridade = $paridadeRn->getBySymbol($mercado);
        
        if ($paridade == null) {
            throw new \Exception($this->idioma->getText("mercadoInvalido"), 401);
        }
        
        if ($paridade->ativoApi != 1 || $paridade->ativo != 1) {
            throw new \Exception("Market is not available.", 401);
        }
                
        $amount = (isset($object->amount) ? number_format($object->amount, $paridade->moedaBook->casasDecimais, ".", "") : null);
        if (is_null($amount)) {
            throw new \Exception($this->idioma->getText("valumeMaiorQueZero"), 401);
        }
        
        $limited = (isset($object->limited) ? $object->limited : NULL);
        if (is_null($limited)) {
            throw new \Exception($this->idioma->getText("tipoOrdemInvalida"), 401);
        }
        
        if ($limited) {
            $preco = (isset($object->price) ? number_format($object->price, $paridade->moedaTrade->casasDecimais, ".", "") : null);
            if (is_null($preco)) {
                throw new \Exception($this->idioma->getText("distribuicaoTokenRn5"), 401);
            }
        }
        
        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
        }

        $direta = 0;
        $precoReferencia = 0;
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        
        if (!$limited) {            
            $dadosPreco = $orderBookRn->calcularPreco($amount, $tipo, $paridade->id, $cliente);
            
            $p = number_format($dadosPreco["preco"], $paridade->moedaTrade->casasDecimais, ".", "");
             
            $base = number_format(($tipo == \Utils\Constantes::ORDEM_COMPRA ? $dadosPreco["maior"] : $dadosPreco["menor"]), $paridade->moedaTrade->casasDecimais, ".", "");
            if (!$base > 0) {
                $base = $dadosPreco["preco"];
            }
            
            $direta = 1;
            $preco = $p;            
            $precoReferencia = $base;           
        } else {
            $direta = 0;
            $precoReferencia = $preco;
        }
        
        //exit("{$amount}    {$preco}    {$paridade->symbol}    {$direta}      {$precoReferencia}     {$cliente->nome}"   );
        
        $orderBook = null;
        if ($tipo == \Utils\Constantes::ORDEM_COMPRA) {
           $orderBook = $orderBookRn->registrarOrdemCompra($amount, $preco, $paridade, $direta, $precoReferencia, $cliente);
        } else {            
            $orderBook = $orderBookRn->registrarOrdemVenda($amount, $precoReferencia, $paridade, $direta, $cliente);
        }
        
        $httpResponse->addBody(null, $this->montarObjectOrdem($orderBook, $paridade));
        
        return $orderBook;
    }
    
    public function deleteOrder($params) {        

        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');

        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "DELETE") {
                throw new \Exception("Invalid Method", 400);
            }
            
            $cliente = Auth::logar(apache_request_headers());
            
            $idOrder = \Utils\Get::getEncrypted($params, "idOrder", NULL);
            
            $market = \Utils\Get::get($params, "market", NULL);

            if (empty($idOrder) || empty($market)) {
                throw new \Exception("Request parameter error", 400);
            }

            if (!is_numeric($idOrder)) {
                throw new \Exception("Request parameter error", 400);
            }
           
            $market = strtoupper(str_replace("_", ":", $market));
                
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol($market);

            if ($paridade == null) {
                throw new \Exception("Invalid Market.", 400);
            } 
            
            if ($paridade->ativoApi != 1) {
                throw new \Exception("Market is not available.", 400);
            }
            
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo("id_cliente", $cliente->id);
            $where->equalTo("id", $idOrder);
            

            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $orderBook = $orderBookRn->conexao->select($where);
            $orderBook = $orderBook->current();
            
            if($orderBook->idParidade != $paridade->id){
                throw new \Exception("Invalid Market.", 400);
            } else {
                $where->equalTo("id_paridade", $paridade->id); 
            }
            
            if($orderBook->cancelada == 1 || $orderBook->executada == 1){
                throw new \Exception("Order cannot be changed.", 400);
            }
            
            $orderBookRn->conexao->update(Array("cancelada" => 1), $where);
            $orderBookRn->carregar($orderBook, true, false);
                        
            $httpResponse->addBody(null, $this->montarObjectOrdem($orderBook, $paridade));
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }

    public function deleteAllOrders($params) {
        
        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');
        $dadosFinal = Array();
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "DELETE") {
                throw new \Exception("Invalid Method", 400);
            }

            $cliente = Auth::logar(apache_request_headers());

            $market = \Utils\Get::get($params, "market", NULL);
            
            $type = \Utils\Get::get($params, "type", NULL);

            if (empty($market)) {
                throw new \Exception("Request parameter error", 400);
            }
            
            if (empty($type)) {
                throw new \Exception("Request parameter error", 400);
            }
            
                   
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo("id_cliente", $cliente->id);
            $where->equalTo("executada", 0);
            $where->equalTo("cancelada", 0);
            
            switch (strtoupper($type)) {                
                case "SELL":
                    $where->equalTo("tipo", "V");
                    break;
                case "BUY":
                    $where->equalTo("tipo", "C");
                    break;
                case "ALL":
                    break;
                default:
                    throw new \Exception("Request parameter error", 400);
                    break;
            }

            if (strtoupper($market) != "ALL") {

                $market = strtoupper(str_replace("_", ":", $market));
                
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridade = $paridadeRn->getBySymbol($market);

                if ($paridade == null) {
                    throw new \Exception("Invalid Market.", 400);
                }

                $where->equalTo("id_paridade", $paridade->id);
            }

            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            
            $dados = $orderBookRn->conexao->select($where);
            
            $orderBookRn->conexao->update(Array("cancelada" => 1), $where);        
            
            foreach ($dados as $order) {
                $orderBookRn->carregar($order, true, true);
                $dadosFinal[] = $this->montarObjectOrdem($order, $order->paridade);
            }
            
            $httpResponse->addBody(null, $dadosFinal);            
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
        
    }
    
    public function replaceAllOrders($params) {
        
        $httpResponse = new HttpResult();
        $json = $entityBody = file_get_contents('php://input');
        
        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 400);
            }
        
            $cliente = Auth::logar(apache_request_headers());
            
            $object = json_decode($json);
            
            $market = (isset($object->market) ? $object->market : "");
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol($market);            
            
            if ($paridade == null) {
                throw new \Exception("Mercado inválido", 403);
            }
                
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $result =  $orderBookRn->conexao->listar("cancelada = 0 AND executada = 0 AND id_cliente = {$cliente->id} AND id_paridade = {$paridade->id}");
            
            $ordensCompraPlotadas = Array();
            $ordensVendaPlotadas = Array();
            
            $listaRetorno = Array("buy" => Array(), "sell" => Array(), "cancelled" => Array());
            
            foreach ($result as $ob) {
                if ($ob->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                    $ordensCompraPlotadas[] = $ob;
                } else {
                    $ordensVendaPlotadas[] = $ob;
                }
            }
            
            $sellOrders = (isset($object->sell) ? $object->sell  : Array());
            $buyOrders = (isset($object->buy) ? $object->buy  : Array());
            
            try {
                foreach ($sellOrders as $sell) {
                    $orderBook = $this->plotarOrdem($httpResponse, $cliente, $sell, \Utils\Constantes::ORDEM_VENDA);
                    
                    $listaRetorno["sell"][] = $this->montarObjectOrdem($orderBook, $paridade);
                    
                    $first = array_shift($ordensVendaPlotadas);
                    if ($first != null) {
                        
                        $orderBookRn->conexao->update(Array("cancelada" => 1), Array("id" => $first->id));
                        //$this->deleteOrder($cliente, \Utils\Criptografia::encriptyPostId($first->id));
                        $orderBookRn->conexao->carregar($first);
                        $listaRetorno["cancelled"][] = $this->montarObjectOrdem($first, $paridade);
                    }
                }
            } catch (\Exception $ex) {
                 $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
            }

            try {
                foreach ($buyOrders as $buy) {
                    $orderBook = $this->plotarOrdem($httpResponse, $cliente, $buy, \Utils\Constantes::ORDEM_COMPRA);
                    $listaRetorno["buy"][] = $this->montarObjectOrdem($orderBook, $paridade);

                    $first = array_shift($ordensCompraPlotadas);
                    if ($first != null) {
                        $orderBookRn->conexao->update(Array("cancelada" => 1), Array("id" => $first->id));
                        $orderBookRn->conexao->carregar($first);
                        $listaRetorno["cancelled"][] = $this->montarObjectOrdem($first, $paridade);
                    }
                }
            } catch (\Exception $ex) {
                $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
            }
            
            
            /*if (sizeof($ordensCompraPlotadas)) {
                foreach ($ordensCompraPlotadas as $buy) {
                    $this->deleteOrder($cliente, \Utils\Criptografia::encriptyPostId($buy->id));
                    $orderBookRn->conexao->carregar($buy);
                    $listaRetorno["cancelled"][] = $this->montarObjectOrdem($buy, $paridade);
                }
            }
            
            if (sizeof($ordensVendaPlotadas)) {
                foreach ($ordensVendaPlotadas as $sell) {
                    $this->deleteOrder($cliente, \Utils\Criptografia::encriptyPostId($sell->id));
                    $orderBookRn->conexao->carregar($sell);
                    $listaRetorno["cancelled"][] = $this->montarObjectOrdem($sell, $paridade);
                }
            }
            
            $httpResponse->addBody("orders", $listaRetorno);*/
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();
    }
    
}