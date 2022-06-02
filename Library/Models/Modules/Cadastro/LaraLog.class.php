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
class LaraLog { 
    
        /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;
    
     /**
     * 
     * @var String
     */
    public $response;
    
    /**
     * 
     * @var String
     */
    public $categoria;
    
    /**
     * 
     * @var Data
     */
    public $data; 
    
    
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
        $this->response = ((isset($dados ['response'])) ? ($dados ['response']) : (null));
        $this->categoria = ((isset($dados ['categoria'])) ? ($dados ['categoria']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : new \Utils\Data(substr($dados['data'], 0, 19))) : (null));

    }
    
    public function getTable() {
        return "lara_log";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LaraLog();
    }

}
