<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class PontoPdv {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idEstabelecimento;
    
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
            
    
    /**
     * Data em que o ponto de pdv foi cadastrado no sistema
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    /**
     *
     * @var Estabelecimento 
     */
    public $estabelecimento;
    
    /**
     *
     * @var String 
     */
    public $callbackProducao;
    
    /**
     *
     * @var String
     */
    public $callbackHomologacao;
    
    /**
     *
     * @var Integer 
     */
    public $habilitarSaqueAutomatico;
    
    /**
     *
     * @var String 
     */
    public $walletSaqueAutomatico;
    
    /**
     *
     * @var Double 
     */
    public $comissaoPdv;
    
    /**
     *
     * @var String 
     */
    public $tipoComissaoPdv;
    
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
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idEstabelecimento = ((isset($dados ['id_estabelecimento'])) ? ($dados ['id_estabelecimento']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));        
        $this->callbackHomologacao = ((isset($dados ['callback_homologacao'])) ? ($dados ['callback_homologacao']) : (null));
        $this->callbackProducao = ((isset($dados ['callback_producao'])) ? ($dados ['callback_producao']) : (null));    
        
        
        $this->habilitarSaqueAutomatico = ((isset($dados ['habilitar_saque_automatico'])) ? ($dados ['habilitar_saque_automatico']) : (null));
        $this->walletSaqueAutomatico = ((isset($dados ['wallet_saque_automatico'])) ? ($dados ['wallet_saque_automatico']) : (null));
        $this->comissaoPdv = ((isset($dados ['comissao_pdv'])) ? ($dados ['comissao_pdv']) : (null));
        $this->tipoComissaoPdv = ((isset($dados ['tipo_comissao_pdv'])) ? ($dados ['tipo_comissao_pdv']) : (null));
    }
    
    public function getTable() {
        return "pontos_pdv";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new PontoPdv();
    }


}

?>