<?php

namespace Models\Modules\Cadastro;

class PedidoCartao {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $numeroCartao;
    
    /**
     *
     * @var String 
     */
    public $nomeCartao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataPedido;
    
    /**
     *
     * @var Double 
     */
    public $valorTotal;
    
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
     * @var Integer  
     */
    public $idCliente;
    
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
    public $dataExpiracaoInvoice;
    
    /**
     *
     * @var Integer 
     */
    public $transferToAccountEstimateTimestamp;
    
    /**
     *
     * @var Integer 
     */
    public $transferToAccountTimestamp;
    
    /**
     *
     * @var Double 
     */
    public $digitalCurrencyAmount;
    
    /**
     *
     * @var String 
     */
    public $digitalCurrency;
    
    /**
     *
     * @var String 
     */
    public $redirectUrl;
    
    /**
     *
     * @var Integer 
     */
    public $expirationTimestamp;
    
    /**
     *
     * @var String 
     */
    public $tc0015Id;
    
    /**
     *
     * @var Double 
     */
    public $currencyTotal;
    
    /**
     *
     * @var Double 
     */
    public $digitalCurrencyAmountPaid;
    
    /**
     *
     * @var String 
     */
    public $currency;
    
    /**
     *
     * @var String 
     */
    public $customId;
    
    /**
     *
     * @var Double 
     */
    public $digitalCurrencyQuotation;
    
    /**
     *
     * @var String 
     */
    public $notificationEmail;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $transferToAccountEstimateDate;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $transferToAccountDate;
    
    /**
     *
     * @var String 
     */
    public $redirectUrlReturn;
    
    /**
     *
     * @var type Integer
     */
    public $ativo;
    
    /**
     *
     * @var String 
     */
    public $idCartao;
    
    /**
     *
     * @var String 
     */
    public $senhaCartao;
    
    /**
     *
     * @var Integer 
     */
    public $cancelado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCancelamentoCartao;
    
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
     * @var Double 
     */
    public $saldo;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $ultimaAtualizacaoCartao;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
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
        $this->valorTotal = ((isset($dados ['valor_total'])) ? ($dados ['valor_total']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idInvoice = ((isset($dados ['id_invoice'])) ? ($dados ['id_invoice']) : (null));
        $this->nomeCartao = ((isset($dados ['nome_cartao'])) ? ($dados ['nome_cartao']) : (null));
        $this->numeroCartao = ((isset($dados ['numero_cartao'])) ? ($dados ['numero_cartao']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->address = ((isset($dados ['address'])) ? ($dados ['address']) : (null));
        $this->dataPedido = ((isset($dados['data_pedido'])) ? ($dados['data_pedido'] instanceof \Utils\Data ? $dados['data_pedido'] : 
        new \Utils\Data(substr($dados['data_pedido'], 0, 19))) : (null));
        $this->dataCancelamentoCartao = ((isset($dados['data_cancelamento_cartao'])) ? ($dados['data_cancelamento_cartao'] instanceof \Utils\Data ? $dados['data_cancelamento_cartao'] : 
        new \Utils\Data(substr($dados['data_cancelamento_cartao'], 0, 19))) : (null));
        $this->cancelado = ((isset($dados ['cancelado'])) ? ($dados ['cancelado']) : (null));
        
        $this->transferToAccountEstimateTimestamp = ((isset($dados ['transfer_to_account_estimate_timestamp'])) ? ($dados ['transfer_to_account_estimate_timestamp']) : (null));
        $this->transferToAccountTimestamp = ((isset($dados ['transfer_to_account_timestamp'])) ? ($dados ['transfer_to_account_timestamp']) : (null));
        $this->digitalCurrencyAmount = ((isset($dados ['digital_currency_amount'])) ? ($dados ['digital_currency_amount']) : (null));
        $this->digitalCurrency = ((isset($dados ['digital_currency'])) ? ($dados ['digital_currency']) : (null));
        $this->redirectUrl = ((isset($dados ['redirect_url'])) ? ($dados ['redirect_url']) : (null));
        $this->expirationTimestamp = ((isset($dados ['expiration_timestamp'])) ? ($dados ['expiration_timestamp']) : (null));
        $this->tc0015Id = ((isset($dados ['tc0015_id'])) ? ($dados ['tc0015_id']) : (null));
        $this->currencyTotal = ((isset($dados ['currency_total'])) ? ($dados ['currency_total']) : (null));
        $this->digitalCurrencyAmountPaid = ((isset($dados ['digital_currency_amount_paid'])) ? ($dados ['digital_currency_amount_paid']) : (null));
        $this->currency = ((isset($dados ['currency'])) ? ($dados ['currency']) : (null));
        $this->customId = ((isset($dados ['custom_id'])) ? ($dados ['custom_id']) : (null));
        $this->digitalCurrencyQuotation = ((isset($dados ['digital_currency_quotation'])) ? ($dados ['digital_currency_quotation']) : (null));
        $this->notificationEmail = ((isset($dados ['notification_email'])) ? ($dados ['notification_email']) : (null));
        $this->transferToAccountEstimateDate = ((isset($dados['transfer_to_account_estimate_date'])) ? ($dados['transfer_to_account_estimate_date'] instanceof \Utils\Data ? $dados['transfer_to_account_estimate_date'] : 
        new \Utils\Data(substr($dados['transfer_to_account_estimate_date'], 0, 19))) : (null));
        $this->transferToAccountDate = ((isset($dados['transfer_to_account_date'])) ? ($dados['transfer_to_account_date'] instanceof \Utils\Data ? $dados['transfer_to_account_date'] : 
        new \Utils\Data(substr($dados['transfer_to_account_date'], 0, 19))) : (null));
        $this->redirectUrlReturn = ((isset($dados ['redirect_url_return'])) ? ($dados ['redirect_url_return']) : (null));
        
        
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (0));
        $this->senhaCartao = ((isset($dados ['senha_cartao'])) ? ($dados ['senha_cartao']) : (null));
        $this->idCartao = ((isset($dados ['id_cartao'])) ? ($dados ['id_cartao']) : (null));
        $this->validade = ((isset($dados ['validade'])) ? ($dados ['validade']) : (null));
        $this->bandeira = ((isset($dados ['bandeira'])) ? ($dados ['bandeira']) : (null));
        
        $this->saldo = ((isset($dados ['saldo'])) ? ($dados ['saldo']) : (null));
        $this->ultimaAtualizacaoCartao = ((isset($dados['ultima_atualizacao_cartao'])) ? ($dados['ultima_atualizacao_cartao'] instanceof \Utils\Data ? $dados['ultima_atualizacao_cartao'] : 
        new \Utils\Data(substr($dados['ultima_atualizacao_cartao'], 0, 19))) : (null));
        
    }
    
    public function getTable() {
        return "pedidos_cartoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new PedidoCartao();
    }
    
    
    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO: return "Aguardando Pagamento";
            case \Utils\Constantes::STATUS_PEDIDO_CARTAO_CANCELADO: return "Cancelado";
            case \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO: return "Pago";
            default:
                "Status Desconhecido";
        }
    }
}