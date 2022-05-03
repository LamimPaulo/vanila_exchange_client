<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

/**
 *
 *
 * @author willianchiquetto
 */
class AtarClientes { 
    
        /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;
    
    /**
     * 
     * @var Data
     */
    public $dataCadastro; 
    
    /**
     * 
     * @var String
     */
    public $descricao; 
    
    
     /**
     * 
     * @var String
     */
    public $idAtar;
    
    /**
     * 
     * @var String
     */
    public $documentAtar;

    
     /**
     * 
     * @var Integer
     */
    public $idCliente;
    
    /**
     * 
     * @var Integer
     */
    public $ativo;

    
    
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
        $this->idAtar = ((isset($dados ['id_atar'])) ? ($dados ['id_atar']) : (null));
        $this->documentAtar = ((isset($dados ['document_atar'])) ? ($dados ['document_atar']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
    }

    public function getTable() {
        return "atar_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new AtarClientes();
    }

}
