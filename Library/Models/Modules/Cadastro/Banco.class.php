<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Banco {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     * Código do banco
     * @var String
     */
    public $codigo;

    
    /**
     * Nome do banco
     * @var String 
     */
    public $nome;
    
    
    /**
     * 
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var String 
     */
    public $logo;
    
    
    
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
        $this->codigo = ((isset($dados ['codigo'])) ? ($dados ['codigo']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->logo = ((isset($dados ['logo'])) ? ($dados ['logo']) : (null));
    }
    
    public function getTable() {
        return "bancos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Banco();
    }


}

?>