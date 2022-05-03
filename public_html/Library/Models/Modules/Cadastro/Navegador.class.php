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
class Navegador { 
    
        /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var String
     */
    public $navegador;
    
    /**
     * 
     * @var String
     */
    public $localizacao; 
    
    /**
     * 
     * 
     *@var \Utils\Data 
     */
    public $dataAcesso;
    
    /**
     * 
     * @var String
     */
    public $ipUltimoAcesso; 
    
    /**
     * 
     * @var String
     */
    public $sistemaOperacional;
    
    /**
     * 
     * @var Integer 
     */
    public $ativo;

    
     /**
     * 
     * @var Integer 
     */
    public $idCliente;
    
     /**
     * 
     * @var Integer 
     */
    public $revogado;
    
    /**
     * 
     * 
     *@var \Utils\Data 
     */
    public $dataRevogado;
    
    /**
     * 
     * @var String
     */
    public $rayId;
    
    /**
     * 
     * @var String
     */
    public $idSession;

    
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
        $this->navegador = ((isset($dados ['navegador'])) ? ($dados ['navegador']) : (null));
        $this->localizacao = ((isset($dados ['localizacao'])) ? ($dados ['localizacao']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->ipUltimoAcesso = ((isset($dados ['ip_ultimo_acesso'])) ? ($dados ['ip_ultimo_acesso']) : (null));
        $this->sistemaOperacional = ((isset($dados ['sistema_operacional'])) ? ($dados ['sistema_operacional']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->dataAcesso = ((isset($dados['data_acesso'])) ? ($dados['data_acesso'] instanceof \Utils\Data ? $dados['data_acesso'] : 
            new \Utils\Data(substr($dados['data_acesso'], 0, 19))) : (null));
        $this->revogado = ((isset($dados ['revogado'])) ? ($dados ['revogado']) : (null));
        $this->dataRevogado = ((isset($dados['data_revogado'])) ? ($dados['data_revogado'] instanceof \Utils\Data ? $dados['data_revogado'] : 
            new \Utils\Data(substr($dados['data_revogado'], 0, 19))) : (null));
        $this->rayId = ((isset($dados ['ray_id'])) ? ($dados ['ray_id']) : (null));
        $this->idSession = ((isset($dados ['id_session'])) ? ($dados ['id_session']) : (null));
        
    }
    
    public function getTable() {
        return "navegadores";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Navegador();
    }

}
