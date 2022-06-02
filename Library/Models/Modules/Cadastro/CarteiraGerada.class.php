<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class CarteiraGerada {

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
     * @var String 
     */
    public $address;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    /**
     *
     * @var Integer 
     */
    public $utilizada;
    
    /**
     *
     * @var String 
     */
    public $hash;
    
    /**
     *
     * @var String 
     */
    public $seed;
    
    /**
     *
     * @var Integer 
     */
    public $inutilizada;
    
    
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
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->utilizada = ((isset($dados ['utilizada'])) ? ($dados ['utilizada']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->seed = ((isset($dados ['seed'])) ? ($dados ['seed']) : (null));
        $this->inutilizada = ((isset($dados ['inutilizada'])) ? ($dados ['inutilizada']) : (null));
    }
    
    public function getTable() {
        return "carteiras_geradas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new CarteiraGerada();
    }


}

?>