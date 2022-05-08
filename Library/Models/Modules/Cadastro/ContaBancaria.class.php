<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaBancaria {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $agencia;
    
    /**
     *
     * @var String 
     */
    public $agenciaDigito;
    
    /**
     *
     * @var Conta corrente 
     */
    public $conta;
    
    /**
     *
     * @var Conta corrente 
     */
    public $contaDigito;

    
    /**
     * 
     * @var String
     */
    public $tipoConta;
    
    /**
     *
     * @var Integer
     */
    public $idBanco;
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
    
    /**
     *
     * @var Banco 
     */
    public $banco;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var String
     */
    public $nomeCliente;
    
    /**
     *
     * @var String 
     */
    public $documentoCliente;
    
    
    /**
     *
     * @var Data 
     */
    public $dataCadastro;
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
        $this->agencia = ((isset($dados ['agencia'])) ? ($dados ['agencia']) : (null));
        $this->agenciaDigito = ((isset($dados ['agencia_digito'])) ? ($dados ['agencia_digito']) : (null));
        $this->conta = ((isset($dados ['conta'])) ? ($dados ['conta']) : (null));
        $this->contaDigito = ((isset($dados ['conta_digito'])) ? ($dados ['conta_digito']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->tipoConta = ((isset($dados ['tipo_conta'])) ? ($dados ['tipo_conta']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->nomeCliente = ((isset($dados ['nome_cliente'])) ? ($dados ['nome_cliente']) : (null));
        $this->documentoCliente = ((isset($dados ['documento_cliente'])) ? ($dados ['documento_cliente']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "contas_bancarias";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ContaBancaria();
    }

    public function getTipoConta() {
        switch ($this->tipoConta) {
            case \Utils\Constantes::CONTA_CORRENTE:
                return "Conta Corrente";
            case \Utils\Constantes::CONTA_POUPANCA:
                return "Conta Poupança";
            default:
                break;
        }
    }
}

?>