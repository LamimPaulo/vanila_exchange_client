<?php

namespace Models\Modules\Cadastro;

/**
 * @Table moedas
 */
class Moeda {
    /**
     * Chave primária da tabela
     * @var Integer
     */
    public $id;

    /**
     * Símbolo da moeda
     * @var String
     */
    public $simbolo;


    /**
     * Nome da moeda
     * @var String
     */
    public $nome;

    /**
     * Ativa?
     * @var String
     */
    public $ativo;

    /**
     * Último usuário que alterou o registro
     * @var String
     */
    public $codUsr;

    /**
     * Data da última alteração
     * @var String
     */
    public $dtAtualizacao;
    
    /**
     *
     * @var Integer
     */
    public $principal;
    
    /**
     *
     * @var String
     */
    public $icone;
    
    /**
     *
     * @var String
     */
    public $mainColor;
    
    /**
     *
     * @var Integer 
     */
    public $statusMercado;
    
    /**
     *
     * @var String 
     */
    public $urlExplorer;
    
    /**
     *
     * @var String 
     */
    public $corFonte;
    
    /**
     *
     * @var Integer 
     */
    public $qtdMaximaCarteiras;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaPrincipal;
    
    /**
     *
     * @var Integer 
     */
    public $statusSaque;
    
    /**
     *
     * @var String 
     */
    public $contrato;
    
    /**
     *
     * @var Integer 
     */
    public $statusDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $token;
    
    /**
     *
     * @var Integer 
     */
    public $casasDecimais;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $ultAtualizacaoTicker;
    
    /**
     *
     * @var Double 
     */
    public $volume;
    
    /**
     *
     * @var Integer 
     */
    public $sincDeposito;
    
    /**
     *
     * @var String
     */
    public $carteiraEmpresa;
    
    /**
     *
     * @var Integer 
     */
    public $infoDecimal;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaConversao;
    
    /**
     *
     * @var Double 
     */
    public $valorConversao;
    
    /**
     *
     * @var String 
     */
    public $coinType;
    
    /**
     *
     * @var String 
     */
    public $redesSaque;
    
    /**
     *
     * @var String 
     */
    public $redesDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaSaque;
    
    /**
     *
     * @var Integer 
     */
    public $idCategoriaMoeda;
    
    /**
     *
     * @var Integer 
     */
    public $gerarCarteira;
    
    /**
     *
     * @var Boolean 
     */
    public $hasStaking;
    /**
     *
     * @var string 
     */
    public $stakingContract;

