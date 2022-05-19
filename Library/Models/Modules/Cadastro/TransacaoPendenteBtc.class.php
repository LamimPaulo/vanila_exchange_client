<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TransacaoPendenteBtc {

    
    /**
     * 
     * @var Integer
     */
    public $idCliente;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $data;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     * 0 indica que ainda não foi realizada a transferência e 1 indica que já foi realizada
     * @var Integer
     */
    public $executada;
    
    /**
     * ENdereço para qual a transferência foi realizada
     * @var String
     */
    public $enderecoBitcoin;
    
    /**
     *
     * @var String 
     */
    public $hash;
    
    /**
     *
     * @var String 
     */
    public $erro;
    
    /**
     *
     * @var String 
     */
    public $tipo;
    
    /**
     *
     * @var Integer 
     */
    public $idContaCorrenteBtc;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataConfirmacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Integer 
     */
    public $idCarteiraPdv;
    
    
    /**
     *
     * @var Integer 
     */
    public $idInvoicePdv;
    
    /**
     *
     * @var Usuario
     */
    public $usuario;
    
    /**
     *
     * @var Moeda
     */
    public $moeda;
    
    
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
        $this->data = ((isset($dados ['data'])) ? ($dados ['data'] instanceof \Utils\Data ? $dados ['data'] : 
            new \Utils\Data(substr($dados ['data'], 0, 19))) : (null));
        $this->dataCadastro = ((isset($dados ['data_cadastro'])) ? ($dados ['data_cadastro'] instanceof \Utils\Data ?
                $dados ['data_cadastro'] : new \Utils\Data(substr($dados ['data_cadastro'], 0, 19))) : (null));
        $this->dataConfirmacao = ((isset($dados ['data_confirmacao'])) ? ($dados ['data_confirmacao'] instanceof \Utils\Data ?
                $dados ['data_confirmacao'] : new \Utils\Data(substr($dados ['data_confirmacao'], 0, 19))) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->executada = ((isset($dados ['executada'])) ? ($dados ['executada']) : (null));
        $this->enderecoBitcoin = ((isset($dados ['endereco_bitcoin'])) ? ($dados ['endereco_bitcoin']) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->erro = ((isset($dados ['erro'])) ? ($dados ['erro']) : (null));
        $this->idContaCorrenteBtc = ((isset($dados ['id_conta_corrente_btc'])) ? ($dados ['id_conta_corrente_btc']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->idCarteiraPdv = ((isset($dados ['id_carteira_pdv'])) ? ($dados ['id_carteira_pdv']) : (null));
        $this->idInvoicePdv = ((isset($dados ['id_invoice_pdv'])) ? ($dados ['id_invoice_pdv']) : (null));
    }
    
    public function getTable() {
        return "transacoes_pendentes_btc";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new TransacaoPendenteBtc();
    }


}
?>