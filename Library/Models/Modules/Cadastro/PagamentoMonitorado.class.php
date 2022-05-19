<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class PagamentoMonitorado {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $enderecoOrigem;
    
    /**
     *
     * @var String 
     */
    public $hash;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $status;
    
    
    /**
     *
     * @var String 
     */
    public $callback;
    
    
    /**
     *
     * @var String 
     */
    public $enderecoDestino;
    
    
    /**
     *
     * @var Double 
     */
    public $volume;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataPagamento;
    
    
    /**
     *
     * @var String 
     */
    public $parametroUm;
    
    
    /**
     *
     * @var String 
     */
    public $parametroDois;
    
    
    /**
     *
     * @var String 
     */
    public $parametroTres;
    
    
    /**
     *
     * @var Double 
     */
    public $volumePago;
    
    
    
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
        $this->callback = ((isset($dados ['callback'])) ? ($dados ['callback']) : (null));
        $this->enderecoDestino = ((isset($dados ['endereco_destino'])) ? ($dados ['endereco_destino']) : (null));
        $this->enderecoOrigem = ((isset($dados ['endereco_origem'])) ? ($dados ['endereco_origem']) : (null));
        $this->hash = ((isset($dados ['hash'])) ? ($dados ['hash']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->volume = ((isset($dados ['volume'])) ? ($dados ['volume']) : (null));
        $this->volumePago = ((isset($dados ['volume_pago'])) ? ($dados ['volume_pago']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->dataPagamento = ((isset($dados['data_pagamento'])) ? ($dados['data_pagamento'] instanceof \Utils\Data ? $dados['data_pagamento'] : 
            new \Utils\Data(substr($dados['data_pagamento'], 0, 19))) : (null));
        $this->parametroUm = ((isset($dados ['parametro_um'])) ? ($dados ['parametro_um']) : (null));
        $this->parametroDois = ((isset($dados ['parametro_dois'])) ? ($dados ['parametro_dois']) : (null));
        $this->parametroTres = ((isset($dados ['parametro_tres'])) ? ($dados ['parametro_tres']) : (null));
    }
    
    public function getTable() {
        return "pagamentos_monitorados";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new PagamentoMonitorado();
    }


}

?>