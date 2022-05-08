<?php

namespace Models\Modules\Cadastro;

class ReferenciaCliente {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $referencia;
    
    
    /**
     *
     * @var \Utils\Data
     */
    public $dataCriacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idEstabelecimento;
    
    /**
     *
     * @var Estabelecimento
     */
    public $estabelecimento;
    
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
        $this->referencia = ((isset($dados ['referencia'])) ? ($dados ['referencia']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idEstabelecimento = ((isset($dados ['id_estabelecimento'])) ? ($dados ['id_estabelecimento']) : (null));
        
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "referencias_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ReferenciaCliente();
    }

    
}