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
class TransacaoInvoicePdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TransacaoInvoicePdv());
        } else {
            $this->conexao = new GenericModel($adapter, new TransacaoInvoicePdv());
        }
    }
    
    public function salvar(TransacaoInvoicePdv &$transacaoInvoicePdv) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            if (empty($transacaoInvoicePdv->address)) {
                throw new \Exception("É necessário informar o endereço");
            }
            
            if ($transacaoInvoicePdv->amount <= 0) {
                throw new \Exception("É necessário informar o volume");
            }
            
            if ($transacaoInvoicePdv->confirmations < 0) {
                $transacaoInvoicePdv->confirmations = 0;
            }
            
            if (!$transacaoInvoicePdv->idInvoicePdv > 0) {
                throw new \Exception("É necessário informar a identificação da invoice");
            }
            
            if ($transacaoInvoicePdv->safe < 0) {
                $transacaoInvoicePdv->safe = 0;
            }
            
            if (empty($transacaoInvoicePdv->scriptPubKey)) {
                $transacaoInvoicePdv->scriptPubKey = "";
            }
            
            if ($transacaoInvoicePdv->solvable < 0) {
                $transacaoInvoicePdv->solvable = 0;
            }
            
            
            if ($transacaoInvoicePdv->spendable < 0) {
                $transacaoInvoicePdv->spendable = 0;
            }
            
            
            if (empty($transacaoInvoicePdv->txid)) {
                throw new \Exception("Hash da transação inválido");
            }
            
            
            if ($transacaoInvoicePdv->vout < 0) {
                $transacaoInvoicePdv->vout = 0;
            }
            
            $result = $this->getByTxId($transacaoInvoicePdv->txid, $transacaoInvoicePdv->address, $transacaoInvoicePdv->amount);
            if ($result != null) {
                throw new \Exception("Já existe uma transação com este txid");
            }
            
            $this->conexao->salvar($transacaoInvoicePdv);
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function getByTxId($txid, $wallet, $amount) {
        if (empty($txid)) {
            throw new \Exception("É necessário informar o TXID");
        }
        $result = $this->conexao->listar( " txid = '{$txid}' AND address= '{$wallet}' AND amount = {$amount} ", null, null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
}

?>