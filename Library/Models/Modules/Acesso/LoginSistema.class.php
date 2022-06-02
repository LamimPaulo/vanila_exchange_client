<?php

namespace Models\Modules\Acesso;


/**
 * 
 *
 */
class LoginSistema {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    
    
    /**
     *
     * @var String 
     */
    public $ip;
    
    /**
     *
     * @var Descricao 
     */
    public $webkit;
    
    /**
     *
     * @var String 
     */
    public $origem;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataHora;
    
    /**
     *
     * @var String 
     */
    public $queryString;
    
    
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
        $this->dataHora = ((isset($dados['data_hora'])) ? ($dados['data_hora'] instanceof \Utils\Data ? $dados['data_hora'] : new \Utils\Data(substr($dados['data_hora'], 0, 19))) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->origem = ((isset($dados ['origem'])) ? ($dados ['origem']) : (null));
        $this->webkit = ((isset($dados ['webkit'])) ? ($dados ['webkit']) : (null));
        $this->queryString = ((isset($dados ['query_string'])) ? ($dados ['query_string']) : (null));
        $this->ip = ((isset($dados ['ip'])) ? ($dados ['ip']) : (null));
    }
    
    public function getTable() {
        return "logins_sistema";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LoginSistema();
    }


}

?>