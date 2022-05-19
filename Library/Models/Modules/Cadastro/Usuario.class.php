<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos usuários do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Usuario {
    /**
     * Chave primária da tabela
     * @var Integer 
     */
    public $id;

    /**
     * E-mail do usuário
     * @var String
     */
    public $email;

    
    /**
     * Nome do usuário
     * @var String 
     */
    public $nome;
    
    
    /**
     * Indica se o usuário está ativo para acesso ao sistema
     * @var Boolean 
     */
    public $ativo;
    
    /**
     * Senha de acesso
     * @var String 
     */
    public $senha;
    
    /**
     *
     * @var String 
     */
    public $cidade;
    
    /**
     *
     * @var String 
     */
    public $celular;
    
    /**
     *
     * @var String 
     */
    public $anotacoes;
    
    /**
     * A = Administrador, V = Vendedor
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExpiracao;
    
    /**
     *
     * @var String 
     */
    public $cpf;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $foto;
    
    
    /**
     *
     * @var Integer 
     */
    public $twoFactorAuth;
    
    /**
     *
     * @var Integer
     */
    public $tipoAutenticacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $statusEnviarNotificacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $permiteAlteracao;
    
    /**
     *
     * @var String 
     */
    public $matricula;
    
    /**
     *
     * @var String 
     */
    public $observacoes;
    
    /**
     *
     * @var Integer 
     */
    public $googleAuthAtivado;
    
    
    /**
     *
     * @var String 
     */
    public $googleAuthSecret;
    
    /**
     *
     * @var String 
     */
    public $codAppAdvisor;

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
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->senha = ((isset($dados ['senha'])) ? ($dados ['senha']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->anotacoes = ((isset($dados ['anotacoes'])) ? ($dados ['anotacoes']) : (null));
        $this->cidade = ((isset($dados ['cidade'])) ? ($dados ['cidade']) : (null));
        $this->celular = ((isset($dados ['celular'])) ? ($dados ['celular']) : (null));
        $this->dataExpiracao = ((isset($dados['data_expiracao'])) ? ($dados['data_expiracao'] instanceof \Utils\Data ? $dados['data_expiracao'] : 
        new \Utils\Data(substr($dados['data_expiracao'], 0, 19))) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
        new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->cpf = ((isset($dados ['cpf'])) ? ($dados ['cpf']) : (null));
        $this->foto = ((isset($dados ['foto'])) ? ($dados ['foto']) : (null));
        $this->twoFactorAuth = ((isset($dados ['two_factor_auth'])) ? ($dados ['two_factor_auth']) : (null));
        $this->tipoAutenticacao = ((isset($dados ['tipo_autenticacao'])) ? ($dados ['tipo_autenticacao']) : (null));
        $this->statusEnviarNotificacao = ((isset($dados ['status_enviar_notificacao'])) ? ($dados ['status_enviar_notificacao']) : (null));
        $this->permiteAlteracao = ((isset($dados ['permite_alteracao'])) ? ($dados ['permite_alteracao']) : (null));
        $this->matricula = ((isset($dados ['matricula'])) ? ($dados ['matricula']) : (null));
        $this->observacoes = ((isset($dados ['observacoes'])) ? \Utils\Criptografia::decriptyPostId($dados ['observacoes'], false) : (null));
        
        
        $this->googleAuthSecret = ((isset($dados ['google_auth_secret'])) ? ($dados ['google_auth_secret']) : (null));
        $this->googleAuthAtivado = ((isset($dados ['google_auth_ativado'])) ? ($dados ['google_auth_ativado']) : (null));
        $this->codAppAdvisor = ((isset($dados ['cod_app_advisor'])) ? ($dados ['cod_app_advisor']) : (null));
    }
    
    public function getTable() {
        return "usuarios";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Usuario();
    }
}

?>