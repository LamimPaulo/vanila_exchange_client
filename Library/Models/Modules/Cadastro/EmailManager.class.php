<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class EmailManager {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * 
     * @var Integer
     */
    public $logAcesso;
    
    /**
     * 
     * @var String
     */
    public $imagemMarketing;
    
    /**
     *
     * @var Integer 
     */
    public $enderecoRodape;
    
    /**
     *
     * @var Integer 
     */
    public $canaisAtendimento;
    
    /**
     *
     * @var Integer 
     */
    public $redesSociais;
    
    /**
     *
     * @var String
     */
    public $mensagem;
    
    /**
     *
     * @var String
     */
    public $tituloMensagem;
    
    /**
     *
     * @var String
     */
    public $imagemCentro;
    
    /**
     *
     * @var String
     */
    public $imagemTopo;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $assunto;
    
    
    /**
     *
     * @var Integer 
     */
    public $tituloTopo;
    
    /**
     *
     * @var Integer 
     */
    public $fraseSeguranca;
    
    /**
     *
     * @var Integer 
     */
    public $clienteUrl;
    
    /**
     *
     * @var Integer 
     */
    public $homeUrl;
    
    /**
     *
     * @var Integer 
     */
    public $cliente2fa;
    
    /**
     *
     * @var String 
     */
    public $idTemplate;
    
    /**
     *
     * @var String 
     */
    public $parametrosJson;
    
    
    
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
        $this->logAcesso = ((isset($dados ['log_acesso'])) ? ($dados ['log_acesso']) : (null));
        $this->imagemMarketing = ((isset($dados ['imagem_marketing'])) ? ($dados ['imagem_marketing']) : (null));
        $this->enderecoRodape = ((isset($dados ['endereco_rodape'])) ? ($dados ['endereco_rodape']) : (null));
        $this->canaisAtendimento = ((isset($dados ['canais_atendimento'])) ? ($dados ['canais_atendimento']) : (null));
        $this->redesSociais = ((isset($dados ['redes_sociais'])) ? ($dados ['redes_sociais']) : (null));
        $this->mensagem = ((isset($dados ['mensagem'])) ? ($dados ['mensagem']) : (null));
        $this->tituloMensagem = ((isset($dados ['titulo_mensagem'])) ? ($dados ['titulo_mensagem']) : (null));
        $this->imagemCentro= ((isset($dados ['imagem_centro'])) ? ($dados ['imagem_centro']) : (null));
        $this->imagemTopo = ((isset($dados ['imagem_topo'])) ? ($dados ['imagem_topo']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->assunto = ((isset($dados ['assunto'])) ? ($dados ['assunto']) : (null));
        $this->tituloTopo = ((isset($dados ['titulo_topo'])) ? ($dados ['titulo_topo']) : (null));
        $this->fraseSeguranca = ((isset($dados ['frase_seguranca'])) ? ($dados ['frase_seguranca']) : (null));
        $this->clienteUrl = ((isset($dados ['cliente_url'])) ? ($dados ['cliente_url']) : (null));
        $this->homeUrl = ((isset($dados ['home_url'])) ? ($dados ['home_url']) : (null));
        $this->cliente2fa = ((isset($dados ['cliente_2fa'])) ? ($dados ['cliente_2fa']) : (null));
        $this->idTemplate = ((isset($dados ['id_template'])) ? ($dados ['id_template']) : (null));
        $this->parametrosJson = ((isset($dados ['parametros_json'])) ? ($dados ['parametros_json']) : (null));

    }
    
    public function getTable() {
        return "email_manager";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new EmailManager();
    }

}

?>