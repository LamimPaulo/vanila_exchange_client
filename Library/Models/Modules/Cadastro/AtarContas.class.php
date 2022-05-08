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
class AtarContas { 
    
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
     * @var Integer
     */
    public $idClienteAtar;
    
    /**
     * 
     * @var Data
     */
    public $dataCadastro; 
    
    /**
     * 
     * @var String
     */
    public $tipo; 
    
    /**
     * 
     * @var Decimal
     */
    public $valor;     
        
    /**
     * 
     * @var Integer
     */
    public $confirmado;
    
    /**
     * 
     * @var Data
     */
    public $dataConfirmacao; 
    
    /**
     * 
     * @var String
     */
    public $idTransacao; 
    
    /**
     * 
     * @var Decimal
     */
    public $taxa;  
    
    /**
     * 
     * @var String
     */
    public $retorno; 
    
    /**
     * 
     * @var Decimal
     */
    public $tarifa;  
    
    /**
     * 
     * @var String
     */
    public $documentAtar; 
    
    /**
     * 
     * @var Decimal
     */
    public $valorCreditado;
    
    /**
     * 
     * @var Decimal
     */
    public $taxaPorcentagem;
    
    
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
        $this->idClienteAtar = ((isset($dados ['id_cliente_atar'])) ? ($dados ['id_cliente_atar']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->dataConfirmacao = ((isset($dados['data_confirmacao'])) ? ($dados['data_confirmacao'] instanceof \Utils\Data ? $dados['data_confirmacao'] : new \Utils\Data(substr($dados['data_confirmacao'], 0, 19))) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->confirmado = ((isset($dados ['confirmado'])) ? ($dados ['confirmado']) : (null));   
        $this->idTransacao = ((isset($dados ['id_transacao'])) ? ($dados ['id_transacao']) : (null)); 
        $this->taxa = ((isset($dados ['taxa'])) ? ($dados ['taxa']) : (null));
        $this->retorno = ((isset($dados ['retorno'])) ? ($dados ['retorno']) : (null));
        $this->tarifa = ((isset($dados ['tarifa'])) ? ($dados ['tarifa']) : (null));
        $this->documentAtar = ((isset($dados ['document_atar'])) ? ($dados ['document_atar']) : (null));
        $this->valorCreditado = ((isset($dados ['valor_creditado'])) ? ($dados ['valor_creditado']) : (null));
        $this->taxaPorcentagem = ((isset($dados ['taxa_porcentagem'])) ? ($dados ['taxa_porcentagem']) : (null));
    }
    
    public function getTable() {
        return "atar_contas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new AtarContas();
    }

}
