<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaCorrenteReais {


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
    public $data;
    
    /**
     *
     * @var Double 
     */
    public $valor;

    public $txid;
    public $txidIndex;
    public $confirmations;
    public $confirmationsRequired;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Integer 
     */
    public $idClienteDestino;
    
    /**
     *
     * @var Double 
     */
    public $valorTaxa;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    
    /**
     *
     * @var Integer 
     */
    public $transferencia;
    
    /**
     *
     * @var Integer 
     */
    public $orderBook;
    
    /**
     *
     * @var Integer 
     */
    public $idReferenciado;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     *
     * @var Cliente 
     */
    public $clienteDestino;
    
    /**
     *
     * @var Integer 
     */
    public $comissaoConvidado;
    
    
    /**
     *
     * @var Integer 
     */
    public $comissaoLicenciado;
    
    /**
     *
     * 0 - Depósito
     * 1 - Saque
     * 2 - Book
     * 3 - Comissão Book
     * 4 - Comissão Saque
     * 5 - Comissão Depósito
     * 6 - Extorno
     * 7 - Comissão Boleto
     * 8 - Comissão Remessa
     * 9 - Pagamentos
     * 10 - Atar
     * 11 - Conversao
     * @var Integer
     */
    public $origem;
    
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
        $this->data = ((isset($dados ['data'])) ? ($dados ['data'] instanceof \Utils\Data ? $dados ['data'] : 
            new \Utils\Data(substr($dados ['data'], 0, 19))) : (null));
        $this->dataCadastro = ((isset($dados ['data_cadastro'])) ? ($dados ['data_cadastro'] instanceof \Utils\Data ?
                $dados ['data_cadastro'] : new \Utils\Data(substr($dados ['data_cadastro'], 0, 19))) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->txid = ((isset($dados ['txid'])) ? ($dados ['txid']) : (null));
        $this->txidIndex = ((isset($dados ['txid_index'])) ? ($dados ['txid_index']) : (null));
        $this->confirmations = ((isset($dados ['confirmations'])) ? ($dados ['confirmations']) : (null));
        $this->confirmationsRequired = ((isset($dados ['confirmations_required'])) ? ($dados ['confirmations_required']) : (null));
        $this->transferencia = ((isset($dados ['transferencia'])) ? ($dados ['transferencia']) : (null));
        $this->idClienteDestino = ((isset($dados ['id_cliente_destino'])) ? ($dados ['id_cliente_destino']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->orderBook = ((isset($dados ['order_book'])) ? ($dados ['order_book']) : (null));
        $this->comissaoConvidado = ((isset($dados ['comissao_convidado'])) ? ($dados ['comissao_convidado']) : (null));
        $this->comissaoLicenciado = ((isset($dados ['comissao_licenciado'])) ? ($dados ['comissao_licenciado']) : (null));
        $this->idReferenciado = ((isset($dados ['id_referenciado'])) ? ($dados ['id_referenciado']) : (null));
        $this->origem = ((isset($dados ['origem'])) ? ($dados ['origem']) : (null));
        $this->idSession = ((isset($dados ['id_session'])) ? ($dados ['id_session']) : (null));
        $this->ipSession = ((isset($dados ['ip_session'])) ? ($dados ['ip_session']) : (null));
    }
    
    public function getTable() {
        return "conta_corrente_reais";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ContaCorrenteReais();
    }


}

?>