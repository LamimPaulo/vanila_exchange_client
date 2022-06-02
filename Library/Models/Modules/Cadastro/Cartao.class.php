<?php

namespace Models\Modules\Cadastro;

class Cartao {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $numero;
    
    /**
     * 
     * @var String
     */
    public $senha;

    
    /**
     * 
     * @var boolean
     */
    public $ativo;
    
    /**
     *
     * @var String 
     */
    public $validade;
    
    /**
     *
     * @var String 
     */
    public $bandeira;
    
    /**
     *
     * @var Integer 
     */
    public $idPedidoCartao;
    
    /**
     *
     * @var PedidoCartao 
     */
    public $pedidoCartao;
    
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
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idPedidoCartao = ((isset($dados ['id_pedido_cartao'])) ? ($dados ['id_pedido_cartao']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->senha = ((isset($dados ['senha'])) ? ($dados ['senha']) : (null));
        $this->validade = ((isset($dados ['validade'])) ? ($dados ['validade']) : (null));
        $this->bandeira = ((isset($dados ['bandeira'])) ? ($dados ['bandeira']) : (null));
    }
    
    public function getTable() {
        return "cartoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Cartao();
    }


}