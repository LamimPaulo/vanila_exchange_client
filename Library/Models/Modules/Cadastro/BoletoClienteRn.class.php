<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade BoletoCliente
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class BoletoClienteRn {
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new BoletoCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new BoletoCliente());
        }
    }

    public function salvar(BoletoCliente &$boletoCliente) {
        $this->conexao->adapter->iniciar();

        try {
            if ($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO) {
                throw new \Exception($this->idioma->getText("naoPossivelAlterarDadosBoletoCancelado"));
            }
            if ($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO) {
                throw new \Exception($this->idioma->getText("naoPossAltBoletopago"));
            }
            if ($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPossAltBoletoFinalizado"));
            }

            $cliente = \Utils\Geral::getCliente();
            $novo = false;
            if ($boletoCliente->id > 0) {

                $aux = new BoletoCliente(Array("id" => $boletoCliente->id));
                $this->conexao->carregar($aux);

                if (empty($boletoCliente->arquivoBoleto)) {
                    $boletoCliente->arquivoBoleto = $aux->arquivoBoleto;
                }

                if (empty($boletoCliente->arquivoComprovante)) {
                    $boletoCliente->arquivoComprovante = $aux->arquivoComprovante;
                }

                $boletoCliente->dataCadastro = $aux->dataCadastro;
                $boletoCliente->status = $aux->status;
                $boletoCliente->idReferencia = $aux->idReferencia;
                $boletoCliente->idCliente = $aux->idCliente;
                $boletoCliente->comentario = $aux->comentario;

            } else {
                $novo = true;

                $boletoCliente->status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO;
                $boletoCliente->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $boletoCliente->dataPagamento = null;


                if ($cliente != null) {
                    $boletoCliente->idCliente = $cliente->id;
                }

            }

            if (!$cliente->id > 0) {
                throw new \Exception($this->idioma->getText("necessarioEstarLogadoOperacao"));
            }

            if (empty($boletoCliente->barras)) {
                throw new \Exception($this->idioma->getText("necessarioInformarBarras"));
            }

            if (!isset($boletoCliente->dataVencimento->data) || $boletoCliente->dataVencimento->data == null) {
                throw new \Exception($this->idioma->getText("dataVencimentoInvalida"));
            }

            if (empty($boletoCliente->email)) {
                throw new \Exception($this->idioma->getText("necessarioEmail"));
            }

            if (!\Utils\Validacao::email($boletoCliente->email)) {
                throw new \Exception($this->idioma->getText("emailInvalido"));
            }

            if (!$boletoCliente->idBanco > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarBanco"));
            }

            if (!is_numeric($boletoCliente->valor) || !$boletoCliente->valor>0) {
                throw new \Exception($this->idioma->getText("valorBoletoInvalido"));
            }
            
            
            $configuracao = ConfiguracaoRn::get();

            $boletoCliente->taxa = number_format($configuracao->taxaBoleto, 2, ".", "");
            $boletoCliente->valorTaxa = number_format(($boletoCliente->valor / 100 * $boletoCliente->taxa), 2, ".", "");
            $boletoCliente->valor = number_format(($boletoCliente->valor + $boletoCliente->valorTaxa), 2, ".", "");
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            
            if ($boletoCliente->valor > $saldo) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            ClienteHasCreditoRn::validar($cliente);
            
            $this->conexao->salvar($boletoCliente);
            
            $this->debitarDoSaldo($cliente, $boletoCliente);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function filtrarBoletosClientes(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $status, $idBanco, $tipoData, $email, $idCliente = null, $limit = "T", $barras = null) {
        $where = Array();
        
        if (!empty($email) && $idCliente > 0) {
            $where[] = " (b.email = '{$email}' OR b.id_cliente = {$idCliente}) ";
        } else if (!empty($email) ) {
            $where[] = " b.email = '{$email}' ";
        } else if ($idCliente > 0) {
            $where[] = " b.id_cliente = {$idCliente} ";
        }
        
        
        
        if ($status != "T") {
            $where[] = " b.status = '{$status}'";
        }
        
        if ($idBanco > 0) {
            $where[] = " b.id_banco = {$idBanco} ";
        }
        
        
        if (!empty($barras)) {
            $where[] = " b.barras = '{$barras}' ";
        }
        
        if (in_array($tipoData, 
                Array(\Utils\Constantes::STATUS_BOLETO_TIPO_DATA_CADASTRO, 
                    \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_PAGAMENTO, 
                    \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_VENCIMENTO))) {
            
            switch ($tipoData) {
                case \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_CADASTRO:
                    $tipoData = "b.data_cadastro";
                    break;
                case \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_PAGAMENTO:
                    $tipoData = "b.data_pagamento";
                    break;
                case \Utils\Constantes::STATUS_BOLETO_TIPO_DATA_VENCIMENTO:
                    $tipoData = "b.data_vencimento";
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
                . " b.* "
                . " FROM boletos_clientes b "
                . " WHERE "
                . " {$where} "
                . " ORDER BY b.status, b.data_vencimento"
                . " {$limitString};";
              
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $boletoCliente = new BoletoCliente($dados);
            $lista[] = $boletoCliente;
        }
        return $lista;  
    }
    
    
    public function marcarComoPago(BoletoCliente &$boletoCliente, $comprovante) {
        $this->conexao->adapter->iniciar();
        try {
            $boletoCliente->status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO;
            $boletoCliente->arquivoComprovante = $comprovante;
            $boletoCliente->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));

            if (empty($boletoCliente->arquivoComprovante)) {
                throw new \Exception($this->idioma->getText("informarCompPagamento"));
            }
            
            $this->conexao->update(
                    Array(
                        "status" => $boletoCliente->status, 
                        "arquivo_comprovante" => $comprovante,
                        "data_pagamento" => $boletoCliente->dataPagamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                    ), 
                    Array("id" => $boletoCliente->id)
                );

            $contaCorrenteEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresa->bloqueado = 1;
            $contaCorrenteEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresa->descricao = "Boleto pago {$boletoCliente->id}";
            $contaCorrenteEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresa->transferencia = 0;
            $contaCorrenteEmpresa->valor = number_format($boletoCliente->valor - $boletoCliente->valorTaxa, 2, ".", "");
            $contaCorrenteEmpresa->id = 0;
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);
            
            if ($boletoCliente->valorTaxa > 0) {
                
                // Começa a validação do Crédito de referência ou convite
                $cliente = new Cliente(Array("id" => $boletoCliente->idCliente));
                $clienteRn = new ClienteRn();
                $clienteRn->conexao->carregar($cliente);

                if ($cliente->idReferencia > 0) {
                    $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia, true);
                    if ($clienteHasComissao != null) {
                        if ($clienteHasComissao->boleto > 0) { 
                            $descricao = "Pagamento comissão boleto Referência {$cliente->nome} ";
                            $comissao = number_format(($boletoCliente->valorTaxa * ($clienteHasComissao->boleto / 100)), 2, ".", "");
                            $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" =>  $cliente->idReferencia)), $comissao, $descricao, false, $cliente->id, null, 7);
                        }
                    }
                }
                
                if (AMBIENTE == "producao") { 
                    // Emissão da NFE
                    $dadosNF = \ENotasGW\NotaFiscal::emitir($boletoCliente, false);

                    $jsonNotaFiscal = \ENotasGW\NotaFiscal::consultar($dadosNF->nfeId);

                    $notaFiscal = new NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idBoleto = $boletoCliente->id;
                    $notaFiscal->idCliente = $boletoCliente->idCliente;

                    NotaFiscalRn::setNotaFiscalFromJson($notaFiscal, $jsonNotaFiscal);

                    $notaFiscalRn = new NotaFiscalRn($this->conexao->adapter);

                    $notaFiscalRn->salvar($notaFiscal);
                } 
                
            }
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception (\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function marcarComoCancelado(BoletoCliente &$boletoCliente, $motivoCancelamento, $idCanceladoPor) {
        try {
            try {
                $this->conexao->carregar($boletoCliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("boletoInvalido"));
            }
            
            $boletoCliente->status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO;
            $boletoCliente->motivoCancelamento = $motivoCancelamento;
            $boletoCliente->dataPagamento = null;
            $boletoCliente->idCanceladoPor = $idCanceladoPor;
            
            $this->conexao->update(
                    Array("status" => $boletoCliente->status, "data_pagamento" => null, "motivo_cancelamento" => $motivoCancelamento,
                          "data_cancelamento" => date("Y-m-d H:i:s"), "id_cancelado_por" => $boletoCliente->idCanceladoPor), 
                    Array("id" => $boletoCliente->id));
            
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Extorno de Pagamento de boleto.";
            $contaCorrenteReais->idCliente = $boletoCliente->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = number_format($boletoCliente->valor - $boletoCliente->valorTaxa, 2, ".", "");
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
            $contaCorrenteReaisTaxa->descricao = "Extorno de Taxa de pagamento de boleto.";
            $contaCorrenteReaisTaxa->idCliente = $boletoCliente->idCliente;
            $contaCorrenteReaisTaxa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisTaxa->transferencia = 0;
            $contaCorrenteReaisTaxa->valor = number_format($boletoCliente->valorTaxa, 2, ".", "");
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
            $contaCorrenteEmpresa->descricao = "Extorno Solicitação de Pagamento de Boleto {$boletoCliente->id}";
            $contaCorrenteEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresa->transferencia = 0;
            $contaCorrenteEmpresa->valor = number_format($boletoCliente->valor - $boletoCliente->valorTaxa, 2, ".", "");
            $contaCorrenteEmpresa->id = 0;
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);

            $contaCorrenteEmpresaTaxa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresaTaxa->bloqueado = 1;
            $contaCorrenteEmpresaTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresaTaxa->descricao = "Extorno de Taxa de cobrança de boleto {$boletoCliente->id}";
            $contaCorrenteEmpresaTaxa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteEmpresaTaxa->transferencia = 0;
            $contaCorrenteEmpresaTaxa->valor = number_format($boletoCliente->valorTaxa, 2, ".", "");
            $contaCorrenteEmpresaTaxa->id = 0;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresaTaxa);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function getByIdInvoice($idInvoice) {
        $result = $this->conexao->select(Array("id_invoice" => $idInvoice));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    
    public function getByIdGateway($tipoPagamento, $idGateway) {
        $result = $this->conexao->select(Array("tipo_pagamento" => $tipoPagamento, "id_gateway" => $idGateway));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function getRelatorioIndicacoes($idReferencia, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = "";
        if ($dataInicial != null) {
            if ($dataFinal != null) {
                $where = " AND b.data_pagamento BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            } else {
                $where = " AND b.data_pagamento >= '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            }
        }
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $configuracao->percentualComissaoBoleto1 = ($configuracao->percentualComissaoBoleto1 / 100);
        
        $queryLv1 = "SELECT "
                    . "b.email, "
                    . "sum(b.valor) AS valor "
                    . "FROM boletos_clientes b "
                    . "WHERE b.id_referencia = {$idReferencia} AND "
                    . "b.status IN ('".\Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO."', '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."') {$where} "
                    . "GROUP BY b.email;";
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
            $comissaoLv1 +=  ($dadosLv1["valor"] * $configuracao->percentualComissaoBoleto1);
            $valorLv1 += $dadosLv1["valor"];
            $valorGeral += $dadosLv1["valor"];
            $comissaoGeral += ($dadosLv1["valor"] * $configuracao->percentualComissaoBoleto1);
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
    
    public function debitarDoSaldo(Cliente $cliente, BoletoCliente $boletoCliente) {
        $contaCorrenteReais = new ContaCorrenteReais();
        $contaCorrenteReais->id = 0;
        $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteReais->descricao = "Pagamento de boleto.";
        $contaCorrenteReais->idCliente = $cliente->id;
        $contaCorrenteReais->tipo = \Utils\Constantes::SAIDA;
        $contaCorrenteReais->transferencia = 0;
        $contaCorrenteReais->valor = number_format($boletoCliente->valor - $boletoCliente->valorTaxa, 2, ".", "");
        $contaCorrenteReais->comissaoConvidado = 0;
        $contaCorrenteReais->comissaoLicenciado = 0;
        $contaCorrenteReais->clienteDestino = null;
        $contaCorrenteReais->idReferenciado = null;
        $contaCorrenteReais->orderBook = 0;
        $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:I:ss"));

        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
        $contaCorrenteReaisRn->salvar($contaCorrenteReais);
        
        $contaCorrenteReaisTaxa = new ContaCorrenteReais();
        $contaCorrenteReaisTaxa->id = 0;
        $contaCorrenteReaisTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteReaisTaxa->descricao = "Taxa de pagamento de boleto.";
        $contaCorrenteReaisTaxa->idCliente = $cliente->id;
        $contaCorrenteReaisTaxa->tipo = \Utils\Constantes::SAIDA;
        $contaCorrenteReaisTaxa->transferencia = 0;
        $contaCorrenteReaisTaxa->valor = number_format($boletoCliente->valorTaxa, 2, ".", "");
        $contaCorrenteReaisTaxa->comissaoConvidado = 0;
        $contaCorrenteReaisTaxa->comissaoLicenciado = 0;
        $contaCorrenteReaisTaxa->clienteDestino = null;
        $contaCorrenteReaisTaxa->idReferenciado = null;
        $contaCorrenteReaisTaxa->orderBook = 0;
        $contaCorrenteReaisTaxa->dataCadastro = new \Utils\Data(date("d/m/Y H:I:ss"));
        $contaCorrenteReaisRn->salvar($contaCorrenteReaisTaxa);
        
        $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);

        if ($saldo < 0) {
            $this->conexao->excluir($contaCorrenteReais);
            $this->conexao->excluir($contaCorrenteReaisTaxa);
            throw new \Exception($this->idioma->getText("voceNaoTemSaldoSufuciente"));
        }
        
        $contaCorrenteEmpresa = new ContaCorrenteReaisEmpresa();
        $contaCorrenteEmpresa->bloqueado = 1;
        $contaCorrenteEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteEmpresa->descricao = "Solicitação de Pagamento de Boleto {$boletoCliente->id}";
        $contaCorrenteEmpresa->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteEmpresa->transferencia = 0;
        $contaCorrenteEmpresa->valor = number_format($boletoCliente->valor - $boletoCliente->valorTaxa, 2, ".", "");
        $contaCorrenteEmpresa->id = 0;
        $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
        
        $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);
        
        $contaCorrenteEmpresaTaxa = new ContaCorrenteReaisEmpresa();
        $contaCorrenteEmpresaTaxa->bloqueado = 1;
        $contaCorrenteEmpresaTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteEmpresaTaxa->descricao = "Taxa de cobrança de boleto {$boletoCliente->id}";
        $contaCorrenteEmpresaTaxa->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteEmpresaTaxa->transferencia = 0;
        $contaCorrenteEmpresaTaxa->valor = number_format($boletoCliente->valorTaxa, 2, ".", "");
        $contaCorrenteEmpresaTaxa->id = 0;
        $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresaTaxa);
    }
    
    
    public function getQuantidadePorStatus() {
        $query = "SELECT status, COUNT(*) AS qtd FROM boletos_clientes GROUP BY status;";
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
        $where[] = " b.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " b.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            switch (strtoupper($tipoData)) {
                case "A":
                    $tipoData = "b.data_vencimento";
                    break;
                case "B":
                    $tipoData = "b.data_pagamento";
                    break;
                case "C":
                    $tipoData = "b.data_cadastro";
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
                . " SUM(b.valor) AS total "
                . " FROM boletos_clientes b "
                . " LEFT JOIN categorias_servicos c ON (b.id_categoria_servico = c.id) "
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
                $tipoData = "b.data_vencimento";
                break;
            case "B":
                $tipoData = "b.data_pagamento";
                break;
            case "C":
                $tipoData = "b.data_cadastro";
                break;
            default:
                throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $where = Array();
        $where[] = " b.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " b.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            
            
            $where[] = " {$tipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " CONCAT(EXTRACT(YEAR FROM {$tipoData}) , '-' , EXTRACT(MONTH FROM {$tipoData}) ) AS mesOrder, "
                . " CONCAT(EXTRACT(MONTH FROM {$tipoData}) , '/' , EXTRACT(YEAR FROM {$tipoData}) ) AS mes, "
                . " SUM(b.valor) AS total "
                . " FROM boletos_clientes b "
                . " {$sWhere} "
                . " GROUP BY mes, mesOrder "
                . " ORDER BY mesOrder ";
                
        //exit($query);
                
        $result = $this->conexao->adapter->query($query)->execute();
        $categorias = Array();
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
                $tipoData = "b.data_vencimento";
                break;
            case "B":
                $tipoData = "b.data_pagamento";
                break;
            case "C":
                $tipoData = "b.data_cadastro";
                break;
            default:
                throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $where = Array();
        $where[] = " b.status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO."' ";
        $cliente = \Utils\Geral::getCliente();
        $where[] = " b.id_cliente = {$cliente->id} ";
        if ($dataInicial != null && $dataFinal != null && isset($dataInicial->data) && isset($dataFinal->data) && $dataInicial->data != null && $dataFinal->data != null) {
            
            
            $where[] = " {$tipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " CONCAT(EXTRACT(YEAR FROM {$tipoData}) , '-' , EXTRACT(MONTH FROM {$tipoData}) ) AS mesOrder, "
                . " CONCAT(EXTRACT(MONTH FROM {$tipoData}) , '/' , EXTRACT(YEAR FROM {$tipoData}) ) AS mes, "
                . " c.id, "
                . " c.descricao, "
                . " SUM(b.valor) AS total "
                . " FROM boletos_clientes b "
                . " LEFT JOIN categorias_servicos c ON (b.id_categoria_servico = c.id) "
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
    
}

?>