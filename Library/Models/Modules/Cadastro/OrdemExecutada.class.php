<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OrdemExecutada {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idOrderBookCompra;
    
    /**
     *
     * @var Double 
     */
    public $valorCotacao;
    
    /**
     *
     * @var Integer 
     */
    public $idOrderBookVenda;
    
    /**
     *
     * @var String
     */
    public $tipo;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExecucao;
    
    
    /**
     *
     * @var Double 
     */
    public $volumeExecutado;
    
    /**
     *
     * @var String 
     */
    public $symbol;
    
    /**
     *
     * @var Integer
     */
    public $idClienteComprador;
    
    /**
     *
     * @var Integer
     */
    public $idMoedaBook;
    
    /**
     *
     * @var Integer
     */
    public $idClienteVendedor;
    
    /**
     *
     * @var Integer
     */
    public $idParidade;
    
    /**
     *
     * @var Integer
     */
    public $idMoedaTrade;
    
    /**
     *
     * @var String 
     */
    public $nomeComprador;
    
    /**
     *
     * @var String 
     */
    public $nomeVendedor;
    
    /**
     *
     * @var Integer 
     */
    public $direta;
    
    
    
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
        $this->idOrderBookCompra = ((isset($dados ['id_order_book_compra'])) ? ($dados ['id_order_book_compra']) : (null));
        $this->idOrderBookVenda = ((isset($dados ['id_order_book_venda'])) ? ($dados ['id_order_book_venda']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->dataExecucao = ((isset($dados['data_execucao'])) ? ($dados['data_execucao'] instanceof \Utils\Data ? $dados['data_execucao'] : 
            new \Utils\Data(substr($dados['data_execucao'], 0, 19))) : (null));
        $this->valorCotacao = ((isset($dados ['valor_cotacao'])) ? ($dados ['valor_cotacao']) : (null));
        $this->volumeExecutado = ((isset($dados ['volume_executado'])) ? ($dados ['volume_executado']) : (null));
        
        
        $this->idParidade = ((isset($dados ['id_paridade'])) ? ($dados ['id_paridade']) : (null));
        $this->idMoedaBook = ((isset($dados ['id_moeda_book'])) ? ($dados ['id_moeda_book']) : (null));
        $this->idMoedaTrade = ((isset($dados ['id_moeda_trade'])) ? ($dados ['id_moeda_trade']) : (null));
        $this->idClienteComprador = ((isset($dados ['id_cliente_comprador'])) ? ($dados ['id_cliente_comprador']) : (null));
        $this->idClienteVendedor = ((isset($dados ['id_cliente_vendedor'])) ? ($dados ['id_cliente_vendedor']) : (null));
        $this->symbol = ((isset($dados ['symbol'])) ? ($dados ['symbol']) : (null));
        $this->nomeComprador = ((isset($dados ['nome_comprador'])) ? ($dados ['nome_comprador']) : (null));
        $this->nomeVendedor = ((isset($dados ['nome_vendedor'])) ? ($dados ['nome_vendedor']) : (null));
        $this->direta = ((isset($dados ['direta'])) ? ($dados ['direta']) : (null));
        $this->quoteVolume = ((isset($dados ['quote_volume'])) ? ($dados ['quote_volume']) : (null)); //Generated Value - Não adicionar como atributo
    }
    
    public function getTable() {
        return "ordens_executadas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new OrdemExecutada();
    }


}

?>