    /**
     * Construtor da classe
     *
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null, $lazing = false, $seed = 0) {
        if (!is_null($dados)) {
              $this->exchangeArray($dados, ($lazing ? "{$seed}_moeda_" : ""));
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados, $pre = "") {
        //Só atribuo os dados do array somente se eles existem
        $this->id = ((isset($dados ["{$pre}id"])) ? ($dados ["{$pre}id"]) : (null));
        $this->simbolo = ((isset($dados ["{$pre}simbolo"])) ? ($dados ["{$pre}simbolo"]) : (null));
        $this->nome = ((isset($dados ["{$pre}nome"])) ? ($dados ["{$pre}nome"]) : (null));
        $this->ativo = ((isset($dados ["{$pre}ativo"])) ? ($dados ["{$pre}ativo"]) : (null));
        $this->codUsr = ((isset($dados ["{$pre}cod_usr"])) ? ($dados ["{$pre}cod_usr"]) : (null));
        $this->dtAtualizacao = ((isset($dados ["{$pre}dt_atualizacao"])) ? ($dados ["{$pre}dt_atualizacao"]) : (null));
        $this->principal = ((isset($dados ["{$pre}principal"])) ? ($dados ["{$pre}principal"]) : (null));
        $this->icone = ((isset($dados ["{$pre}icone"])) ? ($dados ["{$pre}icone"]) : (null));
        $this->mainColor = ((isset($dados ["{$pre}main_color"])) ? ($dados ["{$pre}main_color"]) : (null));
        $this->statusMercado = ((isset($dados ["{$pre}status_mercado"])) ? ($dados ["{$pre}status_mercado"]) : (null));
        $this->urlExplorer = ((isset($dados ["{$pre}url_explorer"])) ? ($dados ["{$pre}url_explorer"]) : (null));
        $this->qtdMaximaCarteiras = ((isset($dados ["{$pre}qtd_maxima_carteiras"])) ? ($dados ["{$pre}qtd_maxima_carteiras"]) : (null));
        $this->corFonte = ((isset($dados ["{$pre}cor_fonte"])) ? ($dados ["{$pre}cor_fonte"]) : (null));
        
        $this->statusDeposito = ((isset($dados ["{$pre}status_deposito"])) ? ($dados ["{$pre}status_deposito"]) : (null));
        $this->statusSaque = ((isset($dados ["{$pre}status_saque"])) ? ($dados ["{$pre}status_saque"]) : (null));
        $this->idMoedaPrincipal = ((isset($dados ["{$pre}id_moeda_principal"])) ? ($dados ["{$pre}id_moeda_principal"]) : (null));
        $this->token = ((isset($dados ["{$pre}token"])) ? ($dados ["{$pre}token"]) : (null));
        $this->contrato = ((isset($dados ["{$pre}contrato"])) ? ($dados ["{$pre}contrato"]) : (null));
        $this->casasDecimais = ((isset($dados ["{$pre}casas_decimais"])) ? ($dados ["{$pre}casas_decimais"]) : (null));
        
        $this->ultAtualizacaoTicker  = ((isset($dados ["{$pre}ult_atualizacao_ticker"])) ? ($dados ["{$pre}ult_atualizacao_ticker"] instanceof \Utils\Data ? $dados ["{$pre}ult_atualizacao_ticker"] : 
            new \Utils\Data(substr($dados ["{$pre}ult_atualizacao_ticker"], 0, 19))) : (null));
        $this->volume = ((isset($dados ["{$pre}volume"])) ? ($dados ["{$pre}volume"]) : (null));
        $this->sincDeposito = ((isset($dados ["{$pre}sinc_deposito"])) ? ($dados ["{$pre}sinc_deposito"]) : (null));
        $this->carteiraEmpresa = ((isset($dados ["{$pre}carteira_empresa"])) ? ($dados ["{$pre}carteira_empresa"]) : (null));
        $this->infoDecimal = ((isset($dados ["{$pre}info_decimal"])) ? ($dados ["{$pre}info_decimal"]) : (null));
        $this->idMoedaConversao = ((isset($dados ["{$pre}id_moeda_conversao"])) ? ($dados ["{$pre}id_moeda_conversao"]) : (null));
        $this->valorConversao = ((isset($dados ["{$pre}valor_conversao"])) ? ($dados ["{$pre}valor_conversao"]) : (null));
        $this->coinType = ((isset($dados ["{$pre}coin_type"])) ? ($dados ["{$pre}coin_type"]) : (null));
        $this->redesSaque = ((isset($dados ["{$pre}redes_saque"])) ? ($dados ["{$pre}redes_saque"]) : (null));
        $this->redesDeposito = ((isset($dados ["{$pre}redes_deposito"])) ? ($dados ["{$pre}redes_deposito"]) : (null));
        $this->idMoedaSaque = ((isset($dados ["{$pre}id_moeda_saque"])) ? ($dados ["{$pre}id_moeda_saque"]) : (null));
        $this->idCategoriaMoeda = ((isset($dados ["{$pre}id_categoria_moeda"])) ? ($dados ["{$pre}id_categoria_moeda"]) : (null));
        $this->gerarCarteira = ((isset($dados ["{$pre}gerar_carteira"])) ? ($dados ["{$pre}gerar_carteira"]) : (null));
        $this->hasStaking = ((isset($dados ["{$pre}has_staking"])) ? ($dados ["{$pre}has_staking"]) : (null));
        $this->stakingContract = ((isset($dados ["{$pre}staking_contract"])) ? ($dados ["{$pre}staking_contract"]) : (null));

    }

    
    public function getTable() {
        return "moedas";
    }

    public function getSequence() {
      return null;
    }
    public function getInstance() {
        return new Moeda();
    }
    
    public static function getLazingColumns($alias = "", $seed = 0) {
        $pre = "{$seed}_moeda_";
        
        return " {$alias}id AS {$pre}id, "
        . " {$alias}simbolo AS {$pre}simbolo, "
        . " {$alias}nome AS {$pre}nome, "
        . " {$alias}ativo AS {$pre}ativo, "
        . " {$alias}cod_usr AS {$pre}cod_usr, "
        . " {$alias}dt_atualizacao AS {$pre}dt_atualizacao, "
        . " {$alias}principal AS {$pre}principal, "
        . " {$alias}icone AS {$pre}icone, "
        . " {$alias}main_color AS {$pre}main_color, "
        . " {$alias}status_mercado AS {$pre}status_mercado, "
        . " {$alias}url_explorer AS {$pre}url_explorer, "
        . " {$alias}qtd_maxima_carteiras AS  {$pre}qtd_maxima_carteiras, "
        . " {$alias}cor_fonte AS {$pre}cor_fonte, "
        . " {$alias}id_moeda_principal AS {$pre}id_moeda_principal, "
        . " {$alias}status_saque AS {$pre}status_saque, "
        . " {$alias}contrato AS {$pre}contrato, "
        . " {$alias}status_deposito AS {$pre}status_deposito, "
        . " {$alias}token AS {$pre}token, "
        . " {$alias}casas_decimais AS {$pre}casas_decimais, "
        . " {$alias}volume AS {$pre}volume, "
        . " {$alias}ult_atualizacao_ticker AS {$pre}ult_atualizacao_ticker, "
        . " {$alias}id_categoria_moeda AS {$pre}id_categoria_moeda, "
        . " {$alias}sinc_deposito AS {$pre}sinc_deposito, "
        . " {$alias}carteira_empresa AS {$pre}carteira_empresa, "
        . " {$alias}info_decimal AS {$pre}info_decimal, "
        . " {$alias}coin_type AS {$pre}coin_type, "
        . " {$alias}id_moeda_conversao AS {$pre}id_moeda_conversao, "
        . " {$alias}redes_saque AS {$pre}redes_saque, "
        . " {$alias}redes_deposito AS {$pre}redes_deposito, "
        . " {$alias}id_moeda_saque AS {$pre}id_moeda_saque, "
        . " {$alias}gerar_carteira AS {$pre}gerar_carteira, "
        . " {$alias}valor_conversao AS {$pre}valor_conversao ";
    }
    
    public function getUrlExplorer($hash = null) {
        $url = $this->urlExplorer;
        if (!empty($hash)) {
            $url = str_replace("{hash}", $hash, $url);
        }
        return $url;
    }
}
