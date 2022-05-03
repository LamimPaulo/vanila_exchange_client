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
class NotaFiscalOperacaoCliente { 
    
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;
    
    /**
     * 
     * 
     * @var Interger
     */
    public $idCliente;
    
    /**
     * 
     * @var Interger
     */
    public $saqueAtivo;
    
    /**
     * 
     * @var Interger
     */
    public $depositoAtivo;

    
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
        $this->saqueAtivo = ((isset($dados ['saque_ativo'])) ? ($dados ['saque_ativo']) : (null));
        $this->depositoAtivo = ((isset($dados ['deposito_ativo'])) ? ($dados ['deposito_ativo']) : (null));
    }
    
    public function getTable() {
        return "nf_operacao_cliente";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotaFiscalOperacaoCliente();
    }

}
