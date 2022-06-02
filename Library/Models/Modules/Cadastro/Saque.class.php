<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Saque {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idContaBancaria;
    
    /**
     *
     * @var Double 
     */
    public $taxaComissao;
    
    /**
     *
     * @var Double 
     */
    public $valorComissao;
    
    /**
     *
     * @var Status 
     */
    public $status;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var String 
     */
    public $comprovante;
    
    /**
     *
     * @var String 
     */
    public $notaFiscal;
    
    /**
     *
     * @var Double 
     */
    public $valorSacado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataDeposito;
    
    /**
     *
     * @var Double 
     */
    public $valorSaque;
    
    /**
     *
     * @var String 
     */
    public $tipoDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    /**
     *
     * @var Double 
     */
    public $tarifaTed;
    
    /**
     *
     * @var Integer 
     */
    public $aceitaNota;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataSolicitacao;
    
    /**
     *
     * @var String 
     */
    public $motivoCancelamento;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCancelamento;
    
    /**
     *
     * @var ContaBancaria 
     */
    public $contaBancaria;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     *
     * @var Usuario 
     */
    public $usuario;
    
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
        $this->comprovante = ((isset($dados ['comprovante'])) ? ($dados ['comprovante']) : (null));
        $this->dataDeposito = ((isset($dados['data_deposito'])) ? ($dados['data_deposito'] instanceof \Utils\Data ? $dados['data_deposito'] : 
            new \Utils\Data(substr($dados['data_deposito'], 0, 19))) : (null));
        $this->dataSolicitacao = ((isset($dados['data_solicitacao'])) ? ($dados['data_solicitacao'] instanceof \Utils\Data ? $dados['data_solicitacao'] : 
            new \Utils\Data(substr($dados['data_solicitacao'], 0, 19))) : (null));
        $this->dataCancelamento = ((isset($dados['data_cancelamento'])) ? ($dados['data_cancelamento'] instanceof \Utils\Data ? $dados['data_cancelamento'] : 
            new \Utils\Data(substr($dados['data_cancelamento'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idContaBancaria = ((isset($dados ['id_conta_bancaria'])) ? ($dados ['id_conta_bancaria']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->taxaComissao = ((isset($dados ['taxa_comissao'])) ? ($dados ['taxa_comissao']) : (null));
        $this->tipoDeposito = ((isset($dados ['tipo_deposito'])) ? ($dados ['tipo_deposito']) : (null));
        $this->valorComissao = ((isset($dados ['valor_comissao'])) ? ($dados ['valor_comissao']) : (null));
        $this->valorSacado = ((isset($dados ['valor_sacado'])) ? ($dados ['valor_sacado']) : (null));
        $this->valorSaque = ((isset($dados ['valor_saque'])) ? ($dados ['valor_saque']) : (null));
        $this->notaFiscal = ((isset($dados ['nota_fiscal'])) ? ($dados ['nota_fiscal']) : (null));
        $this->tarifaTed = ((isset($dados ['tarifa_ted'])) ? ($dados ['tarifa_ted']) : (null));
        $this->aceitaNota = ((isset($dados ['aceita_nota'])) ? ($dados ['aceita_nota']) : (null));
        $this->motivoCancelamento = ((isset($dados ['motivo_cancelamento'])) ? ($dados ['motivo_cancelamento']) : (null));
    }
    
    public function getTable() {
        return "saques";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Saque();
    }


    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_SAQUE_PENDENTE:
                return \Utils\Idiomas::get("saqueStatusPendente", IDIOMA);
            case \Utils\Constantes::STATUS_SAQUE_CONFIRMADO:
                return \Utils\Idiomas::get("saqueStatusConfirmado", IDIOMA);
            case \Utils\Constantes::STATUS_SAQUE_CANCELADO:
                return \Utils\Idiomas::get("saqueStatusCancelado", IDIOMA);
        }
        return "";
    }
}

?>