<?php

namespace Modules\trade\Controllers;

class CompraVendaDiretaNew {

    private $codigoModulo = "trade";
    private $idioma = null;

    function __construct(&$params) {
        if (\Utils\Geral::isUsuario()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        $this->idioma = new \Utils\PropertiesUtils("compra_venda_direta", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);

    }

    public function index($params) {
        try {
            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $paridade = \Modules\principal\Controllers\Principal::getParity();

            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();

            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaTodasParidades(false);

            $params["paridades"]  = $paridades;

            if($cliente->modoOperacao == "basic"){
                $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, true);

                $params["compra"] = number_format($taxas["compraAtiva"], 4, ".", "");
                $params["venda"] = number_format($taxas["vendaAtiva"], 4, ".", "");

                 \Utils\Layout::view("compra_venda_direta", $params);
            } else {

                $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, false);

                $params["compra"] = number_format($taxas["compra"], 4, ".", "");
                $params["venda"] = number_format($taxas["venda"], 4, ".", "");                    

                 \Utils\Layout::view("book", $params);
            }

        } catch (\Exception $ex) {
            
        }
        
       
    }

    public function consultarPreco($params) {
        try {

            $cliente = \Utils\Geral::getCliente();

            $amount = \Utils\Post::getNumeric($params, "amount", 0);
            $tipo = \Utils\Post::get($params, "tipo", null);
            $from = \Utils\Post::getEncrypted($params, "from", null);
            $to = \Utils\Post::getEncrypted($params, "to", null);
            $par = \Utils\Post::getEncrypted($params, "par", null);
            
            $paridade = \Models\Modules\Cadastro\ParidadeRn::get($par);
            
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();
            $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, true);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $precos = $orderBookRn->calcularPreco($amount, $tipo, $paridade->id);
            $preco = 0;
            $volume = 0;
                        
            //Validar Pariadde
            if($tipo == \Utils\Constantes::ORDEM_COMPRA){
                $taxa = $taxas["compraAtiva"];
                
                if(!empty($precos["preco"]) && $precos["preco"] > 0){
                    $preco = $precos["preco"] +  ($precos["preco"] * ($taxa / 100));
                    $volume = $amount / $preco;
                } else {
                    $precos["preco"] = 0;
                }
                
                $json["preco"] = number_format($precos["preco"], $paridade->moedaTrade->casasDecimais, ".", "");
                $json["preco_format"] = number_format($precos["preco"], $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo;
                $json["volume_format"] = number_format($volume, $paridade->moedaBook->casasDecimais, ",", ".") . " " . $paridade->moedaBook->simbolo;
                $json["volume"] = number_format($volume, $paridade->moedaBook->casasDecimais, ".", "");
                
            } else if ($tipo == \Utils\Constantes::ORDEM_VENDA){
                $taxa = $taxas["vendaAtiva"];
                
                if(!empty($precos["preco"]) && $precos["preco"] > 0){
                    $preco = $precos["preco"] -  ($precos["preco"] * ($taxa / 100));
                    $volume = $amount * $preco;
                } else {
                    $precos["preco"] = 0;
                }
                
                $json["preco"] = number_format($precos["preco"], $paridade->moedaTrade->casasDecimais, ".", "");
                $json["preco_format"] = number_format($precos["preco"], $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo;
                $json["volume_format"] = number_format($volume, $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo;
                $json["volume"] = number_format($volume, $paridade->moedaTrade->casasDecimais, ".", "");
            }
            
            $base = number_format(($tipo == \Utils\Constantes::ORDEM_COMPRA ? $precos["maior"] : $precos["menor"]), $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo;
            
            if (!$base > 0) {
                $base = $precos["preco"];
            }
            
            
            $json["base"] = $base;
            $json["taxa"] = number_format(($tipo == \Utils\Constantes::ORDEM_COMPRA ? $taxas["compraAtiva"] : $taxas["vendaAtiva"]), $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo;

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }

    public function salvarCompra($params) {
        try {

            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_MERCADO, \Utils\Constantes::CADASTRAR)) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermissao"));
            }

            $idParidade = \Utils\Post::getEncrypted($params, "par", 0);
            $amount = \Utils\Post::getNumeric($params, "amount", 0);
            $price = \Utils\Post::getNumeric($params, "price", 0);
            
            $paridade = \Models\Modules\Cadastro\ParidadeRn::get($idParidade);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $dados = $orderBookRn->registrarOrdemCompra($amount, $price, $paridade, true, $price);
            
            if($dados->executada == 0){
                $orderBookRn->finalizada($dados);
            } 

            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("ordemRegistradaSucessoC");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function salvarVenda($params) {
        try {

            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_MERCADO, \Utils\Constantes::CADASTRAR)) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermissao"));
            }

            $idParidade = \Utils\Post::getEncrypted($params, "par", 0);
            $amount = \Utils\Post::getNumeric($params, "amount", 0);
            $price = \Utils\Post::getNumeric($params, "price", 0);
            
            $paridade = \Models\Modules\Cadastro\ParidadeRn::get($idParidade);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $dados = $orderBookRn->registrarOrdemVenda($amount, $price, $paridade);
            
            if($dados->executada == 0){
                $orderBookRn->finalizada($dados);
            }            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("ordemRegistradaSucessoV");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    } 
    
    public function getBalances() {
        
        try {
            
            $cliente = \Utils\Geral::getCliente();
            
            $moedas = Array();
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->listar(" ativo = 1 AND status_mercado = 1 ", null, null, null, true, false);
            
            $lista = Array();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldos = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get(1);
                $lista[] = Array(
                    "id_moeda" => \Utils\Criptografia::encriptyPostId($moeda->id),
                    "saldo_bloqueado" => $saldos["bloqueado"],
                    "saldo_disponivel" => $saldos["saldo"],
                    "decimal" => $moeda->casasDecimais,
                    "imagem" => IMAGES . "currencies/" . $moeda->icone ,
                    "simbolo" => $moeda->simbolo,
                    "nome" => $moeda->nome);
            }
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            foreach ($paridades as $paridade) {
                
            if (!in_array($paridade->idMoedaBook, $moedas)) {
                $moedas[] = $paridade->idMoedaBook;
                    
                $moeda = $paridade->moedaBook;
               
                $saldos = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true, false);             
                
                if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                    $lista[] = Array(
                        "id_moeda" => \Utils\Criptografia::encriptyPostId($moeda->id),
                        "saldo_bloqueado" => $saldos["bloqueado"],
                        "saldo_disponivel" => $saldos["saldo"],
                        "decimal" => $moeda->casasDecimais,
                        "imagem" => IMAGES . "currencies/" . $moeda->icone ,
                        "simbolo" => $moeda->simbolo,
                        "nome" => $moeda->nome);
                    }
                }
            }
            
            
            $json["moedas"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function getParidades($params) {
        
        try {
            $cliente = \Utils\Geral::getCliente();
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", null);
            $tipoMoeda = "trade";
            $saldo = 0;
            $lista = Array();
            
            $moedaFrom = \Models\Modules\Cadastro\MoedaRn::get($idMoeda);
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            
            //Paridades Moeda Trade
            $paridades = $paridadeRn->getListaParidadesByMoeda($moedaFrom, false, true);
            
            //Paridades Moeda Book
            if(empty($paridades)){
                $tipoMoeda = "book";
                $paridades = $paridadeRn->getParidadesComMoeda($moedaFrom, false, true);
               
            } else {
                //Paridades de moeda trader
                $paridadesTrade = $paridadeRn->getParidadesComMoeda($moedaFrom, false, true);
                
                if(!empty($paridadesTrade)) {
                    foreach ($paridadesTrade as $paridadeTrade) {
                        $paridades[] = $paridadeTrade;
                    }
                    
                    $paridades = array_reverse($paridades);
                }
            }
            
            if (!empty($paridades)) {
                foreach ($paridades as $paridade) {
                    
                    if($tipoMoeda == "trade"){
                        
                        if($paridade->moedaBook->id == $moedaFrom->id){
                           $moedaTo = $paridade->moedaTrade;
                           $tipo = \Utils\Constantes::ORDEM_VENDA;
                           $preco = number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", "");
                           
                        } else {
                           $moedaTo = $paridade->moedaBook; 
                           $tipo = \Utils\Constantes::ORDEM_COMPRA;
                           $preco = number_format($paridade->precoVenda, $paridade->moedaTrade->casasDecimais, ".", "");
                        }
                    } else if($tipoMoeda == "book"){
                        $moedaTo = $paridade->moedaTrade;
                        $tipo = \Utils\Constantes::ORDEM_VENDA;
                        $preco = number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ".", "");
                        
                    } else {
                        //Erro
                    }
                    
                    $lista[] = Array(
                        "id_moeda" => \Utils\Criptografia::encriptyPostId($moedaTo->id),
                        "par" => \Utils\Criptografia::encriptyPostId($paridade->id),
                        "preco" => $preco,
                        "tipo" => $tipo,
                        "imagem" => IMAGES . "currencies/" . $moedaTo->icone,
                        "simbolo" => $moedaTo->simbolo,
                        "nome" => $moedaTo->nome);
                }
            }
            
            //Saldo Disponivel
            if($moedaFrom->id == 1){
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldos = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
                if ($saldos["saldo"] > 0) {
                    $saldo = $saldos["saldo"];
                }
            } else {
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saldos = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moedaFrom->id, true, false);   
                
                if ($saldos["saldo"] > 0) {
                    $saldo = $saldos["saldo"];
                }
            }

            $json["saldo"] = number_format($saldo, $moedaFrom->casasDecimais, ".", "");
            $json["saldo_format"] = number_format($saldo, $moedaFrom->casasDecimais, ",", ".") . " " . $moedaFrom->simbolo;
            $json["moedas"] = $lista; 
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
}
