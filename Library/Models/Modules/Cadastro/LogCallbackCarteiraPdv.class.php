<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LogCallbackCarteiraPdv {


    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $url;
    
    /**
     *
     * @var Integer 
     */
    public $idCarteiraPdv;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $manual;
    
    /**
     *
     * @var String 
     */
    public $bodyResponse;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $data;
    
    
    /**
     *
     * @var String 
     */
    public $httpResponse;
    
    
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
        $this->bodyResponse = ((isset($dados ['body_response'])) ? ($dados ['body_response']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
        $this->httpResponse = ((isset($dados ['http_response'])) ? ($dados ['http_response']) : (null));
        $this->idCarteiraPdv = ((isset($dados ['id_carteira_pdv'])) ? ($dados ['id_carteira_pdv']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->manual = ((isset($dados ['manual'])) ? ($dados ['manual']) : (null));
        $this->url = ((isset($dados ['url'])) ? ($dados ['url']) : (null));
    }
    
    public function getTable() {
        return "logs_callbacks_carteiras_pdvs";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LogCallbackCarteiraPdv();
    }


}

?>