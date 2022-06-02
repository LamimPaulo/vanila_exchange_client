<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Empresa {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var String
     */
    public $nomeEmpresarial;

    
    /**
     * 
     * @var int
     */
    public $anoInicio;
    
    /**
     * 
     * @var String 
     */
    public $nomeFantasia;
    
    /**
     * 
     * @var String 
     */
    public $numeroInscricao;
    
    /**
     * 
     * @var Date 
     */
    public $dataAbertura;

    /**
     * 
     * @var String 
     */
    public $logradouro;
    
    /**
     * 
     * @var String 
     */
    public $numero;
    
    /**
     * 
     * @var String 
     */
    public $complemento;
    
    /**
     * 
     * @var String 
     */
    public $cep;
    
    /**
     * 
     * @var String 
     */
    public $bairro;
    
    /**
     * 
     * @var String 
     */
    public $municipio;
    
    /**
     * 
     * @var String 
     */
    public $estado;

    /**
     * 
     * @var String 
     */
    public $pais;
    
    /**
     * 
     * @var String 
     */
    public $telefone;
    
    /**
     * 
     * @var String 
     */
    public $emailSuporte;
    
    /**
     * 
     * @var String 
     */
    public $emailFinanceiro;
    
    /**
     * 
     * @var String 
     */
    public $emailSac;

    /**
     * 
     * @var Integer 
     */
    public $ativo;
    
    /**
     * 
     * @var String 
     */
    public $homePage;
    
    /**
     * 
     * @var String 
     */
    public $facebook;
    
    /**
     * 
     * @var String 
     */
    public $instagram;
    
    /**
     * 
     * @var String 
     */
    public $twitter;
    
    /**
     * 
     * @var String 
     */
    public $linkedin;
    
    /**
     * 
     * @var String 
     */
    public $youtube;
    
    /**
     * 
     * @var String 
     */
    public $medium;
    
    /**
     * 
     * @var String 
     */
    public $marketingEmail;
    
    /**
     * 
     * @var Integer 
     */
    public $notaFiscal;
    
    /**
     * 
     * @var String 
     */
    public $urlTermos;
    
    /**
     * 
     * @var String 
     */
    public $blog;
    
    /**
     * 
     * @var String 
     */
    public $telegram;
    
    /**
     * 
     * @var String 
     */
    public $emailsSeguros;
    
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
        $this->nomeEmpresarial = ((isset($dados ['nome_empresarial'])) ? ($dados ['nome_empresarial']) : (null));
        $this->anoInicio = ((isset($dados ['ano_inicio'])) ? ($dados ['ano_inicio']) : (null));
        $this->nomeFantasia = ((isset($dados ['nome_fantasia'])) ? ($dados ['nome_fantasia']) : (null));
        $this->dataAbertura = ((isset($dados['data_abertura'])) ? ($dados['data_abertura'] instanceof \Utils\Data ? $dados['data_abertura'] : 
            new \Utils\Data(substr($dados['data_abertura'], 0, 19))) : (null));
        $this->logradouro = ((isset($dados ['logradouro'])) ? ($dados ['logradouro']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->complemento = ((isset($dados ['complemento'])) ? ($dados ['complemento']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->municipio = ((isset($dados ['municipio'])) ? ($dados ['municipio']) : (null));
        $this->estado = ((isset($dados ['estado'])) ? ($dados ['estado']) : (null));
        $this->pais = ((isset($dados ['pais'])) ? ($dados ['pais']) : (null));
        $this->telefone = ((isset($dados ['telefone'])) ? ($dados ['telefone']) : (null));
        $this->emailSuporte = ((isset($dados ['email_suporte'])) ? ($dados ['email_suporte']) : (null));
        $this->emailFinanceiro = ((isset($dados ['email_financeiro'])) ? ($dados ['email_financeiro']) : (null));
        $this->emailSac = ((isset($dados ['email_sac'])) ? ($dados ['email_sac']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->homePage = ((isset($dados ['home_page'])) ? ($dados ['home_page']) : (null));
        $this->facebook = ((isset($dados ['facebook'])) ? ($dados ['facebook']) : (null));
        $this->instagram = ((isset($dados ['instagram'])) ? ($dados ['instagram']) : (null));
        $this->twitter = ((isset($dados ['twitter'])) ? ($dados ['twitter']) : (null));
        $this->linkedin = ((isset($dados ['linkedin'])) ? ($dados ['linkedin']) : (null));
        $this->youtube = ((isset($dados ['youtube'])) ? ($dados ['youtube']) : (null));
        $this->medium = ((isset($dados ['medium'])) ? ($dados ['medium']) : (null));
        $this->marketingEmail = ((isset($dados ['marketing_email'])) ? ($dados ['marketing_email']) : (null));
        $this->notaFiscal = ((isset($dados ['nota_fiscal'])) ? ($dados ['nota_fiscal']) : (null));
        $this->urlTermos = ((isset($dados ['url_termos'])) ? ($dados ['url_termos']) : (null));
        $this->blog = ((isset($dados ['blog'])) ? ($dados ['blog']) : (null));
        $this->telegram = ((isset($dados ['telegram'])) ? ($dados ['telegram']) : (null));
        $this->emailsSeguros = ((isset($dados ['emails_seguros'])) ? ($dados ['emails_seguros']) : (null));

    }
    
    public function getTable() {
        return "empresa";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Empresa();
    }


}

?>