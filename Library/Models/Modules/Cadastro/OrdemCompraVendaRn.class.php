<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade OrdemCompraVenda
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OrdemCompraVendaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new OrdemCompraVenda());
        } else {
            $this->conexao = new GenericModel($adapter, new OrdemCompraVenda());
        }
    }
    
    
    public function salvar(OrdemCompraVenda &$ordemCompraVenda) {
        
        if ($ordemCompraVenda->id > 0) {
            
            $aux = new Saque(Array("id" => $ordemCompraVenda->id));
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
        } else {
            $cliente = \Utils\Geral::getCliente();
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoLancamento"));
            }
            
            $saque->idCliente = $cliente->id;
            $saque->idUsuario = null;
            $saque->status = \Utils\Constantes::STATUS_DEPOSITO_PENDENTE;
            $saque->dataSolicitacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $saque->dataDeposito = null;
            $saque->comprovante = "";
            $saque->notaFiscal = "";
            $saque->dataDeposito = null;
        }
        
        if (!$saque->idContaBancaria > 0) {
            //throw new \Exception("É necessário selecionar uma conta bancária para depósito");
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
            throw new \Exception($this->idioma->getText("statusInvalido"));
        }
        
        if (!$saque->valorSaque > 0) {
            throw new \Exception($this->idioma->getText("valorPrecSaqueMaiorZero"));
        }
        
        if (!$saque->valorSacado > 0) {
            throw new \Exception($this->idioma->getText("valorPrecSaqueMaiorZero"));
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

            if (!$saque->valorSaque > 0) {
                throw new \Exception($this->idioma->getText("valorDeveSerInformado"));
            }

            $saque->valorComissao = ($saque->valorSaque * ($saque->taxaComissao / 100));
            
            $saque->valorSacado = $saque->valorSaque - $saque->valorComissao - $saque->tarifaTed;

            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Saque";
            $contaCorrenteReais->idCliente = $cliente->id;
            $contaCorrenteReais->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = $saque->valorSaque;
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
            throw new \Exception($e);
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
            $tipoDeposito = "Q", $status = "T", $filtro = null) {
        
        $where = Array();
        
        if (\Utils\Geral::isCliente()) {
            $idCliente = \Utils\Geral::getCliente()->id;
        }
        
        if ($idCliente > 0) {
            $where[] = " s.id_cliente = {$idCliente} ";
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " s.data_solicitacao BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        }
        
        if ($idBanco > 0) {
            $where[] = " cb.id_banco = {$idBanco} ";
        }
        
        if ($tipoDeposito != "Q") {
            $where[] = " s.tipo_deposito = '{$tipoDeposito}' ";
        }
        if ($status != "T") {
            $where[] = " s.status = '{$status}' ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ("
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%') "
                    . ") ";
        }
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT s.* FROM saques s "
                . " INNER JOIN clientes c ON (c.id = s.id_cliente) "
                . " LEFT JOIN contas_bancarias cb ON (cb.id = s.id_conta_bancaria) "
                . " {$where} "
                . " ORDER BY s.data_solicitacao DESC;";
                
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
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
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
            
            $saque->idContaBancaria = $saq->idContaBancaria;
            $saque->valorSaque = $saq->valorSaque;
            $saque->tipoDeposito = $saq->tipoDeposito;
            $saque->notaFiscal = $saq->notaFiscal;
            $saque->comprovante = $saq->comprovante;
            
            if (empty($saque->comprovante)) {
                throw new \Exception($this->idioma->getText("necessarioEnviarCompDeposito"));
            }
            if (empty($saque->notaFiscal)) {
                //throw new \Exception("É necessário enviar a nota fiscal do serviço");
            }
            
            $saque->dataDeposito = new \Utils\Data(date("d/m/Y H:i:s"));
            $saque->idUsuario = $usuarioLogado->id;
            $saque->status = \Utils\Constantes::STATUS_SAQUE_CONFIRMADO;
            
            $arrayTipos = Array(
                \Utils\Constantes::DOC,
                \Utils\Constantes::TED
            );

            if (!in_array($saque->tipoDeposito, $arrayTipos)) {
                throw new \Exception($this->idioma->getText("necessarioDepositarTipoDeposito"));
            }
            
            if (!$saque->idContaBancaria > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarConta"));
            }
            
            if (!$saque->valorSaque > 0) {
                throw new \Exception($this->idioma->getText("valorPrecSaqueMaiorZero"));
            }

            if (!$saque->valorSacado > 0) {
                throw new \Exception($this->idioma->getText("valorDepositadsoMaiorQueZeroCliente"));
            }

            if (!$saque->valorComissao > 0) {
                $saque->valorComissao = 0;
            }

            if (!$saque->taxaComissao > 0) {
                $saque->taxaComissao = 0;
            }
            
            $this->conexao->update(
                    Array(
                        "id_conta_bancaria" => $saque->idContaBancaria,
                        "valor_comissao" => number_format($saque->valorComissao, 2, ".", ""),
                        "status" => $saque->status,
                        "valor_saque" => number_format($saque->valorSaque, 2, ".", ""),
                        "id_usuario" => $saque->idUsuario,
                        "taxa_comissao" => number_format($saque->taxaComissao, 2, ".", ""),
                        "valor_sacado" => number_format($saque->valorSacado, 2, ".", ""),
                        "data_deposito" => $saque->dataDeposito->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "tipo_deposito" => $saque->tipoDeposito,
                        "nota_fiscal" => $saque->notaFiscal,
                        "comprovante" => $saque->comprovante
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
            $contaCorrenteReaisEmpresa->valor = $saque->valorComissao;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    public function cancelar(Saque $saque) {
        try {
            $this->conexao->adapter->iniciar();
            try {
                $this->carregar($saque, true, false, false, true);
            } catch (Exception $ex) {
                throw new \Exception($this->idioma->getText("saqueNaoEncontrado"));
            }
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CONFIRMADO) {
                throw new \Exception($this->idioma->getText("saqueJaConfirmadoNaPodeCancelado") );
            }
            if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CANCELADO) {
                throw new \Exception($this->idioma->getText("saqueJaCanceladoSistema"));
            }

            $this->conexao->update(Array("status" => \Utils\Constantes::STATUS_SAQUE_CANCELADO), Array("id" => $saque->id));
            
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Estorno Saque Nº {$saque->id}";
            $contaCorrenteReais->idCliente = $saque->idCliente;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
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
}

?>