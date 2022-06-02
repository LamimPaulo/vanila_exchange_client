<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class Rotina {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $ordem;
    
    /**
     *
     * @var String 
     */
    public $nome;
    
    /**
     *
     * @var String 
     */
    public $icone;
    
    /**
     *
     * @var String 
     */
    public $codigo;
    
    /**
     *
     * @var Integer 
     */
    public $idModulo;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var String 
     */
    public $url;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Integer 
     */
    public $novidade;
    
    
    
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
        $this->ordem = ((isset($dados ['ordem'])) ? ($dados ['ordem']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->icone = ((isset($dados ['icone'])) ? ($dados ['icone']) : (null));
        $this->idModulo = ((isset($dados ['id_modulo'])) ? ($dados ['id_modulo']) : (null));
        $this->url = ((isset($dados ['url'])) ? ($dados ['url']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->novidade = ((isset($dados ['novidade'])) ? ($dados ['novidade']) : (null));
        
    }
    
    public function getTable() {
        return "rotinas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Rotina();
    }


}

?>