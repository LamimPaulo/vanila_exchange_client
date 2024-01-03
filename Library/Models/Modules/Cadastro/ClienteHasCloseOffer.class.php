<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasCloseOffer {

    
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
    public $idCloseOffer;
    
    
    
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
        $this->idCliente = ((isset($dados ['cliente_id'])) ? ($dados ['cliente_id']) : (null));
        $this->idCloseOffer = ((isset($dados ['close_offer_id'])) ? ($dados ['close_offer_id']) : (null));
    }
    
    public function getTable() {
        return "cliente_has_close_offer";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasCloseOffer();
    }


}

?>