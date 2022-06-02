<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class PermissaoModuloCliente {

    /**
     *
     * @var Integer 
     */
    public $id;

     /**
     *
     * @var Integer 
     */
    public $idModuloHasAcao;
    
    
    
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
        $this->id = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idModuloHasAcao = ((isset($dados ['id_modulo_has_acao'])) ? ($dados ['id_modulo_has_acao']) : (null));
        
    }
    
    public function getTable() {
        return "permissoes_modulos_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new PermissaoModuloCliente();
    }


}

?>