<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade ResgateComissao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ResgateComissaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;

    
    public function __construct(\Io\BancoDados $adapter = null) {
         $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ResgateComissao());
        } else {
            $this->conexao = new GenericModel($adapter, new ResgateComissao());
        }
    }
    
    
    public function salvar(ResgateComissao $resgateComissao) {
        
        $resgateComissao->dataResgate = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (!isset($resgateComissao->dataReferenciaFechamento->data) || $resgateComissao->dataReferenciaFechamento->data == null) {
            throw new \Exception($this->idioma->getText("necessarioInformDataReferencia"));
        }
        
        if (!is_numeric($resgateComissao->valor) || !$resgateComissao->valor > 0) {
            throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
        }
        
        if (!$resgateComissao->idCliente > 0) {
            throw new \Exception($this->idioma->getText("clientePrecisaInformado"));
        }
        
        $tipos = Array(
            \Utils\Constantes::TIPO_RESGATE,
            \Utils\Constantes::TIPO_TRANSFERENCIA
        );
        
        if (!in_array($resgateComissao->tipo, $tipos)) {
                throw new \Exception($this->idioma->getText("tipoResgateInvalido"));
        }
        
        if ($resgateComissao->tipo == \Utils\Constantes::TIPO_RESGATE) {
            if (!$resgateComissao->idPedidoCartao > 0) {
                //throw new \Exception("O Cartão precisa ser informado");
            }
        } else {
            if (!$resgateComissao->idClienteDestino > 0) {
                throw new \Exception($this->idioma->getText("clientePrecisaSerInformado"));
            }
        }
        
        unset($resgateComissao->cliente);
        unset($resgateComissao->clienteDestino);
        unset($resgateComissao->pedidoCartao);
        
        $this->conexao->salvar($resgateComissao);
    }
    
    public function carregar(ResgateComissao &$resgateComissao, $carregar = true, $carregarCliente = true, $carregarPedidoCartao = true,
            $carregarClienteDestino = true) {
        if ($carregar) {
            $this->conexao->carregar($resgateComissao);
        }
        
        if ($resgateComissao->idCliente > 0 && $carregarCliente) {
            $resgateComissao->cliente = new Cliente(Array("id" => $resgateComissao->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($resgateComissao->cliente);
        }
        
        if ($resgateComissao->idClienteDestino > 0 && $carregarClienteDestino) {
            $resgateComissao->clienteDestino = new Cliente(Array("id" => $resgateComissao->idClienteDestino));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($resgateComissao->clienteDestino);
        }
        
        if ($resgateComissao->idPedidoCartao > 0 && $carregarPedidoCartao) {
            $resgateComissao->pedidoCartao = new PedidoCartao(Array("id" => $resgateComissao->idPedidoCartao));
            $pedidoCartaoRn = new PedidoCartaoRn();
            $pedidoCartaoRn->conexao->carregar($resgateComissao->pedidoCartao);
        }
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarPedidoCartao = true, $carregarClienteDestino = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $resgateComissao) {
            $this->carregar($resgateComissao, false, $carregarCliente, $carregarPedidoCartao, $carregarClienteDestino);
            $lista[] = $resgateComissao;
        }
        return $lista;
    }
    
    
    public function getUltimaDataReferencia(Cliente $cliente) {
        
        $query = "SELECT max(data_referencia_fechamento) AS data FROM resgates_comissoes WHERE id_cliente = {$cliente->id};";
        $result = $this->conexao->adapter->query($query)->execute();
        $data = null;
        if (sizeof($result) > 0) {
            $dados = $result->current();
            if ($dados["data"] != null) {
                $data = new \Utils\Data(substr($dados["data"], 0, 19));
            }
        }
        
        return $data;
    }
    
    
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND r.data_resgate BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND r.data_resgate >= '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        
        $query = "SELECT "
                    . "c.nome, "
                    . "r.valor "
                    . "FROM resgates_comissoes r "
                    . "INNER JOIN clientes c ON (r.id_cliente = c.id) "
                    . "WHERE r.id_cliente_destino = {$idReferencia} AND "
                    . "r.tipo = '".\Utils\Constantes::TIPO_TRANSFERENCIA."' {$where} "
                    . "GROUP BY c.nome;";
                    
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            $lista[] = $dados;
        }
        
        return $lista;
    }
    
    
    public function resgatar(ResgateComissao $resgateComissao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $cliente = \Utils\Geral::getCliente();
            
            $dataRef = $this->getUltimaDataReferencia($cliente);
            $dataLimite = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn($this->conexao->adapter);
            $comissaoCartoes = $pedidoCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn($this->conexao->adapter);
            $comissaoMensalidades = $mensalidadeCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn($this->conexao->adapter);
            $comissaoRecarga = $recargaCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn($this->conexao->adapter);
            $comissaoBoleto = $boletoClienteRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn($this->conexao->adapter);
            $comissaoRemessa = $remessaDinheiroRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $transferencias = $this->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $comissao = $comissaoCartoes["total"] + $comissaoMensalidades["total"] + $comissaoRecarga["comissao"]+$comissaoBoleto["comissao"]+$comissaoRemessa["comissao"];
            
            foreach ($transferencias as $transferencia) {
                $comissao += $transferencia["valor"];
            }
            
            $resgateComissao->valor = $comissao;
            $resgateComissao->tipo = \Utils\Constantes::TIPO_RESGATE;
            $resgateComissao->idCliente = $cliente->id;
            $resgateComissao->dataReferenciaFechamento = $dataLimite;
            
            $this->salvar($resgateComissao);
            $recargaCartao = new RecargaCartao();
            $recargaCartao->idPedidoCartao = $resgateComissao->idPedidoCartao;
            $recargaCartao->valorReal = $resgateComissao->valor;
            $recargaCartao->address = "Resgate de Comissão";
            $recargaCartao->dataExpiracaoInvoice = new \Utils\Data(date("d/m/Y H:i:s"));
            $recargaCartao->idInvoice = 9999;
            $recargaCartao->valorBtc = 0.00000001;

            $recargaCartaoRn->salvar($recargaCartao);

            $recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO;
            $recargaCartao->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));

            $recargaCartaoRn->salvar($recargaCartao);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    public function transferir(ResgateComissao $resgateComissao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $cliente = \Utils\Geral::getCliente();
            
            $dataRef = $this->getUltimaDataReferencia($cliente);
            $dataLimite = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn($this->conexao->adapter);
            $comissaoCartoes = $pedidoCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn($this->conexao->adapter);
            $comissaoMensalidades = $mensalidadeCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn($this->conexao->adapter);
            $comissaoRecarga = $recargaCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn($this->conexao->adapter);
            $comissaoBoleto = $boletoClienteRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn($this->conexao->adapter);
            $comissaoRemessa = $remessaDinheiroRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $transferencias = $this->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $comissao = $comissaoCartoes["total"] + $comissaoMensalidades["total"] + $comissaoRecarga["comissao"]+$comissaoBoleto["comissao"]+$comissaoRemessa["comissao"];
            
            foreach ($transferencias as $transferencia) {
                $comissao += $transferencia["valor"];
            }
            
            $resgateComissao->valor = $comissao;
            $resgateComissao->tipo = \Utils\Constantes::TIPO_TRANSFERENCIA;
            $resgateComissao->idCliente = $cliente->id;
            $resgateComissao->dataReferenciaFechamento = $dataLimite;
            
            $this->salvar($resgateComissao);
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function resgatarSaldo(ResgateComissao $resgateComissao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $cliente = \Utils\Geral::getCliente();
            
            $dataRef = $this->getUltimaDataReferencia($cliente);
            $dataLimite = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn($this->conexao->adapter);
            $comissaoCartoes = $pedidoCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn($this->conexao->adapter);
            $comissaoMensalidades = $mensalidadeCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn($this->conexao->adapter);
            $comissaoRecarga = $recargaCartaoRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn($this->conexao->adapter);
            $comissaoBoleto = $boletoClienteRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn($this->conexao->adapter);
            $comissaoRemessa = $remessaDinheiroRn->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $transferencias = $this->getRelatorioIndicacoes($cliente->id, $dataRef, $dataLimite);
            
            $comissao = $comissaoCartoes["total"] + $comissaoMensalidades["total"] + $comissaoRecarga["comissao"]+$comissaoBoleto["comissao"]+$comissaoRemessa["comissao"];
            
            foreach ($transferencias as $transferencia) {
                $comissao += $transferencia["valor"];
            }
            
            $resgateComissao->valor = $comissao;
            $resgateComissao->tipo = \Utils\Constantes::TIPO_RESGATE;
            $resgateComissao->idCliente = $cliente->id;
            $resgateComissao->dataReferenciaFechamento = $dataLimite;
            
            $this->salvar($resgateComissao);
            
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Resgate de Comissão";
            $contaCorrenteReais->idCliente = $resgateComissao->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = $resgateComissao->valor;

            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
}

?>