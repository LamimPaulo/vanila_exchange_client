<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasCredito {

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
     * @var Double 
     */
    public $volumeCredito;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    
    
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
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->volumeCredito = ((isset($dados ['volume_credito'])) ? ($dados ['volume_credito']) : (null));
    }
    
    public function getTable() {
        return "cliente_has_credito";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasCredito();
    }


}

?>