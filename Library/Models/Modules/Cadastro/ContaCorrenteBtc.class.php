<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados dos estados do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaCorrenteBtc {


    /**
     * 
     * @var Integer
     */
    public $id;
    
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
    public $tipo;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    /**
     *
     * @var Integer 
     */
    public $transferencia;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     * I para transferências internas e E para transferências externas
     * @var String
     */
    public $direcao;
    
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
     * @var Double 
     */
    public $valorTaxa;
    
    /**
     *
     * @var Integer 
     */
    public $autorizada;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Integer 
     */
    public $orderBook;
    
    /**
     *
     * @var String 
     */
    public $seed;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    /**
     *
     * @var String 
     */
    public $enderecoEnvio;
    
    /**
     *
     * @var Integer
     */
    public $idReferenciado;
    
    /**
     * @var Integer 
     * 0 - Transacao
     * 1 - Book
     * 2 - Comissao Book
     * 3 - Investimento
     * 4 - ICO
     * 5 - Bonificacao Ico
     * 6 - Votacao
     * 7 - Airdrop
     * 8 - Recompensa ICO
     * 9 - Bonus Redes Sociais
     * 10 - SWAP
     * 11 - PDV
     * 12 - Carteiras Remotas
     * 13 - Pagamentos
     * 14 - Dividendos
     * 15 - Comissao Depósito
     * 16 - Comissão Saque
     * 17 - Conversao
     */
    public $origem;
    
    /**
     *
     * @var String 
     */
    public $symbol;
    
    /**
     *
     * @var String 
     */
    public $nomeMoeda;
    
    /**
     *
     * @var String 
     */
    public $nomeCliente;
    
    /**
     *
     * @var String 
     */
    public $idSession;
    
    /**
     *
     * @var String 
     */
    public $ipSession;
     
    /**
     *
     * @var Integer 
     */
    public $idMoedaTaxa;

    /**
     *
     * @var Sting
     */
    public $rede;
    
    
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
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->transferencia = ((isset($dados ['transferencia'])) ? ($dados ['transferencia']) : (null));
        $this->direcao = ((isset($dados ['direcao'])) ? ($dados ['direcao']) : (null));
        $this->executada = ((isset($dados ['executada'])) ? ($dados ['executada']) : (null));
        $this->enderecoBitcoin = ((isset($dados ['endereco_bitcoin'])) ? ($dados ['endereco_bitcoin']) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->autorizada = ((isset($dados ['autorizada'])) ? ($dados ['autorizada']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->orderBook = ((isset($dados ['order_book'])) ? ($dados ['order_book']) : (null));
        $this->enderecoEnvio = ((isset($dados ['endereco_envio'])) ? ($dados ['endereco_envio']) : (null));
        $this->seed = ((isset($dados ['seed'])) ? ($dados ['seed']) : (null));
        $this->origem = ((isset($dados ['origem'])) ? ($dados ['origem']) : (null));
        $this->idReferenciado = ((isset($dados ['id_referenciado'])) ? ($dados ['id_referenciado']) : (null));
        
        $this->symbol = ((isset($dados ['symbol'])) ? ($dados ['symbol']) : (null));
        $this->nomeCliente = ((isset($dados ['nome_cliente'])) ? ($dados ['nome_cliente']) : (null));
        $this->nomeMoeda = ((isset($dados ['nome_moeda'])) ? ($dados ['nome_moeda']) : (null));
        $this->idSession = ((isset($dados ['id_session'])) ? ($dados ['id_session']) : (null));
        $this->ipSession = ((isset($dados ['ip_session'])) ? ($dados ['ip_session']) : (null));
        $this->idMoedaTaxa = ((isset($dados ['id_moeda_taxa'])) ? ($dados ['id_moeda_taxa']) : (null));
        $this->rede = ((isset($dados ['rede'])) ? ($dados ['rede']) : (null));
    }
    
    public function getTable() {
        return "conta_corrente_btc";
    }
    
    public function getSequence() {
        return "";
    }
    
    public function getInstance() {
        return new ContaCorrenteBtc();
    }
    
    public function getDescricaoOrigem() {
        
        switch ($this->origem) {
            case 0:
                if ($this->tipo == \Utils\Constantes::SAIDA) {
                    return "Saque";
                } else {
                    return "Depósito";
                }
            case 1: 
                if ($this->tipo == \Utils\Constantes::SAIDA) {
                    return "Venda";
                } else {
                    return "Compra";
                }
            case 2: 
                return "Comissao Book";
            case 3: 
                return "Investimento";
            case 4: 
                return "ICO";
            case 5: 
                return "Bonificação ICO";
            case 6: 
                return "Votação";
            case 7: 
                return "Airdrop";
            case 8: 
                return "Recompensa ICO";
            case 9: 
                return "Bonus Redes Sociais";
            case 10: 
                return "SWAP";
            case 11: 
                return "PDV";
            case 12: 
                return "Carteiras Remotas";
            default:
                return "";
        }
        
    }
    
    
    public function getStatus() {
        
        if ($this->autorizada == 0) {
            return "Aguardando Autorização";
        } else if ($this->autorizada == 1) {
            if ($this->executada < 1) {
                return "Processando Envio";
            } else {
                return "Transferência Concluída";
            }
        } else {
            return "Transação Negada";
        }
        
    }

}
?>