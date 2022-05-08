<?php

namespace Models\Modules\Cadastro;


/**
 * 
 */
class ContatoSite {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $nome;
    
    /**
     * 
     * @var String
     */
    public $email;
    
    /**
     * 
     * @var String
     */
    public $telefone;
    
    /**
     * 
     * @var String
     */
    public $departamento;
    
    /**
     * 
     * @var String
     */
    public $mensagem;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $dataEnvio;
    
    
    
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
        $this->departamento = ((isset($dados ['departamento'])) ? ($dados ['departamento']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->mensagem = ((isset($dados ['mensagem'])) ? ($dados ['mensagem']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->telefone = ((isset($dados ['telefone'])) ? ($dados ['telefone']) : (null));
        $this->dataEnvio = ((isset($dados['data_envio'])) ? ($dados['data_envio'] instanceof \Utils\Data ? $dados['data_envio'] : 
            new \Utils\Data(substr($dados['data_envio'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "contatos_site";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ContatoSite();
    }


}

?>