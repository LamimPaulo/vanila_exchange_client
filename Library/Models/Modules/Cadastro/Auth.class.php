<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Auth {

    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     * Código do banco
     * @var String
     */
    public $codigo;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $data;
    
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "auth";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Auth();
    }


}

?>