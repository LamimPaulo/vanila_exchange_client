<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Banco
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ContaCorrenteReaisRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    /**
     *
     * @var \Utils\PropertiesUtils 
     */
    public $idioma=null;
    public $enviarNotificacao = true;
    
    public function __construct(\Io\BancoDados $adapter = null, $enviarNotificacao = true) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaCorrenteReais());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaCorrenteReais());
        }
        $this->enviarNotificacao = $enviarNotificacao;
    }
    
    
    public function gerarContaCorrente(ContaCorrenteReais &$contaCorrenteReais, $token = null) {
        $novo = ($contaCorrenteReais->id <= 0);
    
            if ($contaCorrenteReais->id > 0) {
                $aux = new ContaCorrenteReais(Array("id" => $contaCorrenteReais->id));
                $this->conexao->carregar($aux);
                $contaCorrenteReais->dataCadastro = $aux->dataCadastro;
                
                
                if ($aux->comissaoConvidado > 0) {
                    throw new \Exception("Esse registro não pode ser alterado");
                }
                
                if ($aux->comissaoLicenciado > 0) {
                    throw new \Exception("Esse registro não pode ser alterado");
                }
            } else {
                $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            }

            if (!$contaCorrenteReais->transferencia > 0) {
                $contaCorrenteReais->transferencia = 0;
            }
            
            
            if (!$contaCorrenteReais->orderBook > 0) {
                $contaCorrenteReais->orderBook = 0;
            }
            
            if (empty($contaCorrenteReais->descricao)) {
                $contaCorrenteReais->descricao = "";
                //throw new \Exception("É necessário informar a descrição do lançamento");
            }

            if (!isset($contaCorrenteReais->data->data) || $contaCorrenteReais->data->data == null) {
                throw new \Exception("É necessário informar a data do lançamento");
            }

            if (!$contaCorrenteReais->idCliente > 0) {
                throw new \Exception("É necessário informar a identificação do cliente");
            }

            if ($contaCorrenteReais->tipo != \Utils\Constantes::ENTRADA && $contaCorrenteReais->tipo != \Utils\Constantes::SAIDA) {
                throw new \Exception("Tipo de movimento inválido");
            }

            if ($contaCorrenteReais->valor < 0) {
                $clienteRn = new ClienteRn();
                $cliente = new Cliente();
                $cliente->id = $contaCorrenteReais->idCliente;
                $clienteRn->conexao->carregar($cliente);
                $cliente->status = 2;
                $clienteRn->alterarStatusCliente($cliente);
                throw new Exception("O valor precisa ser maior que zero");
            }

            if (!$contaCorrenteReais->valorTaxa > 0) {
                $contaCorrenteReais->valorTaxa = 0;                
            }
            
            if (!is_numeric($contaCorrenteReais->comissaoConvidado) || !$contaCorrenteReais->comissaoConvidado > 0) {
                $contaCorrenteReais->comissaoConvidado = 0;
            }

            if (!is_numeric($contaCorrenteReais->comissaoLicenciado) || !$contaCorrenteReais->comissaoLicenciado > 0) {
                $contaCorrenteReais->comissaoLicenciado = 0;
            }
            
            if (!is_numeric($contaCorrenteReais->origem)) {
                $contaCorrenteReais->origem = 0;
            }
            
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                if (strpos($ip, ',') !== false) {
                    $ip = substr($ip, 0, strpos($ip, ','));
                }
            $contaCorrenteReais->idSession = session_id();
            $contaCorrenteReais->ipSession = $ip;
            
            unset($contaCorrenteReais->cliente);
            unset($contaCorrenteReais->clienteDestino);
            $this->conexao->salvar($contaCorrenteReais);
            
            /*
            if ($contaCorrenteReais->tipo == \Utils\Constantes::ENTRADA) {
                SaldoClienteRn::creditar($contaCorrenteReais->valor, $contaCorrenteReais->idCliente, 1);
            } else {
                SaldoClienteRn::debitar($contaCorrenteReais->valor, $contaCorrenteReais->idCliente, 1);
            }
            */
            
            if ($novo) {
                $descricao = "Cadastrou a conta corrente {$contaCorrenteReais->id}.";
            } else {
                $descricao = "Alterou a conta corrente {$contaCorrenteReais->id}.";
            }
            
            if (\Utils\Geral::isLogado()) {
                $logContaCorrenteReaisRn = new LogContaCorrenteReaisRn($this->conexao->adapter);
                $logContaCorrenteReaisRn->salvar($contaCorrenteReais, $descricao, $token);
            }
        
    }
    
    public function salvar(ContaCorrenteReais &$contaCorrenteReais, $token = null) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            $this->gerarContaCorrente($contaCorrenteReais, $token);
        
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
        
    }
    
    public function resumo($filtro = null) {
        $where = Array();
        
        if (!empty($filtro)) {
            $where[] = " LOWER(c.nome) LIKE LOWER('%{$filtro}%') ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT c.id, c.nome, cc.tipo, SUM(cc.valor) AS valor "
                . " FROM conta_corrente_reais cc RIGHT JOIN clientes c ON (cc.id_cliente = c.id) "
                . " {$where} "
                . " GROUP BY c.id, c.nome, cc.tipo  "
                . " ORDER BY c.nome";
                
                
        $lista = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            if (!isset($lista[$dados["id"]])) {
                $lista[$dados["id"]] = Array(
                    "nome" => $dados["nome"],
                    "id" => $dados["id"],
                    "entrada" => 0,
                    "saida" => 0
                );
            }
            
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                $lista[$dados["id"]]["entrada"] = $dados["valor"];
            } else {
                $lista[$dados["id"]]["saida"] = $dados["valor"];
            }
        }
        
        return $lista;
    }
    
    public function filtrar($idCliente, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = 'T', $filtro = null, $transferencia = "T", $qtdRegitros = "T", $orderBook = false, $carregarCliente = true, $carregarClienteDestino = true) {
        
        if ($idCliente <= 0) {
            $idCliente = (\Utils\Geral::isCliente() ? \Utils\Geral::getCliente()->id : 0);
        }
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception($this->idioma->getText("dataInicialInformada"));
        }
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception($this->idioma->getText("dataFinalInformada"));
        }
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
        }
        
        $where = Array();
        $whereHistorico = Array();
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(cc.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " (LOWER(c.descricao) LIKE LOWER('%{$filtro}%') ) OR "
                    . " (CAST(c.id AS CHAR(100))  LIKE LOWER('%{$filtro}%') )  "
                    . " )  ";
            $whereHistorico[] = " ( "
                    . " ( LOWER(c.descricao) NOT LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( CAST(c.id AS CHAR(100))  NOT LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        $where[] = " c.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        $whereHistorico[] = " (c.data < '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' OR c.data > '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}') ";
    
        if ($idCliente > 0) {
            $where[] = " c.id_cliente = {$idCliente} ";
            $whereHistorico[] = " c.id_cliente = {$idCliente} ";
        }
        if ($tipo != "T") {
            $where[] = " tipo = '{$tipo}' ";
            $whereHistorico[] = " tipo != '{$tipo}' ";
        }
        
        if ($transferencia != "T") {
            $where[] = " c.transferencia = ".($transferencia == "S" ? "1" : "0")." ";
            $whereHistorico[] = " c.transferencia = ".($transferencia == "S" ? "0" : "1")." ";
        }
        
        
        if ($orderBook) {
            $where[] = " c.order_book = 1 ";
            $whereHistorico[] = " c.order_book = 0 ";
        } else {
            $where[] = " c.order_book = 0 ";
            $whereHistorico[] = " c.order_book = 1 ";
        }
        
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : " ");
        $whereHistorico = (sizeof($whereHistorico) > 0 ? " WHERE " . implode(" AND ", $whereHistorico) : " ");
        
        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }
        
        
        $query = "SELECT c.*, c.tipo "
                . " FROM conta_corrente_reais c "
                . " INNER JOIN clientes cc ON (c.id_cliente = cc.id) "
                . " {$where} "
                . " ORDER BY data DESC {$limit};";
        
        $queryHistorico = "SELECT SUM(valor) As valor, tipo  FROM conta_corrente_reais c {$whereHistorico} GROUP BY c.tipo;";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $contaCorrenteReais = new ContaCorrenteReais($dados);
            $this->carregar($contaCorrenteReais, false, $carregarCliente, $carregarClienteDestino);
            $lista[] = $contaCorrenteReais;
        }
        
        
        $resultHistorico = $this->conexao->adapter->query($queryHistorico)->execute();
        $entradas = 0;
        $saidas = 0;
        foreach ($resultHistorico as $dados) {
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                $entradas = $dados["valor"];
            } else {
                $saidas = $dados["valor"];
            }
        }
        
        return Array("lista" => $lista, "entradas" => $entradas, "saidas" => $saidas);
    }
    
    
    public function calcularSaldoConta(Cliente $cliente, $saldoBloqueado = false, $desconsiderarCredito = false) {
        
        if (!$cliente->id > 0) {
            throw new \Exception("É necessário informar a identificação do cliente");
        }
        
        $query = " SELECT SUM(valor) AS valor, tipo FROM conta_corrente_reais WHERE id_cliente = {$cliente->id} GROUP BY tipo;";
        $entrada = 0;
        $saida = 0;
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                $entrada = $dados["valor"];
            } else {
                $saida = $dados["valor"];
            }
        }
        
        if (!$desconsiderarCredito) {
            $clienteHasCredito = ClienteHasCreditoRn::get($cliente->id, 1, true);
            
            if ($clienteHasCredito != null && $clienteHasCredito->ativo > 0) {
                $entrada += number_format($clienteHasCredito->volumeCredito, 4, ".", "");
            }
        }
        
        $saldo = $entrada - $saida;
        $paridade = new Paridade(Array("id" => 1));
        $paridadeRn = new ParidadeRn();
        $paridadeRn->carregar($paridade, true, true, true);
        $orderBookRn = new OrderBookRn();
        $saldoOrdens = $orderBookRn->getValorTotalOrdensReais($cliente, $paridade);
            
        if ($saldoBloqueado) {
            return Array("saldo" => number_format($saldo - $saldoOrdens, 2, ".", ""), "bloqueado" => number_format($saldoOrdens, 2, ".", ""));
        } else {
            
            return number_format($saldo - $saldoOrdens, 2, ".", "");
        }
        
    }
    
    
    
    public function carregar(ContaCorrenteReais &$contaCorrenteReais, $carregar = true, $carregarCliente = true, $carregarClienteDestino = true) {
        if ($carregar) {
            $this->conexao->carregar($contaCorrenteReais);
        }
        
        if ($carregarCliente && $contaCorrenteReais->idCliente > 0) {
            $contaCorrenteReais->cliente = new Cliente(Array("id" => $contaCorrenteReais->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($contaCorrenteReais->cliente);
        }
        
        if ($carregarClienteDestino && $contaCorrenteReais->idClienteDestino > 0) {
            $contaCorrenteReais->clienteDestino = new Cliente(Array("id" => $contaCorrenteReais->idClienteDestino));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($contaCorrenteReais->clienteDestino);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarClienteDestino = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $contaCorrenteReais) {
            $this->carregar($contaCorrenteReais, false, $carregarCliente, $carregarClienteDestino);
            $lista[] = $contaCorrenteReais;
        }
        return $lista;
    }
    
    public function excluir(ContaCorrenteReais &$contaCorrenteReais) {
        try {
            
            $logContaCorrenteReaisRn = new LogContaCorrenteReaisRn($this->conexao->adapter);
            
            $logContaCorrenteReaisRn->conexao->delete("id_conta_corrente_reais = {$contaCorrenteReais->id}");
            $this->conexao->excluir($contaCorrenteReais);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function transferir(Cliente $clienteFrom, Cliente $clienteTo, $valor, $descricao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $clienteRn = new ClienteRn($this->conexao->adapter);
            try{
                $clienteRn->conexao->carregar($clienteFrom);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteDebitoIdentificado"));
            }
            try{
                $clienteRn->conexao->carregar($clienteTo);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("contaCorrenteReais2"));
            }
            
            if (!$valor > 0) {
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
            }
            
            
            $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $valorTransferencia = number_format(($valor), 8, ".", "");
            
            $saldoEmConta = $this->calcularSaldoConta($clienteFrom, false, true);
            if ($saldoEmConta < $valorTransferencia) {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            ClienteHasCreditoRn::validar($clienteFrom);
            
            $contaCorrenteFrom = new ContaCorrenteReais();
            $contaCorrenteFrom->id = 0;
            $contaCorrenteFrom->idCliente = $clienteFrom->id;
            $contaCorrenteFrom->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteFrom->descricao = "Transferências entre contas para {$clienteTo->nome}.";
            $contaCorrenteFrom->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteFrom->valor = $valorTransferencia;
            $contaCorrenteFrom->transferencia = 1;
            $contaCorrenteFrom->idClienteDestino = $clienteTo->id;
            $contaCorrenteFrom->valorTaxa = $configuracao->taxaTransferenciaInternaReais;
            
            $this->salvar($contaCorrenteFrom);
            
            $saldo = $this->calcularSaldoConta($clienteFrom);
            if ($saldo < 0) {
                $this->conexao->excluir($contaCorrenteFrom);
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            
            $contaCorrenteTo = new ContaCorrenteReais();
            $contaCorrenteTo->id = 0;
            $contaCorrenteTo->idCliente = $clienteTo->id;
            $contaCorrenteTo->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteTo->descricao = "Transferências entre contas de {$clienteFrom->nome}.";
            $contaCorrenteTo->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteTo->valor = $valor - $configuracao->taxaTransferenciaInternaReais;
            $contaCorrenteTo->transferencia = 1;
            
            $this->salvar($contaCorrenteTo);
            
            if ($configuracao->taxaTransferenciaInternaReais > 0) {
                $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
                $contaCorrenteReaisEmpresa->id = 0;
                $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteReaisEmpresa->descricao = "Taxa sobre Transferências entre contas Saída: {$clienteFrom->nome} Entrada: {$clienteTo->nome}.";
                $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteReaisEmpresa->valor = $configuracao->taxaTransferenciaInternaReais;
                $contaCorrenteReaisEmpresa->transferencia = 1;
                $contaCorrenteEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
                $contaCorrenteEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            }
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function debitarDoSaldo(Cliente $cliente, $valor, $descricao, $throwExcetion = true, $creditarEmpresa = true) {
        
        $contaCorrenteReais = new ContaCorrenteReais();
        $contaCorrenteReais->id = 0;
        $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteReais->descricao = $descricao;
        $contaCorrenteReais->idCliente = $cliente->id;
        $contaCorrenteReais->tipo = \Utils\Constantes::SAIDA;
        $contaCorrenteReais->transferencia = 0;
        $contaCorrenteReais->valor = $valor;
        
        $contaCorrenteReais->comissaoConvidado = 0;
        $contaCorrenteReais->comissaoLicenciado = 0;
        $contaCorrenteReais->clienteDestino = null;
        $contaCorrenteReais->idReferenciado = null;
        $contaCorrenteReais->orderBook = 0;
        $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:I:ss"));
        
        $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
        $contaCorrenteReaisRn->salvar($contaCorrenteReais);
        
        $saldo = $this->calcularSaldoConta($cliente);
        
        if ($saldo < 0) {
            $this->conexao->excluir($contaCorrenteReais);
            if ($throwExcetion) {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            } else {
                return null;
            }
        }
        
        if ($creditarEmpresa) {
            $contaCorrenteEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteEmpresa->bloqueado = 1;
            $contaCorrenteEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresa->descricao = $descricao;
            $contaCorrenteEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteEmpresa->transferencia = 0;
            $contaCorrenteEmpresa->valor = $valor;
            $contaCorrenteEmpresa->id = 0;
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteEmpresa);
        }
        
        return $contaCorrenteReais;
    }
    
    
    
    public function transferirParaEmpresa($valor, $descricao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $cliente = \Utils\Geral::getCliente();
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
            }
            
            if (!$valor > 0) {
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
            }
            
            $contaCorrenteFrom = new ContaCorrenteReais();
            $contaCorrenteFrom->id = 0;
            $contaCorrenteFrom->idCliente = $cliente->id;
            $contaCorrenteFrom->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteFrom->descricao = $descricao;
            $contaCorrenteFrom->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteFrom->valor = $valor;
            $contaCorrenteFrom->transferencia = 1;
            $contaCorrenteFrom->idClienteDestino = null;
            $contaCorrenteFrom->valorTaxa = 0;
            
            $saldo = $this->calcularSaldoConta($cliente, false, true);
            if ($saldo < $contaCorrenteFrom->valor) {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            ClienteHasCreditoRn::validar($cliente);
            
            $this->salvar($contaCorrenteFrom);
            
            
            
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = "Transferência de {$cliente->nome}.";
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresa->valor = $valor;
            $contaCorrenteReaisEmpresa->transferencia = 1;
            $contaCorrenteEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            $contaCorrenteEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function cobranca(Cliente $cliente, $descricaoCliente, $descricaoEmpresa, $valor) {
        
        try {
            
            try{
                $clienteRn = new ClienteRn($this->conexao->adapter);
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
            }
            
            if (!$valor > 0) {
                throw new \Exception($this->idioma->getText("valorInvalido"));
            }
            
            if (empty($descricaoEmpresa)) {
                throw new \Exception($this->idioma->getText("necessarioInformarDescricaoEmpresa"));
            }
            
            if (empty($descricaoCliente)) {
                throw new \Exception($this->idioma->getText("informarDescricaoCliente"));
            }
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = $descricaoCliente;
            $contaCorrenteReais->idClienteDestino = null;
            $contaCorrenteReais->idCliente = $cliente->id;
            $contaCorrenteReais->orderBook = 0;
            $contaCorrenteReais->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReais->transferencia = 1;
            $contaCorrenteReais->valor = number_format($valor, 2, ".", "");
            $contaCorrenteReais->valorTaxa = 0;
            $contaCorrenteReais->id = 0;
            
            
            $this->salvar($contaCorrenteReais, null);
            
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->bloqueado = 1;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = $descricaoEmpresa;
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresa->transferencia = 1;
            $contaCorrenteReaisEmpresa->valor = number_format($valor, 2, ".", "");
            
            $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa, NULL);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    
    
    public function calcularComissaoTotal(Cliente $cliente, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $idMoeda = 1) {
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("informarDescricaoCliente"));
        }
        $tipo = \Utils\Constantes::ENTRADA;
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " cc.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $where[] = " cc.id_cliente = {$cliente->id} ";
        
        if ($idMoeda > 1) {
            $where[] = " cc.id_moeda = {$idMoeda} ";
            $origemComissao = implode(",", \Utils\Constantes::ORIGENS_COMISSAO_BTC);
            $where[] = " cc.origem IN ({$origemComissao}) ";
        } else {
            $origemComissao = implode(",", \Utils\Constantes::ORIGENS_COMISSAO_REAIS);
            $where[] = " (cc.comissao_licenciado > 0 OR cc.comissao_convidado > 0 OR cc.origem IN ({$origemComissao}) ) ";
        }
        $where[] = " cc.tipo = '{$tipo}' ";
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode( " AND ", $where) : "");
        
        $query = " SELECT "
                . " SUM(cc.valor) AS total, "
                . " COUNT(*) AS qtd "
                . " FROM ". ($idMoeda == 1 ? "conta_corrente_reais" : "conta_corrente_btc") . " cc "
                . " {$whereString} ; ";
        
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        $comissoes = 0;
        $valor= 0;
        
        foreach ($result as $dados) {
            $comissoes = $dados["qtd"];
            $valor = $dados["total"];
        }
        
        return Array("qtd" => $comissoes,  "total" => $valor);
    }
    
    
    public function calcularSaldoSistema() {
        
        $query = "select cc.tipo, SUM(cc.valor) AS valor FROM conta_corrente_reais cc GROUP BY cc.tipo";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $entrada = 0;
        $saida = 0;
        
        foreach ($result as $d) {
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $entrada = $d["valor"];
            } else {
                $saida = $d["valor"];
            }
        }
        
        return Array("moeda" => "real", "id" => "1", "entrada" => $entrada, "saida" => $saida, "saldo" => number_format(($entrada - $saida), 2, ".", ""));
    }
    
    
    public function resumoClientes($filtro = null, $saldoMinBrl = 0, $saldoMinBtc = 0) {
        $where = Array();

        if (!empty($filtro)) {
            $where[] = " LOWER(c.nome) LIKE LOWER('%{$filtro}%') ";
        }

        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = "SELECT c.nome, c.id AS cliente, cc.tipo, SUM(cc.valor) AS valor "
                 . " FROM conta_corrente_reais cc  "
                 . " INNER JOIN clientes c ON (cc.id_cliente = c.id) "
                 . " {$sWhere} "
                 . " GROUP BY cc.tipo , c.id, c.nome "
                 . " ORDER BY c.nome";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $d) {
            //print_r($d);
            if (!isset($lista[$d["cliente"]])) {
                $lista[$d["cliente"]] = Array(
                    "id" => $d["cliente"],
                    "nome" => $d["nome"],
                    "moedas" => Array()
                );
            }
            
            $moedas = $lista[$d["cliente"]]["moedas"];
            
            if (!isset($moedas[1])) {
                $moedas[1] = Array(
                    "moeda" => "real",
                    "entrada" => 0,
                    "saida" => 0,
                    "saldo" => 0
                );
            }
            
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $moedas[1]["entrada"] = $d["valor"];
                $moedas[1]["saldo"] = number_format(($moedas[1]["entrada"] - $moedas[1]["saida"]), 2, ".", "");
            } else {
                $moedas[1]["saida"] = $d["valor"];
                $moedas[1]["saldo"] = number_format(($moedas[1]["entrada"] - $moedas[1]["saida"]), 2, ".", "");
            }
            
            
            $lista[$d["cliente"]]["moedas"] = $moedas;
        }
        
        
        return $lista;
    }
    
    public function saldoClienteByReferenciado($idCliente, $idReferenciado){
        try{
            $valor = "";
            
            if($idCliente == null || $idReferenciado == null){
                throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
            }
            $query = " SELECT SUM(valor) AS valor
                      FROM conta_corrente_reais c
                      WHERE id_cliente = {$idCliente} AND id_referenciado = {$idReferenciado} AND origem IN (4, 5)AND
                      data_cadastro BETWEEN  '2018-01-01 00:00:00' AND now() ;";
                      
            $result = $this->conexao->adapter->query($query)->execute();
            
            foreach ($result as $soma){
                $valor = $soma["valor"];
            }
            
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        return $valor;
    }
    
}

?>
