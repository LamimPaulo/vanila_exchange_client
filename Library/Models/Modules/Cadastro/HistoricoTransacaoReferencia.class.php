<?php

namespace Models\Modules\Cadastro;

class HistoricoTransacaoReferencia {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idReferenciaCliente;
    
    
    /**
     *
     * @var \Utils\Data
     */
    public $data;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var Integer 
     */
    public $idCarteiraPdv;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    /**
     *
     * @var ReferenciaCliente 
     */
    public $referenciaCliente;
    
    /**
     *
     * @var CarteiraPdv
     */
    public $carteiraPdv;
    
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
        $this->idReferenciaCliente = ((isset($dados ['id_referencia_cliente'])) ? ($dados ['id_referencia_cliente']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->idCarteiraPdv = ((isset($dados ['id_carteira_pdv'])) ? ($dados ['id_carteira_pdv']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
    }
    
    public function getTable() {
        return "historico_transacoes_referencias";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new HistoricoTransacaoReferencia();
    }

    
}