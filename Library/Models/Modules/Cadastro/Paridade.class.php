<?php

namespace Models\Modules\Cadastro;

/**
 * @Table paridades
 */
class Paridade {
    
    /**
     * Chave primária da tabela
     * @var Integer
     */
    public $id;

    /**
     *
     * @var String 
     */
    public $symbol;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaBook;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaTrade;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $statusMercado;
    /**
     *
     * @var Integer 
     */
    public $isPresale;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $ultAtualizacaoTicker;
    
    /**
     *
     * @var Double 
     */
    public $volume;
    
    /**
     *
     * @var Double 
     */
    public $menorPreco;
    
    /**
     *
     * @var Double 
     */
    public $precoVenda;
    
    /**
     *
     * @var Double 
     */
    public $ultimaCompra;
    
    /**
     *
     * @var Double 
     */
    public $maiorPreco;
    
    /**
     *
     * @var Double 
     */
    public $ultimaVenda;
    
    /**
     *
     * @var Double 
     */
    public $precoCompra;
    
    /**
     *
     * @var Double 
     */
    public $precoMinimo;
    
    
    /**
     *
     * @var Double 
     */
    public $primeiroPreco;
    
    /**
     *
     * @var Double 
     */
    public $quoteVolume;
    
    /**
     *
     * @var Integer 
     */
    public $casasDecimaisMoedaTrade;
    
    /**
     *
     * @var Integer 
     */
    public $casasDecimaisMoedaBook;
    
    
    /**
     *
     * @var Moeda 
     * @Transiente
     * @ForeignKey id_moeda_book
     * @Package Models\Modules\Cadastro
     */
    public $moedaBook;
    
    /**
     *
     * @var Moeda
     * @Transiente 
     * @ForeignKey id_moeda_trade
     * @Package Models\Modules\Cadastro
     */
    public $moedaTrade;
    

    /**
     * Construtor da classe
     *
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null, $lazing = false, $seed = 0) {
      if (!is_null($dados)) {
        $this->exchangeArray($dados, ($lazing ? "{$seed}_paridade_" : ""));
      }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados, $pre = "") {
        //Só atribuo os dados do array somente se eles existem
        $this->id = ((isset($dados ["{$pre}id"])) ? ($dados ["{$pre}id"]) : (null));
        $this->idMoedaBook = ((isset($dados ["{$pre}id_moeda_book"])) ? ($dados ["{$pre}id_moeda_book"]) : (null));
        $this->idMoedaTrade = ((isset($dados ["{$pre}id_moeda_trade"])) ? ($dados ["{$pre}id_moeda_trade"]) : (null));
        $this->ativo = ((isset($dados ["{$pre}ativo"])) ? ($dados ["{$pre}ativo"]) : (null));
        $this->statusMercado = ((isset($dados ["{$pre}status_mercado"])) ? ($dados ["{$pre}status_mercado"]) : (null));
        $this->isPresale = ((isset($dados ["{$pre}is_presale"])) ? ($dados ["{$pre}is_presale"]) : (null));
        $this->symbol = ((isset($dados ["{$pre}symbol"])) ? ($dados ["{$pre}symbol"]) : (null));
        
        $this->ultAtualizacaoTicker  = ((isset($dados ["{$pre}ult_atualizacao_ticker"])) ? ($dados ["{$pre}ult_atualizacao_ticker"] instanceof \Utils\Data ? $dados ["{$pre}ult_atualizacao_ticker"] : 
            new \Utils\Data(substr($dados ["{$pre}ult_atualizacao_ticker"], 0, 19))) : (null));
        $this->volume = ((isset($dados ["{$pre}volume"])) ? ($dados ["{$pre}volume"]) : (null));
        $this->menorPreco = ((isset($dados ["{$pre}menor_preco"])) ? ($dados ["{$pre}menor_preco"]) : (null));
        $this->precoVenda = ((isset($dados ["{$pre}preco_venda"])) ? ($dados ["{$pre}preco_venda"]) : (null));
        $this->ultimaCompra = ((isset($dados ["{$pre}ultima_compra"])) ? ($dados ["{$pre}ultima_compra"]) : (null));
        $this->maiorPreco = ((isset($dados ["{$pre}maior_preco"])) ? ($dados ["{$pre}maior_preco"]) : (null));
        $this->ultimaVenda = ((isset($dados ["{$pre}ultima_venda"])) ? ($dados ["{$pre}ultima_venda"]) : (null));
        $this->precoCompra = ((isset($dados ["{$pre}preco_compra"])) ? ($dados ["{$pre}preco_compra"]) : (null));
        $this->precoMinimo = ((isset($dados ["{$pre}preco_minimo"])) ? ($dados ["{$pre}preco_minimo"]) : (null));
        $this->quoteVolume = ((isset($dados ["{$pre}quote_volume"])) ? ($dados ["{$pre}quote_volume"]) : (null));
        $this->primeiroPreco = ((isset($dados ["{$pre}primeiro_preco"])) ? ($dados ["{$pre}primeiro_preco"]) : (null));

        $this->casasDecimaisMoedaTrade = ((isset($dados ["{$pre}casas_decimais_moeda_trade"])) ? ($dados ["{$pre}casas_decimais_moeda_trade"]) : (null));
        $this->casasDecimaisMoedaBook = ((isset($dados ["{$pre}casas_decimais_moeda_book"])) ? ($dados ["{$pre}casas_decimais_moeda_book"]) : (null));
    }

    
    public static function getLazingColumns($alias = "", $seed = 0) {
        $pre = "{$seed}_paridade_";
        
        return " {$alias}id AS {$pre}id, "
        . "{$alias}id_moeda_book AS {$pre}id_moeda_book, "
        . "{$alias}id_moeda_trade AS {$pre}id_moeda_trade, "
        . "{$alias}status_mercado AS {$pre}status_mercado, "
        . "{$alias}is_presale AS {$pre}is_presale, "
        . "{$alias}symbol AS {$pre}symbol, "
        . "{$alias}ativo AS {$pre}ativo, "
        . "{$alias}volume AS {$pre}volume, "
        . "{$alias}menor_preco AS {$pre}menor_preco, "
        . "{$alias}preco_venda AS  {$pre}preco_venda, "
        . "{$alias}ultima_compra AS {$pre}ultima_compra, "
        . "{$alias}maior_preco AS {$pre}maior_preco, "
        . "{$alias}ultima_venda AS {$pre}ultima_venda, "
        . "{$alias}preco_compra AS {$pre}preco_compra, "
        . "{$alias}preco_minimo AS {$pre}preco_minimo, "
        . "{$alias}primeiro_preco AS {$pre}primeiro_preco, "
        . "{$alias}ult_atualizacao_ticker AS {$pre}ult_atualizacao_ticker, "
        . "{$alias}ordem AS {$pre}ordem, "
        . "{$alias}quote_volume AS {$pre}quote_volume, "
        . "{$alias}casas_decimais_moeda_book AS {$pre}casas_decimais_moeda_book, "
        . "{$alias}casas_decimais_moeda_trade AS {$pre}casas_decimais_moeda_trade ";
         
    }
    
    public function getTable() {
        return "paridades";
    }

    public function getSequence() {
      return null;
    }
    public function getInstance() {
        return new Paridade();
    }
    
    
}