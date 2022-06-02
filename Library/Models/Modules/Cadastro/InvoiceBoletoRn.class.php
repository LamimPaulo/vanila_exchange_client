<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade InvoiceBoleto
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoiceBoletoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new InvoiceBoleto());
        } else {
            $this->conexao = new GenericModel($adapter, new InvoiceBoleto());
        }
    }
    
    public function salvar(InvoiceBoleto &$invoiceBoleto) {
        
        if ($invoiceBoleto->id > 0) {
            $aux = new InvoiceBoleto(Array("id" => $invoiceBoleto->id));
            $this->conexao->carregar($aux);
            
            $invoiceBoleto->dataRecargaFinalizada = $aux->dataRecargaFinalizada;
        } else {
            $invoiceBoleto->dataRecargaFinalizada = null;
        }
        
        if (empty($invoiceBoleto->address)) {
            throw new \Exception($this->idioma->getText("endPagamentoInvalido"));
        }
        
        if (!isset($invoiceBoleto->dataExpiracaoInvoice->data) || $invoiceBoleto->dataExpiracaoInvoice->data == null) {
            throw new \Exception($this->idioma->getText("dataExpiInvalida"));
        }
        
        if (!$invoiceBoleto->idBoletoCliente > 0) {
            throw new \Exception($this->idioma->getText("identBoletoInvalida"));
        }
        
        if (!$invoiceBoleto->idInvoice > 0) {
            throw new \Exception($this->idioma->getText("identificacaoInvoiceInvalida"));
        }
        
        if (!in_array($invoiceBoleto->status, Array(
            \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO,
            \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO,
            \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO,
            \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO,
        ))) {
            throw new \Exception($this->idioma->getText("statusInvalido"));
        }
        
        if (!is_numeric($invoiceBoleto->valorReal) || !$invoiceBoleto->valorReal>0) {
            throw new \Exception($this->idioma->getText("valorBoletoInvalido"));
        }
        
        if (!is_numeric($invoiceBoleto->valorBtc) || !$invoiceBoleto->valorBtc>0) {
            throw new \Exception($this->idioma->getText("valorMoedaDigitalinvalida"));
        }
        
        if ($invoiceBoleto->status == \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_PAGAMENTO) {
            if (!isset($invoiceBoleto->dataPagamento->data) || $invoiceBoleto->dataPagamento->data == null) {
                throw new \Exception($this->idioma->getText("ataPagamentoInvalida"));
            }
        }
        
        $this->conexao->salvar($invoiceBoleto);
    }
    
    public function getCurrentInvoice(BoletoCliente $boletoCliente) {
        $result = $this->conexao->listar("status IN ('".\Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO."', '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."') AND id_boleto_cliente = {$boletoCliente->id}", "id DESC", null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
}

?>