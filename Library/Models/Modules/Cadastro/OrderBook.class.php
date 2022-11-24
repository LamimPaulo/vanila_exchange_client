<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OrderBook {

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
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var Double 
     */
    public $volumeCurrency;
    
    /**
     *
     * @var Double 
     */
    public $valorCotacao;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Integer 
     */
    public $executada;

    public $internalFreeze;

    public $internalLink;
    
    /**
     *
     * @var Integer 
     */
    public $cancelada;
    
    /**
     *
     * @var Double 
     */
    public $volumeExecutado;
    
    /**
     *
     * @var Double 
     */
    public $percentualTaxa;
    
    /**
     *
     * @var Double 
     */
    public $valorTaxa;
    
    /**
     *
     * @var Double 
     */
    public $valorTaxaExecutada;
    
    /**
     *
     * @var Integer 
     */
    public $direta;
    
    
    /**
     *
     * @var Double 
     */
    public $valorCotacaoReferencia;
    
    /**
     *
     * @var Integer 
     */
    public $idParidade;
    
    /**
     *
     * @var String 
     */
    public $nomeCliente;
    
    /**
     *
     * @var String 
     */
    public $symbolMoedaBook;
    
    /**
     *
     * @var String 
     */
    public $idMoedaBook;
    
    /**
     *
     * @var String 
     */
    public $symbol;
    
    /**
     *
     * @var String 
     */
    public $idMoedaTrade;
    
    /**
     *
     * @var String 
     */
    public $symbolMoedaTrade;
    
    /**
     *
     * @var type 
     */
    public $volumeBloqueado;
    
    
    /**
     *
     * @var type 
     */
    public $idMoedaBloqueada;
    
    
    /**
     *
     * @var Paridade 
     */
    public $paridade;
    
    /**
     *
     * @var String 
     */
    public $idSession;
    
    /**
     *
     * @var String 
     */
    public $ipSession;
    
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
        $this->idParidade = ((isset($dados ['id_paridade'])) ? ($dados ['id_paridade']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->valorCotacao = ((isset($dados ['valor_cotacao'])) ? ($dados ['valor_cotacao']) : (null));
        $this->volumeCurrency = ((isset($dados ['volume_currency'])) ? ($dados ['volume_currency']) : (null));
        $this->executada = ((isset($dados ['executada'])) ? ($dados ['executada']) : (null));
        $this->internalFreeze = ((isset($dados ['internal_freeze'])) ? ($dados ['internal_freeze']) : (null));
        $this->internalLink = ((isset($dados ['internal_link'])) ? ($dados ['internal_link']) : (null));
        $this->cancelada = ((isset($dados ['cancelada'])) ? ($dados ['cancelada']) : (null));
        $this->volumeExecutado = ((isset($dados ['volume_executado'])) ? ($dados ['volume_executado']) : (null));
        $this->percentualTaxa = ((isset($dados ['percentual_taxa'])) ? ($dados ['percentual_taxa']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->valorTaxaExecutada = ((isset($dados ['valor_taxa_executada'])) ? ($dados ['valor_taxa_executada']) : (null));
        $this->direta = ((isset($dados ['direta'])) ? ($dados ['direta']) : (null));
        $this->valorCotacaoReferencia = ((isset($dados['valor_cotacao_referencia'])) ? ($dados['valor_cotacao_referencia']) : (null));
        
        $this->idMoedaBook = ((isset($dados['id_moeda_book'])) ? ($dados['id_moeda_book']) : (null));
        $this->idMoedaTrade = ((isset($dados['id_moeda_trade'])) ? ($dados['id_moeda_trade']) : (null));
        $this->symbol = ((isset($dados['symbol'])) ? ($dados['symbol']) : (null));
        $this->symbolMoedaBook = ((isset($dados['symbol_moeda_book'])) ? ($dados['symbol_moeda_book']) : (null));
        $this->symbolMoedaTrade = ((isset($dados['symbol_moeda_trade'])) ? ($dados['symbol_moeda_trade']) : (null));
        $this->nomeCliente = ((isset($dados['nome_cliente'])) ? ($dados['nome_cliente']) : (null));
        $this->volumeBloqueado = ((isset($dados['volume_bloqueado'])) ? ($dados['volume_bloqueado']) : (null));
        $this->idMoedaBloqueada = ((isset($dados['id_moeda_bloqueada'])) ? ($dados['id_moeda_bloqueada']) : (null));
        $this->idSession = ((isset($dados ['id_session'])) ? ($dados ['id_session']) : (null));
        $this->ipSession = ((isset($dados ['ip_session'])) ? ($dados ['ip_session']) : (null));
    }
    
    public function getTable() {
        return "order_book";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new OrderBook();
    }

    public function getTipo() {
        if ($this->tipo == \Utils\Constantes::ORDEM_COMPRA) {
            return "Compra";
        } else {
            return "Venda";
        }
    }
    
    
}

?>