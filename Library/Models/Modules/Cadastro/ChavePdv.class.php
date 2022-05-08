<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ChavePdv {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    
    /**
     *
     * @var String 
     */
    public $chave;
    
    /**
     *
     * @var String 
     */
    public $chaveHomologacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idPontoPdv;
    
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
            
    
    /**
     * Data em que a chave foi cadastrada no sistema
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    
    
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
        $this->chave = ((isset($dados ['chave'])) ? ($dados ['chave']) : (null));
        $this->chaveHomologacao = ((isset($dados ['chave_homologacao'])) ? ($dados ['chave_homologacao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idPontoPdv = ((isset($dados ['id_ponto_pdv'])) ? ($dados ['id_ponto_pdv']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "chaves_pdv";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ChavePdv();
    }


}

?>