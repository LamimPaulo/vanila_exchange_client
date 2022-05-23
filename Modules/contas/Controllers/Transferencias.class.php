<?php

namespace Modules\contas\Controllers;

class Transferencias {
    
    private  $codigoModulo = "transferencias";
    private $idioma = null;
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("transferencia_btc", IDIOMA);
        
        
    }
    
    public function tokenEmpresa($params) {
        try {
            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_TRANSFERENCIAS, \Utils\Constantes::TRANSFERIR_PARA_EMPRESA)) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermissao"));
            }
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $tipoMoeda = \Utils\Post::get($params, "tipoMoeda", "");
            
            $cliente = \Utils\Geral::getLogado();
            
            $auth = new \Models\Modules\Cadastro\Auth();
            
            if ($tipoMoeda == "r") { 
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);

                if ($saldo < $valor) {
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                }
            } else if ($tipoMoeda == "c") {
                $moeda = \Modules\principal\Controllers\Principal::getCurrency();
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false, true);

                if ($saldo < $valor) {
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                }
            } else {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);
            
            $tipo = "";
            $email = "";
            $telefone = "";
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $email = $usuario->email;
                $telefone = $usuario->celular;
                $auth->idUsuario = $usuario->id;
                $tipo = $usuario->tipoAutenticacao;
            } else {
                $cliente = \Utils\Geral::getCliente();
                
                if (empty($cliente->pin)) {
                    throw new \Exception($this->idioma->getText("precisaCadPin"));
                }
                
                $email = $cliente->email;
                $telefone = $cliente->celular;
                $auth->idCliente = $cliente->id;
                $tipo = $cliente->tipoAutenticacao;
            }
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth);
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["meio"] = $this->idioma->getText("foiEnviadoEmail") . $email . $this->idioma->getText("porFavorInsiraToken")  ;
            } 
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_SMS){
                $json["meio"] =  $this->idioma->getText("foiEnviadoSMS") . $telefone . $this->idioma->getText("porFavorInsiraToken");
            }
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                $json["meio"] = $this->idioma->getText("useGoogle");
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function transferirParaEmpresa($params) {
        try {
            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_TRANSFERENCIAS, \Utils\Constantes::TRANSFERIR_PARA_EMPRESA)) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermissao"));
            }
            
            $cliente = \Utils\Geral::getCliente();
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $tipoMoeda = \Utils\Post::get($params, "tipoMoeda", "");
            $descricao = \Utils\Post::get($params, "descricao", null);
            $token = \Utils\Post::get($params, "token", null);
            $pin = \Utils\Post::get($params, "pin", null);
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token);
            
            if ($cliente != null) {
                if (empty($cliente->pin)) {
                    throw new \Exception($this->idioma->getText("precisaCadPin"));
                }
                if ($cliente->pin != $pin) {
                    throw new \Exception($this->idioma->getText("pinInvalido"));
                }
            }
            
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            if ($tipoMoeda == "r") { 
                $contaCorrenteReaisRn->transferirParaEmpresa($valor, $descricao);
            } else if ($tipoMoeda == "c") {
                $contaCorrenteBtcRn->transferirParaEmpresa($valor, $descricao, $moeda->id, null);
            } else {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            $saldobrl = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false);
            
            $saldobtc = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false);
            
            $json["saldobrl"] = number_format($saldobrl, 2, ",", ".");
            $json["saldobtc"] = number_format($saldobtc, $moeda->casasDecimais, ".", ".");
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("transfSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}