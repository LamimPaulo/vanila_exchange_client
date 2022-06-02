<?php

namespace Models\Modules\Cadastro;

class Cofre {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Double 
     */
    public $volumeDepositado;
    
    /**
     *
     * @var Integer 
     */
    public $sacado;
    
    /**
     *
     * @var Double 
     */
    public $volumeCobradoTaxa;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataEntrada;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataProvisaoSaque;
    
    
    /**
     *
     * @var Double 
     */
    public $taxa;
    
    
    /**
     *
     * @var \Utils\Data
     */
    public $dataSolicitacaoSaque;
    
    /**
     *
     * @var Integer 
     */
    public $saqueSolicitado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataSaque;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaCobrancaTaxa;
    
    
    /**
     *
     * @var Cliente
     */
    public $cliente;
    
    /**
     *
     * @var Moeda
     */
    public $moeda;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExpiracaoContrato;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    
    /**
     *
     * @var Double 
     */
    public $volumePagoRendimento;
    
    /**
     *
     * @var Integer 
     */
    public $contrato;
    
    
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
        $this->sacado = ((isset($dados ['sacado'])) ? ($dados ['sacado']) : (null));
        $this->taxa = ((isset($dados ['taxa'])) ? ($dados ['taxa']) : (null));
        $this->volumeCobradoTaxa = ((isset($dados ['volume_cobrado_taxa'])) ? ($dados ['volume_cobrado_taxa']) : (null));
        $this->volumeDepositado = ((isset($dados ['volume_depositado'])) ? ($dados ['volume_depositado']) : (null));
        $this->dataEntrada = ((isset($dados['data_entrada'])) ? ($dados['data_entrada'] instanceof \Utils\Data ? $dados['data_entrada'] : 
            new \Utils\Data(substr($dados['data_entrada'], 0, 19))) : (null));
        $this->dataProvisaoSaque = ((isset($dados['data_provisao_saque'])) ? ($dados['data_provisao_saque'] instanceof \Utils\Data ? $dados['data_provisao_saque'] : 
            new \Utils\Data(substr($dados['data_provisao_saque'], 0, 19))) : (null));
        $this->dataSolicitacaoSaque = ((isset($dados['data_solicitacao_saque'])) ? ($dados['data_solicitacao_saque'] instanceof \Utils\Data ? $dados['data_solicitacao_saque'] : 
            new \Utils\Data(substr($dados['data_solicitacao_saque'], 0, 19))) : (null));
        $this->dataSaque = ((isset($dados['data_saque'])) ? ($dados['data_saque'] instanceof \Utils\Data ? $dados['data_saque'] : 
            new \Utils\Data(substr($dados['data_saque'], 0, 19))) : (null));
        $this->dataUltimaCobrancaTaxa = ((isset($dados['data_ultima_cobranca_taxa'])) ? ($dados['data_ultima_cobranca_taxa'] instanceof \Utils\Data ? $dados['data_ultima_cobranca_taxa'] : 
            new \Utils\Data(substr($dados['data_ultima_cobranca_taxa'], 0, 19))) : (null));
        $this->dataExpiracaoContrato = ((isset($dados['data_expiracao_contrato'])) ? ($dados['data_expiracao_contrato'] instanceof \Utils\Data ? $dados['data_expiracao_contrato'] : 
            new \Utils\Data(substr($dados['data_expiracao_contrato'], 0, 19))) : (null));
        $this->saqueSolicitado = ((isset($dados ['saque_solicitado'])) ? ($dados ['saque_solicitado']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->volumePagoRendimento = ((isset($dados['volume_pago_rendimento'])) ? ($dados['volume_pago_rendimento']) : null);
        $this->contrato = ((isset($dados['contrato'])) ? ($dados['contrato']) : null);
    }
    
    public function getTable() {
        return "cofre";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Cofre();
    }

    
    public function getStatus() {
        
        if ($this->saqueSolicitado < 1) {
            return \Utils\Idiomas::get("cofreSaldoInvestido", IDIOMA);
        } else if ($this->sacado < 1) {
            return \Utils\Idiomas::get("cofreSaldoEmProvisionamento", IDIOMA);
        } else if ($this->sacado > 0) {
            return \Utils\Idiomas::get("cofreSaldoSacado", IDIOMA);
        }
        return "";
    }
    
}