<?php

namespace Models\Modules\Cadastro;


/**
 * 
 */
class ClienteHasTaxa {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Numeric 
     */
    public $taxaCompraIndireta;
    
    /**
     *
     * @var Numeric 
     */
    public $taxaVendaIndireta;
    
    /**
     *
     * @var Numeric 
     */
    public $taxaCompraDireta;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Numeric 
     */
    public $taxaVendaDireta;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Boolean 
     */
    public $utilizar;
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->utilizar = ((isset($dados ['utilizar'])) ? ($dados ['utilizar']) : (null));
        $this->taxaCompraDireta = ((isset($dados ['taxa_compra_direta'])) ? ($dados ['taxa_compra_direta']) : (null));
        $this->taxaCompraIndireta = ((isset($dados ['taxa_compra_indireta'])) ? ($dados ['taxa_compra_indireta']) : (null));
        $this->taxaVendaDireta = ((isset($dados ['taxa_venda_direta'])) ? ($dados ['taxa_venda_direta']) : (null));
        $this->taxaVendaIndireta = ((isset($dados ['taxa_venda_indireta'])) ? ($dados ['taxa_venda_indireta']) : (null));
    }
    
    public function getTable() {
        return "clientes_has_taxas_moedas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasTaxa();
    }


}

?>