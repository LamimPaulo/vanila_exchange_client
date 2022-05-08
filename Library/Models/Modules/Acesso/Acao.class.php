<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class Acao {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $codigo;
    
    /**
     *
     * @var Descricao 
     */
    public $descricao;
    
    /**
     *
     * @var String 
     */
    public $nome;
    
    /**
     *
     * @var Boolean 
     */
    public $ativo;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    
    
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
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
    }
    
    public function getTable() {
        return "acoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Acao();
    }


}

?>