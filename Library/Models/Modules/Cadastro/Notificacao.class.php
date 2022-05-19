<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Notificacao {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var \Utils\Data
     */
    public $data;

    
    /**
     * 
     * @var String 
     */
    public $html;
    
    
    
    /**
     * 
     * @var Integer 
     */
    public $idUsuarioCriacao;
    
    
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Integer
     */
    public $clientes;
    
    /**
     *
     * @var Integer
     */
    public $usuarios;
    
    
    
    /**
     *
     * @var \Models\Modules\Cadastro\Usuario 
     */
    public $usuario;
    
    
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
        $this->html = ((isset($dados ['html'])) ? ($dados ['html']) : (null));
        $this->idUsuarioCriacao = ((isset($dados ['id_usuario_criacao'])) ? ($dados ['id_usuario_criacao']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->clientes = ((isset($dados ['clientes'])) ? ($dados ['clientes']) : (null));
        $this->usuarios = ((isset($dados ['usuarios'])) ? ($dados ['usuarios']) : (null));
    }
    
    public function getTable() {
        return "notificacoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Notificacao();
    }
    
    public function getTipo (){
    switch ($this->tipo) {
        case "s": return "success";
        case "w": return "warning";
        case "e": return "error";

        default:
            break;
    }
    }
}

?>