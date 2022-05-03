<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasVoto {

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
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idVotacaoListagem;
    
    /**
     *
     * @var Integer 
     */
    public $votos;
    
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idVotacaoListagem = ((isset($dados ['id_votacao_listagem'])) ? ($dados ['id_votacao_listagem']) : (null));
        $this->votos = ((isset($dados ['votos'])) ? ($dados ['votos']) : (null));
        $this->data = ((isset($dados['data'])) ? ($dados['data'] instanceof \Utils\Data ? $dados['data'] : 
            new \Utils\Data(substr($dados['data'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "cliente_has_voto";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasVoto();
    }


}

?>