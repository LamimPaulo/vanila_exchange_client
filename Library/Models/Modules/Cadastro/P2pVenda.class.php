<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class P2pVenda {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Double 
     */
    public $volumeBtc;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataOperacao;
    
    /**
     *
     * @var String 
     */
    public $hash;
    
    /**
     *
     * @var String 
     */
    public $carteira;
    
    /**
     *
     * @var Integer 
     */
    public $idBanco;
    
    /**
     *
     * @var String 
     */
    public $status;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAlteracao;
    
    /**
     *
     * @var String 
     */
    public $nomeCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    /**
     *
     * @var Double 
     */
    public $valorCotacao;
    
    /**
     *
     * @var String 
     */
    public $telefone;
    
    /**
     *
     * @var String 
     */
    public $emailCliente;
    
    /**
     *
     * @var String 
     */
    public $tipoOperacao;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataLancamento;
    
    
    /**
     *
     * @var String 
     */
    public $tipoDeposito;
  
    /**
     *
     * @var Integer 
     */
    public $idOrderBook;
    
    /**
     *
     * @var Usuario 
     */
    public $usuario;
    
    /**
     *
     * @var Banco 
     */
    public $banco;
    
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
        $this->carteira = ((isset($dados ['carteira'])) ? ($dados ['carteira']) : (null));
        $this->dataAlteracao = ((isset($dados['data_alteracao'])) ? ($dados['data_alteracao'] instanceof \Utils\Data ? $dados['data_alteracao'] : 
            new \Utils\Data(substr($dados['data_alteracao'], 0, 19))) : (null));
        
        $this->dataOperacao = ((isset($dados['data_operacao'])) ? ($dados['data_operacao'] instanceof \Utils\Data ? $dados['data_operacao'] : 
            new \Utils\Data(substr($dados['data_operacao'], 0, 19))) : (null));
        $this->dataLancamento = ((isset($dados['data_lancamento'])) ? ($dados['data_lancamento'] instanceof \Utils\Data ? $dados['data_lancamento'] : 
            new \Utils\Data(substr($dados['data_lancamento'], 0, 19))) : (null));
        
        $this->emailCliente = ((isset($dados ['email_cliente'])) ? ($dados ['email_cliente']) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco-*']) : (null));
        $this->nomeCliente = ((isset($dados ['nome_cliente'])) ? ($dados ['nome_cliente']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->telefone = ((isset($dados ['telefone'])) ? ($dados ['telefone']) : (null));
        $this->tipoOperacao = ((isset($dados ['tipo_operacao'])) ? ($dados ['tipo_operacao']) : (null));
        $this->tipoDeposito = ((isset($dados ['tipo_deposito'])) ? ($dados ['tipo_deposito']) : (null));
       
        $this->valor = (isset($dados['valor']) ? $dados['valor'] : null);
        $this->valorCotacao = ((isset($dados ['valor_cotacao'])) ? ($dados ['valor_cotacao']) : (null));
        $this->volumeBtc = ((isset($dados ['volume_btc'])) ? ($dados ['volume_btc']) : (null));
        $this->idOrderBook = ((isset($dados ['id_order_book'])) ? ($dados ['id_order_book']) : (null));
        
    }
    
    public function getTable() {
        return "p2p_venda";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new P2pVenda();
    }


    public function getTipoDeposito() {
        switch ($this->tipoDeposito) {
            case \Utils\Constantes::TIPO_DEPOSITO_DINHEIRO: return "Dinheiro";
            case \Utils\Constantes::TIPO_DEPOSITO_DOC: return "DOC";
            case \Utils\Constantes::TIPO_DEPOSITO_TED: return "TED";
            case \Utils\Constantes::TIPO_DEPOSITO_TEF: return "TEF";

            default:
                return "Não informado";
        }
    }
    
    
    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::P2P_STATUS_CONCLUIDO: return "Concluído";
            case \Utils\Constantes::P2P_STATUS_AGUARDANDO_DEPOSITO: return "Aguardando Depósito";
            case \Utils\Constantes::P2P_STATUS_PENDENTE: return "Pendente";
            case \Utils\Constantes::P2P_STATUS_PROCESSANDO: return "Processando";

            default:
                return "";
        }
    }
    
    
    
}

?>