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
class Layout { 
    
    /**
     * Chave primária da tabela
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
     * @var String
     */
    public $orderBook;
    
    
     /**
     * 
     * @var String
     */
    public $dashboard;
    
        
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
        $this->orderBook = ((isset($dados ['order_book'])) ? ($dados ['order_book']) : (null));
        $this->dashboard = ((isset($dados ['dashboard'])) ? ($dados ['dashboard']) : (null));
        
    }
    
    public function getTable() {
        return "layouts";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Layout();
    }

}
