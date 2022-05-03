<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasLicenca {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $idLicencaSoftware;

    
    
    /**
     * 
     * @var Integer
     */
    public $idCliente;
    
    
    
    /**
     * 
     * @var Double
     */
    public $preco;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAdesao;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataVencimento;
    
    /**
     * A = Aprovada, P = Pendente, N = Negada
     * @var String 
     */
    public $situacao;
    
    
    /**
     *
     * @var String 
     */
    public $motivoNegacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAprovacao;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataNegacao;
    
    /**
     *
     * @var Integer 
     */
    public $bloqueada;
    
    
    /**
     *
     * @var Cliente
     */
    public $cliente;
    
    
    /**
     *
     * @var Usuario
     */
    public $usuario;
    
    
    /**
     *
     * @var LicencaSoftware
     */
    public $licencaSoftware;
    
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
        $this->idCliente = ((isset($dados['id_cliente'])) ? ($dados['id_cliente']) : (null));
        $this->idLicencaSoftware = ((isset($dados['id_licenca_software'])) ? ($dados['id_licenca_software']) : (null));
        $this->id = ((isset($dados['id'])) ? ($dados['id']) : (null));
        $this->preco = ((isset($dados['preco'])) ? ($dados['preco']) : (null));
        $this->dataAdesao = ((isset($dados['data_adesao'])) ? ($dados['data_adesao'] instanceof \Utils\Data ? $dados['data_adesao'] : new \Utils\Data(substr($dados['data_adesao'], 0, 19))) : (null));
        $this->dataVencimento = ((isset($dados['data_vencimento'])) ? ($dados['data_vencimento'] instanceof \Utils\Data ? $dados['data_vencimento'] : new \Utils\Data(substr($dados['data_vencimento'], 0, 19))) : (null));
        $this->dataAprovacao = ((isset($dados['data_aprovacao'])) ? ($dados['data_aprovacao'] instanceof \Utils\Data ? $dados['data_aprovacao'] : new \Utils\Data(substr($dados['data_aprovacao'], 0, 19))) : (null));
        $this->dataNegacao = ((isset($dados['data_negacao'])) ? ($dados['data_negacao'] instanceof \Utils\Data ? $dados['data_negacao'] : new \Utils\Data(substr($dados['data_negacao'], 0, 19))) : (null));
        $this->situacao = ((isset($dados['situacao'])) ? ($dados['situacao']) : (null));
        $this->motivoNegacao = ((isset($dados ['motivo_negacao'])) ? ($dados ['motivo_negacao']) : (null));
        $this->idUsuario = ((isset($dados['id_usuario'])) ? ($dados['id_usuario']) : (null));
        $this->bloqueada = ((isset($dados['bloqueada'])) ? ($dados['bloqueada']) : (null));
    }
    
    public function getTable() {
        return "clientes_has_licencas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasLicenca();
    }


}

?>