<?php

namespace Modules\trade\Controllers;

class CompraIco {
    
    private $codigoModulo = "trade";
    private $idioma = null;
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("compraico", IDIOMA);
    }
    
    public function index($params) {
        
        try {
            
            $tabelaConversaoIcoRn = new \Models\Modules\ICO\TabelaConversaoIcoRn();
            
            $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn();
            $faseAtual = $faseIcoRn->getFaseIco(\Utils\Constantes::ID_ICO);
            
            $tabelas = $tabelaConversaoIcoRn->listar("id_fase = {$faseAtual->id}", "id_moeda", null, null, false);
            
            $precoBitcoin = 0;
            $precoLitecoin = 0;
            $precoDash = 0;
            $precoEthereum = 0;
            $precoReais = 0;
            
            foreach ($tabelas as $tabelaConversaoIco) {
                switch ($tabelaConversaoIco->idMoeda) {
                    case 1:
                        $precoReais = $tabelaConversaoIco->volumeMoeda;
                        break;
                    case 2:
                        $precoBitcoin = $tabelaConversaoIco->volumeMoeda;
                        break;
                    case 4:
                        $precoLitecoin = $tabelaConversaoIco->volumeMoeda;
                        break;
                    case 7:
                        $precoDash = $tabelaConversaoIco->volumeMoeda;
                        break;
                    case 3:
                        $precoEthereum = $tabelaConversaoIco->volumeMoeda;
                        break;
                    default:
                        break;
                }
            }
            $cliente = \Utils\Geral::getCliente();
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            $saldoBtc = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 2, FALSE);
            $saldoLtc = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 4, FALSE);
            $saldoDash = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 7, FALSE);
            $saldoEth = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 3, FALSE);
            $saldoBrl = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false);
            
            
            $params["saldoBtc"] = $saldoBtc;
            $params["saldoLtc"] = $saldoLtc;
            $params["saldoDash"] = $saldoDash;
            $params["saldoEth"] = $saldoEth;
            $params["saldoBrl"] = $saldoBrl;
            
            $params["faseAtual"] = $faseAtual;
            $params["precoBitcoin"] = $precoBitcoin;
            $params["precoLitecoin"] = $precoLitecoin;
            $params["precoDash"] = $precoDash;
            $params["precoEthereum"] = $precoEthereum;
            $params["precoReais"] = $precoReais;
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("compraico", $params);
    }
    
    
    public function comprar($params) {
        try {
            $moeda = \Models\Modules\Cadastro\MoedaRn::find(\Utils\Post::get($params, "moeda", NULL));
            if ($moeda == null) {
                throw new \Exception($this->idioma->getText("compraico15"));
            }
            $volume = \Utils\Post::getNumeric($params, "volume", NULL);
            if (!$volume > 0) {
                throw new \Exception($this->idioma->getText("compraico16"));
            }
            
            $cliente = \Utils\Geral::getCliente();
            
            $depositoIcoRn = new \Models\Modules\ICO\DepositoIcoRn();
            $tokens = $depositoIcoRn->comprarTokens($moeda, $cliente, $volume);
            
            $msg = $this->idioma->getText("compraico17");
            $msg = str_replace("{var1}", number_format($tokens["comprados"], 8, ".", ""), $msg);
            $msg = str_replace("{var2}", number_format($tokens["bonificados"], 8, ".", ""), $msg);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $msg;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
   
}