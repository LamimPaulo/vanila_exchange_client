<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaBancariaEmpresa {

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
     * @var Conta corrente 
     */
    public $conta;

    
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
     * @var String
     */
    public $observacoes;
    
    /**
     *
     * @var String 
     */
    public $titular;
    
    /**
     *
     * @var String 
     */
    public $cnpj;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Banco 
     */
    public $banco;

    /**
     *
     * @var String
     */
    public $chavePix;
    
    
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
        $this->conta = ((isset($dados ['conta'])) ? ($dados ['conta']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco']) : (null));
        $this->tipoConta = ((isset($dados ['tipo_conta'])) ? ($dados ['tipo_conta']) : (null));
        $this->observacoes = ((isset($dados ['observacoes'])) ? ($dados ['observacoes']) : (null));
        $this->titular = ((isset($dados ['titular'])) ? ($dados ['titular']) : (null));
        $this->cnpj = ((isset($dados ['cnpj'])) ? ($dados ['cnpj']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->chavePix = ((isset($dados ['chave_pix'])) ? ($dados ['chave_pix']) : (null));
    }
    
    public function getTable() {
        return "contas_bancarias_empresa";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ContaBancariaEmpresa();
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