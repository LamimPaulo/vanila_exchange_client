<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TaxaLicencaSoftware {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idLicencaSoftware;

    
    /**
     *
     * @var Double 
     */
    public $taxaDeposito;

    
    /**
     *
     * @var Double 
     */
    public $taxaSaque;

    
    /**
     *
     * @var Double 
     */
    public $taxaCompraPassiva;

    
    /**
     *
     * @var Double 
     */
    public $taxaCompraAtiva;

    
    /**
     *
     * @var Double 
     */
    public $taxaVendaPassiva;

    
    /**
     *
     * @var Double 
     */
    public $taxaVendaAtiva;
    
    
    
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
        $this->idLicencaSoftware = ((isset($dados ['id_licenca_software'])) ? ($dados ['id_licenca_software']) : (null));
        $this->taxaCompraAtiva = ((isset($dados ['taxa_compra_ativa'])) ? ($dados ['taxa_compra_ativa']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->taxaCompraPassiva = ((isset($dados ['taxa_compra_passiva'])) ? ($dados ['taxa_compra_passiva']) : (null));
        $this->taxaDeposito = ((isset($dados ['taxa_deposito'])) ? ($dados ['taxa_deposito']) : (null));
        $this->taxaSaque = ((isset($dados ['taxa_saque'])) ? ($dados ['taxa_saque']) : (null));
        $this->taxaVendaAtiva = ((isset($dados ['taxa_venda_ativa'])) ? ($dados ['taxa_venda_ativa']) : (null));
        $this->taxaVendaPassiva = ((isset($dados ['taxa_venda_passiva'])) ? ($dados ['taxa_venda_passiva']) : (null));
    }
    
    public function getTable() {
        return "taxas_licencas_software";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new TaxaLicencaSoftware();
    }


}

?>