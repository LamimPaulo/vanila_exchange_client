<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Estado {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

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
     * @var Integer 
     */
    public $idPais;
    
    
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
        $this->sigla = ((isset($dados ['sigla'])) ? ($dados ['sigla']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->idPais = ((isset($dados ['id_pais'])) ? ($dados ['id_pais']) : (null));
    }
    
    public function getTable() {
        return "estados";
    }
    
    public function getSequence() {
        return "estados_id_seq";
    }
    
    public function getInstance() {
        return new Estado();
    }


}

?>