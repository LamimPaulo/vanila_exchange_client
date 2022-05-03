<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Deposito
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class DepositoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Deposito());
        } else {
            $this->conexao = new GenericModel($adapter, new Deposito());
        }
    }
    
    
    public function salvar(Deposito &$deposito) {
        
        $cliente = \Utils\Geral::getCliente();
        
        if ($cliente == null) {
            throw new \Exception($this->idioma->getText("vocePrecisaLogadoLancamento"));
        }
        
        if ($cliente->statusDepositoBrl < 1) {
            throw new \Exception($this->idioma->getText("solicitacaoDepositoSuspensa"));
        }
        
        if ($deposito->id > 0) {
            
            $aux = new Deposito(Array("id" => $deposito->id));
            $this->conexao->carregar($aux);
            
            if ($aux->status != \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                throw new \Exception($this->idioma->getText("naoPossivelAlterarDeposito"));
            }
            
            $deposito->idCliente = $aux->idCliente;
            $deposito->idUsuario = $aux->idUsuario;
            $deposito->status = $aux->status;
            $deposito->dataSolicitacao = $aux->dataSolicitacao;
            $deposito->dataConfirmacao = $aux->dataConfirmacao;
            $deposito->dataCancelamento = $aux->dataCancelamento;
            $deposito->motivoCancelamento = $aux->motivoCancelamento;
            $deposito->aceitaNota = $aux->aceitaNota;
            if (empty($deposito->comprovante)) {
                $deposito->comprovante = $aux->comprovante;
            }
            
            if (empty($deposito->notaFiscal)) {
                $deposito->notaFiscal = $aux->notaFiscal;
            }
            
        } else {
            $deposito->idCliente = $cliente->id;
            $deposito->idUsuario = null;
            $deposito->status = \Utils\Constantes::STATUS_DEPOSITO_PENDENTE;
            $deposito->dataSolicitacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $deposito->dataConfirmacao = null;
            $deposito->dataCancelamento = null;
            $deposito->motivoCancelamento = null;
            $deposito->notaFiscal = null;
            
            if (!$deposito->aceitaNota > 0) {
                $deposito->aceitaNota = 0;
            }
        }
        
        if ($deposito->tipoDeposito != \Utils\Constantes::GERENCIA_NET) {
            if (empty($deposito->comprovante)) {
                throw new \Exception($this->idioma->getText("vocePrecisaInformarCompDeposito"));
            }

            if (!$deposito->idContaBancariaEmpresa > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarConta"));
            }
        }
        
        if (!$deposito->idCliente > 0) {
            throw new \Exception($this->idioma->getText("necessarioIdentifarDepositante"));
        }
        
        $arrayTipos = Array(
            \Utils\Constantes::DOC,
            \Utils\Constantes::TED,
            \Utils\Constantes::DINHEIRO,
            \Utils\Constantes::TRANSF_ENTRE_CONTAS,
            \Utils\Constantes::GERENCIA_NET
        );
        
        if (!in_array($deposito->tipoDeposito, $arrayTipos)) {
            throw new \Exception($this->idioma->getText("necessarioDepositarTipoDeposito"));
        }
        
        $arrayStatus = Array(
            \Utils\Constantes::STATUS_DEPOSITO_CANCELADO,
            \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO,
            \Utils\Constantes::STATUS_DEPOSITO_PENDENTE
        );
        
        if (!in_array($deposito->status, $arrayStatus)) {
            throw new \Exception($this->idioma->getText("statusInvalido"));
        }
        
        if (!$deposito->valorDepositado > 0) {
            throw new \Exception($this->idioma->getText("valorPrecisaMaiorZero"));
        }
        
        if (!$deposito->valorCreditado > 0) {
            throw new \Exception($this->idioma->getText("creditadoNaContaMaiorZero"));
        }
        
        if (!$deposito->valorComissao > 0) {
            $deposito->valorComissao = 0;
        }
        
        if (!$deposito->taxaComissao > 0) {
            $deposito->taxaComissao = 0;
        }
        
        unset($deposito->cliente);
        unset($deposito->usuario);
        unset($deposito->contaBancariaEmpresa);
        
        
        $this->conexao->salvar($deposito);
    }
    
    public function solicitarDeposito(Deposito $deposito) {
        
        $cliente = \Utils\Geral::getCliente();
        
        if ($cliente == null) {
            throw new \Exception($this->idioma->getText("vocePrecisaLogadoLancamento"));
        }
        
        if ($cliente->statusDepositoBrl < 1) {
            throw new \Exception($this->idioma->getText("solicitacaoDepositoSuspensa"));
        }
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $clienteRn->conexao->carregar($cliente);

        $deposito->taxaComissao = 0;
        
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn->conexao->carregar($configuracao);
        
        $deposito->valorTarifa = 0;
        if ($deposito->tipoDeposito == \Utils\Constantes::DINHEIRO) { 
            if ($deposito->valorDepositado < 2000) {
                $deposito->taxaComissao = $configuracao->percentualDepositos;
            } else if ($deposito->valorDepositado >= 2000 && $deposito->valorDepositado < 5000) {
                $deposito->taxaComissao = $configuracao->depositoDoisCinco;
            } else if ($deposito->valorDepositado >= 5000 && $deposito->valorDepositado < 10000) {
                $deposito->taxaComissao = $configuracao->depositoCincoDez;
            } else if ($deposito->valorDepositado >= 10000 && $deposito->valorDepositado < 50000) {
                $deposito->taxaComissao = $configuracao->depositoDezCinquenta;
            } else {
                $deposito->taxaComissao = $configuracao->depositoCinquentaAcima;
            }
        }  else if ($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET) { 
            $deposito->taxaComissao = $configuracao->taxaDepositoBoleto;
            $deposito->valorTarifa = $configuracao->tarifaDepositoBoleto;
        } else {
            if ($cliente->considerarTaxaDepositoCliente) {
                $deposito->taxaComissao = $cliente->taxaComissaoDeposito;
            } else {
                $deposito->taxaComissao = $configuracao->taxaDeposito;
            }
        }
        
        if (!$deposito->valorDepositado > 0) {
            throw new \Exception($this->idioma->getText("valorDepositadoDeveInformado"));
        }
        
        if($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET){
            $deposito->valorComissao = number_format((($deposito->valorDepositado) * ($deposito->taxaComissao / 100)), 2, ".", "");
        } else {
            $deposito->valorComissao = number_format((($deposito->valorDepositado - $deposito->valorTarifa) * ($deposito->taxaComissao / 100)), 2, ".", "");
        }        
        
        $deposito->valorCreditado = number_format($deposito->valorDepositado - $deposito->valorTarifa - $deposito->valorComissao, 2, ".", "");
        if (!$deposito->valorCreditado > 0) {
            throw new \Exception($this->idioma->getText("O Valor depositado não pode ser inferior ao valor das taxas e tarifas cobradas"));
        }
        /*if ($deposito->tipoDeposito == \Utils\Constantes::DINHEIRO) {
            $deposito->aceitaNota = 1;
        }*/
        
        if ($deposito->valorCreditado < 0) {
            throw new \Exception("O valor creditado é inferior ao valor das taxas e encargos.");
        }
        
        $this->salvar($deposito);
    }
    
    public function carregar(Deposito &$deposito, $carregar = true, $carregarContaBancariaEmpresa = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($deposito);
        }
        
        if ($carregarContaBancariaEmpresa && $deposito->idContaBancariaEmpresa > 0) {
            $deposito->contaBancariaEmpresa = new ContaBancariaEmpresa(Array("id" => $deposito->idContaBancariaEmpresa));
            $contaBancariaEmpresaRn = new ContaBancariaEmpresaRn();
            $contaBancariaEmpresaRn->carregar($deposito->contaBancariaEmpresa, true, true);
        }
        
        if ($carregarUsuario && $deposito->idUsuario > 0) {
            $deposito->usuario = new Usuario(Array("id" => $deposito->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($deposito->usuario);
        }
        
        if ($carregarCliente && $deposito->idCliente > 0) {
            $deposito->cliente = new Cliente(Array("id" => $deposito->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($deposito->cliente);
        }
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarContaBancariaEmpresa = true, $carregarUsuario = true, 
            $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $deposito) {
            $this->carregar($deposito, false, $carregarContaBancariaEmpresa, $carregarUsuario, $carregarCliente);
            $lista[] = $deposito;
        }
        return $lista;
    }
    
    public function filtrar($idCliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $idContaBancariaEmpresa = null, 
            $tipoDeposito = "Q", $status = "T", $filtro = null, $qtdRegitros = "T", $boleto = false) {
        
        $where = Array();
        
        if ($idCliente > 0) {
            $where[] = " d.id_cliente = {$idCliente} ";
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " d.data_solicitacao BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        }
        
        if ($idContaBancariaEmpresa > 0) {
            $where[] = " d.id_conta_bancaria_empresa = {$idContaBancariaEmpresa} ";
        }
        
        if ($tipoDeposito != "Q" && !$boleto) {
            $where[] = " d.tipo_deposito = '{$tipoDeposito}' ";
        }
        
        $gerenciaNet = \Utils\Constantes::GERENCIA_NET;
        if (!$boleto) {
            $where[] = " d.tipo_deposito != '{$gerenciaNet}' ";
        } else{
            $where[] = " d.tipo_deposito = '{$gerenciaNet}' ";
        }
        
        if ($status != "T") {
            $where[] = " d.status = '{$status}' ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ("
                    . " (DATE_FORMAT(data_solicitacao, '%d/%m/%Y %H:%i:%S') LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                    . " (d.id LIKE '%{$filtro}%') OR "
                    . " (LOWER(valor_depositado) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(valor_creditado) LIKE LOWER('%{$filtro}%')) "
                    . ") ";
        }
        
        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT d.* FROM depositos d "
                . "INNER JOIN clientes c ON (c.id = d.id_cliente) "
                . " {$where} "
                . " ORDER BY d.data_solicitacao DESC"
                . " {$limit}; ";

        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $deposito = new Deposito($dados);
            $this->carregar($deposito, false, true, true, true);
            $lista[] = $deposito;            
        }
        //exit(print_r(sizeof($lista)));
        return $lista;
    }
    
    public function aprovar(Deposito $dep) {
        try {
            
            if (!$dep->id > 0) {
                throw new \Exception($this->idioma->getText("identificacaoInvalidaDeposito"));
            }
            $deposito = new Deposito(Array("id" => $dep->id));
            $this->carregar($deposito, true, false, false, true);
            
            if ($deposito->tipoDeposito != \Utils\Constantes::GERENCIA_NET) {
            
                $usuarioLogado = \Utils\Geral::getLogado();
                if (!\Utils\Geral::isUsuario()) {
                    throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
                }

                if ($usuarioLogado->tipo != \Utils\Constantes::ADMINISTRADOR) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoEfetuarOperacao"));
                }
            
            }
            
            $this->conexao->adapter->iniciar();
            
            
            if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO) {
                throw new \Exception($this->idioma->getText("depositoConfirmado"));
            }
            
            if ($deposito->tipoDeposito != \Utils\Constantes::GERENCIA_NET) {
                if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) {
                    throw new \Exception($this->idioma->getText("depositoCancelado"));
                }
            }

            $deposito->dataConfirmacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $deposito->idUsuario = ($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET ? 1483296812 : $usuarioLogado->id);
            $deposito->status = \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO;
            
            $this->conexao->update(
                    Array(
                        "status" => $deposito->status,
                        "id_usuario" => $deposito->idUsuario,
                        "data_confirmacao" => $deposito->dataConfirmacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                    ), 
                    Array(
                        "id" => $deposito->id
                    ));
            
        
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = $deposito->dataConfirmacao;
            $contaCorrenteReais->descricao = "Depósito Nº {$deposito->id}";
            $contaCorrenteReais->idCliente = $deposito->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->origem = 0;
            $contaCorrenteReais->valor = $deposito->valorCreditado;
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = $deposito->dataConfirmacao;
            $contaCorrenteReaisEmpresa->descricao = "Comissão Depósito Nº {$deposito->id} de {$deposito->cliente->nome}";
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresa->transferencia = 0;
            $contaCorrenteReaisEmpresa->valor = $deposito->valorComissao + $deposito->valorTarifa;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            
            
            
            // Começa a validação do Crédito de referência ou convite
            $cliente = new Cliente(Array("id" => $deposito->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $convite = false;
            $pagarComissao = false;
            $comissao = 0;
            $descricao = "";
            $idCliente = 0;
            
            /*
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            */
            
            if ($cliente->idReferencia > 0) {
                $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia, true);
                if ($clienteHasComissao != null) {
                    if ($clienteHasComissao->deposito > 0) { 
                        $descricao = $this->idioma->getText("pagamentoComissaoDepositoConvidado") . $cliente->nome;
                        $comissao = number_format(($deposito->valorComissao * ($clienteHasComissao->deposito / 100)), 2, ".", "");
                        $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" => $cliente->idReferencia)), $comissao, $descricao, false, $cliente->id, null, 5);
                    }
                }
            }
            
            
            /*
            if ($cliente->idReferencia > 0) {
                $idCliente = $cliente->idReferencia;
                $clienteHasLicencaRn = new ClienteHasLicencaRn();
                $licenca = $clienteHasLicencaRn->carregarLicencaCliente(new Cliente(Array("id" => $idCliente)));
                
                if ($licenca != null) {
                    $comissao = number_format(($deposito->valorComissao * ($licenca->licencaSoftware->comissao / 100)), 2, ".", "");
                    
                    $convite = false;
                    $pagarComissao = true;
                    $descricao = $this->idioma->getText("pagamentoComissaoDepositoReferencia") . $cliente->nome ;
                    
                } else {
                    $comissao = number_format(($deposito->valorComissao * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                    $convite = true;
                    $pagarComissao = true;
                    $descricao = $this->idioma->getText("pagamentoComissaoDepositoConvidado") . $cliente->nome;
                }
                
            } else if ($cliente->idClienteConvite > 0) {
                if ($cliente->comissaoConvitePago < 1) {
                    $idCliente = $cliente->idClienteConvite;
                    $comissao = number_format(($deposito->valorComissao * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                    $convite = true;
                    $pagarComissao = true;
                    $descricao = $this->idioma->getText("pagamentoComissaoDepositoConvidado") . $cliente->nome;
                }
            }
            
            if ($pagarComissao) {
                $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" => $idCliente)), $comissao, $descricao, $convite, $cliente->id, null, 5);
                $clienteRn->conexao->update(Array("comissao_convite_pago" => 1), Array("id"=> $cliente->id) );
                if ($convite) { 
                    $clienteConvidadoRn = new ClienteConvidadoRn($this->conexao->adapter);
                    $clienteConvidadoRn->setComissao(new Cliente(Array("id" => $idCliente)), $cliente->email, $comissao, "Depósito");
                }
            }
            */
            
            //$qtdDepositos = $this->getQuantidadeDepositosValidados($cliente);
           // $saqueRn = new SaqueRn();
            //$qtdSaques = $saqueRn->getQuantidadeSaquesValidados($cliente);
            
            /*if (($qtdDepositos + $qtdSaques) == 1) {
                \Lahar\Cadastro::estagioLead($cliente, 3);
            }
            
            if (AMBIENTE == "producao") { 
                // Emissão da NFE
                if ($deposito->valorComissao > 0) {
                    $dadosNF = \ENotasGW\NotaFiscal::emitir($deposito, $deposito->aceitaNota > 0);

                    $jsonNotaFiscal = \ENotasGW\NotaFiscal::consultar($dadosNF->nfeId);

                    $notaFiscal = new NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idDeposito = $deposito->id;
                    $notaFiscal->idCliente = $deposito->idCliente;

                    NotaFiscalRn::setNotaFiscalFromJson($notaFiscal, $jsonNotaFiscal);

                    $notaFiscalRn = new NotaFiscalRn($this->conexao->adapter);

                    $notaFiscalRn->salvar($notaFiscal);
                }
                
                
                if ($deposito->idCliente > 0) {
                    $cliente = new Cliente(Array("id" => $deposito->idCliente));
                    $clienteRn = new ClienteRn();
                    $clienteRn->conexao->carregar($cliente);

                    $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);
                    if ((strlen($celular) == 11 || strlen($celular) == 10) && $cliente->ddi == "55") {
                        $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                        $api->EnviaSMS("{$cliente->ddi}{$celular}", $this->idioma->getText("newCashDepositoAprovadoValorCreditado") . number_format($deposito->valorCreditado, 2, ",", ""));
                    }
                }
            }*/
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function getQuantidadeDepositosValidados(Cliente $cliente) {
        $idCliente = ($cliente != null ? " AND id_cliente = {$cliente->id}" : "");
        $status = \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO;
        
        $query = "SELECT COUNT(*) AS qtd FROM depositos WHERE status = '{$status}' {$idCliente};";
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $dados) {
            $qtd = $dados["qtd"];
        }
        
        return $qtd;
    }
    
    
    public function cancelar(Deposito $deposito) {
        $motivoCancelamento = $deposito->motivoCancelamento;
        try {
            $this->conexao->carregar($deposito);
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("depositoNaoEncontrado"));
        }
        if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO) {
            throw new \Exception($this->idioma->getText("depositoConfirmadoNaoPodeCancelado"));
        }
        if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) {
            throw new \Exception($this->idioma->getText("depositoCanceladoSistema"));
        }
        $usuario = \Utils\Geral::getLogado();
        if ($usuario == null) {
             throw new \Exception($this->idioma->getText("vocePrecisaLogadoExecutarOperacao"));
        }
        if (!$usuario instanceof Usuario || $usuario->tipo != \Utils\Constantes::ADMINISTRADOR) {
            throw new \Exception($this->idioma->getText("voceNaoTemPermissaoEssaOperacao"));
        }
        
        if (empty($motivoCancelamento)) {
            throw new \Exception($this->idioma->getText("necessarioMotivoCancelamento"));
        }
        $deposito->idUsuario = $usuario->id;
        $deposito->dataCancelamento = new \Utils\Data(date("d/m/Y H:i:s"));
        $deposito->motivoCancelamento = $motivoCancelamento;
        
        $this->conexao->update(
                Array(
                    "status" => \Utils\Constantes::STATUS_DEPOSITO_CANCELADO,
                    "data_cancelamento" => $deposito->dataCancelamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                    "id_usuario" => $deposito->idUsuario,
                    "motivo_cancelamento" => $deposito->motivoCancelamento
                ), 
                Array("id" => $deposito->id));
       
    }
    
    
    public function getQuantidadePorStatus() {
        $query = "SELECT status, COUNT(*) AS qtd, SUM(valor_depositado) AS valor FROM depositos GROUP BY status";
        
        $lista = Array(
            "cancelado" => Array(
                "valor" => 0.00,
                "qtd" => 0
            ),
            "confirmado" => Array(
                "valor" => 0.00,
                "qtd" => 0
            ),
            "pendente" => Array(
                "valor" => 0.00,
                "qtd" => 0
            )
        );
        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            switch ($dados["status"]) {
                case \Utils\Constantes::STATUS_DEPOSITO_CANCELADO:
                    $lista["cancelado"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
                case \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO:
                    $lista["confirmado"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
                case \Utils\Constantes::STATUS_DEPOSITO_PENDENTE:
                    $lista["pendente"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
            }
        }
        
        
        return $lista;
    }
    
    
    public function calcularQuantiadeHorasMediasValidacaoDeposito(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null)  {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            $where[] = " data_confirmacao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
            . " AVG(TIMESTAMPDIFF(hour, data_solicitacao, data_confirmacao)) AS horas "
            . "  FROM depositos "
            . " {$where}  ";
            
        $result = $this->conexao->adapter->query($query)->execute();
        $media = 0;
        foreach ($result as $dados) {
            $media = intval($dados["horas"]);
        }
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $max = ($media < $configuracao->prazoHorasValidacaoDepositos ? $configuracao->prazoHorasValidacaoDepositos : $media);
        
        return Array("media" => $media, "max" => $max);
        
    }
}

?>