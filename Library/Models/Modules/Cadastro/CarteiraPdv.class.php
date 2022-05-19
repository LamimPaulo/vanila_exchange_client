<?php

namespace Models\Modules\Cadastro;

class CarteiraPdv {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    
    /**
     *
     * @var String 
     */
    public $enderecoCarteira;
    
    
    /**
     *
     * @var Double 
     */
    public $saldoBtc;
    
    /**
     *
     * @var Double 
     */
    public $saldoGastoBtc;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idReferenciaCliente;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAtualizacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $sacado;
    
    /**
     *
     * @var Integer
     */
    public $confirmado;
    
    /**
     *
     * @var Integer
     */
    public $transferido;
    
    /**
     *
     * @var ReferenciaCliente 
     */
    public $referenciaCliente;
    
    /**
     *
     * @var String 
     */
    public $parametroUm;
    
    /**
     *
     * @var String 
     */
    public $parametroDois;
    
    /**
     *
     * @var String 
     */
    public $parametroTres;
    
    
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
        $this->enderecoCarteira = ((isset($dados ['endereco_carteira'])) ? ($dados ['endereco_carteira']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idReferenciaCliente = ((isset($dados ['id_referencia_cliente'])) ? ($dados ['id_referencia_cliente']) : (null));
        $this->sacado = ((isset($dados ['sacado'])) ? ($dados ['sacado']) : (null));
        $this->transferido = ((isset($dados ['transferido'])) ? ($dados ['transferido']) : (null));
        $this->saldoBtc = ((isset($dados ['saldo_btc'])) ? ($dados ['saldo_btc']) : (null));
        $this->confirmado = ((isset($dados ['confirmado'])) ? ($dados ['confirmado']) : (null));
        $this->saldoGastoBtc = ((isset($dados ['saldo_gasto_btc'])) ? ($dados ['saldo_gasto_btc']) : (null));
        $this->dataAtualizacao = ((isset($dados['data_atualizacao'])) ? ($dados['data_atualizacao'] instanceof \Utils\Data ? $dados['data_atualizacao'] : 
            new \Utils\Data(substr($dados['data_atualizacao'], 0, 19))) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->parametroUm = ((isset($dados ['parametro_um'])) ? ($dados ['parametro_um']) : (null));
        $this->parametroDois = ((isset($dados ['parametro_dois'])) ? ($dados ['parametro_dois']) : (null));
        $this->parametroTres = ((isset($dados ['parametro_tres'])) ? ($dados ['parametro_tres']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
    }
    
    public function getTable() {
        return "carteiras_pdv";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new CarteiraPdv();
    }

    public function getSaldo() {
        return ($this->saldoBtc);
    }
    
}