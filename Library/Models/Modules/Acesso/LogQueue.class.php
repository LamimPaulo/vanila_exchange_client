<?php

namespace Models\Modules\Acesso;


class LogQueue {

    /**
     *
     * @var Integer 
     */
    public $id;

    /**
     *
     * @var String
     */
    public $mensagem;

    /**
     *
     * @var String
     */
    public $id_process;

    /**
     *
     * @var Descricao
     */
    public $body;

    /**
     *
     * @var \Utils\Data 
     */
    public $data;
    
    /**
     *
     * @var String 
     */
    public $tipo;

    /**
     *
     * @var String
     */
    public $queue;
    
    
    
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
        $this->id_process = ((isset($dados ['id_process'])) ? ($dados ['id_process']) : (null));
        $this->mensagem = ((isset($dados ['mensagem'])) ? ($dados ['mensagem']) : (null));
        $this->body = ((isset($dados ['body'])) ? ($dados ['body']) : (null));
        $this->data = ((isset($dados ['data'])) ? ($dados ['data']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->queue = ((isset($dados ['queue'])) ? ($dados ['queue']) : (null));

    }
    
    public function getTable() {
        return "log_queue";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LogQueue();
    }


}

?>
