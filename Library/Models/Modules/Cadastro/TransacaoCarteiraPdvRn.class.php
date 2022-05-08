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
class TransacaoCarteiraPdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TransacaoCarteiraPdv());
        } else {
            $this->conexao = new GenericModel($adapter, new TransacaoCarteiraPdv());
        }
    }
    
    public function salvar(TransacaoCarteiraPdv &$transacaoCarteiraPdv) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            if (empty($transacaoCarteiraPdv->idMoeda > 0)) {
                throw new \Exception("É necessário informar a moeda");
            }
            
            if (empty($transacaoCarteiraPdv->address)) {
                throw new \Exception("É necessário informar o endereço");
            }
            
            if (!$transacaoCarteiraPdv->amount > 0) {
                throw new \Exception("É necessário informar o volume");
            }
            
            if (!$transacaoCarteiraPdv->confirmacoes >= 0) {
                $transacaoCarteiraPdv->confirmacoes = 0;
            }
            
            if (!$transacaoCarteiraPdv->idCarteiraPdv > 0) {
                throw new \Exception("É necessário informar a identificação da carteira");
            }
            
            if (!$transacaoCarteiraPdv->safe >= 0) {
                $transacaoCarteiraPdv->safe = 0;
            }
            
            if (!$transacaoCarteiraPdv->scriptPubKey >= 0) {
                $transacaoCarteiraPdv->scriptPubKey = "";
            }
            
            if (!$transacaoCarteiraPdv->solvable >= 0) {
                $transacaoCarteiraPdv->solvable = 0;
            }
            
            
            if (!$transacaoCarteiraPdv->spendable >= 0) {
                $transacaoCarteiraPdv->spendable = 0;
            }
            
            
            if (empty($transacaoCarteiraPdv->txid)) {
                throw new \Exception("Hash da transação inválido");
            }
            
            
            if (!$transacaoCarteiraPdv->vout >= 0) {
                $transacaoCarteiraPdv->vout = 0;
            }
            
            $result = $this->getByTxId($transacaoCarteiraPdv->txid, $transacaoCarteiraPdv->address, $transacaoCarteiraPdv->amount);
            
            if ($result != null) {
                throw new \Exception("Já existe uma transação com este txid");
            }
            
            unset($transacaoCarteiraPdv->moeda);
            $this->conexao->salvar($transacaoCarteiraPdv);
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    
    public function getByTxId($txid, $endereco, $valor) {
        if (empty($txid)) {
            throw new \Exception("É necessário informar o TXID");
        }
        
        $result = $this->conexao->listar( " txid = '{$txid}' AND address = '{$endereco}' AND amount = {$valor} ", null, null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    public function carregar(TransacaoCarteiraPdv &$transacaoCarteiraPdv, $carregar = true, $carregarMoeda = true) {
        
        if ($carregar) {
            $this->conexao->carregar($transacaoCarteiraPdv);
        }
        
        if ($carregarMoeda && $transacaoCarteiraPdv->idMoeda > 0) {
            $transacaoCarteiraPdv->moeda = new Moeda(Array("id" => $transacaoCarteiraPdv->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($transacaoCarteiraPdv->moeda);
        }
        
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $transacaoCarteiraPdv) {
            $this->carregar($transacaoCarteiraPdv, false, $carregarMoeda);
            $lista[] = $transacaoCarteiraPdv;
        }
        return $lista;
    }
    
    
}

?>