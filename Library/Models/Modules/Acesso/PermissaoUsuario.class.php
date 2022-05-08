<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class PermissaoUsuario {

    /**
     *
     * @var Integer 
     */
    public $idUsuario;

    /**
     *
     * @var Integer 
     */
    public $idRotinaHasAcao;
    
    
    
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
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idRotinaHasAcao = ((isset($dados ['id_rotina_has_acao'])) ? ($dados ['id_rotina_has_acao']) : (null));
        
    }
    
    public function getTable() {
        return "permissoes_usuarios";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new PermissaoUsuario();
    }


}

?>