<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class BoletoCliente {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $barras;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idBanco;
    
    /**
     *
     * @var Double 
     */
    public $valor;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataVencimento;
    
    /**
     *
     * @var String 
     */
    public $email;
    
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
     * @var \Utils\Data 
     */
    public $dataPagamento;
    
    /**
     *
     * @var String 
     */
    public $arquivoBoleto;
    
    
    /**
     *
     * @var String 
     */
    public $arquivoComprovante;
    
    /**
     *
     * @var Integer 
     */
    public $idReferencia;
    
    
    
    /**
     *
     * @var Double 
     */
    public $taxa;
    
    
    
    /**
     *
     * @var Double 
     */
    public $valorTaxa;
    
    
    
    /**
     *
     * @var String 
     */
    public $notaFiscal;
    
    /**
     *
     * @var String 
     */
    public $comentario;
    
    /**
     *
     * @var Integer
     */
    public $idCategoriaServico;
    
    /**
     *
     * @var String 
     */
    public $motivoCancelamento;
    
     /**
     *
     * @var Integer 
     */
    public $idCanceladoPor;

    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCancelamento;
    
    
    
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
        $this->barras = ((isset($dados ['barras'])) ? ($dados ['barras']) : (null));
        $this->dataCadastro = ((isset($dados ['data_cadastro'])) ? ($dados ['data_cadastro'] instanceof \Utils\Data ? $dados ['data_cadastro'] : 
            new \Utils\Data(substr($dados ['data_cadastro'], 0, 19))) : (null));
        $this->dataPagamento = ((isset($dados ['data_pagamento'])) ? ($dados ['data_pagamento'] instanceof \Utils\Data ? $dados ['data_pagamento'] 
                : new \Utils\Data(substr($dados ['data_pagamento'], 0, 19))) : (null));
        $this->dataVencimento = ((isset($dados ['data_vencimento'])) ? ($dados ['data_vencimento'] instanceof \Utils\Data ? $dados ['data_vencimento']
                : new \Utils\Data(substr($dados ['data_vencimento'], 0, 19))) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->arquivoBoleto = ((isset($dados ['arquivo_boleto'])) ? ($dados ['arquivo_boleto']) : (null));
        $this->arquivoComprovante = ((isset($dados ['arquivo_comprovante'])) ? ($dados ['arquivo_comprovante']) : (null));
        
        $this->idReferencia = ((isset($dados ['id_referencia'])) ? ($dados ['id_referencia']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->taxa = ((isset($dados ['taxa'])) ? ($dados ['taxa']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->notaFiscal = ((isset($dados ['nota_fiscal'])) ? ($dados ['nota_fiscal']) : (null));
        $this->comentario = ((isset($dados ['comentario'])) ? ($dados ['comentario']) : (null));
        $this->motivoCancelamento = ((isset($dados ['motivo_cancelamento'])) ? ($dados ['motivo_cancelamento']) : (null));
        $this->idCategoriaServico = ((isset($dados ['id_categoria_servico'])) ? ($dados ['id_categoria_servico']) : (null));
        $this->dataCancelamento = ((isset($dados ['data_cancelamento'])) ? ($dados ['data_cancelamento']) : (null));
        $this->idCanceladoPor = ((isset($dados ['id_cancelado_por'])) ? ($dados ['id_cancelado_por']) : (null));
    }
    
    public function getTable() {
        return "boletos_clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new BoletoCliente();
    }

    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO:
                return \Utils\Idiomas::get("aguardandoPagamento", 'IDIOMA');
            case \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO:
                return \Utils\Idiomas::get("solicitacaoCancelada", 'IDIOMA');
            case \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO:
                return \Utils\Idiomas::get("boletoPago", 'IDIOMA');
            case \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO:
                return \Utils\Idiomas::get("pagamentoRecebido", 'IDIOMA');
            default:
                \Utils\Idiomas::get("desconhecido", 'IDIOMA');
        }
    }

}

?>
