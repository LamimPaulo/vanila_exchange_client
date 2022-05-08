<?php

namespace Models\Modules\Cadastro;

class MovimentacaoMes {
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var String 
     */
    public $periodoRef;
    
    /**
     *
     * @var Double 
     */
    public $deposito;
    
    /**
     *
     * @var Double 
     */
    public $saque;
    
    /**
     *
     * @var Double 
     */
    public $venda;
    
    /**
     *
     * @var Double 
     */
    public $compra;
    
    /**
     *
     * @var Integer 
     */
    public $pago;
    
    /**
     *
     * @var Double 
     */
    public $valorPago;
    
    /**
     *
     * @var Double 
     */
    public $btcPago;
    
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
        $this->compra = ((isset($dados ['compra'])) ? ($dados ['compra']) : (null));
        $this->deposito = ((isset($dados ['deposito'])) ? ($dados ['deposito']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->pago = ((isset($dados ['pago'])) ? ($dados ['pago']) : (null));
        $this->periodoRef = ((isset($dados ['periodo_ref'])) ? ($dados ['periodo_ref']) : (null));
        $this->saque = ((isset($dados ['saque'])) ? ($dados ['saque']) : (null));
        $this->valorPago = ((isset($dados ['valor_pago'])) ? ($dados ['valor_pago']) : (null));
        $this->venda = ((isset($dados ['venda'])) ? ($dados ['venda']) : (null));
        $this->btcPago = ((isset($dados ['btc_pago'])) ? ($dados ['btc_pago']) : (null));
    }
    
    public function getTable() {
        return "movimentacoes_mes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new MovimentacaoMes();
    }
    
}