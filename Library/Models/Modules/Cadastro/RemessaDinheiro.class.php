<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados da Remessa de Dinheiro
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RemessaDinheiro {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $titular;
    
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
     * @var String
     */
    public $documento;
    
    /**
     *
     * @var String 
     */
    public $email;
    
    /**
     *
     * @var String 
     */
    public $tipoConta;
    
    /**
     *
     * @var String 
     */
    public $agencia;
    
    /**
     *
     * @var String 
     */
    public $agenciaDigito;
    
    /**
     *
     * @var String 
     */
    public $conta;
    
    /**
     *
     * @var String 
     */
    public $contaDigito;
    
    
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
    public $arquivoComprovante;
    
    /**
     *
     * @var String 
     */
    public $operacao;
    
    /**
     *
     * @var Integer 
     */
    public $idReferencia;
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
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
     * @var Cliente
     */
    public $cliente;
    
    
    /**
     *
     * @var Integer 
     */
    public $aceitaNota;
    
    /**
     *
     * @var Integer 
     */
    public $motivoCancelamento;
    
    /**
     *
     * @var Integer 
     */
    public $dataCancelamento;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $idCanceladoPor;
    
    /**
     *
     * @var Integer
     */
    public $idCategoriaServico;
    
    /**
     *
     * @var Double 
     */
    public $tarifaTed;
    
    
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
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->dataPagamento = ((isset($dados['data_pagamento'])) ? ($dados['data_pagamento'] instanceof \Utils\Data ? $dados['data_pagamento'] : 
            new \Utils\Data(substr($dados['data_pagamento'], 0, 19))) : (null));
        $this->conta = ((isset($dados ['conta'])) ? ($dados ['conta']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idBanco = ((isset($dados ['id_banco'])) ? ($dados ['id_banco']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->valor = ((isset($dados ['valor'])) ? ($dados ['valor']) : (null));
        $this->tipoConta = ((isset($dados ['tipo_conta'])) ? ($dados ['tipo_conta']) : (null));
        $this->titular = ((isset($dados ['titular'])) ? ($dados ['titular']) : (null));
        $this->documento = ((isset($dados ['documento'])) ? ($dados ['documento']) : (null));
        $this->operacao = ((isset($dados ['operacao'])) ? ($dados ['operacao']) : (null));
        $this->arquivoComprovante = ((isset($dados ['arquivo_comprovante'])) ? ($dados ['arquivo_comprovante']) : (null));
        $this->idReferencia = ((isset($dados ['id_referencia'])) ? ($dados ['id_referencia']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->taxa = ((isset($dados ['taxa'])) ? ($dados ['taxa']) : (null));
        $this->valorTaxa = ((isset($dados ['valor_taxa'])) ? ($dados ['valor_taxa']) : (null));
        $this->notaFiscal = ((isset($dados ['nota_fiscal'])) ? ($dados ['nota_fiscal']) : (null));
        $this->aceitaNota = ((isset($dados ['aceita_nota'])) ? ($dados ['aceita_nota']) : (null));
        $this->motivoCancelamento = ((isset($dados ['motivo_cancelamento'])) ? ($dados ['motivo_cancelamento']) : (null));
        $this->dataCancelamento = ((isset($dados ['data_cancelamento'])) ? ($dados ['data_cancelamento']) : (null));
        $this->idCanceladoPor = ((isset($dados ['id_cancelado_por'])) ? ($dados ['id_cancelado_por']) : (null));
        $this->idCategoriaServico = ((isset($dados ['id_categoria_servico'])) ? ($dados ['id_categoria_servico']) : (null));
        $this->tarifaTed = ((isset($dados ['tarifa_ted'])) ? ($dados ['tarifa_ted']) : (null));
        $this->contaDigito = ((isset($dados ['conta_digito'])) ? ($dados ['conta_digito']) : (null));
        $this->agenciaDigito = ((isset($dados ['agencia_digito'])) ? ($dados ['agencia_digito']) : (null));
    }
    
    public function getTable() {
        return "remessas_dinheiro";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new RemessaDinheiro();
    }


    public function getStatus() {
        switch ($this->status) {
            case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO:
                return \Utils\Idiomas::get("aguardandoPagamento", 'IDIOMA');
            case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO:
                return \Utils\Idiomas::get("solicitacaoCancelada", 'IDIOMA');
            case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO:
                return \Utils\Idiomas::get("transferenciaEfetuada", 'IDIOMA');
            case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO:
                return \Utils\Idiomas::get("pagamentoRecebido", 'IDIOMA');
            default:
                \Utils\Idiomas::get("desconhecido", 'IDIOMA');
        }
    }
}

?>
