<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class MarketingImagem {

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
    public $nomePropaganda;
    
    /**
     *
     * @var String
     */
    public $url;    
        
     /**
     *
     * @var String 
     */
    public $prioridade;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $intervalo;
    
    /**
     *
     * @var Integer 
     */
    public $qtdMaxVisualizacao;
    
    
    
    
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
        $this->dataInicial = ((isset($dados['data_inicial'])) ? ($dados['data_inicial'] instanceof \Utils\Data ? $dados['data_inicial'] : new \Utils\Data(substr($dados['data_inicial'], 0, 19))) : (null));
        $this->dataFinal = ((isset($dados['data_final'])) ? ($dados['data_final'] instanceof \Utils\Data ? $dados['data_final'] : new \Utils\Data(substr($dados['data_final'], 0, 19))) : (null));
        $this->nomePropaganda = ((isset($dados ['nome_propaganda'])) ? ($dados ['nome_propaganda']) : (null));
        $this->url = ((isset($dados ['url'])) ? ($dados ['url']) : (null));
        $this->prioridade = ((isset($dados ['prioridade'])) ? ($dados ['prioridade']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->intervalo = ((isset($dados ['intervalo'])) ? ($dados ['intervalo']) : (null));
        $this->qtdMaxVisualizacao = ((isset($dados ['qtd_max_visualizacao'])) ? ($dados ['qtd_max_visualizacao']) : (null));
    }
    
    public function getTable() {
        return "marketing_imagem";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new MarketingImagem();
    }


}

?>