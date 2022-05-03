<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LogContaCorrenteReaisEmpresa {


    /**
     * 
     * @var Integer
     */
    public $id;
    
    /**
     * 
     * @var Integer
     */
    public $idContaCorrenteReaisEmpresa;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $data;
    
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
     *
     * @var String 
     */
    public $descricao;
    
    /**
     *
     * @var Usuario 
     */
    public $usuario;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
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
        $this->data = ((isset($dados ['data'])) ? ($dados ['data'] instanceof \Utils\Data ? $dados ['data'] : 
            new \Utils\Data(substr($dados ['data'], 0, 19))) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idContaCorrenteReaisEmpresa = ((isset($dados ['id_conta_corrente_reais_empresa'])) ? ($dados ['id_conta_corrente_reais_empresa']) : (null));
    }
    
    public function getTable() {
        return "log_conta_corrente_reais_empresa";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LogContaCorrenteReaisEmpresa();
    }


    public function getNome() {
        if ($this->usuario != null) {
            return $this->usuario->nome;
        }
        
        if ($this->cliente != null) {
            return $this->cliente->nome;
        }
        
        return "";
    }
}

?>