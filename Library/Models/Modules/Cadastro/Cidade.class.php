<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Cidade {

    /**
     * Código do municipio
     * @var String
     */
    public $codigo;

    
    /**
     * Nome da cidade
     * @var String 
     */
    public $nome;
    
    
    /**
     * Identificação do estado
     * @var Integer 
     */
    public $idEstado;
    
    /**
     *
     * @var Estado 
     */
    public $estado;
    
    
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
        $this->idEstado = ((isset($dados ['id_estado'])) ? ($dados ['id_estado']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
    }
    
    public function getTable() {
        return "cidades";
    }
    
    public function getSequence() {
        return "cidades_id_seq";
    }
    
    public function getInstance() {
        return new Cidade();
    }


}

?>