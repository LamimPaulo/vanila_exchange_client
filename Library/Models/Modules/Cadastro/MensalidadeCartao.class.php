<?php

namespace Models\Modules\Cadastro;

class MensalidadeCartao {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idPedidoCartao;
    
    /**
     *
     * @var String 
     */
    public $mesRef;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataValidade;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var Double 
     */
    public $valorBtc;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExpiracaoInvoice;
    
    /**
     *
     * @var Integer 
     */
    public $idInvoice;
    
    /**
     *
     * @var String 
     */
    public $address;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataPagamento;
    
    /**
     *
     * @var String 
     */
    public $status;
    
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
        $this->address = ((isset($dados ['address'])) ? ($dados ['address']) : (null));
        $this->dataExpiracaoInvoice = ((isset($dados['data_expiracao_invoice'])) ? ($dados['data_expiracao_invoice'] instanceof \Utils\Data ? 
                $dados['data_expiracao_invoice'] : new \Utils\Data(substr($dados['data_expiracao_invoice'], 0, 19))) : (null));
        $this->dataPagamento = ((isset($dados ['data_pagamento'])) ? ($dados ['data_pagamento'] instanceof \Utils\Data ? $dados ['data_pagamento'] : 
            new \Utils\Data(substr($dados ['data_pagamento'], 0, 19))) : (null));
        $this->dataValidade = ((isset($dados ['data_validade'])) ? ($dados ['data_validade'] instanceof \Utils\Data ? $dados ['data_validade'] : 
            new \Utils\Data(substr($dados ['data_validade'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idInvoice = ((isset($dados ['id_invoice'])) ? ($dados ['id_invoice']) : (null));
        $this->idPedidoCartao = ((isset($dados ['id_pedido_cartao'])) ? ($dados ['id_pedido_cartao']) : (null));
        $this->mesRef = ((isset($dados ['mes_ref'])) ? ($dados ['mes_ref']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->valorBtc = ((isset($dados ['valor_btc'])) ? ($dados ['valor_btc']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
    }
    
    public function getTable() {
        return "mensalidades_cartoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new MensalidadeCartao();
    }

    
}