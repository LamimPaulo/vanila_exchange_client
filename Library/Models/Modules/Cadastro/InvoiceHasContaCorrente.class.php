<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoiceHasContaCorrente {

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
     * @var Integer 
     */
    public $idInvoicePdv;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $data;
    
    
    /**
     *
     * @var Integer 
     */
    public $idContaCorrenteReais;
    
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    
    /**
     *
     * @var Integer 
     */
    public $idContaCorrenteBtc;
    
    
    /**
     *
     * @var ContaCorrenteBtc 
     */
    public $contaCorrenteBtc;
    
    /**
     *
     * @var ContaCorrenteReais 
     */
    public $contaCorrenteReais;
    
    /**
     *
     * @var InvoicePdv 
     */
    public $invoicePdv;
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idContaCorrenteBtc = ((isset($dados ['id_conta_corrente_btc'])) ? ($dados ['id_conta_corrente_btc']) : (null));
        $this->idContaCorrenteReais = ((isset($dados ['id_conta_corrente_reais'])) ? ($dados ['id_conta_corrente_reais']) : (null));
        $this->idInvoicePdv = ((isset($dados ['id_invoice_pdv'])) ? ($dados ['id_invoice_pdv']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "invoices_has_conta_corrente";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new InvoiceHasContaCorrente();
    }


}

?>