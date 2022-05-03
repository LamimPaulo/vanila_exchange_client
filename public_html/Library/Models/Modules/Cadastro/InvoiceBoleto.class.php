<?php

namespace Models\Modules\Cadastro;

class InvoiceBoleto {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idBoletoCliente;
    
    
    /**
     *
     * @var String 
     */
    public $status;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataPagamento;
    
    /**
     *
     * @var Double 
     */
    public $valorReal;
    
    /**
     *
     * @var Double 
     */
    public $valorBtc;
    
    /**
     *
     * @var Integer 
     */
    public $idInvoice;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExpiracaoInvoice;
    
    /**
     *
     * @var String 
     */
    public $address;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataRecargaFinalizada;
    
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
        
        $this->dataExpiracaoInvoice = ((isset($dados['data_expiracao_invoice'])) ? ($dados['data_expiracao_invoice'] instanceof \Utils\Data ? $dados['data_expiracao_invoice'] : 
        new \Utils\Data(substr($dados['data_expiracao_invoice'], 0, 19))) : (null));
        
        $this->dataPagamento = ((isset($dados['data_pagamento'])) ? ($dados['data_pagamento'] instanceof \Utils\Data ? $dados['data_pagamento'] : 
        new \Utils\Data(substr($dados['data_pagamento'], 0, 19))) : (null));
        
        $this->dataRecargaFinalizada = ((isset($dados['data_recarga_finalizada'])) ? ($dados['data_recarga_finalizada'] instanceof \Utils\Data ? $dados['data_recarga_finalizada'] : 
        new \Utils\Data(substr($dados['data_recarga_finalizada'], 0, 19))) : (null));
        
        $this->idBoletoCliente = ((isset($dados ['id_boleto_cliente'])) ? ($dados ['id_boleto_cliente']) : (null));
        $this->idInvoice = ((isset($dados ['id_invoice'])) ? ($dados ['id_invoice']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->valorBtc = ((isset($dados ['valor_btc'])) ? ($dados ['valor_btc']) : (null));
        $this->valorReal = ((isset($dados ['valor_real'])) ? ($dados ['valor_real']) : (null));
        $this->address = ((isset($dados ['address'])) ? ($dados ['address']) : (null));
        
    }
    
    public function getTable() {
        return "invoice_boleto";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new InvoiceBoleto();
    }
    
    
    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO: return "Aguardando Pagamento";
            case \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO: return "Cancelado";
            case \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO: return "Pago";
            case \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO: return "Recarga Efetuada";
            default:
                "Status Desconhecido";
        }
    }
}