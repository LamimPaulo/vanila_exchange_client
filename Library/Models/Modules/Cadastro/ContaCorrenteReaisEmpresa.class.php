<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaCorrenteReaisEmpresa {


    /**
     * 
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
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    /**
     *
     * @var Integer 
     */
    public $transferencia;
    
    /**
     *
     * @var Integer 
     */
    public $bloqueado;
    
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
        $this->dataCadastro = ((isset($dados ['data_cadastro'])) ? ($dados ['data_cadastro'] instanceof \Utils\Data ?
                $dados ['data_cadastro'] : new \Utils\Data(substr($dados ['data_cadastro'], 0, 19))) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->transferencia = ((isset($dados ['transferencia'])) ? ($dados ['transferencia']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->bloqueado = ((isset($dados ['bloqueado'])) ? ($dados ['bloqueado']) : (null));
    }
    
    public function getTable() {
        return "conta_corrente_reais_empresa";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ContaCorrenteReaisEmpresa();
    }


}

?>