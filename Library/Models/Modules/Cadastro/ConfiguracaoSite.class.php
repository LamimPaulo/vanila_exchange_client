<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ConfiguracaoSite {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $imagemCacentro;
    
    /**
     *
     * @var String 
     */
    public $textoCacentro;
    
    /**
     *
     * @var String 
     */
    public $tituloCacentro;
    
    /**
     *
     * @var String 
     */
    public $labelBotaoCacentro;
    /**
     *
     * @var Integer 
     */
    public $showCelularCacentro;
    /**
     *
     * @var Integer 
     */
    public $showBotaoCacentro;
    
    /**
     *
     * @var String 
     */
    public $urlBotaoCacentro;
    /**
     *
     * @var Integer 
     */
    public $showCacentro;
    
    /**
     *
     * @var String 
     */
    public $imagemCabase;
    
    /**
     *
     * @var String 
     */
    public $textoCabase;
    
    /**
     *
     * @var String 
     */
    public $tituloCabase;
    
    /**
     *
     * @var String 
     */
    public $labelBotaoCabase;
    /**
     *
     * @var Integer 
     */
    public $showCelularCabase;
    /**
     *
     * @var Integer 
     */
    public $showBotaoCabase;
    
    /**
     *
     * @var String 
     */
    public $urlBotaoCabase;
    /**
     *
     * @var Integer 
     */
    public $showCabase;
    
    
    
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
        $this->imagemCabase = ((isset($dados ['imagem_cabase'])) ? ($dados ['imagem_cabase']) : (null));
        $this->imagemCacentro = ((isset($dados ['imagem_cacentro'])) ? ($dados ['imagem_cacentro']) : (null));
        $this->labelBotaoCabase = ((isset($dados ['label_botao_cabase'])) ? ($dados ['label_botao_cabase']) : (null));
        $this->labelBotaoCacentro = ((isset($dados ['label_botao_cacentro'])) ? ($dados ['label_botao_cacentro']) : (null));
        $this->showBotaoCabase = ((isset($dados ['show_botao_cabase'])) ? ($dados ['show_botao_cabase']) : (null));
        $this->showBotaoCacentro = ((isset($dados ['show_botao_cacentro'])) ? ($dados ['show_botao_cacentro']) : (null));
        $this->showCabase = ((isset($dados ['show_cabase'])) ? ($dados ['show_cabase']) : (null));
        $this->showCacentro = ((isset($dados ['show_cacentro'])) ? ($dados ['show_cacentro']) : (null));
        $this->showCelularCabase = ((isset($dados ['show_celular_cabase'])) ? ($dados ['show_celular_cabase']) : (null));
        $this->showCelularCacentro = ((isset($dados ['show_celular_cacentro'])) ? ($dados ['show_celular_cacentro']) : (null));
        $this->textoCabase = ((isset($dados ['texto_cabase'])) ? ($dados ['texto_cabase']) : (null));
        $this->textoCacentro = ((isset($dados ['texto_cacentro'])) ? ($dados ['texto_cacentro']) : (null));
        $this->tituloCabase = ((isset($dados ['titulo_cabase'])) ? ($dados ['titulo_cabase']) : (null));
        $this->tituloCacentro = ((isset($dados ['titulo_cacentro'])) ? ($dados ['titulo_cacentro']) : (null));
        $this->urlBotaoCacentro = ((isset($dados ['url_botao_cacentro'])) ? ($dados ['url_botao_cacentro']) : (null));
        $this->urlBotaoCabase = ((isset($dados ['url_botao_cabase'])) ? ($dados ['url_botao_cabase']) : (null));
    }
    
    public function getTable() {
        return "configuracoes_site";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ConfiguracaoSite();
    }


}

?>