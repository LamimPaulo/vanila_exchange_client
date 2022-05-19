<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

/**
 * Description of NotificacaoClienteOperacao
 *
 * @author willianchiquetto
 */
class NotificacaoComunicacao { 
    
        /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var String
     */
    public $nome;
    
    /**
     * 
     * @var String
     */
    public $descricao; 
    
    /**
     * 
     * 
     * @var Interger
     */
    public $ativo;
    
    /**
     * 
     * @var Interger
     */
    public $novo;
    
        /**
     * 
     * @var String
     */
    public $traducao; 


    

    
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
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->novo = ((isset($dados ['novo'])) ? ($dados ['novo']) : (null));
        $this->traducao = ((isset($dados ['traducao'])) ? ($dados ['traducao']) : (null));
    }
    
    public function getTable() {
        return "notificacao_comunicacao";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotificacaoComunicacao();
    }

}
