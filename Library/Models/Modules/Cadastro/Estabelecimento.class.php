<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Estabelecimento {

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
    public $endereco;
    
    
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
    public $bairro;
    
    
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
            
    
    /**
     *
     * @var String 
     */        
    public $telefone;
    
    
    /**
     *
     * @var String 
     */
    public $email;
    
    
    /**
     *
     * @var String 
     */
    public $telefone2;
    
    
    /**
     *
     * @var String 
     */
    public $cpf;
    
    
    /**
     *
     * @var String 
     */
    public $codigoCidade;
    
    
    /**
     *
     * @var String 
     */
    public $cep;
    
    
    /**
     *
     * @var String 
     */
    public $idCliente;
    
    
    
    /**
     *
     * @var String 
     */
    public $cnpj;
    
    
    
    /**
     *
     * @var String 
     */
    public $chave;
    
    
    /**
     *
     * @var String 
     */
    public $chaveSandbox;
    
    /**
     * Data em que o estabelecimento foi cadastrado no sistema
     * @var \Utils\Data 
     */
    public $dataCriacao;
    
    
    /**
     *
     * @var String 
     */
    public $callbackHomologacao;
    
    /**
     *
     * @var String 
     */
    public $callbackProducao;
    
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
     * @var Cliente
     */
    public $cliente;
    
    
    /**
     * 
     * @var Cidade
     */
    public $cidade;
    
    /**
     *
     * @var Double 
     */
    public $comissaoEstabelecimento;
    
    /**
     *
     * @var String 
     */
    public $tipoComissaoEstabelecimento;
    
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
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->chave = ((isset($dados ['chave'])) ? ($dados ['chave']) : (null));
        $this->chaveSandbox = ((isset($dados ['chave_sandbox'])) ? ($dados ['chave_sandbox']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->cnpj = ((isset($dados ['cnpj'])) ? ($dados ['cnpj']) : (null));
        $this->complemento = ((isset($dados ['complemento'])) ? ($dados ['complemento']) : (null));
        $this->cpf = ((isset($dados ['cpf'])) ? ($dados ['cpf']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->endereco = ((isset($dados ['endereco'])) ? ($dados ['endereco']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->codigoCidade = ((isset($dados ['codigo_cidade'])) ? ($dados ['codigo_cidade']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->telefone = ((isset($dados ['telefone'])) ? ($dados ['telefone']) : (null));
        $this->telefone2 = ((isset($dados ['telefone2'])) ? ($dados ['telefone2']) : (null));
        $this->dataCriacao = ((isset($dados['data_criacao'])) ? ($dados['data_criacao'] instanceof \Utils\Data ? $dados['data_criacao'] : 
            new \Utils\Data(substr($dados['data_criacao'], 0, 19))) : (null));
        $this->callbackHomologacao = ((isset($dados ['callback_homologacao'])) ? ($dados ['callback_homologacao']) : (null));
        $this->callbackProducao = ((isset($dados ['callback_producao'])) ? ($dados ['callback_producao']) : (null));
        
        $this->habilitarSaqueAutomatico = ((isset($dados ['habilitar_saque_automatico'])) ? ($dados ['habilitar_saque_automatico']) : (null));
        $this->walletSaqueAutomatico = ((isset($dados ['wallet_saque_automatico'])) ? ($dados ['wallet_saque_automatico']) : (null));
        $this->comissaoEstabelecimento = ((isset($dados ['comissao_estabelecimento'])) ? ($dados ['comissao_estabelecimento']) : (null));
        $this->tipoComissaoEstabelecimento = ((isset($dados ['tipo_comissao_estabelecimento'])) ? ($dados ['tipo_comissao_estabelecimento']) : (null));
    }
    
    public function getTable() {
        return "estabelecimentos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Estabelecimento();
    }


}

?>