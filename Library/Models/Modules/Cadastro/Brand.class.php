<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Brand {
    /**
     * Chave primária da tabela
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
    public $colorBackground;
    
    /**
     * 
     * @var String 
     */
    public $colorNome;
    
    /**
     * 
     * @var String 
     */
    public $logo;

    /**
     * 
     * @var Integer 
     */
    public $idMoedaDashboardPrimary;
    
    /**
     * 
     * @var Integer 
     */
    public $idMoedaDashboardSecondary;

    
    
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
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->colorBackground = ((isset($dados ['color_background'])) ? ($dados ['color_background']) : (null));
        $this->colorNome = ((isset($dados ['color_nome'])) ? ($dados ['color_nome']) : (null));
        $this->logo = ((isset($dados ['logo'])) ? ($dados ['logo']) : (null));
        $this->idMoedaDashboardPrimary = ((isset($dados ['id_moeda_dashboard_primary'])) ? ($dados ['id_moeda_dashboard_primary']) : (null));
        $this->idMoedaDashboardSecondary = ((isset($dados ['id_moeda_dashboard_secondary'])) ? ($dados ['id_moeda_dashboard_secondary']) : (null));
    }
    
    public function getTable() {
        return "brand";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Brand();
    }


}

?>