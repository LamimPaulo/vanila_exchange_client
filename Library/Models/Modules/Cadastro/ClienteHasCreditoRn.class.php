<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasCreditoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    /**
     *
     * @var  \Utils\PropertiesUtils
     */
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasCredito());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasCredito());
        }
    }
    
    public function salvar(ClienteHasCredito &$clienteHasCredito) {
        if ($clienteHasCredito->volumeCredito > 0) {

            if (!$clienteHasCredito->idMoeda > 0) {
                throw new \Exception("A identificação da moeda deve ser informada");
            }

            if ($clienteHasCredito->idCliente > 0) {
                $result = $this->conexao->select(Array("id_cliente" => $clienteHasCredito->idCliente, "id_moeda" => $clienteHasCredito->idMoeda));
                if (sizeof($result) > 0) {
                    $aux = $result->current();
                    $clienteHasCredito->id = $aux->id;
                } else {
                    $clienteHasCredito->id = 0;
                }
            } else {
                throw new \Exception("A identificação do cliente deve ser informada");
            }

            $this->conexao->salvar($clienteHasCredito);
        }
    }
    
    
    /**
     * 
     * @param Integer $idCliente
     * @param Integer $idMoeda
     * @return ClienteHasCredito
     */
    public static function get($idCliente, $idMoeda, $somenteEmUso = false) {
        $clienteHasCreditoRn = new ClienteHasCreditoRn();
        
        $result = $clienteHasCreditoRn->conexao->select(Array("id_cliente" => $idCliente, "id_moeda" => $idMoeda));
        if (sizeof($result) > 0) {
            $clienteHasCredito = $result->current();
            if ($somenteEmUso) {
                if ($clienteHasCredito->ativo > 0) {
                    return $clienteHasCredito;
                }
            } else {
                return $clienteHasCredito;
            }

        }
        
        return null;
    }
    
    
    public function salvarCreditos(Cliente $cliente, $moedas) {
        
        if (sizeof($moedas) > 0) {
            
            foreach ($moedas as $moeda) {
                $clienteHasCredito = new ClienteHasCredito();
                $clienteHasCredito->id = 0;
                $clienteHasCredito->idMoeda = (isset($moeda["moeda"]) ? \Utils\SQLInjection::clean($moeda["moeda"]) : 0);
                $clienteHasCredito->volumeCredito = (isset($moeda["credito"]) ? str_replace(",", ".", \Utils\SQLInjection::clean($moeda["credito"], true)) : 0);
                $clienteHasCredito->ativo =  (isset($moeda["ativo"]) ? ($moeda["ativo"] > 0 ? 1 : 0) : 0);
                $clienteHasCredito->idCliente = $cliente->id;
                
                $this->salvar($clienteHasCredito);
            }
            
        }
        
    }
    
    
    public static function validar(Cliente $cliente) {
        $moedasNegativas = Array();
                
        $moedaRn = new MoedaRn();
        $contaCorrenteBtcRn = new ContaCorrenteBtcRn($moedaRn->conexao->adapter);
        
        $resultMoedas = $moedaRn->conexao->listar(null, "id");
        foreach ($resultMoedas as $mVerificacao) {
            if ($mVerificacao->id > 1) {
                $saldoMoeda = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $mVerificacao->id, false, true);
                
            } else {
                $contaCorrenteReaisRn = new ContaCorrenteReaisRn($moedaRn->conexao->adapter);
                $saldoMoeda = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            }
            
            if ($saldoMoeda < 0) {
                $moedasNegativas[] = $mVerificacao->simbolo;
            }
        }
        
        if (sizeof($moedasNegativas) > 0) {
            $msg = $contaCorrenteBtcRn->idioma->getText("clienteHasCreditoRn1");
            $msg = str_replace("{var1}", implode(",", $moedasNegativas), $msg);
            throw new \Exception($msg);
        }
        
    }
    
}

?>