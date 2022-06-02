<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade PedidoCartao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class PedidoCartaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PedidoCartao());
        } else {
            $this->conexao = new GenericModel($adapter, new PedidoCartao());
        }
    }
    
    public function salvar(PedidoCartao &$pedidoCartao) {
        
        if ($pedidoCartao->id > 0) {
            $aux = new PedidoCartao(Array("id" => $pedidoCartao->id));
            $this->conexao->carregar($aux);
            
            
            $pedidoCartao->numeroCartao = $aux->numeroCartao;
            $pedidoCartao->nomeCartao = $aux->nomeCartao;
            $pedidoCartao->senhaCartao = $aux->senhaCartao;
            $pedidoCartao->idCartao = $aux->idCartao;
            $pedidoCartao->validade = $aux->validade;
            $pedidoCartao->ativo = $aux->ativo;
            $pedidoCartao->cancelado = $aux->cancelado;
            $pedidoCartao->dataCancelamentoCartao = $aux->dataCancelamentoCartao;
        } else {
            if ($pedidoCartao->idInvoice != null || empty($pedidoCartao->status)) {
                $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO;
            }
            $pedidoCartao->dataPagamento = null;
            $pedidoCartao->dataPedido = new \Utils\Data(date("d/m/Y H:i:s"));
            $pedidoCartao->numeroCartao = null;
            $pedidoCartao->nomeCartao = null;
            $pedidoCartao->senhaCartao = null;
            $pedidoCartao->idCartao = null;
            $pedidoCartao->validade = null;
            $pedidoCartao->ativo = 0;
            $pedidoCartao->dataCancelamentoCartao = null;
            $pedidoCartao->cancelado = 0;
        }
        
        if (empty($pedidoCartao->address)) {
            // O Address não é mais obrigatório por que os cartões podem ser pagos com saldo em vez de invoice
            //throw new \Exception("É necessário informar o endereço de pagamento");
        }
        
        if (!isset($pedidoCartao->dataExpiracaoInvoice->data) || $pedidoCartao->dataExpiracaoInvoice->data == null) {
            // O dataExpiracaoInvoice não é mais obrigatório por que os cartões podem ser pagos com saldo em vez de invoice
            //throw new \Exception("Data da expiração da invoice inválida.");
        }
        
        if (!$pedidoCartao->idInvoice > 0) {
            // O idInvoice não é mais obrigatório por que os cartões podem ser pagos com saldo em vez de invoice
            //throw new \Exception("A identificação da invoice deve ser informada");
        }
        
        if (!$pedidoCartao->idCliente > 0) {
            throw new \Exception("A identificação do cliente deve ser informada");
        }
        
        if (!is_numeric($pedidoCartao->valorTotal) || !$pedidoCartao->valorTotal>0) {
            throw new \Exception("Valor total da invoice inválido");
        }
        
        unset($pedidoCartao->saldo);
        unset($pedidoCartao->ultimaAtualizacaoCartao);
        unset($pedidoCartao->cliente);
        $this->conexao->salvar($pedidoCartao);
    }
    
    public function carregar(PedidoCartao &$pedidoCartao, $carregar = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($pedidoCartao);
        }
        
        if ($carregarCliente && $pedidoCartao->idCliente > 0) {
            $pedidoCartao->cliente = new Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($pedidoCartao->cliente);
        }
    }
    
    
    public function filtrarPedidosCartoes(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $status, $pais, $ativo, $cancelado, $bandeira, $filtro) {
        $where = Array();
        
        if ($bandeira != "T") {
            $where[] = " AND p.bandeira = '{$bandeira}'";
        }
        
        if ($status != "T") {
            $where[] = " AND p.status = '{$status}'";
        }
        
        if ($pais != "000") {
            $where[] = " AND c.codigo_pais = '{$pais}' ";
        }
        
        if ($ativo != "T") {
            $where[] = " AND p.ativo = {$ativo} ";
        }
        
        if ($cancelado != "T") {
            $where[] = " AND p.cancelado = {$cancelado} ";
        }
        
        if (!empty($filtro)) {
            $where[] = " AND ( (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.email) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.cidade) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.celular) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.rg) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.numero_cartao) LIKE LOWER('%{$filtro}%')) ) ";
        }
        
        $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        
        $where = (sizeof($where) > 0 ? implode(" ", $where) : " ");
        
        $query = "SELECT "
                . " p.* "
                . " FROM pedidos_cartoes p "
                . " LEFT JOIN clientes c ON (p.id_cliente = c.id) "
                . " WHERE "
                . " data_pedido BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' "
                . " {$where} "
                . " ORDER BY p.data_pedido DESC;";
                
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            $pedidoCartao = new PedidoCartao($dados);
            $lista[] = $pedidoCartao;
        }
        return $lista;  
    }
    
    public function filtrarCartoesCadastrados($pais, $ativo, $cancelado, $bandeira, $filtro) {
        $where = Array();
        
        if ($pais != "000") {
            $where[] = " AND c.codigo_pais = '{$pais}' ";
        }
        
        if ($ativo != "T") {
            $where[] = " AND p.ativo = {$ativo} ";
        }
        
        if ($cancelado != "T") {
            $where[] = " AND p.cancelado = {$cancelado} ";
        }
        
        if ($bandeira != "T") {
            if ($bandeira == "N") { 
                $where[] = " AND p.bandeira IS NULL ";
            } else {
                $where[] = " AND p.bandeira = '{$bandeira}' ";
            }
        }
        
        
        if (!empty($filtro)) {
            $where[] = " AND ( (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.email) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.cidade) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.celular) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(c.rg) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.nome_cartao) LIKE LOWER('%{$filtro}%')) OR "
                        . " (LOWER(p.numero_cartao) LIKE LOWER('%{$filtro}%')) ) ";
        }
        
        $where = (sizeof($where) > 0 ? implode(" ", $where) : " ");
        
        $query = "SELECT "
                . " p.* "
                . " FROM pedidos_cartoes p "
                . " INNER JOIN clientes c ON (p.id_cliente = c.id) "
                . " WHERE "
                . " (nome_cartao IS NOT NULL OR "
                . " numero_cartao IS NOT NULL OR "
                . " senha_cartao IS NOT NULL OR "
                . " id_cartao IS NOT NULL) "
                . " {$where} "
                . " ORDER BY p.numero_cartao;";
                
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $pedidoCartao = new PedidoCartao($dados);
            $lista[] = $pedidoCartao;
        }
        return $lista;  
    }
    
    public function marcarComoPago(PedidoCartao &$pedidoCartao, \Utils\Data $dataPagamento) {
        
        if (!isset($dataPagamento->data) || $dataPagamento->data == null) {
            throw new \Exception("Data da pagamento da invoice inválida.");
        }
        $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO;
        $pedidoCartao->dataPagamento = $dataPagamento;
        $this->conexao->update(
                Array("status" => $pedidoCartao->status, "data_pagamento" => $pedidoCartao->dataPagamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)), 
                Array("id" => $pedidoCartao->id));
    }
    
    
    public function marcarComoCancelado(PedidoCartao &$pedidoCartao, \Utils\Data $dataPagamento) {
        
        $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_CANCELADO;
        $pedidoCartao->dataPagamento = null;
        $this->conexao->update(
                Array("status" => $pedidoCartao->status, "data_pagamento" => null), 
                Array("id" => $pedidoCartao->id));
    }
    
    
    public function getByIdInvoice($idInvoice) {
        $result = $this->conexao->select(Array("id_invoice" => $idInvoice));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    /**
     * Cadastra os dados do cartão
     * @param Integer $idPedidoCartao
     * @param String $nomeCartao
     * @param String $numeroCartao
     * @param String $idCartao
     * @param String $senhaCartao
     * @return \Models\Modules\Cadastro\PedidoCartao
     * @throws \Exception
     */
    public function alterarDadosCartao($idPedidoCartao, $nomeCartao = null, $numeroCartao = null, $idCartao = null, $senhaCartao = null, $validadeCartao = null) {
        try {
            $this->conexao->adapter->iniciar();
            $pedidoCartao = new PedidoCartao();
            $pedidoCartao->id = $idPedidoCartao;
            try {
                $this->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não localizado");
            }

            if (empty($nomeCartao)) {
                $nomeCartao = null;
            }
            if (empty($numeroCartao)) {
                $numeroCartao = null;
            }

            if (empty($idCartao)) {
                $idCartao = null;
            }
            if (empty($senhaCartao)) {
                $senhaCartao = null;
            }
            if (empty($validadeCartao)) {
                $validadeCartao = null;
            }

            $cartaoRn = new CartaoRn($this->conexao->adapter);
            $cartao = $cartaoRn->getByNumero($numeroCartao);

            if ($cartao == null) {
                throw new \Exception("Cartão não localizado no sistema. Por favor, verifique o número informado");
            }

            if ($cartao->idPedidoCartao > 0 && $cartao->idPedidoCartao != $pedidoCartao->id) {
                throw new \Exception("Cartão já ativado no sistema. Por favor, verifique o número informado e tente novamente");
            }

            if ($validadeCartao != $cartao->validade) {
                throw new \Exception("A Data de Validade do cartão não confere com a validade cadastrada");
            }
            
            $pedidoCartao->nomeCartao = $nomeCartao;
            $pedidoCartao->numeroCartao = $cartao->numero;
            $pedidoCartao->senhaCartao = $cartao->senha;
            $pedidoCartao->idCartao = $idCartao;
            $pedidoCartao->validade = $cartao->validade;
            $pedidoCartao->bandeira = $cartao->bandeira;


            $this->conexao->update(Array(
                "nome_cartao" => $pedidoCartao->nomeCartao,
                "numero_cartao" => $pedidoCartao->numeroCartao,
                "id_cartao" => $pedidoCartao->idCartao,
                "senha_cartao" => $pedidoCartao->senhaCartao,
                "validade" => $pedidoCartao->validade
            ),
                Array(
                    "id" => $pedidoCartao->id
                )      
            );

            $cartaoRn->conexao->update(Array("id_pedido_cartao" => $pedidoCartao->id), Array("id" => $cartao->id));
            
            $this->conexao->adapter->finalizar();
            return $pedidoCartao;
        } catch (\Exception $e) {
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    /**
     * Inverte o estado ativando ou desativando o cartão
     * @param Integer $idPedidoCartao
     * @return \Models\Modules\Cadastro\PedidoCartao
     * @throws \Exception
     */
    public function alterarStatusCartao($idPedidoCartao) {
        
        try {
        
            $pedidoCartao = new PedidoCartao();
            $pedidoCartao->id = $idPedidoCartao;
            try {
                $this->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não localizado");
            }

            if (empty($pedidoCartao->nomeCartao)) {
                throw new \Exception("É necessário informar o nome do cliente no cartão antes de ativá-lo");
            }

            if (empty($pedidoCartao->numeroCartao)) {
                throw new \Exception("É necessário informar o número do cartão antes de ativá-lo");
            }
            if (empty($pedidoCartao->idCartao)) {
                throw new \Exception("É necessário informar identificador do cartão antes de ativá-lo");
            }
            if (empty($pedidoCartao->senhaCartao)) {
                throw new \Exception("É necessário informar a senha do cartão antes de ativá-lo");
            }
            if (empty($pedidoCartao->validade)) {
                throw new \Exception("É necessário informar a validade do cartão antes de ativá-lo");
            }

            $pedidoCartao->ativo = ($pedidoCartao->ativo > 0 ? 0 : 1);
            $this->conexao->update(Array("ativo" => $pedidoCartao->ativo), Array("id" => $pedidoCartao->id));
            
            if ($pedidoCartao->ativo > 0) {
                // se ainda não foram geradas as mensalidades do cartão, então elas são geradas no ato da ativação
                $mensalidadeCartaoRn = new MensalidadeCartaoRn();
                $result = $mensalidadeCartaoRn->conexao->listar("id_pedido_cartao = {$pedidoCartao->id}");
                if (sizeof($result) != 24) {
                    $dataInicial = new \Utils\Data(date("d/m/Y") . " 23:59:59");
                    $dataInicial->somar(0, 1);
                    $mensalidadeCartaoRn->gerarMensalidades($pedidoCartao, $dataInicial, 24);
                }
            }
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        return $pedidoCartao;
    }
    
    public function cancelar(PedidoCartao &$pedidoCartao) {
        try {
            $this->conexao->carregar($pedidoCartao);
        } catch (\Exception $ex) {
            throw new \Exception("Cartão não encontrado no sistema");
        }
        
        $pedidoCartao->cancelado = 1;
        $pedidoCartao->dataCancelamentoCartao = new \Utils\Data(date("d/m/Y H:i:s"));
        $this->conexao->update(
                Array(
                    "cancelado" => $pedidoCartao->cancelado, 
                    "data_cancelamento_cartao" => $pedidoCartao->dataCancelamentoCartao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                ), 
                Array("id" => $pedidoCartao->id)
            );
    }
    
    
    public function getByNumero($numero) {
        $result = $this->conexao->select(Array("numero_cartao" => $numero));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND p.data_pagamento BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND p.data_pagamento > '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $queryLv1 = "SELECT "
                    . "c.id, "
                    . "c.nome, "
                    . "count(p.id) AS pedidos "
                    . "FROM pedidos_cartoes p INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia = {$idReferencia} AND p.status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO."' {$where} "
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
            $totalLv1 +=  ($dadosLv1["pedidos"] * $configuracao->valorComissaoIndicacaoCartao1);
            $qtdLv1 += $dadosLv1["pedidos"];
            $qtdGeral += $dadosLv1["pedidos"];
            $totalGeral += ($dadosLv1["pedidos"] * $configuracao->valorComissaoIndicacaoCartao1);
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
                    . "count(p.id) AS pedidos "
                    . "FROM pedidos_cartoes p INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv1).") AND p.status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv2 = $this->conexao->adapter->query($queryLv2)->execute();
            foreach ($resultLv2 as $dadosLv2) {
                $clientesLv2[] = Array("id" => $dadosLv2["id"], "nome" => $dadosLv2["nome"], "cartoes" => $dadosLv2["pedidos"]);
                $idsLv2[] = $dadosLv2["id"];
                $totalLv2 +=  ($dadosLv2["pedidos"] * $configuracao->valorComissaoIndicacaoCartao2);
                $qtdLv2 += $dadosLv2["pedidos"];
                $qtdGeral += $dadosLv2["pedidos"];
                $totalGeral += ($dadosLv2["pedidos"] * $configuracao->valorComissaoIndicacaoCartao2);
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
                    . "count(p.id) AS pedidos "
                    . "FROM pedidos_cartoes p INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv2).") AND p.status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv3 = $this->conexao->adapter->query($queryLv3)->execute();
            foreach ($resultLv3 as $dadosLv3) {
                $clientesLv3[] = Array("id" => $dadosLv3["id"], "nome" => $dadosLv3["nome"], "cartoes" => $dadosLv3["pedidos"]);
                $idsLv3[] = $dadosLv3["id"];
                $totalLv3 +=  ($dadosLv3["pedidos"] * $configuracao->valorComissaoIndicacaoCartao3);
                $qtdLv3 += $dadosLv3["pedidos"];
                $qtdGeral += $dadosLv3["pedidos"];
                $totalGeral += ($dadosLv3["pedidos"] * $configuracao->valorComissaoIndicacaoCartao3);
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
                    . "count(p.id) AS pedidos "
                    . "FROM pedidos_cartoes p INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv3).") AND p.status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv4 = $this->conexao->adapter->query($queryLv4)->execute();
            foreach ($resultLv4 as $dadosLv4) {
                $clientesLv4[] = Array("id" => $dadosLv4["id"], "nome" => $dadosLv4["nome"], "cartoes" => $dadosLv4["pedidos"]);
                $idsLv4[] = $dadosLv4["id"];
                $totalLv4 +=  ($dadosLv4["pedidos"] * $configuracao->valorComissaoIndicacaoCartao4);
                $qtdLv4 += $dadosLv4["pedidos"];
                $qtdGeral += $dadosLv4["pedidos"];
                $totalGeral += ($dadosLv4["pedidos"] * $configuracao->valorComissaoIndicacaoCartao4);
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
                    . "count(p.id) AS pedidos "
                    . "FROM pedidos_cartoes p INNER JOIN clientes c ON (p.id_cliente = c.id) "
                    . "WHERE c.id_referencia IN (". implode(",", $idsLv4).") AND p.status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO."' "
                    . "GROUP BY c.id, c.nome;";
            
            $resultLv5 = $this->conexao->adapter->query($queryLv5)->execute();
            foreach ($resultLv5 as $dadosLv5) {
                $clientesLv5[] = Array("id" => $dadosLv5["id"], "nome" => $dadosLv5["nome"], "cartoes" => $dadosLv5["pedidos"]);
                $totalLv5 +=  ($dadosLv5["pedidos"] * $configuracao->valorComissaoIndicacaoCartao4);
                $qtdLv5 += $dadosLv5["pedidos"];
                $qtdGeral += $dadosLv5["pedidos"];
                $totalGeral += ($dadosLv5["pedidos"] * $configuracao->valorComissaoIndicacaoCartao4);
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
    
    
    public function debitarDoSaldo(Cliente $cliente, $bandeira) {
        try {
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);

            $valor = ($cliente->codigoPais == "076" ? $configuracao->valorCartao : $configuracao->valorCartaoEx);

            $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
            
            
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false);
            
            if ($valor > $saldo) {
                throw new \Exception("Saldo insuficiente");
            }
            
            $contaCorrenteReais = $contaCorrenteReaisRn->debitarDoSaldo($cliente, $valor, "Taxa de solicitação de cartão", false);

            if ($contaCorrenteReais != null) {
                $pedidoCartao = new PedidoCartao();
                $pedidoCartao->id = 0;
                $pedidoCartao->address = null;
                $pedidoCartao->dataExpiracaoInvoice = null;
                $pedidoCartao->valorTotal = $valor;
                $pedidoCartao->idInvoice = null;
                $pedidoCartao->idCliente = $cliente->id;
                $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO;

                $pedidoCartao->bandeira = $bandeira;

                $this->salvar($pedidoCartao);

                return $pedidoCartao;
            }
        } catch (\Exception $e) {
            if (isset($contaCorrenteReais) && $contaCorrenteReais->id > 0) {
                $contaCorrenteReaisRn->excluir($contaCorrenteReais);
            }
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
        
        return null;
    }
    
    
    public function getQuantidadeCartoesPorStatus() {
        $query = " SELECT status, ativo, COUNT(*) AS qtd FROM  pedidos_cartoes  GROUP BY status, ativo; ";
        $dados = $this->conexao->adapter->query($query)->execute();
        $cartoesAtivos = 0;
        $cartoesAguardando = 0;
        $cartoesPagos = 0;
        $cartoesCancelados = 0;
        
        foreach ($dados as $d) {
            if ($d["ativo"] > 0) {
                $cartoesAtivos += $d["qtd"];
            } else {
                switch ($d["status"]) {
                    case "A" :
                        $cartoesAguardando += $d["qtd"];
                        break;
                    case "P" :
                        $cartoesPagos += $d["qtd"];
                        break;
                    case "C" :
                        $cartoesCancelados += $d["qtd"];
                        break;
                    default:
                        break;
                }
            }
        }
        return Array("ativos" => $cartoesAtivos, "pagos" => $cartoesPagos, "aguardando" => $cartoesAguardando, "cancelados" => $cartoesCancelados);
    }
}

?>