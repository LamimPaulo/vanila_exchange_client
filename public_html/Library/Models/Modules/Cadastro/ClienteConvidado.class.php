<?php

namespace Models\Modules\Cadastro;

class ClienteConvidado {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataConvite;
    
    /**
     *
     * @var Double
     */
    public $comissao;
    
    
    /**
     *
     * @var Integer 
     */
    public $cadastrou;
    
    
    /**
     *
     * @var String
     */
    public $email;
    
    
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
    
    /**
     *
     * @var String 
     */
    public $movimento;
    
    /**
     *
     * @var Integer 
     */
    public $qtdEnvios;
    
    
    
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
        $this->cadastrou = ((isset($dados ['cadastrou'])) ? ($dados ['cadastrou']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->comissao = ((isset($dados ['comissao'])) ? ($dados ['comissao']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->movimento = ((isset($dados ['movimento'])) ? ($dados ['movimento']) : (null));
        $this->qtdEnvios = ((isset($dados ['qtd_envios'])) ? ($dados ['qtd_envios']) : (null));
        $this->dataConvite = ((isset($dados['data_convite'])) ? ($dados['data_convite'] instanceof \Utils\Data ? $dados['data_convite'] : 
            new \Utils\Data(substr($dados['data_convite'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "clientes_convidados";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteConvidado();
    }


}

?>