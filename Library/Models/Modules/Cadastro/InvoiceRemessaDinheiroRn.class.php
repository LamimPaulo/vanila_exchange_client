<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade InvoiceRemessaDinheiro
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoiceRemessaDinheiroRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new InvoiceRemessaDinheiro());
        } else {
            $this->conexao = new GenericModel($adapter, new InvoiceRemessaDinheiro());
        }
    }
    
    public function salvar(InvoiceRemessaDinheiro &$invoiceRemessaDinheiro) {
        
        if ($invoiceRemessaDinheiro->id > 0) {
            $aux = new InvoiceRemessaDinheiro(Array("id" => $invoiceRemessaDinheiro->id));
            $this->conexao->carregar($aux);
            
            $invoiceRemessaDinheiro->dataRecargaFinalizada = $aux->dataRecargaFinalizada;
        } else {
            $invoiceRemessaDinheiro->dataRecargaFinalizada = null;
        }
        
        if (empty($invoiceRemessaDinheiro->address)) {
            throw new \Exception($this->idioma->getText("endPagamentoInvalido"));
        }
        
        if (!isset($invoiceRemessaDinheiro->dataExpiracaoInvoice->data) || $invoiceRemessaDinheiro->dataExpiracaoInvoice->data == null) {
            throw new \Exception($this->idioma->getText("dataExpiInvalida"));
        }
        
        if (!$invoiceRemessaDinheiro->idRemessaDinheiro > 0) {
            throw new \Exception($this->idioma->getText("identificacaoBoleoInvalida"));
        }
        
        if (!$invoiceRemessaDinheiro->idInvoice > 0) {
            throw new \Exception($this->idioma->getText("identificacaoInvoiceInvalida"));
        }
        
        if (!in_array($invoiceRemessaDinheiro->status, Array(
            \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO,
            \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO,
            \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO,
            \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO,
        ))) {
            throw new \Exception($this->idioma->getText("statusInvalido"));
        }
        
        if (!is_numeric($invoiceRemessaDinheiro->valorReal) || !$invoiceRemessaDinheiro->valorReal>0) {
            throw new \Exception($this->idioma->getText("valorRemessaInvalido"));
        }
        
        if (!is_numeric($invoiceRemessaDinheiro->valorBtc) || !$invoiceRemessaDinheiro->valorBtc>0) {
            throw new \Exception($this->idioma->getText("valorMoedaDigitalinvalida"));
        }
        
        if ($invoiceRemessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO) {
            if (!isset($invoiceRemessaDinheiro->dataPagamento->data) || $invoiceRemessaDinheiro->dataPagamento->data == null) {
                throw new \Exception($this->idioma->getText("dataPagamentoInvalida"));
            }
        }
        
        $this->conexao->salvar($invoiceRemessaDinheiro);
    }
    
    public function getCurrentInvoice(RemessaDinheiro $remessaDinheiro) {
        $result = $this->conexao->listar("status IN ('".\Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO."', '".\Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO."') AND id_remessa_dinheiro = {$remessaDinheiro->id}", "id DESC", null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
}

?>