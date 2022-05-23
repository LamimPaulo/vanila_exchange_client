<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class ClienteHasTaxaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasTaxa()); 
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasTaxa()); 
        }
    }
    
    public function salvar(ClienteHasTaxa  &$clienteHasTaxa) {
        
        if (!$clienteHasTaxa->idMoeda > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        if (!$clienteHasTaxa->idCliente > 0) {
            throw new \Exception($this->idioma->getText("clienteInvalido"));
        }
        
        if (!$clienteHasTaxa->taxaCompraDireta > 0) {
            $clienteHasTaxa->taxaCompraDireta = 0;
        }
        
        if (!$clienteHasTaxa->taxaCompraIndireta > 0) {
            $clienteHasTaxa->taxaCompraIndireta = 0;
        }
        
        if (!$clienteHasTaxa->taxaVendaDireta > 0) {
            $clienteHasTaxa->taxaVendaDireta = 0;
        }
        
        if (!$clienteHasTaxa->taxaVendaIndireta > 0) {
            $clienteHasTaxa->taxaVendaIndireta = 0;
        }
        
        $this->conexao->salvar($clienteHasTaxa);
    }
    
    /**
     * 
     * @param \Models\Modules\Cadastro\Cliente $cliente
     * @param type $idMoeda
     * @return ClienteHasTaxa
     * @throws \Exception
     */
    public function getByMoeda(Cliente $cliente, $idMoeda) {
        
        if (!($idMoeda > 0)) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        if (!($cliente->id > 0)) {
            throw new \Exception($this->idioma->getText("clienteInvalido"));
        }
        $result = $this->conexao->select(Array("id_moeda" => $idMoeda, "id_cliente" => $cliente->id));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
    
    public function salvarTaxas(Cliente $cliente, $array) {
        try {
            $this->conexao->adapter->iniciar();
            
            foreach ($array as $taxa) {
                $clienteHasTaxa = $this->getByMoeda($cliente, $taxa["idMoeda"]);
                
                if ($clienteHasTaxa == null) {
                    $clienteHasTaxa->id = 0;
                    $clienteHasTaxa = new ClienteHasTaxa(Array("id_moeda" => $taxa["idMoeda"]));
                }
                $clienteHasTaxa->idCliente = $cliente->id;
                $clienteHasTaxa->taxaCompraDireta = str_replace(",", ".", $taxa["taxaCompraDireta"]);
                $clienteHasTaxa->taxaCompraIndireta = str_replace(",", ".", $taxa["taxaCompraIndireta"]);
                $clienteHasTaxa->taxaVendaDireta = str_replace(",", ".", $taxa["taxaVendaDireta"]);
                $clienteHasTaxa->taxaVendaIndireta = str_replace(",", ".", $taxa["taxaVendaIndireta"]);
                $clienteHasTaxa->utilizar = ($taxa["utilizar"] > 0 ? 1 : 0);
                
                $this->salvar($clienteHasTaxa);
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
        return null;
    }
    
    
    public function getTaxaCliente(Cliente $cliente, $idMoeda, $direta = false) {
        $compra = 0;
        $venda = 0;
        
        $compraPassiva = 0;
        $compraAtiva = 0;
        $vendaPassiva = 0;
        $vendaAtiva = 0;
        
        $clienteHasTaxa = $this->getByMoeda($cliente, $idMoeda);
        if ($clienteHasTaxa != null && $clienteHasTaxa->utilizar > 0) {
            if ($direta) {
                $compra = $clienteHasTaxa->taxaCompraDireta;
                $venda = $clienteHasTaxa->taxaVendaDireta;
            } else {
                $compra = $clienteHasTaxa->taxaCompraIndireta;
                $venda = $clienteHasTaxa->taxaVendaIndireta;
            }
            
            $compraPassiva = $clienteHasTaxa->taxaCompraIndireta;
            $compraAtiva = $clienteHasTaxa->taxaCompraDireta;
            $vendaPassiva = $clienteHasTaxa->taxaVendaIndireta;
            $vendaAtiva = $clienteHasTaxa->taxaVendaDireta;
        } else {
            if ($idMoeda == 2) {
                $configuracao = ConfiguracaoRn::get();
                if ($direta) {
                    $compra = $configuracao->percentualCompra;
                    $venda = $configuracao->percentualVenda;
                } else {
                    $compra = $configuracao->percentualCompraPassiva;
                    $venda = $configuracao->percentualVendaPassiva;
                }
                
                $compraPassiva = $configuracao->percentualCompraPassiva;
                $compraAtiva = $configuracao->percentualCompra;
                $vendaPassiva = $configuracao->percentualVendaPassiva;
                $vendaAtiva = $configuracao->percentualVenda;
            } else {
                
                $taxaMoedaRn = new TaxaMoedaRn();
                $taxaMoeda = $taxaMoedaRn->getByMoeda($idMoeda);
                
                if ($taxaMoeda != null) { 
                    if ($direta) {
                        $compra = $taxaMoeda->taxaCompraDireta;
                        $venda = $taxaMoeda->taxaVendaDireta;
                    } else {
                        $compra = $taxaMoeda->taxaCompraIndireta;
                        $venda = $taxaMoeda->taxaVendaIndireta;
                    }
                }
                
                
                $compraPassiva = $taxaMoeda->taxaCompraIndireta;
                $compraAtiva = $taxaMoeda->taxaCompraDireta;
                $vendaPassiva = $taxaMoeda->taxaVendaIndireta;
                $vendaAtiva = $taxaMoeda->taxaVendaDireta;
                
            }
            
        }
        
        $desconto = self::getDescontoTaxasCliente($cliente);
        if ($desconto > 0) {
            $compra = number_format(($compra - ($compra * $desconto)), 2, ".", "");
            $venda = number_format(($venda - ($venda * $desconto)), 2, ".", "");
            
            
            $compraPassiva = number_format(($compraPassiva - ($compraPassiva * $desconto)), 2, ".", "");
            $compraAtiva = number_format(($compraAtiva - ($compraAtiva * $desconto)), 2, ".", "");
            $vendaPassiva = number_format(($vendaPassiva - ($vendaPassiva * $desconto)), 2, ".", "");
            $vendaAtiva = number_format(($vendaAtiva - ($vendaAtiva * $desconto)), 2, ".", "");
        }
        
        return Array(
            "compra" => number_format($compra, 2, ".", ""), 
            "venda" => number_format($venda, 2, ".", ""), 
            "compraAtiva" => number_format($compraAtiva, 2, ".", ""), 
            "compraPassiva" => number_format($compraPassiva, 2, ".", ""), 
            "vendaAtiva" => number_format($vendaAtiva, 2, ".", ""), 
            "vendaPassiva" => number_format($vendaPassiva, 2, ".", "")
        );
    }
    
    
    public static function getDescontoTaxasCliente(Cliente $cliente) {
        
        $contaCorrenteBtcRn = new ContaCorrenteBtcRn();
        $saldoNEWC = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 33, false, true);
        
        
        if ($saldoNEWC >= 25000) {
            return 1;
        } else if ($saldoNEWC >= 16500) {
            return 0.5;
        } else if ($saldoNEWC >= 12500) {
            return 0.4;
        } else if ($saldoNEWC >= 7500) {
            return 0.3;
        } else if ($saldoNEWC >= 4000) {
            return 0.2;
        } else if ($saldoNEWC >= 1500) {
            return 0.1;
        }
        
        return 0;
    }
}

?>