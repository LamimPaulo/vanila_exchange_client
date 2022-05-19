<?php

namespace Models\Modules\Cadastro;

class StatusConsumivel {
    
    
    
    /**
     *
     * @var Integer 
     */
    public $quantidadeSms;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $validadeSms;
    
    /**
     * 
     * @var Integer
     */
    public $creditosConsultaDocumentos;

    
    
    
    
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
        
        $this->creditosConsultaDocumentos = ((isset($dados ['creditos_consulta_documentos'])) ? ($dados ['creditos_consulta_documentos']) : (null));
        $this->quantidadeSms = ((isset($dados ['quantidade_sms'])) ? ($dados ['quantidade_sms']) : (null));
        $this->validadeSms = ((isset($dados['validade_sms'])) ? ($dados['validade_sms'] instanceof \Utils\Data ? $dados['validade_sms'] : 
            new \Utils\Data(substr($dados['validade_sms'], 0, 19))) : (null));
        
    }
    
    public function getTable() {
        return "status_consumiveis";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new StatusConsumivel();
    }


    
    
}