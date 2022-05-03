<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TokenApi {

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
    public $token;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $validade;
    
    
    
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
        $this->token = ((isset($dados ['token'])) ? ($dados ['token']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->validade = ((isset($dados['validade'])) ? ($dados['validade'] instanceof \Utils\Data ? $dados['validade'] : 
            new \Utils\Data(substr($dados['validade'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "tokens_api";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new TokenApi();
    }


}

?>