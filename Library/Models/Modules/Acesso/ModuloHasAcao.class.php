<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class ModuloHasAcao {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idAcao;
    
    /**
     *
     * @var Integer 
     */
    public $idModulo;
    
    /**
     *
     * @var Acao 
     */
    public $acao;
    
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
        $this->idAcao = ((isset($dados ['id_acao'])) ? ($dados ['id_acao']) : (null));
        $this->idModulo = ((isset($dados ['id_modulo'])) ? ($dados ['id_modulo']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        
    }
    
    public function getTable() {
        return "modulos_has_acoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ModuloHasAcao();
    }


}

?>