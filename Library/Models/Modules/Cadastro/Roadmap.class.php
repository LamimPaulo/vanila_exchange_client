<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class Roadmap {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $titulo;
    
    /**
     *
     * @var String 
     */
    public $texto;
    
    /**
     *
     * @var String 
     */
    public $imagem;
    
    /**
     *
     * @var Integer 
     */
    public $posicao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $data;
    
    /**
     *
     * @var Integer 
     */
    public $concluido;
    
    
    /**
     *
     * @var Integer 
     */
    public $publicado;
    
    
    
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
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->imagem = ((isset($dados ['imagem'])) ? ($dados ['imagem']) : (null));
        $this->posicao = ((isset($dados ['posicao'])) ? ($dados ['posicao']) : (null));
        $this->texto = ((isset($dados ['texto'])) ? ($dados ['texto']) : (null));
        $this->titulo = ((isset($dados ['titulo'])) ? ($dados ['titulo']) : (null));
        $this->publicado = ((isset($dados ['publicado'])) ? ($dados ['publicado']) : (null));
        $this->concluido = ((isset($dados ['concluido'])) ? ($dados ['concluido']) : (null));
    }
    
    public function getTable() {
        return "roadmap";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Roadmap();
    }


}

?>