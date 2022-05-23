<?php

namespace Models\Modules\Cadastro;


class TaxaMoeda {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Numeric 
     */
    public $valorMaxSaqueSemConfirmacao;
    
    
    /**
     *
     * @var Numeric 
     */
    public $taxaTransferencia;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Double 
     */
    public $volumeMinimoNegociacao;
    
    /**
     *
     * @var Double 
     */
    public $taxaRede;
    
    /**
     *
     * @var Integer 
     */
    public $minConfirmacoes;
    
    /**
     *
     * @var Integer 
     */
    public $maxConfirmacoes;
    
    /**
     *
     * @var Double 
     */
    public $taxaVendaIndireta;
    
    /**
     *
     * @var Double 
     */
    public $taxaCompraIndireta;
    
    /**
     *
     * @var Double 
     */
    public $taxaCompraDireta;
    
    /**
     *
     * @var Double 
     */
    public $taxaVendaDireta;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    /**
     *
     * @var Double 
     */
    public $minMoverSaldo;
    
    /**
     *
     * @var Double 
     */
    public $valorMinimoDeposito;
    
    /**
     *
     * @var Double 
     */
    public $taxaConversao;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaTaxa;

    /**
     *
     * @var Integer
     */
    public $poolSize;
    
    /**
     *
     * @var Double 
     */
    public $taxaMoedaTransferencia;

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
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->taxaTransferencia = ((isset($dados ['taxa_transferencia'])) ? ($dados ['taxa_transferencia']) : (null));
        $this->taxaRede = ((isset($dados ['taxa_rede'])) ? ($dados ['taxa_rede']) : (null));
        $this->valorMaxSaqueSemConfirmacao = ((isset($dados ['valor_max_saque_sem_confirmacao'])) ? ($dados ['valor_max_saque_sem_confirmacao']) : (null));
        $this->volumeMinimoNegociacao = ((isset($dados ['volume_minimo_negociacao'])) ? ($dados ['volume_minimo_negociacao']) : (null));
        $this->minConfirmacoes = ((isset($dados ['min_confirmacoes'])) ? ($dados ['min_confirmacoes']) : (null));
        $this->maxConfirmacoes = ((isset($dados ['max_confirmacoes'])) ? ($dados ['max_confirmacoes']) : (null));
        $this->taxaCompraDireta = ((isset($dados ['taxa_compra_direta'])) ? ($dados ['taxa_compra_direta']) : (null));
        $this->taxaCompraIndireta = ((isset($dados ['taxa_compra_indireta'])) ? ($dados ['taxa_compra_indireta']) : (null));
        $this->taxaVendaDireta = ((isset($dados ['taxa_venda_direta'])) ? ($dados ['taxa_venda_direta']) : (null));
        $this->taxaVendaIndireta = ((isset($dados ['taxa_venda_indireta'])) ? ($dados ['taxa_venda_indireta']) : (null));
        $this->minMoverSaldo = ((isset($dados ['min_mover_saldo'])) ? ($dados ['min_mover_saldo']) : (null));
        $this->taxaConversao = ((isset($dados ['taxa_conversao'])) ? ($dados ['taxa_conversao']) : (null));
        $this->valorMinimoDeposito = ((isset($dados ['valor_minimo_deposito'])) ? ($dados ['valor_minimo_deposito']) : (null));
        $this->taxaMoedaTransferencia = ((isset($dados ['taxa_moeda_transferencia'])) ? ($dados ['taxa_moeda_transferencia']) : (null));
        $this->idMoedaTaxa = ((isset($dados ['id_moeda_taxa'])) ? ($dados ['id_moeda_taxa']) : (null));
        $this->poolSize = ((isset($dados ['pool_size'])) ? ($dados ['pool_size']) : (null));
    }

    public function getTable() {
        return "taxas_moedas";
    }

    public function getSequence() {
      return null;
    }
    public function getInstance() {
        return new TaxaMoeda();
    }
}
