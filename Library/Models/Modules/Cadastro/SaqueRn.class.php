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
class SaqueRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Saque());
        } else {
            $this->conexao = new GenericModel($adapter, new Saque());
        }
    }
    
    
    public function salvar(Saque &$saque) {
        
        $cliente = \Utils\Geral::getCliente();
        
        if ($cliente == null) {
            throw new \Exception($this->idioma->getText("vocePrecisaLogadoLancamento"));
        }
        
        if ($cliente->statusSaqueBrl < 1) {
            throw new \Exception($this->idioma->getText("solicitacaoDepositoSuspensa"));
        }
        
        if ($saque->id > 0) {
            
            $aux = new Saque(Array("id" => $saque->id));
            $this->conexao->carregar($aux);
            
            if ($aux->status != \Utils\Constantes::STATUS_SAQUE_PENDENTE) {
                throw new \Exception($this->idioma->getText("naoPOssivelAltSaque"));
            }
            
            $saque->idCliente = $aux->idCliente;
            $saque->idUsuario = $aux->idUsuario;
            $saque->status = $aux->status;
            $saque->dataSolicitacao = $aux->dataSolicitacao;
            $saque->dataDeposito = $aux->dataDeposito;
            $saque->comprovante = $aux->comprovante;
            $saque->notaFiscal = $aux->notaFiscal;
            $saque->tipoDeposito = $aux->tipoDeposito;
            $saque->aceitaNota = $aux->aceitaNota;
            
        } else {
            
            
            $saque->idCliente = $cliente->id;
            $saque->idUsuario = null;
            $saque->status = \Utils\Constantes::STATUS_DEPOSITO_PENDENTE;
            $saque->dataSolicitacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $saque->dataDeposito = null;
            $saque->comprovante = "";
            $saque->notaFiscal = "";
            $saque->dataDeposito = null;
            
            if (!$saque->aceitaNota > 0) {
                $saque->aceitaNota = 0;
            }
        }
        
        if (!$saque->idContaBancaria > 0) {
            throw new \Exception($this->idioma->getText("necessarioInformarConta"));
        }
        if (!$saque->idCliente > 0) {
            throw new \Exception($this->idioma->getText("necessarioIdentifarDepositante"));
        }
        
        $arrayStatus = Array(
            \Utils\Constantes::STATUS_DEPOSITO_CANCELADO,
            \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO,
            \Utils\Constantes::STATUS_DEPOSITO_PENDENTE
        );
        
        if (!in_array($saque->status, $arrayStatus)) {
            throw new \Exception("Status inválido");
        }
        
        if (!$saque->valorSaque > 0) {
            throw new \Exception($this->idioma->getText("valorPrecSaqueMaiorZero"));
        }
        
        if (!$saque->valorSacado > 0) {
            throw new \Exception($this->idioma->getText("valorSacadoMaiorZero"));
        }
        
        if (!$saque->valorComissao > 0) {
            $saque->valorComissao = 0;
        }
        
        
        if (!$saque->tarifaTed > 0) {
            $saque->tarifaTed = 0;
        }
        
        if (!$saque->taxaComissao > 0) {
            $saque->taxaComissao = 0;
        }
        
        ClienteHasCreditoRn::validar(new Cliente(Array("id" => $saque->idCliente)));
        
        unset($saque->cliente);
        unset($saque->usuario);
        unset($saque->contaBancaria);
        
        $this->conexao->salvar($saque);

    }
    
    
    
    public function solicitarSaque(Saque $saque) {
        try {
            $this->conexao->adapter->iniciar();
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("necessarioEstarLogadoOperacao"));
            }
            
            if ($cliente->statusSaqueBrl < 1) {
                throw new \Exception($this->idioma->getText("solicitacaoDepositoSuspensa"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn($this->conexao->adapter);
            $clienteRn->conexao->carregar($cliente);
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn($this->conexao->adapter);
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $saque->idCliente = $cliente->id;
            $saque->taxaComissao = 0;
            if ($cliente->considerarTaxaSaqueCliente) {
                $saque->taxaComissao = $cliente->taxaComissaoSaque;
            } else {
                $saque->taxaComissao = $configuracao->taxaSaque;
            }
            
            $saque->tarifaTed = $configuracao->tarifaTed;
            $contaBancariaEmpresaRn = new ContaBancariaEmpresaRn();
            $bancosEmpresa = $contaBancariaEmpresaRn->getIdsBancosEmpresa();
            
            $contaBancaria = new ContaBancaria();
            $contaBancaria->id = $saque->idContaBancaria;
            try {
                $contaBancariaRn = new ContaBancariaRn();
                $contaBancariaRn->conexao->carregar($contaBancaria);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("contaBancariaInvalida"));
            }
            
            if (in_array($contaBancaria->idBanco, $bancosEmpresa)) {
                //$saque->tarifaTed = 0;
            }

            if (!$saque->valorSaque > 0) {
                throw new \Exception($this->idioma->getText("valorDeveSerInformado"));
            }

            if ($saque->valorSaque < $configuracao->valorMinSaqueReais) {
                throw new \Exception($this->idioma->getText("valorMinimoSaque"). $configuracao->valorMinSaqueReais);
            }
            
            $saque->valorComissao = ($saque->valorSaque * ($saque->taxaComissao / 100));
            
            $saque->valorSacado = $saque->valorSaque - $saque->valorComissao - $saque->tarifaTed;

            if ($saque->valorSacado <= 0) {
                throw new \Exception($this->idioma->getText("valorSolicitadoValorTaxas"));
            }
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            if ($saldo < $saque->valorSaque) {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            ClienteHasCreditoRn::validar($cliente);
            
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Saque";
            $contaCorrenteReais->idCliente = $cliente->id;
            $contaCorrenteReais->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = $saque->valorSaque;
            $contaCorrenteReais->origem = 4;
            $contaCorrenteReais->idReferenciado = $saque->id;
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta(new Cliente(Array("id" => $cliente->id)));
            if ($saldo < 0) {
                $contaCorrenteReaisRn->excluir($contaCorrenteReais);
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            $this->salvar($saque);
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            try{
                if (isset($contaCorrenteReais) && $contaCorrenteReais->id > 0) {
                    $contaCorrenteReaisRn->excluir($contaCorrenteReais);
                }
            } catch (\Exception $ex) {

            }
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function carregar(Saque &$saque, $carregar = true, $carregarContaBancaria = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($saque);
        }
        
        if ($carregarContaBancaria && $saque->idContaBancaria > 0) {
            $saque->contaBancaria = new ContaBancaria(Array("id" => $saque->idContaBancaria));
            $contaBancariaRn = new ContaBancariaRn();
            $contaBancariaRn->carregar($saque->contaBancaria, true, true);
        }
        
        if ($carregarUsuario && $saque->idUsuario > 0) {
            $saque->usuario = new Usuario(Array("id" => $saque->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($saque->usuario);
        }
        
        if ($carregarCliente && $saque->idCliente > 0) {
            $saque->cliente = new Cliente(Array("id" => $saque->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($saque->cliente);
        }
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarContaBancaria = true, $carregarUsuario = true, 
            $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $deposito) {
            $this->carregar($deposito, false, $carregarContaBancaria, $carregarUsuario, $carregarCliente);
            $lista[] = $deposito;
        }
        return $lista;
    }
    
    public function filtrar($idCliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $idBanco = null, 
            $status = "T", $filtro = null, $limit = "T") {
        
        $where = Array();
        
        if (\Utils\Geral::isCliente()) {
            $idCliente = \Utils\Geral::getCliente()->id;
        }
        
        if ($idCliente > 0) {
            $where[] = " s.id_cliente = {$idCliente} ";
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal")  );
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " s.data_solicitacao BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        }
        
        if ($idBanco > 0) {
            $where[] = " s.id_conta_bancaria = {$idBanco} ";
        }

        if ($status != "T") {
            $where[] = " s.status = '{$status}' ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ("
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                    . " (s.id LIKE '%{$filtro}%') OR "
                    . " (LOWER(valor_saque) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(valor_sacado) LIKE LOWER('%{$filtro}%')) "
                    . ") ";
        }
        
        $limitString = "";
        if ($limit != "T") {
            $limitString = " limit {$limit}";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT s.* FROM saques s "
                . " INNER JOIN clientes c ON (c.id = s.id_cliente) "
                . " LEFT JOIN contas_bancarias cb ON (cb.id = s.id_conta_bancaria) "
                . " {$where} "
                . " ORDER BY s.data_solicitacao DESC"
                . " {$limitString};";
 
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $saque = new Saque($dados);
            $this->carregar($saque, false, true, true, true);
            $lista[] = $saque;
        }
        return $lista;
    }
    
    
    
    public function aprovar(Saque $saq) {
        try {
            
            $usuarioLogado = \Utils\Geral::getLogado();
            if (!\Utils\Geral::isUsuario()) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
            }
            
            if ($usuarioLogado->tipo != \Utils\Constantes::ADMINISTRADOR) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermissaoEfetuarOperacao"));
            }
            
            $this->conexao->adapter->iniciar();
            
            if (!$saq->id > 0) {
                throw new \Exception($this->idioma->getText("identificacaoSaqueInvalida"));
            }
            
            $saque = new Saque(Array("id" => $saq->id));
            $this->carregar($saque, true, false, false, true);
            
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CONFIRMADO) {
                throw new \Exception($this->idioma->getText("saqueJaConfirmado"));
            }
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CANCELADO) {
                throw new \Exception($this->idioma->getText("saqueJaCancelado"));
            }
            
            $saque->comprovante = $saq->comprovante;
            
            if (empty($saque->comprovante)) {
                throw new \Exception($this->idioma->getText("necessarioEnviarCompDeposito"));
            }
            
            
            $saque->dataDeposito = new \Utils\Data(date("d/m/Y H:i:s"));
            $saque->idUsuario = $usuarioLogado->id;
            $saque->status = \Utils\Constantes::STATUS_SAQUE_CONFIRMADO;
            
            
            
            $this->conexao->update(
                    Array(
                        "status" => $saque->status,
                        "data_deposito" => $saque->dataDeposito->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "comprovante" => $saque->comprovante,
                        "id_usuario" => $saque->idUsuario
                    ), 
                    Array(
                        "id" => $saque->id
                    ));
            
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = $saque->dataDeposito;
            $contaCorrenteReaisEmpresa->descricao = "Comissão Saque Nº {$saque->id} de {$saque->cliente->nome}";
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresa->transferencia = 0;
            $contaCorrenteReaisEmpresa->valor = number_format(($saque->valorComissao + $saque->tarifaTed), 2, ".", "");
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            
            // Começa a validação do Crédito de referência ou convite
            $cliente = new Cliente(Array("id" => $saque->idCliente));
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
                $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia);
                if ($clienteHasComissao != null) {
                    if ($clienteHasComissao->saque > 0) { 
                        $descricao = "Pagamento comissão saque Referência {$cliente->nome} ";
                        $comissao = number_format(($saque->valorComissao * ($clienteHasComissao->saque / 100)), 2, ".", "");
                        $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" => $cliente->idReferencia)), $comissao, $descricao, false, $cliente->id, null, 4);
                    }
                }
            }
            
            /*
            if ($cliente->idReferencia > 0) {
                $idCliente = $cliente->idReferencia;
                $clienteHasLicencaRn = new ClienteHasLicencaRn();
                $licenca = $clienteHasLicencaRn->carregarLicencaCliente(new Cliente(Array("id" => $idCliente)));
                
                if ($licenca != null) {
                    $comissao = number_format(($saque->valorComissao * ($licenca->licencaSoftware->comissao / 100)), 2, ".", "");
                    
                    $convite = false;
                    $pagarComissao = true;
                    $descricao = "Pagamento comissão saque Referência {$cliente->nome} ";
                    
                } else {
                    $comissao = number_format(($saque->valorComissao * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                    $convite = true;
                    $pagarComissao = true;
                    $descricao = "Pagamento comissão saque convidado {$cliente->nome} ";
                }
                
            } else if ($cliente->idClienteConvite > 0) {
                if ($cliente->comissaoConvitePago < 1) {
                    $idCliente = $cliente->idClienteConvite;
                    $comissao = number_format(($saque->valorComissao * ($configuracao->comissaoConvite / 100)), 2, ".", "");
                    $convite = true;
                    $pagarComissao = true;
                    $descricao = "Pagamento comissão saque convidado {$cliente->nome} ";
                }
            }
            
            if ($pagarComissao) {
                $clienteRn->creditarComissaoReferencia(new Cliente(Array("id" => $idCliente)), $comissao, $descricao, $convite, $cliente->id, null, 4);
                $clienteRn->conexao->update(Array("comissao_convite_pago" => 1), Array("id"=> $cliente->id) );
                if ($convite) { 
                    $clienteConvidadoRn = new ClienteConvidadoRn($this->conexao->adapter);
                    $clienteConvidadoRn->setComissao(new Cliente(Array("id" => $idCliente)), $cliente->email, $comissao, "Saque");
                }
            }
            */
            
            $qtdSaques = $this->getQuantidadeSaquesValidados($cliente);
            $depositoRn = new DepositoRn();
            $qtdDepositos = $depositoRn->getQuantidadeDepositosValidados($cliente);
            if (($qtdDepositos + $qtdSaques) == 1) {
                \Lahar\Cadastro::estagioLead($cliente, 3);
            }
            
            if (AMBIENTE == "producao") { 
                
                if ($saque->valorComissao > 0) {
                    $dadosNF = \ENotasGW\NotaFiscal::emitir($saque, $saque->aceitaNota > 0);

                    $jsonNotaFiscal = \ENotasGW\NotaFiscal::consultar($dadosNF->nfeId);

                    $notaFiscal = new NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idSaque = $saque->id;
                    $notaFiscal->idCliente = $saque->idCliente;

                    NotaFiscalRn::setNotaFiscalFromJson($notaFiscal, $jsonNotaFiscal);

                    $notaFiscalRn = new NotaFiscalRn($this->conexao->adapter);

                    $notaFiscalRn->salvar($notaFiscal);
                    
                }
                
                if ($saque->idCliente) {
                    $cliente = new Cliente(Array("id" => $saque->idCliente));
                    $clienteRn = new ClienteRn();
                    $clienteRn->conexao->carregar($cliente);

                    $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);
                    
                    if ((strlen($celular) == 11 || strlen($celular) == 10) && $cliente->ddi == "55") {
                        $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                        $api->EnviaSMS("{$cliente->ddi}{$celular}", $this->idioma->getText("newCashSaqueAprovado") . number_format($saque->valorSacado, 2, ",", ""));
                    }
                }
            }
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function getQuantidadeSaquesValidados(Cliente $cliente) {
        $idCliente = ($cliente != null ? " AND id_cliente = {$cliente->id}" : "");
        $status = \Utils\Constantes::STATUS_SAQUE_CONFIRMADO;
        
        $query = "SELECT COUNT(*) AS qtd FROM saques WHERE status = '{$status}' {$idCliente};";
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $dados) {
            $qtd = $dados["qtd"];
        }
        
        return $qtd;
    }
    
    
    
    public function cancelar(Saque $saque) {
        try {
            $this->conexao->adapter->iniciar();
            $motivo = $saque->motivoCancelamento;
            try {
                $this->carregar($saque, true, false, false, true);
            } catch (Exception $ex) {
                throw new \Exception($this->idioma->getText("saqueNaoEncontrado"));
            }
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CONFIRMADO) {
                throw new \Exception($this->idioma->getText("saqueJaConfirmadoNaPodeCancelado"));
            }
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CANCELADO) {
                throw new \Exception($this->idioma->getText("saqueJaCanceladoSistema"));
            }

            $this->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::STATUS_SAQUE_CANCELADO, 
                        "motivo_cancelamento" => $motivo,
                        "data_cancelamento" => date("Y-m-d H:i:s")
                    ), 
                    Array("id" => $saque->id));
            
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Estorno Saque Nº {$saque->id}";
            $contaCorrenteReais->idCliente = $saque->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->origem = 6;
            $contaCorrenteReais->valor = $saque->valorSaque;
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = "Estorno Comissão Saque Nº {$saque->id} de {$saque->cliente->nome}";
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReaisEmpresa->transferencia = 0;
            $contaCorrenteReaisEmpresa->valor = $saque->valorComissao;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    public function getQuantidadePorStatus() {
        $query = "SELECT status, COUNT(*) AS qtd, SUM(valor_sacado) AS valor FROM saques GROUP BY status";
        
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
                case \Utils\Constantes::STATUS_SAQUE_CANCELADO:
                    $lista["cancelado"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
                case \Utils\Constantes::STATUS_SAQUE_CONFIRMADO:
                    $lista["confirmado"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
                case \Utils\Constantes::STATUS_SAQUE_PENDENTE:
                    $lista["pendente"] = Array(
                        "valor" => number_format($dados["valor"], 2, '.', ""),
                        "qtd" => $dados["qtd"]
                    );
                    break;
            }
        }
        
        
        return $lista;
    }
    
    
    
    
    public function calcularQuantiadeHorasMediasValidacaoSaque(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null)  {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            $where[] = " data_deposito BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
            . " AVG(TIMESTAMPDIFF(hour, data_solicitacao, data_deposito)) AS horas "
            . "  FROM saques "
            . " {$where}  ";
            
        $result = $this->conexao->adapter->query($query)->execute();
        $media = 0;
        foreach ($result as $dados) {
            $media = intval($dados["horas"]);
        }
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $max = ($media < $configuracao->prazoHorasValidacaoSaques ? $configuracao->prazoHorasValidacaoSaques : $media);
        
        return Array("media" => $media, "max" => $max);
        
    }
}

?>