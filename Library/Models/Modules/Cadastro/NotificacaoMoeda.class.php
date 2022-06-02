<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class NotificacaoMoeda {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer
     */
    public $idUsuario;
    
    /**
     *
     * @var Integer
     */
    public $idMoeda;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataInicial;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataFinal;
    
    /**
     *
     * @var String 
     */
    public $tituloPortugues;
    
    /**
     *
     * @var String
     */
    public $tituloIngles;
    
    /**
     *
     * @var String 
     */
    public $descricaoPortugues;
    
    
    /**
     *
     * @var String 
     */
    public $descricaoIngles;
    
     /**
     *
     * @var String 
     */
    public $prioridade;
    
    /**
     *
     * @var String 
     */
    public $publicacao;
    
    
    
    
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
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->dataInicial = ((isset($dados['data_inicial'])) ? ($dados['data_inicial'] instanceof \Utils\Data ? $dados['data_inicial'] : new \Utils\Data(substr($dados['data_inicial'], 0, 19))) : (null));
        $this->dataFinal = ((isset($dados['data_final'])) ? ($dados['data_final'] instanceof \Utils\Data ? $dados['data_final'] : new \Utils\Data(substr($dados['data_final'], 0, 19))) : (null));
        $this->tituloPortugues = ((isset($dados ['titulo_portugues'])) ? ($dados ['titulo_portugues']) : (null));
        $this->tituloIngles = ((isset($dados ['titulo_ingles'])) ? ($dados ['titulo_ingles']) : (null));
        $this->descricaoPortugues = ((isset($dados ['descricao_portugues'])) ? ($dados ['descricao_portugues']) : (null));
        $this->descricaoIngles = ((isset($dados ['descricao_ingles'])) ? ($dados ['descricao_ingles']) : (null));
        $this->prioridade = ((isset($dados ['prioridade'])) ? ($dados ['prioridade']) : (null));
        $this->publicacao = ((isset($dados ['publicacao'])) ? ($dados ['publicacao']) : (null));

    }
    
    public function getTable() {
        return "notificacao_moeda";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotificacaoMoeda();
    }


}

?>