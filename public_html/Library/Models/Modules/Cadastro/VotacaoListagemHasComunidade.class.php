<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class VotacaoListagemHasComunidade {

    /**
     *
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var Integer 
     */
    public $idComunidade;
    
    /**
     * 
     * @var Integer 
     */
    public $idVotacaoListagem;
    
    /**
     * 
     * @var String 
     */
    public $link;
    
    /**
     *
     * @var Integer 
     */
    public $membros;
    
    /**
     *
     * @var Comunidade 
     */
    public $comunidade;
    
    
    
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
        $this->idComunidade = ((isset($dados ['id_comunidade'])) ? ($dados ['id_comunidade']) : (null));
        $this->idVotacao = ((isset($dados ['id_votacao_listagem'])) ? ($dados ['id_votacao_listagem']) : (null));
        $this->link = ((isset($dados ['link'])) ? ($dados ['link']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->membros = ((isset($dados ['membros'])) ? ($dados ['membros']) : (null));
    }
    
    public function getTable() {
        return "votacao_listagem_has_comunidades";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new VotacaoListagemHasComunidade();
    }


}

?>