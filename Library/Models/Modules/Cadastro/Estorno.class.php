<?php

namespace Models\Modules\Cadastro;


/**
 * 
 */
class Estorno {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    
    /**
     *
     * @var String 
     */
    public $status;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuarioFinalizacao;
    
    /**
     *
     * @var String 
     */
    public $tipoConta;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuarioAbertura;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var String 
     */
    public $motivoRejeicao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataFinalizacao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCancelamento;
    
    /**
     *
     * @var Integer 
     */
    public $idDeposito;
    
    /**
     *
     * @var String 
     */
    public $conta;
    
    /**
     *
     * @var String 
     */
    public $agencia;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAbertura;
    
    /**
     *
     * @var Integer 
     */
    public $idBanco;
    
    
    /**
     *
     * @var String 
     */
    public $cpfCnpj;
    
    
    
    /**
     *
     * @var String 
     */
    public $nomeTitular;
    
    /**
     *
     * @var Double 
     */
    public $percentualTaxa;
    
    /**
     *
     * @var Doube 
     */
    public $valorTaxa;
    
    /**
     *
     * @var Double 
     */
    public $taxaTed;
    
    /**
     *
     * @var Usuario 
     */
    public $usuarioAbertura;
    
    /**
     *
     * @var Usuario 
     */
    public $usuarioFinalizacao;
    
    /**
     *
     * @var Banco 
     */
    public $banco;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    
    /**
     *
     * @var Deposito 
     */
    public $deposito;
    
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
        $this->agencia = ((isset($dados ['agencia'])) ? ($dados ['agencia']) : (null));
        $this->conta = ((isset($dados ['conta'])) ? ($dados ['conta']) : (null));
        $this->cpfCnpj = ((isset($dados ['cpf_cnpj'])) ? ($dados ['cpf_cnpj']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idDeposito = ((isset($dados ['id_deposito'])) ? ($dados ['id_deposito']) : (null));
        $this->idUsuarioAbertura = ((isset($dados ['id_usuario_abertura'])) ? ($dados ['id_usuario_abertura']) : (null));
        $this->idUsuarioFinalizacao = ((isset($dados ['id_usuario_finalizacao'])) ? ($dados ['id_usuario_finalizacao']) : (null));
        $this->motivoRejeicao = ((isset($dados ['motivo_rejeicao'])) ? ($dados ['motivo_rejeicao']) : (null));
        $this->nomeTitular = ((isset($dados ['nome_titular'])) ? ($dados ['nome_titular']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->tipoConta = ((isset($dados ['tipo_conta'])) ? ($dados ['tipo_conta']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->percentualTaxa = ((isset($dados ['percentual_taxa'])) ? ($dados ['percentual_taxa']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->taxaTed = ((isset($dados ['taxa_ted'])) ? ($dados ['taxa_ted']) : (null));
        
        $this->dataAbertura = ((isset($dados['data_abertura'])) ? ($dados['data_abertura'] instanceof \Utils\Data ? $dados['data_abertura'] : 
            new \Utils\Data(substr($dados['data_abertura'], 0, 19))) : (null));
        $this->dataCancelamento = ((isset($dados['data_cancelamento'])) ? ($dados['data_cancelamento'] instanceof \Utils\Data ? $dados['data_cancelamento'] : 
            new \Utils\Data(substr($dados['data_cancelamento'], 0, 19))) : (null));
        $this->dataFinalizacao = ((isset($dados['data_finalizacao'])) ? ($dados['data_finalizacao'] instanceof \Utils\Data ? $dados['data_finalizacao'] : 
            new \Utils\Data(substr($dados['data_finalizacao'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "estornos";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Estorno();
    }

    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::EXTORNO_APROVADO: return "Aprovado";
            case \Utils\Constantes::EXTORNO_CANCELADO: return "Cancelado";
            case \Utils\Constantes::EXTORNO_FINALIZADO: return "Finalizado";
            case \Utils\Constantes::EXTORNO_PENDENTE: return "Pendente";
            case \Utils\Constantes::EXTORNO_REJEITADO: return "Rejeitado";
            default:
                return "";
        }
    }
    
    
    public function getTipoConta() {
        switch ($this->tipoConta) {
            case \Utils\Constantes::CONTA_CORRENTE: return "Conta Corrente";
            case \Utils\Constantes::CONTA_POUPANCA: return "Conta Poupança";
            default:
                return "";
        }
    }
}

?>