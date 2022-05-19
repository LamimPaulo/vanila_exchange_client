<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Carteira {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $nome;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $data;
    
    /**
     *
     * @var String
     */
    public $endereco;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    
    /**
     *
     * @var Integer 
     */
    public $principal;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var String 
     */
    public $seed;
    
    /**
     *
     * @var String 
     */
    public $hash;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    /**
     *
     * @var Integer 
     */
    public $inutilizada;
    
    /**
     *
     * @var Integer 
     */
    public $callbackDeposito;
    
     /**
     *
     * @var Integer 
     */
    public $prioridade;
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->principal = ((isset($dados ['principal'])) ? ($dados ['principal']) : (null));
        $this->endereco = ((isset($dados ['endereco'])) ? ($dados ['endereco']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->seed = ((isset($dados ['seed'])) ? ($dados ['seed']) : (null));
        $this->inutilizada = ((isset($dados ['inutilizada'])) ? ($dados ['inutilizada']) : (null));
        $this->callbackDeposito = ((isset($dados ['callback_deposito'])) ? ($dados ['callback_deposito']) : (null));
        $this->prioridade = ((isset($dados ['prioridade'])) ? ($dados ['prioridade']) : (null));
        
    }
    
    public function getTable() {
        return "carteiras_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Carteira();
    }


}

?>