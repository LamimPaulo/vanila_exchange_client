<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class SeguimentoComercial {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $nome;
    
    /**
     * Código do banco
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
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
    }
    
    public function getTable() {
        return "seguimentos_comercios";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new SeguimentoComercial();
    }


}

?>