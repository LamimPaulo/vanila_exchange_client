<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TransacaoCarteiraPdv {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $spendable;
    
    /**
     *
     * @var Double 
     */
    public $amount;
    
    /**
     *
     * @var Integer 
     */
    public $idCarteiraPdv;
    
    /**
     *
     * @var Integer 
     */
    public $vout;
    
    /**
     *
     * @var Integer 
     */
    public $solvable;
    
    /**
     *
     * @var Integer 
     */
    public $safe;
    
    /**
     *
     * @var String 
     */
    public $txid;
    
    /**
     *
     * @var Integer 
     */
    public $confirmacoes;
    
    /**
     *
     * @var String 
     */
    public $address;
    
    /**
     *
     * @var String 
     */
    public $scriptPubKey;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
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
        $this->address = ((isset($dados ['address'])) ? ($dados ['address']) : (null));
        $this->amount = ((isset($dados ['amount'])) ? ($dados ['amount']) : (null));
        $this->confirmacoes = ((isset($dados ['confirmacoes'])) ? ($dados ['confirmacoes']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCarteiraPdv = ((isset($dados ['id_carteira_pdv'])) ? ($dados ['id_carteira_pdv']) : (null));
        $this->safe = ((isset($dados ['safe'])) ? ($dados ['safe']) : (null));
        $this->scriptPubKey = ((isset($dados ['script_pub_key'])) ? ($dados ['script_pub_key']) : (null));
        $this->solvable = ((isset($dados ['solvable'])) ? ($dados ['solvable']) : (null));
        $this->spendable = ((isset($dados ['spendable'])) ? ($dados ['spendable']) : (null));
        $this->txid = ((isset($dados ['txid'])) ? ($dados ['txid']) : (null));
        $this->vout = ((isset($dados ['vout'])) ? ($dados ['vout']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
    }
    
    public function getTable() {
        return "transacoes_carteiras_pdv";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new TransacaoCarteiraPdv();
    }


}

?>