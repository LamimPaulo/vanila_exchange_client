<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class VotacaoListagem {

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
    public $votosMaxPorCliente;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataInicial;
    
    /**
     *
     * @var Integer 
     */
    public $votosNecessarios;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataFinal;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    
    /**
     *
     * @var String
     */
    public $descricao;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $aprovado;
    
    /**
     *
     * @var String 
     */
    public $moedaBase;
    
    /**
     *
     * @var String 
     */
    public $logo;
    
    /**
     *
     * @var Integer 
     */
    public $casasDecimais;
    
    /**
     *
     * @var String 
     */
    public $nomeMoeda;
    
    /**
     *
     * @var String 
     */
    public $email;
    
    /**
     *
     * @var String 
     */
    public $sigla;
    
    /**
     *
     * @var String 
     */
    public $responsavel;
    
    
    /**
     *
     * @var String 
     */
    public $site;
    
    /**
     *
     * @var String 
     */
    public $linkWhitepapper;
    
    
    /**
     *
     * @var String 
     */
    public $descricaoComunidade;
    
    
    /**
     *
     * @var String 
     */
    public $marketcap;
    
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
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->dataFinal = ((isset($dados['data_final'])) ? ($dados['data_final'] instanceof \Utils\Data ? $dados['data_final'] : 
            new \Utils\Data(substr($dados['data_final'], 0, 19))) : (null));
        $this->dataInicial = ((isset($dados['data_inicial'])) ? ($dados['data_inicial'] instanceof \Utils\Data ? $dados['data_inicial'] : 
            new \Utils\Data(substr($dados['data_inicial'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->votosMaxPorCliente = ((isset($dados ['votos_max_por_cliente'])) ? ($dados ['votos_max_por_cliente']) : (null));
        $this->votosNecessarios = ((isset($dados ['votos_necessarios'])) ? ($dados ['votos_necessarios']) : (null));
        $this->moedaBase = ((isset($dados ['moeda_base'])) ? ($dados ['moeda_base']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->logo = ((isset($dados ['logo'])) ? ($dados ['logo']) : (null));
        $this->sigla = ((isset($dados ['sigla'])) ? ($dados ['sigla']) : (null));
        $this->responsavel = ((isset($dados ['responsavel'])) ? ($dados ['responsavel']) : (null));
        $this->nomeMoeda = ((isset($dados ['nome_moeda'])) ? ($dados ['nome_moeda']) : (null));
        $this->casasDecimais = ((isset($dados ['casas_decimais'])) ? ($dados ['casas_decimais']) : (null));
        $this->aprovado = ((isset($dados ['aprovado'])) ? ($dados ['aprovado']) : (null));
        $this->site = ((isset($dados ['site'])) ? ($dados ['site']) : (null));
        $this->linkWhitepapper = ((isset($dados ['link_whitepapper'])) ? ($dados ['link_whitepapper']) : (null));
        $this->descricaoComunidade = ((isset($dados ['descricao_comunidade'])) ? ($dados ['descricao_comunidade']) : (null));
        $this->marketcap = ((isset($dados ['marketcap'])) ? ($dados ['marketcap']) : (null));
    }
    
    public function getTable() {
        return "votacao_listagem";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new VotacaoListagem();
    }


}

?>