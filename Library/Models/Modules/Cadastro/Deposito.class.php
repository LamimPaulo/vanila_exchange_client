<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Deposito {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idContaBancariaEmpresa;
    
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
     * @var Double 
     */
    public $valorCreditado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataConfirmacao;
    
    /**
     *
     * @var Double 
     */
    public $valorDepositado;
    
    /**
     *
     * @var String 
     */
    public $tipoDeposito;
    
    /**
     *
     * @var String 
     */
    public $notaFiscal;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
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
     * @var ContaBancariaEmpresa 
     */
    public $contaBancariaEmpresa;
    
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
     * @var String 
     */
    public $statusGateway;
    
    /**
     *
     * @var String 
     */
    public $idGateway;
    
    /**
     *
     * @var String 
     */
    public $linkGateway;
    
    /**
     *
     * @var String 
     */
    public $barcodeGateway;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataVencimentoGateway;
    
    /**
     *
     * @var Numeric 
     */
    public $valorTarifa;
    
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
        $this->dataConfirmacao = ((isset($dados['data_confirmacao'])) ? ($dados['data_confirmacao'] instanceof \Utils\Data ? $dados['data_confirmacao'] : 
            new \Utils\Data(substr($dados['data_confirmacao'], 0, 19))) : (null));
        $this->dataSolicitacao = ((isset($dados['data_solicitacao'])) ? ($dados['data_solicitacao'] instanceof \Utils\Data ? $dados['data_solicitacao'] : 
            new \Utils\Data(substr($dados['data_solicitacao'], 0, 19))) : (null));
        $this->dataCancelamento = ((isset($dados['data_cancelamento'])) ? ($dados['data_cancelamento'] instanceof \Utils\Data ? $dados['data_cancelamento'] : 
            new \Utils\Data(substr($dados['data_cancelamento'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idContaBancariaEmpresa = ((isset($dados ['id_conta_bancaria_empresa'])) ? ($dados ['id_conta_bancaria_empresa']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->taxaComissao = ((isset($dados ['taxa_comissao'])) ? ($dados ['taxa_comissao']) : (null));
        $this->tipoDeposito = ((isset($dados ['tipo_deposito'])) ? ($dados ['tipo_deposito']) : (null));
        $this->valorComissao = ((isset($dados ['valor_comissao'])) ? ($dados ['valor_comissao']) : (null));
        $this->valorCreditado = ((isset($dados ['valor_creditado'])) ? ($dados ['valor_creditado']) : (null));
        $this->valorDepositado = ((isset($dados ['valor_depositado'])) ? ($dados ['valor_depositado']) : (null));
        $this->notaFiscal = ((isset($dados ['nota_fiscal'])) ? ($dados ['nota_fiscal']) : (null));
        $this->motivoCancelamento = ((isset($dados ['motivo_cancelamento'])) ? ($dados ['motivo_cancelamento']) : (null));
        $this->aceitaNota = ((isset($dados ['aceita_nota'])) ? ($dados ['aceita_nota']) : (null));
        $this->idGateway = ((isset($dados ['id_gateway'])) ? ($dados ['id_gateway']) : (null));
        $this->statusGateway = ((isset($dados ['status_gateway'])) ? ($dados ['status_gateway']) : (null));
        $this->barcodeGateway = ((isset($dados ['barcode_gateway'])) ? ($dados ['barcode_gateway']) : (null));
        $this->linkGateway = ((isset($dados ['link_gateway'])) ? ($dados ['link_gateway']) : (null));
        $this->valorTarifa = ((isset($dados ['valor_tarifa'])) ? ($dados ['valor_tarifa']) : (null));
        $this->dataVencimentoGateway = ((isset($dados['data_vencimento_gateway'])) ? ($dados['data_vencimento_gateway'] instanceof \Utils\Data ? $dados['data_vencimento_gateway'] : 
            new \Utils\Data(substr($dados['data_vencimento_gateway'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "depositos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Deposito();
    }

    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_DEPOSITO_PENDENTE:
                return \Utils\Idiomas::get("depositoStatusPendente", 'IDIOMA');
            case \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO:
                return \Utils\Idiomas::get("depositoStatusConfirmado", 'IDIOMA');
            case \Utils\Constantes::STATUS_DEPOSITO_CANCELADO:
                return \Utils\Idiomas::get("depositoStatusCancelado", 'IDIOMA');
        }
        return "";
    }
    
    
    public function getTipoDeposito() {
        switch ($this->tipoDeposito) {
            case \Utils\Constantes::DINHEIRO:
                return "Dinheiro";
            case \Utils\Constantes::TED:
                return "TED";
            case \Utils\Constantes::DOC:
                return "DOC";
        }
        return "";
    }
}

?>