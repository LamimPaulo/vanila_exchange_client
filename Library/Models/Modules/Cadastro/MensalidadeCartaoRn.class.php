<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade MensalidadeCartao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class MensalidadeCartaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new MensalidadeCartao());
        } else {
            $this->conexao = new GenericModel($adapter, new MensalidadeCartao());
        }
    }
    
    public function salvar(MensalidadeCartao &$mensalidadeCartao) {
        
        if ($mensalidadeCartao->id > 0) {
            $aux = new MensalidadeCartao(Array("id" => $mensalidadeCartao->id));
            $this->conexao->carregar($aux);
            
            $mensalidadeCartao->address = $aux->address;
            $mensalidadeCartao->dataExpiracaoInvoice = $aux->dataExpiracaoInvoice;
            $mensalidadeCartao->dataPagamento = $aux->dataPagamento;
            $mensalidadeCartao->idInvoice = $aux->idInvoice;
            $mensalidadeCartao->valorBtc = $aux->valorBtc;
            $mensalidadeCartao->status = $aux->status;
            
        } else {
            
            $mensalidadeCartao->address = null;
            $mensalidadeCartao->dataExpiracaoInvoice = null;
            $mensalidadeCartao->dataPagamento = null;
            $mensalidadeCartao->idInvoice = null;
            $mensalidadeCartao->valorBtc = null;
            
            $mensalidadeCartao->status = \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_AGUARDANDO;
        }
        
        if (!isset($mensalidadeCartao->dataValidade->data) || $mensalidadeCartao->dataValidade->data==null) {
            throw new \Exception("É necessário informar a data de vencimento da parcela");
        }
        
        if (!$mensalidadeCartao->idPedidoCartao > 0) {
            throw new \Exception("Identificação do cartão não informada");
        }
        
        if (empty($mensalidadeCartao->mesRef)) {
            throw new \Exception("O mês de referencia deve ser informado");
        }
        
        if (!is_numeric($mensalidadeCartao->valor) || !($mensalidadeCartao->valor > 0)) {
            throw new \Exception("O mês de referencia deve ser informado");
        }
        
        unset($mensalidadeCartao->pedidoCartao);
        
        $this->conexao->salvar($mensalidadeCartao);
    }
    
    public function gerarMensalidades(PedidoCartao $pedidoCartao, \Utils\Data $dataInicial, $qtdMeses) {
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $pedidoCartaoRn = new PedidoCartaoRn($this->conexao->adapter);
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("O cartão não foi localizado no sistema");
            }
            
            if (!$pedidoCartao->ativo > 0) {
                throw new \Exception("O cartão não está ativo no sistema");
            }
            
            if ($pedidoCartao->cancelado > 0) {
                throw new \Exception("O cartão está cancelado no sistema");
            }
            
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception("É necessário informar a data de início da cobrança de mensalidade");
            }
            
            if (!($qtdMeses > 0)) {
                throw new \Exception("A quantidade de manutenções cobradas deve ser informada");
            }
            
            $configuracao = new Configuracao(Array("id" => 1));
            try {
                $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
                $configuracaoRn->conexao->carregar($configuracao);
            } catch (\Exception $ex) {
                throw new \Exception("Erro interno do sistema. Por favor, tente novamente.");
            }
            
            for ($i = 0; $i < $qtdMeses; $i++) {
                $data = new \Utils\Data($dataInicial->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
                $data->somar(0, $i);
                
                $mensalidadeCartao = new MensalidadeCartao();
                $mensalidadeCartao->id = 0;
                $mensalidadeCartao->dataValidade = $data;
                $mensalidadeCartao->idPedidoCartao = $pedidoCartao->id;
                $mensalidadeCartao->mesRef = "{$data->getNomeMes(false)}/{$data->formatar("Y")}";
                $mensalidadeCartao->valor = $configuracao->valorMensalidadeCartao;
                
                
                $this->salvar($mensalidadeCartao);
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function carregar(MensalidadeCartao &$mensalidadeCartao, $carregar = true, $carregarPedidoCartao = true) {
        if ($carregar) {
            $this->conexao->carregar($mensalidadeCartao);
        }
        
        if ($carregarPedidoCartao && $mensalidadeCartao->idPedidoCartao > 0) {
            $mensalidadeCartao->pedidoCartao = new PedidoCartao(Array("id" => $mensalidadeCartao->idPedidoCartao));
            $pedidoCartaoRn = new PedidoCartaoRn();
            $pedidoCartaoRn->carregar($mensalidadeCartao->pedidoCartao, true, true);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarPedidoCartao = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $mensalidadeCartao) {
            $this->carregar($mensalidadeCartao, false, $carregarPedidoCartao);
            $lista[] = $mensalidadeCartao;
        }
        return $lista;
    }
    
    public function atualizarDadosInvoice(MensalidadeCartao &$mensalidadeCartao) {
        
        if (!isset($mensalidadeCartao->dataExpiracaoInvoice->data) || $mensalidadeCartao->dataExpiracaoInvoice->data == null) {
            throw new \Exception("Data de expiração da invoice inválida");
        }
        
        if (empty($mensalidadeCartao->address)) {
            throw new \Exception("É necessário informar o endereço de pagamento da invoice");
        }
        
        if (!($mensalidadeCartao->idInvoice > 0)) {
            throw new \Exception("É necessário informar a identificação da invoice");
        }
        
        if (!is_numeric($mensalidadeCartao->valorBtc) || $mensalidadeCartao->valorBtc < 0) {
            throw new \Exception("Valor da Invoice inválido");
        }
        
        $mensalidadeCartao->status = \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_AGUARDANDO;
        
        $this->conexao->update(
                Array(
                    "address" => $mensalidadeCartao->address,
                    "data_expiracao_invoice" => $mensalidadeCartao->dataExpiracaoInvoice->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                    "id_invoice" => $mensalidadeCartao->idInvoice,
                    "valor_btc" => $mensalidadeCartao->valorBtc,
                    "status" => $mensalidadeCartao->status
                ), 
                Array(
                    "id" => $mensalidadeCartao->id
                ));
        
    }
    
    /**
     * Função que zera os dados de invoice de todas as mensalidades com o mesmo id de invoice das mensalidades cujas identificações foram passadas no array ids
     * o objetivo é impedir que ao gerar uma nova invoice que não contenha as mesmas mensalidades da invoice anterior restem mensalidades na invoice antiga
     * @param array $ids array de inteiros contendo identificações de mensalidades
     */
    public function zerarDadosInvoices(array $ids) {
        if (sizeof($ids) > 0) {
            
            $query = "SELECT id_invoice FROM mensalidades_cartoes WHERE id IN (".implode(",", $ids).") GROUP BY id_invoice";
            $dados = $this->conexao->adapter->query($query)->execute();
            
            $idsInvoices = Array();
            foreach ($dados as $d) {
                $idsInvoices[] = $d["id_invoice"];
            }
            if ($idsInvoices > 0) {
                
                
                $where = new \Zend\Db\Sql\Where();
                $where->in("id_invoice", $idsInvoices);
                $where->notEqualTo("status", \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO);
                
                $this->conexao->update(Array(
                    "address" => null,
                    "data_expiracao_invoice" => null,
                    "id_invoice" => null,
                    "valor_btc" => null
                ), 
                $where
                );
            }
        }
    }
    
    
    public function getIdsInvoicesPendentes() {
        $query = "SELECT id_invoice FROM mensalidades_cartoes WHERE status != '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' GROUP BY id_invoice";
        $dados = $this->conexao->adapter->query($query)->execute();

        $idsInvoices = Array();
        foreach ($dados as $d) {
            $idsInvoices[] = $d["id_invoice"];
        }
        
        return $idsInvoices;
    }
    
    
    public function filtrar($idCliente, $idPedidoCartao, $status) {
        
        $where = Array();
        
        if ($idCliente > 0) {
            $where[] = " p.id_cliente = {$idCliente} ";
        }
        
        if ($idPedidoCartao > 0) {
            $where[] = " p.id = {$idPedidoCartao} ";
        }
        
        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
        if ($status != "T") {
            if ($status == \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA) { 
                $where[] = " m.data_validade < '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where[] = " m.status = '{$status}' ";
            }
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " m.* "
                . " FROM mensalidades_cartoes m "
                . " INNER JOIN pedidos_cartoes p ON (m.id_pedido_cartao = p.id) "
                . " {$where} "
                . " ORDER BY m.data_validade ";
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        
        foreach ($result as $dados) {
            $mensalidadeCartao = new MensalidadeCartao($dados);
            $this->carregar($mensalidadeCartao, false, true);
            
            if ($mensalidadeCartao->status != \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO) {
                if ($mensalidadeCartao->dataValidade->menor($dataAtual)) {
                    $mensalidadeCartao->status = \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA;
                }
            }
            $lista[] = $mensalidadeCartao;
        }
        return $lista;
    }
    
    public function hasMensalidadesAtrasadas(PedidoCartao $pedidoCartao) {
        $dataAtual = date("Y-m-d H:i:s");
        $query = " SELECT COUNT(*) AS qtd FROM mensalidades_cartoes WHERE data_validade < '{$dataAtual}' AND id_pedido_cartao = {$pedidoCartao->id};";
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            return (isset($dados["qtd"]) && $dados["qtd"] > 0) ? true : false;
        }
        return false;
    }
    
    public function getInvoiceStatus($invoiceId) {
        if (!$invoiceId > 0) {
            throw new \Exception("A identificação da invoice deve ser informada");
        }
        $query = "SELECT id_invoice, status, data_expiracao_invoice, valor_btc, address "
                . "FROM mensalidades_cartoes "
                . "WHERE id_invoice = {$invoiceId} "
                . "GROUP BY id_invoice, status, data_expiracao_invoice, valor_btc, address";
        
                
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            return Array(
                "idInvoice" => $dados["id_invoice"],
                "status" => $dados["status"],
                "dataExpiracaoInvoice" => $dados["data_expiracao_invoice"],
                "valorBtc" => $dados["valor_btc"],
                "address" => $dados["address"],
            );
        }
        return Array(
            "idInvoice" => null,
            "status" => null,
            "dataExpiracaoInvoice" => null,
            "valorBtc" => null,
            "address" => null,
        );
    }
    
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND m.data_pagamento BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND m.data_pagamento >= '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $queryLv1 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(m.id) AS mensalidades "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN mensalidades_cartoes m ON (p.id = m.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia = {$idReferencia} AND m.status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' {$where} "
                    . "GROUP BY c.id, c.nome;";
                    
        $mmn = Array();
        $resultLv1 = $this->conexao->adapter->query($queryLv1)->execute();
        
        $qtdGeral = 0;
        $totalGeral = 0;
        
        $idsLv1 = Array();
        $clientesLv1 = Array();
        $qtdLv1 = 0;
        $totalLv1 = 0;
        foreach ($resultLv1 as $dadosLv1) {
            $clientesLv1[] = Array("id" => $dadosLv1["id"], "nome" => $dadosLv1["nome"], "cartoes" => $dadosLv1["pedidos"]);
            $idsLv1[] = $dadosLv1["id"];
            $totalLv1 +=  ($dadosLv1["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao1);
            $qtdLv1 += $dadosLv1["mensalidades"];
            $qtdGeral += $dadosLv1["mensalidades"];
            $totalGeral += ($dadosLv1["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao1);
        }
        
        $mmn["lv1"] = Array(
            "clientes" => $clientesLv1,
            "total" => $totalLv1,
            "qtd" => $qtdLv1
        );
        
        $idsLv2 = Array();
        $clientesLv2 = Array();
        $qtdLv2 = 0;
        $totalLv2 = 0;
        if (sizeof($idsLv1) > 0) {
            $queryLv2 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(m.id) AS mensalidades "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN mensalidades_cartoes m ON (p.id = m.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv1).") AND m.status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv2 = $this->conexao->adapter->query($queryLv2)->execute();
            foreach ($resultLv2 as $dadosLv2) {
                $clientesLv2[] = Array("id" => $dadosLv2["id"], "nome" => $dadosLv2["nome"], "cartoes" => $dadosLv2["pedidos"]);
                $idsLv2[] = $dadosLv2["id"];
                $totalLv2 +=  ($dadosLv2["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao2);
                $qtdLv2 += $dadosLv2["mensalidades"];
                $qtdGeral += $dadosLv2["mensalidades"];
                $totalGeral += ($dadosLv2["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao2);
            }
        }
        
        
        $mmn["lv2"] = Array(
            "clientes" => $clientesLv2,
            "total" => $totalLv2,
            "qtd" => $qtdLv2
        );
        
        $idsLv3 = Array();
        $clientesLv3 = Array();
        $qtdLv3 = 0;
        $totalLv3 = 0;
        if (sizeof($idsLv2) > 0) {
            $queryLv3 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(m.id) AS mensalidades "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN mensalidades_cartoes m ON (p.id = m.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv2).") AND m.status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv3 = $this->conexao->adapter->query($queryLv3)->execute();
            foreach ($resultLv3 as $dadosLv3) {
                $clientesLv3[] = Array("id" => $dadosLv3["id"], "nome" => $dadosLv3["nome"], "cartoes" => $dadosLv3["pedidos"]);
                $idsLv3[] = $dadosLv3["id"];
                $totalLv3 +=  ($dadosLv3["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao3);
                $qtdLv3 += $dadosLv3["mensalidades"];
                $qtdGeral += $dadosLv3["mensalidades"];
                $totalGeral += ($dadosLv3["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao3);
            }
        }
        
        $mmn["lv3"] = Array(
            "clientes" => $clientesLv3,
            "total" => $totalLv3,
            "qtd" => $qtdLv3
        );
        
        
        $idsLv4 = Array();
        $clientesLv4 = Array();
        $qtdLv4 = 0;
        $totalLv4 = 0;
        if (sizeof($idsLv3) > 0) {
            $queryLv4 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(m.id) AS mensalidades "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN mensalidades_cartoes m ON (p.id = m.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv3).") AND m.status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv4 = $this->conexao->adapter->query($queryLv4)->execute();
            foreach ($resultLv4 as $dadosLv4) {
                $clientesLv4[] = Array("id" => $dadosLv4["id"], "nome" => $dadosLv4["nome"], "cartoes" => $dadosLv4["pedidos"]);
                $idsLv4[] = $dadosLv4["id"];
                $totalLv4 +=  ($dadosLv4["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao4);
                $qtdLv4 += $dadosLv4["mensalidades"];
                $qtdGeral += $dadosLv4["mensalidades"];
                $totalGeral += ($dadosLv4["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao4);
            }
        }
        
        $mmn["lv4"] = Array(
            "clientes" => $clientesLv4,
            "total" => $totalLv4,
            "qtd" => $qtdLv4
        );
        
        
        
        $clientesLv5 = Array();
        $totalLv5 = 0;
        $qtdLv5 = 0;
        if (sizeof($idsLv4) > 0) {
            $queryLv5 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(m.id) AS mensalidades "
                    . "FROM pedidos_cartoes p "
                    . "INNER JOIN mensalidades_cartoes m ON (p.id = m.id_pedido_cartao) "
                    . "INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv4).") AND m.status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv5 = $this->conexao->adapter->query($queryLv5)->execute();
            foreach ($resultLv5 as $dadosLv5) {
                $clientesLv5[] = Array("id" => $dadosLv5["id"], "nome" => $dadosLv5["nome"], "cartoes" => $dadosLv5["pedidos"]);
                $totalLv5 +=  ($dadosLv5["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao5);
                $qtdLv5 += $dadosLv5["mensalidades"];
                $qtdGeral += $dadosLv5["mensalidades"];
                $totalGeral += ($dadosLv5["mensalidades"] * $configuracao->valorComissaoMensalidadeCartao5);
            }
        }
        
        $mmn["lv5"] = Array(
            "clientes" => $clientesLv5,
            "total" => $totalLv5,
            "qtd" => $qtdLv5
        );
        
        $mmn["total"] = $totalGeral;
        $mmn["qtd"] = $qtdGeral;
        
        return $mmn;
    }
    
    
    public function debitarDoSaldo(Cliente $cliente, $idsMensalidades, $valorTotal) {
        
        try {
            if (!sizeof($idsMensalidades)) {
                throw new \Exception("Você precisa selecionar ao menos uma mensalidade");
            }
            
            
            $contaCorrenteRn = new ContaCorrenteReaisRn();
            $contaCorrente = $contaCorrenteRn->debitarDoSaldo($cliente, $valorTotal, "Recebimento de mensalidade", false);
            
            if ($contaCorrente != null) {
                $update = "UPDATE mensalidades_cartoes m SET "
                        . " status = '".\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO."', "
                        . " address = null, "
                        . " data_expiracao_invoice = null, "
                        . " id_invoice = null, "
                        . " valor_btc = null, "
                        . " data_pagamento = '".date("Y-m-d H:i:s")."' "
                        . " WHERE id IN (".implode(",", $idsMensalidades).") ";
                
                $this->conexao->adapter->query($update)->execute();
                
                
                return true;
            }
            
        } catch (\Exception $ex) {
            if (isset($contaCorrente) && $contaCorrente->id > 0) {
                $contaCorrenteRn->excluir($contaCorrente);
            }
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
        return false;
    }
    
}

?>