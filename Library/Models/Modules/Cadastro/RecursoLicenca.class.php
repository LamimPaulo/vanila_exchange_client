<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RecursoLicenca {

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
     * @var Integer
     */
    public $ordem;
    
    
    
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
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->ordem = ((isset($dados ['ordem'])) ? ($dados ['ordem']) : (null));
    }
    
    public function getTable() {
        return "recursos_licenca";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new RecursoLicenca();
    }


}

?>