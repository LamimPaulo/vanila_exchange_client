<?php

namespace Models\Modules\Cadastro;


/**
 * 
 */
class NewsLetter {

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
     * 
     * @var String
     */
    public $email;

    
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
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
    }
    
    public function getTable() {
        return "news_letter";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NewsLetter();
    }


}

?>