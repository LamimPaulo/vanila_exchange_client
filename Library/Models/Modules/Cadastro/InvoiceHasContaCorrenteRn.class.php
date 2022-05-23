<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoiceHasContaCorrenteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new InvoiceHasContaCorrente());
        } else {
            $this->conexao = new GenericModel($adapter, new InvoiceHasContaCorrente());
        }
    }
    
    public function salvar(InvoiceHasContaCorrente &$invoiceHasContaCorrente) {
        $invoiceHasContaCorrente->id = 0;
        $invoiceHasContaCorrente->data = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (!$invoiceHasContaCorrente->idCliente > 0) {
            throw new \Exception("O cliente precisa ser informado");
        }
        
        if (!$invoiceHasContaCorrente->idInvoicePdv > 0) {
            throw new \Exception("A invocie  precisa ser informado");
        }
        
        $tipos = Array(
            \Utils\Constantes::ENTRADA,
            \Utils\Constantes::SAIDA
        );
        
        if (!in_array($invoiceHasContaCorrente->tipo, $tipos)) {
            throw new \Exception("Tipo de movimento inválido");
        }
        
        unset($invoiceHasContaCorrente->cliente);
        unset($invoiceHasContaCorrente->invoicePdv);
        unset($invoiceHasContaCorrente->contaCorrenteBtc);
        unset($invoiceHasContaCorrente->contaCorrenteReais);
        
        $this->conexao->salvar($invoiceHasContaCorrente);
    }
    
    
    public function carregar(InvoiceHasContaCorrente &$invoiceHasContaCorrente, $carregar = true, $carregarContaCorrenteBtc = true, $carregarContaCorrenteReais = true, $carregarCliente = true, $carregarInvoice = true) {
        
        if ($carregar) {
            $this->conexao->carregar($invoiceHasContaCorrente);
        }
        
        if ($carregarContaCorrenteBtc && $invoiceHasContaCorrente->idContaCorrenteBtc > 0) {
            $invoiceHasContaCorrente->contaCorrenteBtc = new ContaCorrenteBtc(Array("id" => $invoiceHasContaCorrente->idContaCorrenteBtc));
            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
            $contaCorrenteBtcRn->carregar($invoiceHasContaCorrente->contaCorrenteBtc, true, false, true);
        }
        
        if ($carregarContaCorrenteReais && $invoiceHasContaCorrente->idContaCorrenteReais > 0) {
            $invoiceHasContaCorrente->contaCorrenteReais = new ContaCorrenteReais(Array("id" => $invoiceHasContaCorrente->idContaCorrenteReais));
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
            $contaCorrenteReaisRn->carregar($invoiceHasContaCorrente->contaCorrenteReais, true, false, true);
        }
        
        if ($carregarCliente && $invoiceHasContaCorrente->idCliente > 0) {
            $invoiceHasContaCorrente->cliente = new Cliente(Array("id" => $invoiceHasContaCorrente->idCliente));
            $clienteRn = new ClienteRn($this->conexao->adapter);
            $clienteRn->conexao->carregar($invoiceHasContaCorrente->cliente);
        }
        
        if ($carregarInvoice && $invoiceHasContaCorrente->idInvoicePdv > 0) {
            $invoiceHasContaCorrente->invoicePdv = new InvoicePdv(Array("id" => $invoiceHasContaCorrente->idInvoicePdv));
            $invoicePdvRn = new InvoicePdvRn($this->conexao->adapter);
            $invoicePdvRn->carregar($invoiceHasContaCorrente->invoicePdv, false, true, true);
        }
        
    }
    
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarContaCorrenteBtc = true, $carregarContaCorrenteReais = true, $carregarCliente = true, $carregarInvoice = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        
        foreach ($result as $invoiceHasContaCorrente) {
            $this->carregar($invoiceHasContaCorrente, false, $carregarContaCorrenteBtc, $carregarContaCorrenteReais, $carregarCliente, $carregarInvoice);
            $lista[] = $invoiceHasContaCorrente;
        }
        
        return $lista;
    }
    
}

?>