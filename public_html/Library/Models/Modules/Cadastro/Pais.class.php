<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Pais {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     * Sigla do estado
     * @var String
     */
    public $codigo;
    
    /**
     * Sigla do estado
     * @var String
     */
    public $sigla;

    
    /**
     * Nome do estado
     * @var String 
     */
    public $nome;
    
    
    /**
     *
     * @var String 
     */
    public $ddi;
    
    
    /**
     *
     * @var String 
     */
    public $siglaIso;
    
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
        $this->codigo = ((isset($dados ['codigo'])) ? ($dados ['codigo']) : (null));
        $this->sigla = ((isset($dados ['sigla'])) ? ($dados ['sigla']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->siglaIso = ((isset($dados ['sigla_iso'])) ? ($dados ['sigla_iso']) : (null));
        $this->ddi = ((isset($dados ['ddi'])) ? ($dados ['ddi']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
    }
    
    public function getTable() {
        return "paises";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Pais();
    }


}

?>