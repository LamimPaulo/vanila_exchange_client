<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade RemessaDinheiro
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RemessaDinheiroRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;

    
    public function __construct(\Io\BancoDados $adapter = null) {
         $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new RemessaDinheiro());
        } else {
            $this->conexao = new GenericModel($adapter, new RemessaDinheiro());
        }
    }
    
    public function salvar(RemessaDinheiro &$remessaDinheiro) {
        
        try {

            if ($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO) {
                throw new \Exception($this->idioma->getText("naoPossivelAlteRemessaCancelada"));
            }
            if ($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO) {
                throw new \Exception($this->idioma->getText("naoPossivelAlteRemessaPaga"));
            }
            if ($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPossivelAlteRemessaFinalizada"));
            }

            if ($remessaDinheiro->id > 0) {
                $aux = new RemessaDinheiro(Array("id" => $remessaDinheiro->id));
                $this->conexao->carregar($aux);

                if (empty($remessaDinheiro->arquivoComprovante)) {
                    $remessaDinheiro->arquivoComprovante = $aux->arquivoComprovante;
                }

                $remessaDinheiro->dataCadastro = $aux->dataCadastro;
                $remessaDinheiro->status = $aux->status;
                $remessaDinheiro->idReferencia = $aux->idReferencia;
                $remessaDinheiro->idCliente = $aux->idCliente;
            } else {
                $remessaDinheiro->status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO;
                $remessaDinheiro->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $remessaDinheiro->dataPagamento = null;


                $cliente = \Utils\Geral::getCliente();
                if ($cliente != null) {
                    $remessaDinheiro->idCliente = $cliente->id;
                }
            }

            if (empty($remessaDinheiro->titular)) {
                throw new \Exception($this->idioma->getText("informarTitular"));
            }

            if (empty($remessaDinheiro->agencia)) {
                throw new \Exception($this->idioma->getText("informarAgencia"));
            }

            if (empty($remessaDinheiro->conta)) {
                throw new \Exception($this->idioma->getText("informarConta"));
            }

            if ($remessaDinheiro->contaDigito == null) {
                throw new \Exception($this->idioma->getText("informarDigConta"));
            }

            if (empty($remessaDinheiro->documento)) {
                throw new \Exception($this->idioma->getText("informarCpfCnpj"));
            }


            if (strlen($remessaDinheiro->documento) != 18 && strlen($remessaDinheiro->documento) != 14) {
                throw new \Exception($this->idioma->getText("docInvalido"));
            }

            if (strlen($remessaDinheiro->documento) == 14) {
                if (!\Utils\Validacao::cpf($remessaDinheiro->documento)) {
                    throw new \Exception($this->idioma->getText("cpfInvalido"));
                }
            }
            //FUNÇÃO NÃO ESTÁ VERIFICANDO O CNPJ CORRETAMENTE
            /*if (strlen($remessaDinheiro->documento) == 18) {
                if (\Utils\Validacao::cnpj($remessaDinheiro->documento)) {
                    throw new \Exception("CNPJ inválido");
                }
            }*/

            $remessaDinheiro->email = "";

            if (!$remessaDinheiro->idBanco > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarBanco"));
            }

            if (!in_array($remessaDinheiro->tipoConta, Array(\Utils\Constantes::CONTA_CORRENTE, \Utils\Constantes::CONTA_POUPANCA))) {
                throw new \Exception($this->idioma->getText("tipoContaInvalida"));
            }

            if (!is_numeric($remessaDinheiro->valor) || !$remessaDinheiro->valor > 0) {
                throw new \Exception($this->idioma->getText("valorRemessaInvalido"));
            }

            unset($remessaDinheiro->cliente);
            
            
            $configuracao = ConfiguracaoRn::get();

            $remessaDinheiro->taxa = number_format($configuracao->taxaRemessa, 2, ".", "");
            $remessaDinheiro->valorTaxa = number_format(($remessaDinheiro->valor / 100 * $remessaDinheiro->taxa), 2, ".", "");
            $remessaDinheiro->valor += number_format(($remessaDinheiro->valorTaxa + $configuracao->tarifaTed), 2, ".", "");
            $remessaDinheiro->tarifaTed = $configuracao->tarifaTed;
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            
            if ($remessaDinheiro->valor > $saldo) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            ClienteHasCreditoRn::validar($cliente);
            
            $this->conexao->salvar($remessaDinheiro);
            
            $contaCorrenteReaisRn->debitarDoSaldo($cliente, $remessaDinheiro->valor, "Envio de Remessa de dinheiro", true, true);
            
        } catch (\Exception $ex) {
            throw new \Exception (\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function carregar(RemessaDinheiro &$remessaDinheiro, $carregar = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($remessaDinheiro);
        }
        
        if ($carregarCliente && $remessaDinheiro->idCliente > 0) {
            $remessaDinheiro->cliente = new Cliente(Array("id" => $remessaDinheiro->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($remessaDinheiro->cliente);
        }
    } 
    
    
    public function filtrarRemessasClientes(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $status, $idBanco, $tipoData, $email,
            $titular, $agencia, $conta, $idCliente = null, $limit = "T") {
        $where = Array();
        
        
        if (!empty($email) && $idCliente > 0) {
            $where[] = " (r.email = '{$email}' OR r.id_cliente = {$idCliente}) ";
        } else if (!empty($email) ) {
            $where[] = " r.email = '{$email}' ";
        } else if ($idCliente > 0) {
            $where[] = " r.id_cliente = {$idCliente} ";
        }
        
        if (!empty($titular)) {
            $where[] = " lower(r.titular) like lower('%{$email}%') ";
        }
        
        if (!empty($agencia)) {
            $where[] = " r.agencia = '{$agencia}' ";
        }
        
        if (!empty($conta)) {
            $where[] = " r.conta = '{$conta}' ";
        }
        
        if ($status != "T") {
            $where[] = " r.status = '{$status}'";
        }
        
        if ($idBanco > 0) {
            $where[] = " r.id_banco = {$idBanco} ";
        }
        
        if (in_array($tipoData, 
                Array(\Utils\Constantes::STATUS_REMESSA_DINHEIRO_DATA_CADASTRO, 
                    \Utils\Constantes::STATUS_REMESSA_DINHEIRO_DATA_PAGAMENTO))) {
            
            switch ($tipoData) {
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_DATA_CADASTRO:
                    $tipoData = "r.data_cadastro";
                    break;
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_DATA_PAGAMENTO:
                    $tipoData = "r.data_pagamento";
                    break;
                default:
                    break;
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " {$tipoData} BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        } else {
            throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $limitString = "";
        if ($limit != "T") {
            $limitString = " limit {$limit} ";
        }
        
        
        $where = (sizeof($where) > 0 ? implode(" AND ", $where) : " ");
        
        $query = "SELECT "
                . " r.* "
                . " FROM remessas_dinheiro r "
                . " WHERE "
                . " {$where} "
                . " ORDER BY r.status, r.data_cadastro"
                . " {$limitString};";
                
                
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $remessaDinheiro = new RemessaDinheiro($dados);
            $this->carregar($remessaDinheiro, false, true);
            $lista[] = $remessaDinheiro;
        }
        return $lista;  
    }
    
    
    public function marcarComoPago(RemessaDinheiro &$remessaDinheiro, $comprovante) {
        
        try {
            
            try {
                $this->conexao->carregar($remessaDinheiro);
            } catch (\Exception $ex) {
                throw new \Exception;
            }
            
            $remessaDinheiro->status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO;
            $remessaDinheiro->arquivoComprovante = $comprovante;

            if (empty($remessaDinheiro->arquivoComprovante)) {
                throw new \Exception($this->idioma->getText("necessarioComprovanteDeposito"));
            }
            
            $this->conexao->update(
                Array(
                    "status" => $remessaDinheiro->status, 
                    "arquivo_comprovante" => $comprovante,
                    "data_pagamento" => date("Y-m-d H:i:s")
                ), 
                Array("id" => $remessaDinheiro->id)
            );
        
            $contaCorrenteEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresa->bloqueado = 1;
            $contaCorrenteEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresa->descricao = "Remessa de Dinheiro enviada {$remessaDinheiro->id}";
            $contaCorrenteEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresa->transferencia = 0;
            $contaCorrenteEmpresa->valor = number_format($remessaDinheiro->valor - $remessaDinheiro->valorTaxa - $remessaDinheiro->tarifaTed, 2, ".", "");
            $contaCorrenteEmpresa->id = 0;
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);
            
            // Emissão da NFE
            if ($remessaDinheiro->valorTaxa > 0) {
                
                // Começa a validação do Crédito de referência ou convite
                $cliente = new Cliente(Array("id" => $remessaDinheiro->idCliente));
                $clienteRn = new ClienteRn();
                $clienteRn->conexao->carregar($cliente);

                if ($cliente->idReferencia > 0) {
                    $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia, true);
                    if ($clienteHasComissao != null) {
                        if ($clienteHasComissao->remessa > 0) { 
                            $descricao = "Pagamento comissão boleto Referência {$cliente->nome} ";
                            $comissao = number_format(($remessaDinheiro->valorTaxa * ($clienteHasComissao->remessa / 100)), 2, ".", "");
                            $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" =>  $cliente->idReferencia)), $comissao, $descricao, false, $cliente->id, null, 7);
                        }
                    }
                }
                
                /*$convite = false;
                $pagarComissao = false;
                $comissao = 0;
                $descricao = "";
                $idCliente = 0;

                $configuracao = new Configuracao(Array("id" => 1));
                $configuracaoRn = new ConfiguracaoRn();
                $configuracaoRn->conexao->carregar($configuracao);

                if ($cliente->idReferencia > 0) {
                    $idCliente = $cliente->idReferencia;
                    $clienteHasLicencaRn = new ClienteHasLicencaRn();
                    $licenca = $clienteHasLicencaRn->carregarLicencaCliente(new Cliente(Array("id" => $idCliente)));

                    if ($licenca != null) {
                        $comissao = number_format(($remessaDinheiro->valorTaxa * ($licenca->licencaSoftware->comissao / 100)), 2, ".", "");

                        $convite = false;
                        $pagarComissao = true;
                        $descricao = "Pagamento comissão remessa de dinheiro Referência {$cliente->nome} ";

                    } else {
                        $comissao = number_format(($remessaDinheiro->valorTaxa * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                        $convite = true;
                        $pagarComissao = true;
                        $descricao = "Pagamento comissão remessa de dinheiro convidado {$cliente->nome} ";
                    }

                } else if ($cliente->idClienteConvite > 0) {
                    if ($cliente->comissaoConvitePago < 1) {
                        $idCliente = $cliente->idClienteConvite;
                        $comissao = number_format(($boletoCliente->valorTaxa * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                        $convite = true;
                        $pagarComissao = true;
                        $descricao = "Pagamento comissão remessa de dinheiro convidado {$cliente->nome} ";
                    }
                }

                if ($pagarComissao) {
                    $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" => $idCliente)), $comissao, $descricao, $convite, $cliente->id, null);
                    $clienteRn->conexao->update(Array("comissao_convite_pago" => 1), Array("id"=> $cliente->id) );
                    if ($convite) { 
                        $clienteConvidadoRn = new ClienteConvidadoRn($this->conexao->adapter);
                        $clienteConvidadoRn->setComissao(new Cliente(Array("id" => $idCliente)), $cliente->email, $comissao, "Depósito");
                    }
                }
                */
                
                
                if (AMBIENTE == "producao") { 
                    $dadosNF = \ENotasGW\NotaFiscal::emitir($remessaDinheiro, ($remessaDinheiro->aceitaNota > 0));

                    $jsonNotaFiscal = \ENotasGW\NotaFiscal::consultar($dadosNF->nfeId);

                    $notaFiscal = new NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idBoleto = $remessaDinheiro->id;
                    $notaFiscal->idCliente = $remessaDinheiro->idCliente;

                    NotaFiscalRn::setNotaFiscalFromJson($notaFiscal, $jsonNotaFiscal);

                    $notaFiscalRn = new NotaFiscalRn($this->conexao->adapter);

                    $notaFiscalRn->salvar($notaFiscal);
                }
            }
            
            
        } catch (\Exception $ex) {
            throw new \Exception (\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function marcarComoCancelado(RemessaDinheiro &$remessaDinheiro, $motivoCancelamento, $idCanceladoPor) {
        
        try{
            $this->conexao->carregar($remessaDinheiro);            
        } catch (Exception $ex) {
            throw new \Exception ($this->idioma->getText("remessaValoresInvalida"));
        }

        $remessaDinheiro->status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO;
        $remessaDinheiro->dataPagamento = null;
        $remessaDinheiro->motivoCancelamento = $motivoCancelamento;
        $remessaDinheiro->idCanceladoPor = $idCanceladoPor;
        $this->conexao->update(
                Array("status" => $remessaDinheiro->status, "data_pagamento" => null,
                    "motivo_cancelamento" => $remessaDinheiro->motivoCancelamento,
                    "data_cancelamento" => date("Y-m-d H:i:s"),
                    "id_cancelado_por" => $remessaDinheiro->idCanceladoPor), 
                Array("id" => $remessaDinheiro->id));
    
        
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Extorno de Pagamento de remessa de dinheiro.";
            $contaCorrenteReais->idCliente = $remessaDinheiro->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = number_format($remessaDinheiro->valor - $remessaDinheiro->valorTaxa - $remessaDinheiro->tarifaTed, 2, ".", "");
            $contaCorrenteReais->comissaoConvidado = 0;
            $contaCorrenteReais->comissaoLicenciado = 0;
            $contaCorrenteReais->clienteDestino = null;
            $contaCorrenteReais->idReferenciado = null;
            $contaCorrenteReais->orderBook = 0;
            $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:I:ss"));

            $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);

            $contaCorrenteReaisTaxa = new ContaCorrenteReais();
            $contaCorrenteReaisTaxa->id = 0;
            $contaCorrenteReaisTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisTaxa->descricao = "Extorno de taxa de pagamento de remessa de dinheiro.";
            $contaCorrenteReaisTaxa->idCliente = $remessaDinheiro->idCliente;
            $contaCorrenteReaisTaxa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisTaxa->transferencia = 0;
            $contaCorrenteReaisTaxa->valor = number_format($remessaDinheiro->valorTaxa + $remessaDinheiro->tarifaTed, 2, ".", "");
            $contaCorrenteReaisTaxa->comissaoConvidado = 0;
            $contaCorrenteReaisTaxa->comissaoLicenciado = 0;
            $contaCorrenteReaisTaxa->clienteDestino = null;
            $contaCorrenteReaisTaxa->idReferenciado = null;
            $contaCorrenteReaisTaxa->orderBook = 0;
            $contaCorrenteReaisTaxa->dataCadastro = new \Utils\Data(date("d/m/Y H:I:ss"));
            $contaCorrenteReaisRn->salvar($contaCorrenteReaisTaxa);

            $contaCorrenteEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresa->bloqueado = 1;
            $contaCorrenteEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresa->descricao = "Extorno Solicitação de Remessa de Dinheiro {$remessaDinheiro->id}";
            $contaCorrenteEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresa->transferencia = 0;
            $contaCorrenteEmpresa->valor = number_format($remessaDinheiro->valor - $remessaDinheiro->valorTaxa - $remessaDinheiro->tarifaTed, 2, ".", "");
            $contaCorrenteEmpresa->id = 0;
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);

            $contaCorrenteEmpresaTaxa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresaTaxa->bloqueado = 1;
            $contaCorrenteEmpresaTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresaTaxa->descricao = "Extorno de taxa de cobrança de remessa de dinheiro {$remessaDinheiro->id}";
            $contaCorrenteEmpresaTaxa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresaTaxa->transferencia = 0;
            $contaCorrenteEmpresaTaxa->valor = number_format($remessaDinheiro->valorTaxa + $remessaDinheiro->tarifaTed, 2, ".", "");
            $contaCorrenteEmpresaTaxa->id = 0;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresaTaxa);
    }
    
    
    public function getByIdInvoice($idInvoice) {
        $result = $this->conexao->select(Array("id_invoice" => $idInvoice));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND r.data_pagamento BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND r.data_pagamento >= '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $configuracao->percentualComissaoRemessa1 = ($configuracao->percentualComissaoRemessa1 / 100);
        
        $queryLv1 = "SELECT "
                    . "r.email, "
                    . "sum(r.valor) AS valor "
                    . "FROM remessas_dinheiro r "
                    . "WHERE r.id_referencia = {$idReferencia} AND "
                    . "r.status IN ('".\Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO."', '".\Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO."') {$where} "
                    . "GROUP BY r.email;";
        //exit($queryLv1);            
        $mmn = Array();
        $resultLv1 = $this->conexao->adapter->query($queryLv1)->execute();
        
        $valorGeral = 0;
        $comissaoGeral = 0;
        
        $clientesLv1 = Array();
        $valorLv1 = 0;
        $comissaoLv1 = 0;
        foreach ($resultLv1 as $dadosLv1) {
            $clientesLv1[] = Array("email" => $dadosLv1["email"], "valor" => $dadosLv1["valor"]);
            $comissaoLv1 +=  ($dadosLv1["valor"] * $configuracao->percentualComissaoRemessa1);
            $valorLv1 += $dadosLv1["valor"];
            $valorGeral += $dadosLv1["valor"];
            $comissaoGeral += ($dadosLv1["valor"] * $configuracao->percentualComissaoRemessa1);
        }
        
        $mmn["lv1"] = Array(
            "clientes" => $clientesLv1,
            "comissao" => $comissaoLv1,
            "valor" => $valorLv1
        );
        
        
        $mmn["comissao"] = $comissaoGeral;
        $mmn["valor"] = $valorGeral;
        
        return $mmn;
    }
    
    
    public function debitarDoSaldo(RemessaDinheiro $remessaDinheiro, $throwsException = false) {
        try {
            $clienteRn = new ClienteRn();
            
            if ($remessaDinheiro->idCliente > 0) {
                $cliente = new Cliente(Array("id" => $remessaDinheiro->idCliente));
                $clienteRn->conexao->carregar($cliente);
            } else {
                $cliente = $clienteRn->getByEmail($remessaDinheiro->email);
            }
            
            if ($cliente != null) {

                $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
                $contaCorrente = $contaCorrenteReaisRn->debitarDoSaldo($cliente, $remessaDinheiro->valor, "Envio de dinheiro para {$remessaDinheiro->titular} ", $throwsException);

                if ($contaCorrente != null) {
                    $this->conexao->update(Array("status" => \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO, "data_pagamento" => date("Y-m-d H:i:s")), Array("id" => $remessaDinheiro->id));
                }
                
                return $contaCorrente;
            }
        } catch (\Exception $ex) {
            if (isset($contaCorrente) && $contaCorrente->id > 0) {
                $contaCorrenteReaisRn->excluir($contaCorrente);
            }
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        return null;
    }
    
    
    public function getQuantidadePorStatus() {
        $query = "SELECT status, COUNT(*) AS qtd FROM remessas_dinheiro GROUP BY status;";
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $aguardando = 0;
        $cancelado = 0;
        $finalizado = 0;
        $pago = 0;
        
        foreach ($dados as $d) {
            switch ($d["status"]) {
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO:
                    $aguardando += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO:
                    $cancelado += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO:
                    $finalizado += $d["qtd"];
                    break;
                case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO:
                    $pago += $d["qtd"];
                    break;

                default:
                    break;
            }
        }
        
        
        return Array("aguardando" => $aguardando, "cancelado" => $cancelado, "finalizado" => $finalizado, "pago" => $pago);
        
    }
    
    
    
    
    public function getConsumoPorCategoria(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipoData = "A") {
        
        $where = Array();
        $where[] = " r.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " r.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            switch (strtoupper($tipoData)) {
                case "A":
                    $tipoData = "r.data_pagamento";
                    break;
                case "B":
                    $tipoData = "r.data_cadastro";
                    break;
                default:
                    throw new \Exception($this->idioma->getText("tipoDataInvalida"));
            }
            
            $where[] = " {$tipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " c.id, "
                . " c.descricao,"
                . " SUM(r.valor) AS total "
                . " FROM remessas_dinheiro r "
                . " LEFT JOIN categorias_servicos c ON (r.id_categoria_servico = c.id) "
                . " {$sWhere} "
                . " GROUP BY c.id, c.descricao "
                . " ORDER BY c.descricao ";
                
        $result = $this->conexao->adapter->query($query)->execute();
        $dados = Array();
        $categorias = Array();
        foreach ($result as $d) {
            if (empty(trim($d["descricao"]))) {
                $d["descricao"] = "Sem categoria";
            }
            
            $dados[] = Array("categoria" => $d["descricao"], "id" => $d["id"], "total" => $d["total"]);
            $categorias[] = $d["descricao"];
        }
        
        
        return Array("categorias" => $categorias, "dados" => $dados);
    }
    
    
    public function getConsumoPorMes(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipoData = "A") {
        
        switch (strtoupper($tipoData)) {
            case "A":
                $tipoData = "r.data_pagamento";
                break;
            case "B":
                $tipoData = "r.data_cadastro";
                break;
            default:
                throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $where = Array();
        $where[] = " r.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " r.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            
            
            $where[] = " {$tipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " CONCAT(EXTRACT(YEAR FROM {$tipoData}) , '-' , EXTRACT(MONTH FROM {$tipoData}) ) AS mesOrder, "
                . " CONCAT(EXTRACT(MONTH FROM {$tipoData}) , '/' , EXTRACT(YEAR FROM {$tipoData}) ) AS mes, "
                . " SUM(r.valor) AS total "
                . " FROM remessas_dinheiro r "
                . " {$sWhere} "
                . " GROUP BY mes, mesOrder "
                . " ORDER BY mesOrder ";
                
        //exit($query);
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        $meses = Array();
        foreach ($result as $d) {
            
            $meses[] = Array("mes" => $d["mes"], "total" => $d["total"]);
            $categorias[] = $d["mes"];
        }
        
        return Array("categorias" => $categorias, "meses" => $meses);
    }
    
    
    
    public function getConsumoPorMesPorCategoria(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipoData = "A") {
        switch (strtoupper($tipoData)) {
            case "A":
                $tipoData = "r.data_pagamento";
                break;
            case "B":
                $tipoData = "r.data_cadastro";
                break;
            default:
                throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $where = Array();
        $where[] = " r.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " r.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            
            
            $where[] = " {$tipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " CONCAT(EXTRACT(YEAR FROM {$tipoData}) , '-' , EXTRACT(MONTH FROM {$tipoData}) ) AS mesOrder, "
                . " CONCAT(EXTRACT(MONTH FROM {$tipoData}) , '/' , EXTRACT(YEAR FROM {$tipoData}) ) AS mes, "
                . " c.id, "
                . " c.descricao, "
                . " SUM(r.valor) AS total "
                . " FROM remessas_dinheiro r "
                . " LEFT JOIN categorias_servicos c ON (r.id_categoria_servico = c.id) "
                . " {$sWhere} "
                . " GROUP BY mes, c.id, c.descricao, mesOrder "
                . " ORDER BY mesOrder, c.descricao ";
                
        //exit($query);
                
        $result = $this->conexao->adapter->query($query)->execute();
        $categorias = Array();
        $meses = Array();
        $valores = Array();
        
        $ids = Array();
        foreach ($result as $d) {
            if (!isset($ids[$d["id"]])) {
                $categorias[] = Array("categoria" => $d["descricao"], "id" => $d["id"]);
                $ids[$d["id"]] = $d["id"];
            }
            
            if (!in_array($d["mes"], $meses)) {
                $meses[] = $d["mes"];
            }
            
            $valores[$d["mes"]][$d["id"]] = $d["total"];
            
        }
        
        $grafico = Array();
        foreach ($categorias as $categoria) {
            
            $totais = Array();
            foreach ($meses as $mes) {
                $totais[] = number_format((isset($valores[$mes][$categoria["id"]]) ? $valores[$mes][$categoria["id"]] : 0), 2, ".", "");
            }
            
            $grafico[] = Array(
                "categoria" => $categoria["categoria"],
                "totais" => $totais
            );
            
        }
        
        return Array("grafico" => $grafico, "meses" => $meses);
    }
    
    
    public function getContasBancariasCliente(Cliente $cliente) {
        
        $query = "SELECT titular, documento, conta, agencia FROM remessas_dinheiro WHERE id_cliente = {$cliente->id} GROUP BY titular, documento, conta, agencia ";
        $dados = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($dados as $d) {
            $lista[] = new RemessaDinheiro($d);
        }
        return $lista;
    }
    
}

?>