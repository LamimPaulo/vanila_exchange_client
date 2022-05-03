<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class StatusCore {


    /**
     *
     * @var Integer 
     */
    public $id;
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaAtualizacao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaAtualizacaoCore;
    
    /**
     *
     * @var String 
     */
    public $walletVersion;
    
    /**
     *
     * @var Double 
     */
    public $unconfirmedBalance;
    
    /**
     *
     * @var Double 
     */
    public $balance;
    
    /**
     *
     * @var String 
     */
    public $txcount;
    
    
    
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
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->balance = ((isset($dados ['balance'])) ? ($dados ['balance']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->dataUltimaAtualizacao = ((isset($dados['data_ultima_atualizacao'])) ? ($dados['data_ultima_atualizacao'] instanceof \Utils\Data ? $dados['data_ultima_atualizacao'] : 
            new \Utils\Data(substr($dados['data_ultima_atualizacao'], 0, 19))) : (null));
        $this->dataUltimaAtualizacaoCore = ((isset($dados['data_ultima_atualizacao_core'])) ? ($dados['data_ultima_atualizacao_core'] instanceof \Utils\Data ? $dados['data_ultima_atualizacao_core'] : 
            new \Utils\Data(substr($dados['data_ultima_atualizacao_core'], 0, 19))) : (null));
        $this->txcount = ((isset($dados ['txcount'])) ? ($dados ['txcount']) : (null));
        $this->walletVersion = ((isset($dados ['wallet_version'])) ? ($dados ['wallet_version']) : (null));
    }
    
    public function getTable() {
        return "status_core";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new StatusCore();
    }


}

?>