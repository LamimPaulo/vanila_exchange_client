<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class LogErro {

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
    public $mensagem;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $data;
    
    /**
     *
     * @var Boolean 
     */
    public $idCliente;
    
    /**
     *
     * @var String 
     */
    public $idUsuario;
    
    
    
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
        $this->data = ((isset($dados ['data'])) ? ($dados ['data']) : (null));
        $this->mensagem = ((isset($dados ['mensagem'])) ? ($dados ['mensagem']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
    }
    
    public function getTable() {
        return "log_erros";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LogErro();
    }


}

?>