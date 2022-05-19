<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ResgateComissao {

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
    public $dataResgate;
    
    
    /**
     * 
     * @var \Utils\Data 
     */
    public $dataReferenciaFechamento;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var Integer 
     */
    public $idPedidoCartao;
    
    /**
     *
     * @var String
     */
    public $tipo;
    
    /**
     *
     * @var Integer
     */
    public $idClienteDestino;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     *
     * @var Cliente 
     */
    public $clienteDestino;
    
    /**
     *
     * @var PedidoCartao 
     */
    public $pedidoCartao;
    
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
        $this->dataReferenciaFechamento = ((isset($dados ['data_referencia_fechamento'])) ? ($dados ['data_referencia_fechamento'] instanceof \Utils\Data ?
                $dados ['data_referencia_fechamento'] : new \Utils\Data(substr($dados ['data_referencia_fechamento'], 0, 19))) : (null));
        $this->dataResgate = ((isset($dados ['data_resgate'])) ? ($dados ['data_resgate'] instanceof \Utils\Data ? $dados ['data_resgate'] : 
            new \Utils\Data(substr($dados ['data_resgate'], 0, 19))) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->idPedidoCartao = ((isset($dados ['id_pedido_cartao'])) ? ($dados ['id_pedido_cartao']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->idClienteDestino = ((isset($dados ['id_cliente_destino'])) ? ($dados ['id_cliente_destino']) : (null));
    }
    
    public function getTable() {
        return "resgates_comissoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ResgateComissao();
    }


}

?>