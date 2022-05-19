<?php

namespace Models\Modules\Cadastro;

class InvestimentoContratos {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Integer 
     */
    public $tempoMeses;
    
    /**
     *
     * @var Double 
     */
    public $lucroNc;
    
    /**
     *
     * @var Double 
     */
    public $lucroPoupanca;
    
    /**
     *
     * @var Double 
     */
    public $lucroTesouro;
    
    /**
     *
     * @var Double 
     */
    public $lucroImovel;
    
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
   
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCriacao;
    

    /**
     *
     * @var String 
     */
    public $descricao;
    
     /**
     *
     * @var Integer 
     */
    public $ativo;
    

    
    
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
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));        
        $this->dataCriacao= ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->lucroNc = ((isset($dados['lucro_nc'])) ? ($dados['lucro_nc']) : null);
        $this->lucroPoupanca = ((isset($dados['lucro_poupanca'])) ? ($dados['lucro_poupanca']) : null);
        $this->lucroTesouro = ((isset($dados['lucro_tesouro'])) ? ($dados['lucro_tesouro']) : null);
        $this->lucroImovel = ((isset($dados['lucro_imovel'])) ? ($dados['lucro_imovel']) : null);
        $this->tempoMeses = ((isset($dados['tempo_meses'])) ? ($dados['tempo_meses']) : null);
        $this->idUsuario = ((isset($dados['id_usuario'])) ? ($dados['id_usuario']) : null);
    }
    
    public function getTable() {
        return "investimento_contratos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new InvestimentoContratos();
    }
    
}