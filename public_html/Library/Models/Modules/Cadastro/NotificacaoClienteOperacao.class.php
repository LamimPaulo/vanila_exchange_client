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
class NotificacaoClienteOperacao { 
    
        /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var Interger
     */
    public $idCliente;
    
    /**
     * 
     * @var Interger
     */
    public $idNotificacaoOperacao; 
    
    /**
     * 
     * 
     * @var Interger
     */
    public $idNotificacaoComunicacao;
    
    /**
     * 
     * @var Interger
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
        $this->idNotificacaoComunicacao = ((isset($dados ['id_notificacao_comunicacao'])) ? ($dados ['id_notificacao_comunicacao']) : (null));
        $this->idNotificacaoOperacao = ((isset($dados ['id_notificacao_operacao'])) ? ($dados ['id_notificacao_operacao']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
    }
    
    public function getTable() {
        return "notificacao_cliente_operacao";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotificacaoClienteOperacao();
    }

}
