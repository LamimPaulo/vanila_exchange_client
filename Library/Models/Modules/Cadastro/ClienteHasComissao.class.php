<?php

namespace Models\Modules\Cadastro;

class ClienteHasComissao {
    
    /**
     *
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
     * @var Double 
     */
    public $deposito;
    
    /**
     *
     * @var Double 
     */
    public $venda;
    
    /**
     *
     * @var Double 
     */
    public $boleto;
    
    /**
     *
     * @var Integer 
     */
    public $utilizar;
    
    /**
     *
     * @var Double 
     */
    public $saque;
    
    /**
     *
     * @var Double 
     */
    public $remessa;
    
    /**
     *
     * @var Double 
     */
    public $compra;
    
    
    
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
        $this->boleto = ((isset($dados ['boleto'])) ? ($dados ['boleto']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->compra = ((isset($dados ['compra'])) ? ($dados ['compra']) : (null));
        $this->deposito = ((isset($dados ['deposito'])) ? ($dados ['deposito']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->remessa = ((isset($dados ['remessa'])) ? ($dados ['remessa']) : (null));
        $this->saque = ((isset($dados ['saque'])) ? ($dados ['saque']) : (null));
        $this->utilizar = ((isset($dados ['utilizar'])) ? ($dados ['utilizar']) : (null));
        $this->venda = ((isset($dados ['venda'])) ? ($dados ['venda']) : (null));
    }
    
    public function getTable() {
        return "clientes_has_comissoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasComissao();
    }


    
}