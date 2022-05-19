<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoicePdv {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Double 
     */
    public $valorBtc;
    
    /**
     *
     * @var String 
     */
    public $celular;
    
    /**
     *
     * @var Integer 
     */
    public $idPontoPdv;
    
    /**
     *
     * @var Double 
     */
    public $valorBrl;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    /**
     *
     * @var Double 
     */
    public $taxaBtc;
    
    /**
     *
     * @var String 
     */
    public $enderecoCarteira;
    
    /**
     *
     * @var String 
     */
    public $email;
    
    /**
     *
     * @var String 
     */
    public $cotacaoMoedaBtc;
    
    /**
     *
     * @var String 
     */
    public $idMoeda;
    
    /**
     *
     * @var String 
     */
    public $status;
    
    /**
     *
     * @var String 
     */
    public $callback;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataDeposito;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCancelamento; 
    
    /**
     *
     * @var Double 
     */
    public $saldoRecebido;
    
    /**
     *
     * @var Double 
     */
    public $cotacaoBtcBrl;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    /**
     *
     * @var PontoPdv
     */
    public $pontoPdv;
    
    
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
        $this->celular = ((isset($dados ['celular'])) ? ($dados ['celular']) : (null));
        $this->callback = ((isset($dados ['callback'])) ? ($dados ['callback']) : (null));
        $this->cotacaoMoedaBtc = ((isset($dados ['cotacao_moeda_btc'])) ? ($dados ['cotacao_moeda_btc']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->enderecoCarteira = ((isset($dados ['endereco_carteira'])) ? ($dados ['endereco_carteira']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idPontoPdv = ((isset($dados ['id_ponto_pdv'])) ? ($dados ['id_ponto_pdv']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->taxaBtc = ((isset($dados ['taxa_btc'])) ? ($dados ['taxa_btc']) : (null));
        $this->valorBrl = ((isset($dados ['valor_brl'])) ? ($dados ['valor_brl']) : (null));
        $this->valorBtc = ((isset($dados ['valor_btc'])) ? ($dados ['valor_btc']) : (null));
        $this->saldoRecebido = ((isset($dados ['saldo_recebido'])) ? ($dados ['saldo_recebido']) : (null));
        $this->cotacaoBtcBrl = ((isset($dados ['cotacao_btc_brl'])) ? ($dados ['cotacao_btc_brl']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->dataDeposito = ((isset($dados['data_deposito'])) ? ($dados['data_deposito'] instanceof \Utils\Data ? $dados['data_deposito'] : 
            new \Utils\Data(substr($dados['data_deposito'], 0, 19))) : (null));
        $this->dataCancelamento = ((isset($dados['data_cancelamento'])) ? ($dados['data_cancelamento'] instanceof \Utils\Data ? $dados['data_cancelamento'] : 
            new \Utils\Data(substr($dados['data_cancelamento'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "invoices_pdv";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new InvoicePdv();
    }


}

?>