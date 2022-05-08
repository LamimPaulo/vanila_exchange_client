<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class RotinaHasAcao {

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
    public $idRotina;
    
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
        $this->idRotina = ((isset($dados ['id_rotina'])) ? ($dados ['id_rotina']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        
    }
    
    public function getTable() {
        return "rotinas_has_acoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new RotinaHasAcao();
    }


}

?>