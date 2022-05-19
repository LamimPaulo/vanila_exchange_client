<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LicencaSoftware {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $nome;
    
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
     * 
     * @var Integer
     */
    public $ordem;
    
    
    
    /**
     * 
     * @var Double
     */
    public $preco;
    
    /**
     *
     * @var String 
     */
    public $tempoLiberacaoDepositosSaques;
    
    /**
     *
     * @var Double 
     */
    public $comissao;
    
    
    /**
     *
     * @var Integer 
     */
    public $mesesDuracao;
    
    
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
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->ordem = ((isset($dados ['ordem'])) ? ($dados ['ordem']) : (null));
        $this->preco = ((isset($dados ['preco'])) ? ($dados ['preco']) : (null));
        $this->tempoLiberacaoDepositosSaques = ((isset($dados ['tempo_liberacao_depositos_saques'])) ? ($dados ['tempo_liberacao_depositos_saques']) : (null));
        $this->mesesDuracao = ((isset($dados ['meses_duracao'])) ? ($dados ['meses_duracao']) : (null));
        $this->comissao = ((isset($dados ['comissao'])) ? ($dados ['comissao']) : (null));
    }
    
    public function getTable() {
        return "licencas_software";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LicencaSoftware();
    }


}

?>