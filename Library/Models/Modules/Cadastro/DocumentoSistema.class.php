<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class DocumentoSistema {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    
    /**
     * 
     * @var String
     */
    public $versao;

    
    /**
     * 
     * @var String
     */
    public $link;

    
    /**
     * 
     * @var String
     */
    public $codigo;
    
    /**
     * 
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    
    
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
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->link = ((isset($dados ['link'])) ? ($dados ['link']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->versao = ((isset($dados ['versao'])) ? ($dados ['versao']) : (null));
        $this->codigo = ((isset($dados ['codigo'])) ? ($dados ['codigo']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
    }
    
    public function getTable() {
        return "documentos_sistemas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new DocumentoSistema();
    }


}

?>