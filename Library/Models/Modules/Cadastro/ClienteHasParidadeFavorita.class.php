<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasParidadeFavorita {

    
    /**
     * 
     * @var Integer
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idParidade;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idParidade = ((isset($dados ['id_paridade'])) ? ($dados ['id_paridade']) : (null));
    }
    
    public function getTable() {
        return "clientes_has_paridades_favoritas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasParidadeFavorita();
    }


}

?>