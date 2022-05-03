<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class NotaFiscal {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var String 
     */
    public $linkDownloadXml;
    
    /**
     *
     * @var Boolean 
     */
    public $enviadaPorEmail;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaAlteracao;
    
    /**
     *
     * @var Integer 
     */
    public $idSaque;
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
    /**
     *
     * @var String 
     */
    public $numeroNf;
    
    /**
     *
     * @var String 
     */
    public $motivoStatus;
    
    /**
     *
     * @var String
     */
    public $idExterno;
    
    /**
     *
     * @var String 
     */
    public $ambiente;
    
    /**
     *
     * @var String 
     */
    public $idnf;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAutorizacao;
    
    /**
     *
     * @var String 
     */
    public $status;
    
    /**
     *
     * @var Double 
     */
    public $valorTotal;
    
    /**
     *
     * @var String 
     */
    public $linkDownloadPdf;
    
    /**
     *
     * @var Integer 
     */
    public $idDeposito;
    
    
    /**
     *
     * @var Integer 
     */
    public $idBoleto;
    
    
    
    /**
     *
     * @var Integer 
     */
    public $idRemessaDinheiro;
    
    
    /**
     *
     * @var String 
     */
    public $json;
    
    
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
        $this->ambiente = ((isset($dados ['ambiente'])) ? ($dados ['ambiente']) : (null));
        $this->enviadaPorEmail = ((isset($dados ['enviada_por_email'])) ? ($dados ['enviada_por_email']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idDeposito = ((isset($dados ['id_deposito'])) ? ($dados ['id_deposito']) : (null));
        $this->idExterno = ((isset($dados ['id_externo'])) ? ($dados ['id_externo']) : (null));
        $this->idSaque = ((isset($dados ['id_saque'])) ? ($dados ['id_saque']) : (null));
        $this->idnf = ((isset($dados ['idnf'])) ? ($dados ['idnf']) : (null));
        $this->linkDownloadPdf = ((isset($dados ['link_download_pdf'])) ? ($dados ['link_download_pdf']) : (null));
        $this->linkDownloadXml = ((isset($dados ['link_download_xml'])) ? ($dados ['link_download_xml']) : (null));
        $this->motivoStatus = ((isset($dados ['motivo_status'])) ? ($dados ['motivo_status']) : (null));
        $this->numeroNf = ((isset($dados ['numero_nf'])) ? ($dados ['numero_nf']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->json = ((isset($dados ['json'])) ? ($dados ['json']) : (null));
        $this->valorTotal = ((isset($dados ['valor_total'])) ? ($dados ['valor_total']) : (null));
        $this->dataAutorizacao = ((isset($dados['data_autorizacao'])) ? ($dados['data_autorizacao'] instanceof \Utils\Data ? $dados['data_autorizacao'] : 
            new \Utils\Data(substr($dados['data_autorizacao'], 0, 19))) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->dataUltimaAlteracao = ((isset($dados['data_ultima_alteracao'])) ? ($dados['data_ultima_alteracao'] instanceof \Utils\Data ? $dados['data_ultima_alteracao'] : 
            new \Utils\Data(substr($dados['data_ultima_alteracao'], 0, 19))) : (null));
        
        $this->idBoleto = ((isset($dados ['id_boleto'])) ? ($dados ['id_boleto']) : (null));
        $this->idRemessaDinheiro = ((isset($dados ['id_remessa_dinheiro'])) ? ($dados ['id_remessa_dinheiro']) : (null));
    }
    
    public function getTable() {
        return "notas_fiscais";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotaFiscal();
    }


}

?>