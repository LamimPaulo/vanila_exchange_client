<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RankingClienteMensal {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $nomeMoedaTrade;
    
    /**
     *
     * @var Integer 
     */
    public $posicao;
    
    /**
     *
     * @var String 
     */
    public $nomeCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Double 
     */
    public $volume;
    
    /**
     *
     * @var Integer 
     */
    public $idParidade;
    
    /**
     *
     * @var String 
     */
    public $nomeMoedaBook;
    
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idParidade = ((isset($dados ['id_paridade'])) ? ($dados ['id_paridade']) : (null));
        $this->nomeCliente = ((isset($dados ['nome_cliente'])) ? ($dados ['nome_cliente']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->nomeMoedaBook = ((isset($dados ['nome_moeda_book'])) ? ($dados ['nome_moeda_book']) : (null));
        $this->nomeMoedaTrade = ((isset($dados ['nome_moeda_trade'])) ? ($dados ['nome_moeda_trade']) : (null));
        $this->posicao = ((isset($dados ['posicao'])) ? ($dados ['posicao']) : (null));
        $this->volume = ((isset($dados ['volume'])) ? ($dados ['volume']) : (null));
    }
    
    public function getTable() {
        return "ranking_clientes_mensal";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new RankingClienteMensal();
    }


}

?>