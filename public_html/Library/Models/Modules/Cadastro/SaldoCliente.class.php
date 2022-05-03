<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class SaldoCliente {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Double 
     */
    public $saldo;
    
    /**
     *
     * @var Double 
     */
    public $saldoBloqueado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaAtualizacao;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->dataUltimaAtualizacao = ((isset($dados['data_ultima_atualizacao'])) ? ($dados['data_ultima_atualizacao'] instanceof \Utils\Data ? $dados['data_ultima_atualizacao'] : 
            new \Utils\Data(substr($dados['data_ultima_atualizacao'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->saldo = ((isset($dados ['saldo'])) ? ($dados ['saldo']) : (null));
        $this->saldoBloqueado = ((isset($dados ['saldo_bloqueado'])) ? ($dados ['saldo_bloqueado']) : (null));
    }
    
    public function getTable() {
        return "saldos_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new SaldoCliente();
    }


}

?>