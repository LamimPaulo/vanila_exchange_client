<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class HistoricoDispositivoMobile {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     *
     * @var String 
     */
    public $descricao;
    
    /**
     *
     * @var Integer 
     */
    public $idDispositivoMobile;
    
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
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->idDispositivoMobile = ((isset($dados ['id_dispositivo_mobile'])) ? ($dados ['id_dispositivo_mobile']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
    }
    
    public function getTable() {
        return "historico_dispositivo_mobile";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new HistoricoDispositivoMobile();
    }


}

?>