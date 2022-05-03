<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

/**
 * Description of NotificacaoClienteOperacao
 *
 * @author willianchiquetto
 */
class Documentos { 
    
     /**
     * Chave primária da tabela
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
     * @var String
     */
    public $nomeArquivo; 
    /**
     * 
     *  @var \Utils\Data
     */
    public $dataEnvio; 
    
    /**
     * 
     * 
     *  @var \Utils\Data
     */
    public $dataAnalise;
    
    /**
     * 
     * @var String
     */
    public $tipoDocumento;
    
     /**
     * 
     * @var Interger
     */
    public $status;
    
    /**
     * 
     * @var String
     */
    public $motivoRecusa;

    
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
        $this->nomeArquivo = ((isset($dados ['nome_arquivo'])) ? ($dados ['nome_arquivo']) : (null));
        $this->dataEnvio = ((isset($dados ['data_envio'])) ? ($dados ['data_envio'] instanceof \Utils\Data ? $dados['data_envio'] : new \Utils\Data(substr($dados['data_envio'], 0, 19))) : (null));
        $this->dataAnalise = ((isset($dados ['data_analise'])) ? ($dados ['data_analise'] instanceof \Utils\Data ? $dados['data_analise'] : new \Utils\Data(substr($dados['data_analise'], 0, 19))) : (null));
        $this->tipoDocumento = ((isset($dados ['tipo_documento'])) ? ($dados ['tipo_documento']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->motivoRecusa = ((isset($dados ['motivo_recusa'])) ? ($dados ['motivo_recusa']) : (null));
    }
    
    public function getTable() {
        return "documentos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Documentos();
    }

}
