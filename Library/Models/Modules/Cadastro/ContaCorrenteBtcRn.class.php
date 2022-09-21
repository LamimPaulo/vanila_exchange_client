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
class ContaCorrenteBtcRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
    public $idioma=null;
    public $enviarNotificacao = true;

    public function __construct(\Io\BancoDados $adapter = null, $enviarNotificacao = true) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaCorrenteBtc());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaCorrenteBtc());
        }
        $this->enviarNotificacao = $enviarNotificacao;
    }

    public function gerarContaCorrente(ContaCorrenteBtc &$contaCorrenteBtc, $token = null) {
        $novo = ($contaCorrenteBtc->id <= 0);
            if ($contaCorrenteBtc->id > 0) {
                $aux = new ContaCorrenteBtc(Array("id" => $contaCorrenteBtc->id));
                $this->conexao->carregar($aux);
                $contaCorrenteBtc->dataCadastro = $aux->dataCadastro;
                $contaCorrenteBtc->saldoMovido = $aux->saldoMovido;
            } else {
                $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->saldoMovido = 0;
            }

            if (!$contaCorrenteBtc->transferencia > 0) {
                $contaCorrenteBtc->transferencia = 0;
            }

            
            if (!$contaCorrenteBtc->orderBook > 0) {
                $contaCorrenteBtc->orderBook = 0;
            }
            
            $moeda = MoedaRn::get($contaCorrenteBtc->idMoeda);
            
            $contaCorrenteBtc->nomeMoeda = $moeda->nome;
            $contaCorrenteBtc->symbol = $moeda->simbolo;
            
            if (!$contaCorrenteBtc->idCliente > 0) {
                throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
            }

            $clienteRn = new ClienteRn($this->conexao->adapter);
            $cliente = new Cliente(Array("id" => $contaCorrenteBtc->idCliente));
            $clienteRn->conexao->carregar($cliente);
            
            
            
            $contaCorrenteBtc->nomeCliente = $cliente->nome;
            if (!$contaCorrenteBtc->autorizada > 0) {
                
                if ($cliente->forcarAnaliseSaques > 0) {
                    $contaCorrenteBtc->autorizada = 0;
                    $contaCorrenteBtc->executada = 0;
                } else {
                    $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
                    $configuracao = new Configuracao(Array("id" => 1));
                    $configuracaoRn->conexao->carregar($configuracao);

                    $valorMaximo = 0;
                    
                    $taxaMoedaRn = new TaxaMoedaRn();
                    $taxaMoeda = $taxaMoedaRn->getByMoeda($contaCorrenteBtc->idMoeda);
                    if ($taxaMoeda != null) {
                        if(empty($contaCorrenteBtc->valorTaxa)){
                            $contaCorrenteBtc->valorTaxa = $taxaMoeda->taxaTransferencia;
                        }
                        $valorMaximo =  $taxaMoeda->valorMaxSaqueSemConfirmacao;
                    }

                    $valorSemTxa = number_format(($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa), $moeda->casasDecimais, ".", "");

                    if ($valorSemTxa > $valorMaximo) {
                        $contaCorrenteBtc->autorizada = 0;
                        $contaCorrenteBtc->executada = 0;
                    } else {
                        $contaCorrenteBtc->autorizada = 1;
                    }
                }
            } 
            
           
            if (!$contaCorrenteBtc->idMoeda > 0) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            $tiposTransferencia = Array(
                \Utils\Constantes::TRANF_EXTERNA,
                \Utils\Constantes::TRANF_INTERNA
            );

            if (!in_array($contaCorrenteBtc->direcao, $tiposTransferencia)) {
                throw new \Exception($this->idioma->getText("direcaoInvalida"));
            }

            if (empty($contaCorrenteBtc->descricao)) {
                $contaCorrenteBtc->descricao = "";
                //throw new \Exception("É necessário informar a descrição do lançamento");
            }

            if (!isset($contaCorrenteBtc->data->data) || $contaCorrenteBtc->data->data == null) {
                throw new \Exception($this->idioma->getText("informarDataLancamento"));
            }

            if ($contaCorrenteBtc->tipo != \Utils\Constantes::ENTRADA && $contaCorrenteBtc->tipo != \Utils\Constantes::SAIDA) {
                throw new \Exception($this->idioma->getText("tipoMovimentoInvalido"));
            }
            
            if ($contaCorrenteBtc->valor < 0) {
                $clienteRn = new ClienteRn();
                $cliente = new Cliente();
                $cliente->id = $contaCorrenteBtc->idCliente;
                $clienteRn->conexao->carregar($cliente);
                $cliente->status = 2;
                $clienteRn->alterarStatusCliente($cliente);
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
            }

            if (!$contaCorrenteBtc->valorTaxa > 0) {
                $contaCorrenteBtc->valorTaxa = 0;                
            }
            
            
            if ($contaCorrenteBtc->orderBook < 1 && $contaCorrenteBtc->origem != \Utils\Constantes::ORIGEM_VOTACAO && (!in_array($contaCorrenteBtc->origem, \Utils\Constantes::ORIGEM_ICO_BTC)) && $contaCorrenteBtc->tipo == \Utils\Constantes::SAIDA && $cliente->analiseCliente > 0) {
                $contaCorrenteBtc->autorizada = 0;
                $contaCorrenteBtc->executada = 0;
            }
                    
            if ($contaCorrenteBtc->tipo == \Utils\Constantes::SAIDA) {
                $carteira = $clienteRn->getCarteiraPrincipal(new Cliente(Array("id" => $contaCorrenteBtc->idCliente)), $contaCorrenteBtc->idMoeda);
                if ($carteira != null) {
                    $contaCorrenteBtc->enderecoEnvio = $carteira->endereco;
                } else {
                    $contaCorrenteBtc->enderecoEnvio = "";
                }
            } else {
                $contaCorrenteBtc->enderecoEnvio = "";
            }
            
            if ($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA && $contaCorrenteBtc->orderBook < 1) {
                if ($moeda->statusDeposito < 1) {
                    throw new \Exception($this->idioma->getText("oDeposito") . " {$moeda->simbolo} " . $this->idioma->getText("estaTemporariamente") );
                }
            } else if ($contaCorrenteBtc->tipo == \Utils\Constantes::SAIDA && $contaCorrenteBtc->orderBook < 1) {
                if ($moeda->statusSaque < 1 && $contaCorrenteBtc->origem != \Utils\Constantes::ORIGEM_VOTACAO  && $contaCorrenteBtc->origem != \Utils\Constantes::ORIGEM_RECOMPENSA_ICO) {
                    throw new \Exception($this->idioma->getText("oSaqueDe") . $moeda->simbolo .  $this->idioma->getText("estaTemporariamente"));
                }
            }
            
            
            if (!is_numeric($contaCorrenteBtc->origem)) {
                $contaCorrenteBtc->origem = 0;
            }
            
            if (!is_numeric($contaCorrenteBtc->sincronizado))     {
                $contaCorrenteBtc->sincronizado = 0;
            }
            
            if (!is_numeric($contaCorrenteBtc->enviado))     {
                $contaCorrenteBtc->enviado = 0;
            }
            
            
            if (!is_numeric($contaCorrenteBtc->confirmacoes))     {
                $contaCorrenteBtc->confirmacoes = 0;
            }
            
            if (!is_numeric($contaCorrenteBtc->confirmacoesNecessarias))     {
                $contaCorrenteBtc->confirmacoesNecessarias = 0;
            }
            
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                if (strpos($ip, ',') !== false) {
                    $ip = substr($ip, 0, strpos($ip, ','));
                }
            $contaCorrenteBtc->idSession = session_id();
            $contaCorrenteBtc->ipSession = $ip;
            
             
            unset($contaCorrenteBtc->cliente);
            unset($contaCorrenteBtc->moeda);
            $this->conexao->salvar($contaCorrenteBtc);

            
            
            /*
            if ($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                SaldoClienteRn::creditar($contaCorrenteBtc->valor, $contaCorrenteBtc->idCliente, $contaCorrenteBtc->idMoeda);
            } else {
                SaldoClienteRn::debitar($contaCorrenteBtc->valor, $contaCorrenteBtc->idCliente, $contaCorrenteBtc->idMoeda);
            }
            */
            
            if ($novo) {
                $descricao = $this->idioma->getText("cadastrouContaCorrente") . $contaCorrenteBtc->id.".";
            } else {
                $descricao =  $this->idioma->getText("alterouContaCorrente") . $contaCorrenteBtc->id.".";
            }

            /*if (!in_array($contaCorrenteBtc->origem , \Utils\Constantes::ORIGEM_ICO_BTC)) {
                $logContaCorrenteBtcRn = new LogContaCorrenteBtcRn($this->conexao->adapter);
                $logContaCorrenteBtcRn->salvar($contaCorrenteBtc, $descricao, $token);
            }*/
            
            if ($contaCorrenteBtc->tipo == \Utils\Constantes::SAIDA && $contaCorrenteBtc->executada < 1 && AMBIENTE != "desenvolvimento") {
               
            }
    }
    public function salvar(ContaCorrenteBtc &$contaCorrenteBtc, $token = null) {
        try {
            $this->conexao->adapter->iniciar();
            $this->gerarContaCorrente($contaCorrenteBtc, $token);
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }

    public function resumo($filtro = null, $idMoeda = 2, $saldoMinBtc = 0) {
        if (!$saldoMinBtc > 0) {
            $saldoMinBtc = 0;
        }
        $where = Array();

        if (!empty($filtro)) {
            $where[] = " LOWER(c.nome) LIKE LOWER('%{$filtro}%') ";
        }

        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $query = "SELECT c.* "
                . " FROM clientes c "
                . " {$where} "
                . " GROUP BY c.id, c.nome "
                . " ORDER BY c.nome ";

        $lista = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            if ($idMoeda > 1) {
                $saldo = $this->calcularSaldoConta($cliente, $idMoeda, true);
            } else {
                $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            }
            if (($saldo["saldo"]+$saldo["bloqueado"]) >= $saldoMinBtc) { 
                $lista[] = Array(
                    "cliente" => $cliente,
                    "saldoCurrency" => $saldo["saldo"],
                    "bloqueadoCurrency" => $saldo["bloqueado"]
                );
            }
        }

        return $lista;
    }

    public function resumoClientes($filtro = null, $saldoMinBrl = 0, $saldoMinBtc = 0) {
        if (!$saldoMinBrl > 0) {
            $saldoMinBrl = 0;
        }

        if (!$saldoMinBtc > 0) {
            $saldoMinBtc = 0;
        }

        $where = Array();

        if (!empty($filtro)) {
            $where[] = " LOWER(c.nome) LIKE LOWER('%{$filtro}%') ";
        }

        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $query = "SELECT c.id AS cliente, c.nome, m.simbolo, cc.tipo, SUM(cc.valor) AS valor, m.id AS moeda "
                 . " FROM conta_corrente_btc cc "
                 . " INNER JOIN moedas m  ON (cc.id_moeda = m.id) "
                 . " INNER JOIN clientes c ON (c.id = cc.id_cliente)"
                 . " {$sWhere} "
                 . " GROUP BY m.simbolo, cc.tipo, m.id, c.id, c.nome"
                 . " ORDER BY c.nome ";

        

        $lista = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        
        foreach ($result as $d) {
            
            if (!isset($lista[$d["cliente"]])) {
                $lista[$d["cliente"]] = Array(
                    "id" => $d["cliente"],
                    "nome" => $d["nome"],
                    "moedas" => Array()
                );
            }
            
            
            $moedas = $lista[$d["cliente"]]["moedas"];
            
            if (!isset($moedas[$d["moeda"]])) {
                $moedas[$d["moeda"]] = Array(
                    "moeda" => $d["simbolo"],
                    "entrada" => 0,
                    "saida" => 0,
                    "saldo" => 0
                );
            }
            
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $moedas[$d["moeda"]]["entrada"] = $d["valor"];
                $moedas[$d["moeda"]]["saldo"] = number_format(($moedas[$d["moeda"]]["entrada"] - $moedas[$d["moeda"]]["saida"]), 25, ".", "");
            } else {
                $moedas[$d["moeda"]]["saida"] = $d["valor"];
                $moedas[$d["moeda"]]["saldo"] = number_format(($moedas[$d["moeda"]]["entrada"] - $moedas[$d["moeda"]]["saida"]), 25, ".", "");
            }
            
            
            $lista[$d["cliente"]]["moedas"] = $moedas;
        }
        
        
        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
        $resumoReais = $contaCorrenteReaisRn->resumoClientes($filtro, $saldoMinBrl, $saldoMinBtc);
        
        $listaFinal = Array();
        foreach ($lista as $dados) {
            
            $idCliente = $dados["id"];
            
            
            if (isset($resumoReais[$idCliente]["moedas"][1])) {
                $moedaReal = $resumoReais[$idCliente]["moedas"][1];
            } else {
                $moedaReal = Array(
                    "moeda" => $d["simbolo"],
                    "entrada" => 0,
                    "saida" => 0,
                    "saldo" => 0
                );
            }
            
            unset($resumoReais[$idCliente]);
            
            $saldoMoedas = 0;
            foreach ($dados["moedas"] as $d) {
                $saldoMoedas += $d["saldo"];
            }
            
            if ($saldoMoedas >= $saldoMinBtc || $moedaReal["saldo"] >= $saldoMinBrl) { 
                $dados["moedas"][1] = $moedaReal;
                $listaFinal[] = $dados;
            }
            
        }
        
        foreach ($resumoReais as $dados) {
            if ($dados["moedas"][1]["saldo"] >= $saldoMinBrl) {
                $listaFinal[] = $dados;
            }
        }
        
        return $listaFinal;
    }

    public function filtrar($idCliente, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = 'T', $filtro = null, $transferencia = "T", $idMoeda = 2, $qtdRegitros = "T", $orderBook = false, $referencia = false) {

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
                    . " ( LOWER(c.descricao) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( CAST(c.id AS CHAR(100)) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.endereco_bitcoin) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.hash) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
            $whereHistorico[] = " ( "
                    . " ( LOWER(c.descricao) NOT LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( CAST(c.id AS CHAR(100)) NOT LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.endereco_bitcoin) NOT LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.hash) NOT LIKE LOWER('%{$filtro}%') ) "
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
        
        if ($idMoeda > 0) {
            $where[] = " c.id_moeda = {$idMoeda} ";
            $whereHistorico[] = " c.id_moeda = {$idMoeda} ";
        }

        if ($orderBook) {
            $where[] = " c.order_book = 1 ";
            $whereHistorico[] = " c.order_book = 0 ";
        } else {
            if (!$referencia) {
                $where[] = " c.order_book = 0 ";
                $whereHistorico[] = " c.order_book = 1 ";
            }
        }
        
        if($referencia){
            $where[] = " (c.origem = 0 OR c.origem = 2) ";            
        }
        
        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : " ");
        $whereHistorico = (sizeof($whereHistorico) > 0 ? " WHERE " . implode(" AND ", $whereHistorico) : " ");
       
        
        $query = "SELECT c.*, c.tipo "
                . " FROM conta_corrente_btc c "
                . " INNER JOIN clientes cc ON (c.id_cliente = cc.id) "
                . " {$where} "
                . " ORDER BY data DESC "
                . " {$limit};";
        $queryHistorico = "SELECT SUM(valor) As valor, tipo  FROM conta_corrente_btc c {$whereHistorico} GROUP BY c.tipo;";
        //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $contaCorrenteBtc = new ContaCorrenteBtc($dados);
            $this->carregar($contaCorrenteBtc, false, true, true);
            $lista[] = $contaCorrenteBtc;
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

    public function calcularSaldoConta(Cliente $cliente, $idMoeda = 2, $saldoBloqueado = false, $desconsiderarCredito = false) {

        $sWhere = ($idMoeda > 0 ? " m.id = {$idMoeda} " : " m.ativo = 1 ");
        $query = "SELECT 
                    m.*,
                    COALESCE(
                    COALESCE((SELECT SUM(valor) FROM conta_corrente_btc WHERE id_moeda = m.id AND confirmacoes >= confirmacoes_necessarias AND tipo = 'E' AND id_cliente = {$cliente->id}), 0) -
                    COALESCE((SELECT SUM(valor) FROM conta_corrente_btc WHERE id_moeda = m.id AND tipo = 'S' AND id_cliente = {$cliente->id}), 0)
                    , 0)
                    AS saldo_disponivel,

                    COALESCE( 
                    ( SELECT 
                     SUM(volume_bloqueado) FROM order_book where id_moeda_bloqueada = m.id AND executada = 0 AND cancelada = 0 AND id_cliente = {$cliente->id} ) 
                    , 0) 
             
                    AS saldo_bloqueado,
                    COALESCE((SELECT volume_credito FROM cliente_has_credito WHERE id_cliente = {$cliente->id} AND id_moeda = m.id AND ativo > 0), 0) AS credito

                    FROM moedas m
                    WHERE  {$sWhere} 
                    ORDER BY m.principal DESC, m.simbolo;
                    ";
             
        $listaMoedas = Array();
                    
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            
            $saldo = (($dados["saldo_disponivel"] - $dados["saldo_bloqueado"]) + ($desconsiderarCredito ? 0 : $dados["credito"]));
            
            $listaMoedas[$dados["simbolo"]] = Array();
            $listaMoedas[$dados["simbolo"]]["moeda"] = new Moeda($dados);
            $listaMoedas[$dados["simbolo"]]["saldo"] = number_format($saldo, $dados["casas_decimais"], ".", "");
            if ($saldoBloqueado) {
                $listaMoedas[$dados["simbolo"]]["bloqueado"] = number_format($dados["saldo_bloqueado"], $dados["casas_decimais"], ".", "");
            }
        }
        
        if ($idMoeda > 0) {
            $dadosMoeda = array_pop($listaMoedas);
            
            if ($dadosMoeda == null) {
                $dadosMoeda = Array("saldo" => 0, "bloqueado" => 0);
            }
            
            if ($saldoBloqueado) {
                return $dadosMoeda;
            } else {
                return $dadosMoeda["saldo"];
            }
        } else {
            return $listaMoedas;
        }
    }

    public function calcularSaldoRecompensaICO(Cliente $cliente, $idMoeda = 2) {
        
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        if (!$idMoeda > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }

        $query = " SELECT SUM(valor) AS valor, tipo FROM conta_corrente_btc WHERE id_cliente = {$cliente->id} AND id_moeda = {$idMoeda} AND origem = 8 GROUP BY tipo;";
        
        $moeda = MoedaRn::get($idMoeda);
        
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
        $saldo = ($entrada - $saida);
        return number_format($saldo, $moeda->casasDecimais, ".", "");
    }

    public function calcularSaldoReferencia($idCliente, $idMoeda, $idReferencia, $dataInicial, $dataFinal, $origem) {
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception($this->idioma->getText("dataInicialInformada"));
        }
        
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception($this->idioma->getText("dataFinalInformada"));
        }
        
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
        }
         
        if (!$idCliente > 0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        if (!$idReferencia > 0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        if (!$idMoeda > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }

        $query = " SELECT SUM(valor) AS valor, origem FROM conta_corrente_btc WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda} AND id_referenciado = {$idReferencia} AND origem IN({$origem}) AND data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' GROUP BY origem;";
                
        
        $result = $this->conexao->adapter->query($query)->execute();

        return $result;
    }

    public function calcularSaldoCompraBonusICO(Cliente $cliente, $idMoeda = 33) {
        
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        if (!$idMoeda > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }

        $idBonus = "5";
        $idCompra = "4";
        $arrayOrigens = Array($idCompra, $idBonus);
        $inWhere = implode(", ", $arrayOrigens);
        
        $query = " SELECT SUM(valor) AS valor, tipo, origem FROM conta_corrente_btc WHERE id_cliente = {$cliente->id} AND id_moeda = {$idMoeda} AND origem IN ({$inWhere}) GROUP BY tipo, origem; ";
        
        $moeda = MoedaRn::get($idMoeda);
        
        $entradaBonus = 0;
        $saidaBonus = 0;
        $entradaCompra = 0;
        $saidaCompra = 0;
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        foreach ($result as $dados) {
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                
                if ($idBonus == $dados["origem"]) { 
                    $entradaBonus += $dados["valor"];
                } else {
                    $entradaCompra += $dados["valor"];
                }
                
            } else {
                
                if ($idBonus == $dados["origem"]) { 
                    $saidaBonus += $dados["valor"];
                } else {
                    $saidaCompra += $dados["valor"];
                }
                
            }
        }
        
        $saldoCompra = ($entradaCompra - $saidaCompra);
        $saldoBonus = ($entradaBonus - $saidaBonus);
        
        return Array("compra"=> number_format($saldoCompra, $moeda->casasDecimais, ".", ""), "bonus"=> number_format($saldoBonus, $moeda->casasDecimais, ".", ""));
    }

    public function carregar(ContaCorrenteBtc &$contaCorrenteBtc, $carregar = true, $carregarCliente = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($contaCorrenteBtc);
        }

        if ($carregarCliente && $contaCorrenteBtc->idCliente > 0) {
            $contaCorrenteBtc->cliente = new Cliente(Array("id" => $contaCorrenteBtc->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($contaCorrenteBtc->cliente);
        }
        
        
        if ($carregarMoeda && $contaCorrenteBtc->idMoeda > 0) {
            $contaCorrenteBtc->moeda = new Moeda(Array("id" => $contaCorrenteBtc->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($contaCorrenteBtc->moeda);
        }
    }

    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $contaCorrenteBtc) {
            $this->carregar($contaCorrenteBtc, false, $carregarCliente, $carregarMoeda);
            $lista[] = $contaCorrenteBtc;
        }
        return $lista;
    }

    public function excluir(ContaCorrenteBtc &$contaCorrenteBtc) {
        try {
            $logContaCorrenteBtcRn = new LogContaCorrenteBtcRn($this->conexao->adapter);
            $logContaCorrenteBtcRn->conexao->delete("id_conta_corrente_btc = {$contaCorrenteBtc->id}");

            $this->conexao->excluir($contaCorrenteBtc);

        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function  transferir(Cliente $clienteFrom, $enderecoBitcoin, $valor, $descricao, $idMoeda = 2, $token = null, $rede = null) {
        try {
            $this->conexao->adapter->iniciar();

            $clienteRn = new ClienteRn($this->conexao->adapter);
            try{
                $clienteRn->conexao->carregar($clienteFrom);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteDebitoIdentificado"));
            }

            if ($clienteFrom->statusSaqueCurrency < 1) {
                throw new \Exception($this->idioma->getText("saqueCriptoSuspenso"));
            }

            if (!$valor > 0) {
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
            }

            if (!$idMoeda > 0) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }

            $moeda = new Moeda(Array("id" => $idMoeda));
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
            }

            if ($moeda->ativo < 1) {
                throw new \Exception($this->idioma->getText("comercioMoedaSuspenso"), 130);
            }

            if ($moeda->statusMercado < 1) {
                throw new \Exception($this->idioma->getText("comercioMoedaTempSuspenso"), 123);
            }

            $carteiraRn = new CarteiraRn($this->conexao->adapter);
            $carteira = $carteiraRn->getByEndereco($enderecoBitcoin, $moeda->id);


            $clienteTo = null;
            if ($carteira != null) {
                $clienteTo = new Cliente(Array("id" => $carteira->idCliente));
                $clienteRn->conexao->carregar($clienteTo);
            }

            $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);

            //Fazer movimentação de taxa
            $taxa = 0;
            $moedaTaxa = null;
            $taxaMoedaTransf = null;
            $taxaMoedaRn = new TaxaMoedaRn();

            if ($rede == \Utils\Constantes::REDE_BEP20){

                //Moeda Saque
                $moedaSaque = new \Models\Modules\Cadastro\Moeda(Array("id" => $moeda->idMoedaSaque));
                $moedaRn->conexao->carregar($moedaSaque);

                //Taxa da moeda saque
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moedaSaque->id);

            } else {
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);
            }

            //$taxaMoeda = $taxaMoedaRn->getByMoeda($idMoeda);
            $valorTransferencia = 0;

            if ($taxaMoeda != null) {

                //Verifica se a taxa é cobrada em outra moeda
                if(empty($taxaMoeda->idMoedaTaxa)){
                    //Taxa cobrada na mesma moeda

                    //Verifica se o cliente tem taxa especial.
                    if($clienteFrom->considerarTaxaTransferenciaCurrency == 1) {
                        $taxa = $clienteFrom->taxaComissaoTransfenciaCurrency;
                    } else {
                        $taxa = $taxaMoeda->taxaTransferencia;
                    }

                    $saldoEmconta = $this->calcularSaldoConta($clienteFrom, $idMoeda, false, true);
                    $valorTransferencia = number_format($valor + $taxa, $moeda->casasDecimais, ".", "");

                    if ($saldoEmconta < $valorTransferencia) {
                        throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
                    }

                    if ($taxa > $saldoEmconta) {
                        throw new \Exception($this->idioma->getText("valorMinimoTransf") . number_format($taxa, $moeda->casasDecimais, ",", ""));
                    }

                } else {
                    //Taxa cobrada em outra moeda

                    //Verifica se taxa para essa transferencia não está vazia, caso sim, busca a taxa da moeda de transferencia;
                    if(!empty($taxaMoeda->taxaMoedaTransferencia) && $taxaMoeda->taxaMoedaTransferencia > 0){
                        $taxa = $taxaMoeda->taxaMoedaTransferencia;

                    } else {
                        $taxaMoedaTransf = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                        $taxa = $taxaMoedaTransf->taxaTransferencia;
                    }

                    //Verifica se o cliente tem taxa especial.
                    if($clienteFrom->considerarTaxaTransferenciaCurrency == 1) {
                        $taxa = $clienteFrom->taxaComissaoTransfenciaCurrency;
                    }

                    $moedaTaxa = new Moeda(Array("id" => $taxaMoeda->idMoedaTaxa));
                    $moedaRn->carregar($moedaTaxa);

                    // $saldoMoedaTaxa = $this->calcularSaldoConta($clienteFrom, $moedaTaxa->id, false, true);

                    // if($saldoMoedaTaxa < $taxa){
                    //     throw new \Exception("Você precisa ter em seu saldo " . number_format($taxa, $moedaTaxa->casasDecimais, ",", "") . " {$moedaTaxa->nome} para fazer o saque.");
                    // }

                    $saldoEmconta = $this->calcularSaldoConta($clienteFrom, $idMoeda, false, true);
                    $valorTransferencia = number_format($valor, $moeda->casasDecimais, ".", "");

                    if ($saldoEmconta < $valorTransferencia) {
                        throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
                    }
                }
            } else {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }

            ClienteHasCreditoRn::validar($clienteFrom);

            // $moedaDestino = null;
            // if(!empty($rede) && $rede != $moeda->coinType && !empty($moeda->idMoedaSaque)){
            //     $moedaDestino = $this->converterRedes($clienteFrom, $moeda, $valorTransferencia);
            //     $idMoeda = $moedaDestino->id;
            // }

            $contaCorrenteFrom = new ContaCorrenteBtc();
            $contaCorrenteFrom->id = 0;
            $contaCorrenteFrom->idCliente = $clienteFrom->id;
            $contaCorrenteFrom->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteFrom->descricao = $descricao;
            $contaCorrenteFrom->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteFrom->valor = $valorTransferencia;
            $contaCorrenteFrom->valorTaxa = $taxa;
            $contaCorrenteFrom->idMoedaTaxa = empty($moedaTaxa) ? $idMoeda : $moedaTaxa->id;
            $contaCorrenteFrom->transferencia = 1;
            $contaCorrenteFrom->idMoeda = empty($moedaDestino) ? $idMoeda : $moedaDestino->id;
            $contaCorrenteFrom->enderecoBitcoin = $enderecoBitcoin;
            $contaCorrenteFrom->direcao = ($clienteTo == null ? \Utils\Constantes::TRANF_EXTERNA : \Utils\Constantes::TRANF_INTERNA);
            $contaCorrenteFrom->executada = ($clienteTo == null ? 0 : 1);
            $contaCorrenteFrom->rede = empty($rede) ? $moeda->coinType : $rede;

            $this->salvar($contaCorrenteFrom, $token);

            $saldo = $this->calcularSaldoConta($clienteFrom, $idMoeda);
            //throw new \Exception("Em manutenção {$saldo}");
            if ($saldo < 0) {
                $this->excluir($contaCorrenteFrom);
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }

            if (!empty($clienteTo) && $contaCorrenteFrom->autorizada == 1) {
                // se o cliente foi identificado a transferência é interna
                $this->creditarContaDesnataria($contaCorrenteFrom, $clienteTo, $token);
            }
            if ($taxa > 0) {
                if(!empty($taxaMoeda->idMoedaTaxa)){
                    $contaCorrenteTaxa = new ContaCorrenteBtc();
                    $contaCorrenteTaxa->id = 0;
                    $contaCorrenteTaxa->idCliente = $clienteFrom->id;
                    $contaCorrenteTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteTaxa->descricao = "Taxa de transferência - {$contaCorrenteFrom->id}";
                    $contaCorrenteTaxa->tipo = \Utils\Constantes::SAIDA;
                    $contaCorrenteTaxa->valor = $taxa;
                    $contaCorrenteTaxa->valorTaxa = $taxa;
                    $contaCorrenteTaxa->idMoedaTaxa =  $moedaTaxa->id;
                    $contaCorrenteTaxa->transferencia = 0;
                    $contaCorrenteTaxa->idMoeda = $moedaTaxa->id;
                    $contaCorrenteTaxa->direcao = \Utils\Constantes::TRANF_INTERNA;
                    $contaCorrenteTaxa->executada = 1;
                    $contaCorrenteTaxa->autorizada = 1;

                    $this->salvar($contaCorrenteTaxa, $token);
                }
                $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
                $contaCorrenteBtcEmpresa->id = 0;
                $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtcEmpresa->descricao = $this->idioma->getText("taxaSobreTransferencia") ." {$clienteFrom->nome} " . $this->idioma->getText("entrada") . " " . (!empty($clienteTo) ? $clienteTo->nome : $enderecoBitcoin);
                $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteBtcEmpresa->valor = $taxa;
                $contaCorrenteBtcEmpresa->transferencia = 1;
                $contaCorrenteBtcEmpresa->idMoeda = empty($moedaTaxa) ? $idMoeda : $moedaTaxa->id;
                $contaCorrenteEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                $contaCorrenteEmpresaRn->salvar($contaCorrenteBtcEmpresa, $token);

                //Debito Taxa da Rede
                if($contaCorrenteFrom->direcao == \Utils\Constantes::TRANF_EXTERNA){
                    if(!empty($taxaMoeda->idMoedaTaxa) && empty($rede)){
                        if(empty($taxaMoedaTransf)){
                            $taxaRedeMoeda = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                        } else {
                            $taxaRedeMoeda = $taxaMoedaTransf;
                        }
                    } else {
                        $taxaRedeMoeda = $taxaMoeda;
                    }

                    $contaCorrenteTxRede = new ContaCorrenteBtcEmpresa();
                    $contaCorrenteTxRede->id = 0;
                    $contaCorrenteTxRede->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteTxRede->descricao = "Taxa Rede " . (empty($moedaTaxa) ? $moeda->simbolo : $moedaTaxa->simbolo);
                    $contaCorrenteTxRede->tipo = \Utils\Constantes::SAIDA;
                    $contaCorrenteTxRede->valor = $taxaRedeMoeda->taxaRede;
                    $contaCorrenteTxRede->transferencia = 1;
                    $contaCorrenteTxRede->idMoeda = empty($moedaTaxa) ? $idMoeda : $moedaTaxa->id;

                    $contaCorrenteEmpresaRn->salvar($contaCorrenteTxRede, $token);
                }
            }

            $this->carregar($contaCorrenteFrom, false, true, true);

            $this->conexao->adapter->finalizar();
            return $contaCorrenteFrom;
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    private function converterRedes(Cliente $cliente, Moeda $moeda, $valor) {
        
        $contaCorrenteEmpresaRn = new ContaCorrenteBtcEmpresaRn();
        
        //exit(print_r($moeda));
        //Recuperar moeda destino
        $moedaDestino = MoedaRn::get($moeda->idMoedaSaque);
        
        //1 - Fazer debito na conta do cliente
            $contaCorrenteBtcDebito = new ContaCorrenteBtc();
            $contaCorrenteBtcDebito->id = 0;
            $contaCorrenteBtcDebito->autorizada = 1;
            $contaCorrenteBtcDebito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcDebito->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcDebito->enderecoBitcoin = "";
            $contaCorrenteBtcDebito->hash = "";
            $contaCorrenteBtcDebito->descricao = "Mover saldo para " . $moedaDestino->nome;
            $contaCorrenteBtcDebito->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtcDebito->enderecoEnvio = "";
            $contaCorrenteBtcDebito->executada = 1;
            $contaCorrenteBtcDebito->origem = 17;
            $contaCorrenteBtcDebito->idCliente = $cliente->id;
            $contaCorrenteBtcDebito->idMoeda = $moeda->id;
            $contaCorrenteBtcDebito->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtcDebito->transferencia = 0;
            $contaCorrenteBtcDebito->valor = number_format($valor, 8, ".", "");;
            $contaCorrenteBtcDebito->valorTaxa = 0;
            $contaCorrenteBtcDebito->orderBook = 0;
        
            $this->gerarContaCorrente($contaCorrenteBtcDebito);
            
        //2 - Creditar moeda debitada na conta da empresa            
            $contaCorrenteBtcEmpresaCredito = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresaCredito->id = 0;
            $contaCorrenteBtcEmpresaCredito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresaCredito->descricao = "Conversao de saldo do cliente " . $cliente->id . " - {$moeda->simbolo} para {$moedaDestino->simbolo}";
            $contaCorrenteBtcEmpresaCredito->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtcEmpresaCredito->valor = number_format($valor, 8, ".", "");
            $contaCorrenteBtcEmpresaCredito->transferencia = 1;
            $contaCorrenteBtcEmpresaCredito->idMoeda = $moeda->id;

            $contaCorrenteEmpresaRn->gerarContaCorrente($contaCorrenteBtcEmpresaCredito);
        
        //3 - Fazer debito da moeda conversão na conta da empresa
            
            $contaCorrenteBtcEmpresaDebito = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresaDebito->id = 0;
            $contaCorrenteBtcEmpresaDebito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresaDebito->descricao = "Conversao de saldo do cliente " . $cliente->id . " - {$moeda->simbolo} para {$moedaDestino->simbolo}";
            $contaCorrenteBtcEmpresaDebito->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtcEmpresaDebito->valor = number_format($valor , 8, ".", "");
            $contaCorrenteBtcEmpresaDebito->transferencia = 1;
            $contaCorrenteBtcEmpresaDebito->idMoeda = $moedaDestino->id;

            $contaCorrenteEmpresaRn->gerarContaCorrente($contaCorrenteBtcEmpresaDebito);
        
        //4 - Fazer crédito na conta do cliente da moeda conversão
            
            //Criptomoeda
            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->enderecoBitcoin = "";
            $contaCorrenteBtc->hash = "";
            $contaCorrenteBtc->descricao = "Conversao de saldo da moeda " . $moedaDestino->nome;
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->enderecoEnvio = "";
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->origem = 17;
            $contaCorrenteBtc->idCliente = $cliente->id;
            $contaCorrenteBtc->idMoeda = $moedaDestino->id;
            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->valor = number_format($valor, 8, ".", "");
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->orderBook = 0;

            $this->gerarContaCorrente($contaCorrenteBtc);
            
            return $moedaDestino;
    }

    private function creditarContaDesnataria(ContaCorrenteBtc $contaCorrente, \Models\Modules\Cadastro\Cliente $clienteTo, $token = null) {
        
        $moeda = MoedaRn::get($contaCorrente->idMoeda);
        
        $contaCorrenteTo = new ContaCorrenteBtc();
        $contaCorrenteTo->id = 0;
        $contaCorrenteTo->idCliente = $clienteTo->id;
        $contaCorrenteTo->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteTo->descricao = "Depósito Interno {$moeda->nome}";
        $contaCorrenteTo->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteTo->valor = number_format(($contaCorrente->valor - $contaCorrente->valorTaxa), $moeda->casasDecimais, ".", "");
        $contaCorrenteTo->transferencia = 0;
        $contaCorrenteTo->enderecoBitcoin = $contaCorrente->enderecoBitcoin;
        $contaCorrenteTo->direcao = \Utils\Constantes::TRANF_INTERNA;
        $contaCorrenteTo->executada = 1;
        $contaCorrenteTo->idMoeda = $contaCorrente->idMoeda;
        $contaCorrenteTo->autorizada = 1;
        
        $this->salvar($contaCorrenteTo, $token);
    }

    public function find($hash, $wallet, $amount) {
        $result = $this->conexao->select(Array(
            "hash" => $hash,
            "endereco_bitcoin" => $wallet,
            "valor" => number_format($amount, 8, ".", "")
        ));

        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }

    public function getByHash($hash) {
        $result = $this->conexao->select(Array(
            "hash" => $hash
        ));

        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }

    public function confirmarTransferencia(ContaCorrenteBtc $contaCorrenteBtc) {

        $ccb = new ContaCorrenteBtc(Array("id" => $contaCorrenteBtc->id));

        try {
            $this->carregar($ccb, true, false);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("transacaoNaoLocalizada"), 118);
        }

        if ($ccb->executada > 0) {
            throw new \Exception($this->idioma->getText("transacaoConfirmada"), 119);
        }

        if ($ccb->autorizada < 1) {
            throw new \Exception($this->idioma->getText("transacaoNaoAutorizada"), 120);
        }
        
        if ($ccb->autorizada >1) {
            throw new \Exception($this->idioma->getText("transacaoNegada"), 121);
        }
        
        if (empty($contaCorrenteBtc->hash)) {
            throw new \Exception($this->idioma->getText("hashTransacaoInvalido"), 101);
        }

        if (!$contaCorrenteBtc->idMoeda > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
        }
        
        if ($contaCorrenteBtc->idMoeda !== $ccb->idMoeda) {
            throw new \Exception($this->idioma->getText("divergenciaMoedaInformada"), 124);
        }

        if ($contaCorrenteBtc->idCliente !== $ccb->idCliente) {
            //throw new \Exception("Divergência na identificação do cliente", 114);
        }

        if ($contaCorrenteBtc->data == null) {
            throw new \Exception($this->idioma->getText("dataTransacaoInvalida"), 105);
        }

        if ($contaCorrenteBtc->data->formatar(\Utils\Data::FORMATO_PT_BR) != $ccb->data->formatar(\Utils\Data::FORMATO_PT_BR)) {
            //throw new \Exception("Divergência na data da transação", 111);
        }
        if ($contaCorrenteBtc->enderecoBitcoin !== $ccb->enderecoBitcoin) {
            throw new \Exception($this->idioma->getText("divergenciaEnderecoCarteira"), 113);
        }
        /* Alterado por: [alexandre, 14/12/2017] coloquei o round para correção de erro na comparação. */
        if (round($contaCorrenteBtc->valor, 8) != round($ccb->valor - $ccb->valorTaxa, 8)) {
            throw new \Exception($this->idioma->getText("divergenciaValorTransacao") . "[ ". $contaCorrenteBtc->valor . " != " . ($ccb->valor - $ccb->valorTaxa) . "]", 115);
        }

        $this->conexao->update(
                Array(
                    "executada" => 1,
                    "hash" => $contaCorrenteBtc->hash,
                    "sincronizado" => 1
                ),
                Array(
                    "id" => $ccb->id
                )
            );
        
        
        if ($this->enviarNotificacao) {
            try {
                $cliente = new Cliente(Array("id" => $contaCorrenteBtc->idCliente));
                $clienteRn = new ClienteRn($this->conexao->adapter);
                $clienteRn->conexao->carregar($cliente);

                $moeda = new Moeda(Array("id" => $contaCorrenteBtc->idMoeda));
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);

                $valor = number_format(($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA ? $contaCorrenteBtc->valor : ($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa)), $moeda->casasDecimais, ".", "");
                $data = date("d/m/Y - H:i");
                $msg = $this->idioma->getText("CointradeSaqueAprovado") . "{$moeda->simbolo}, {$contaCorrenteBtc->enderecoBitcoin}, {$data}hs," .  $this->idioma->getText("volume") . $valor;

                if ($cliente->recebimentoAlertaMovimentacaoConta === "S") {
                    $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);

                    $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                    $EnviaSMSResult = $api->EnviaSMS("55{$celular}", $msg);
                } else {

                }
            } catch (\Exception $ex) {

            }
        }

    }

    public function calcularVolumeTotalPorEndereco($endereco, $idMoeda = null) {
        $moeda = MoedaRn::get($idMoeda);
        
        $wTokens = "";
        if ($idMoeda > 0) {
            $wTokens = " AND id_moeda = {$idMoeda} ";
        }
        
        $query = "SELECT SUM(valor) AS valor FROM conta_corrente_btc WHERE endereco_bitcoin = '{$endereco}' {$wTokens} AND tipo = '".\Utils\Constantes::ENTRADA."'; ";
        $result = $this->conexao->adapter->query($query)->execute();

        if (sizeof($result) > 0) {
            $dados = $result->current();
            $volume = isset($dados["valor"]) ? $dados["valor"] : 0;
        } else {
            $volume = 0;
        }

        return number_format($volume, $moeda->casasDecimais, ".", "");
    }

    public function autorizarTransacao(ContaCorrenteBtc &$contaCorrenteBtc) {
        try {
            $this->conexao->adapter->iniciar();
            try {
                $this->conexao->carregar($contaCorrenteBtc);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("transacaoNaoLocalizadaSistema"));
            }
            
            // se for uma transferência prossegue com o crédito na conta corrente do destinatário
            if ($contaCorrenteBtc->transferencia > 0) {
                $carteiraRn = new CarteiraRn($this->conexao->adapter);
                $carteira = $carteiraRn->getByEndereco($contaCorrenteBtc->enderecoBitcoin, 0);
                
                $clienteTo = null;
                if ($carteira != null) {
                    $clienteTo = new Cliente(Array("id" => $carteira->idCliente));
                    $clienteRn = new ClienteRn($this->conexao->adapter);
                    $clienteRn->conexao->carregar($clienteTo);
                }
                
            }
            
            
            $this->conexao->update(
                    Array(
                        "autorizada" => 1,
                        "executada" => ($clienteTo != null ? 1 : 0)
                    ),
                    Array(
                        "id" => $contaCorrenteBtc->id
                    )
                );
            
            if ($clienteTo != null) {
                $this->creditarContaDesnataria($contaCorrenteBtc, $clienteTo);
            }
            
            if ($this->enviarNotificacao) {
                try {
                    $cliente = new Cliente(Array("id" => $contaCorrenteBtc->idCliente));
                    $clienteRn = new ClienteRn($this->conexao->adapter);
                    $clienteRn->conexao->carregar($cliente);

                    $moeda = new Moeda(Array("id" => $contaCorrenteBtc->idMoeda));
                    $moedaRn = new MoedaRn();
                    $moedaRn->conexao->carregar($moeda);

                    $valor = number_format(($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA ? $contaCorrenteBtc->valor : ($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa)), $moeda->casasDecimais, ".", "");
                    $data = date("d/m/Y - H:i");

                    if ($clienteTo != null) {
                        $msg = $this->idioma->getText("CointradeSaqueRealizado") . "{$moeda->simbolo}, {$contaCorrenteBtc->enderecoBitcoin}, {$data}hs," . $this->idioma->getText("volume") . $valor;
                    } else {
                        $msg = $this->idioma->getText("CointradeSaqueProcessando") . "{$moeda->simbolo}, {$contaCorrenteBtc->enderecoBitcoin}, {$data}hs," . $this->idioma->getText("volume") . $valor;
                    }


                        $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);
                        $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                        $EnviaSMSResult = $api->EnviaSMS("55{$celular}", $msg);

                } catch (\Exception $ex) {

                }
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function reenviarTransacao(ContaCorrenteBtc &$contaCorrenteBtc) {
    
        try {
            $this->conexao->carregar($contaCorrenteBtc);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("transacaoNaoLocalizadaSistema"));
        }
        
        if ($contaCorrenteBtc->executada > 0) {
            throw new \Exception("Não é possível reenviar para o bot uma transação já executada.");
        }
        
        $this->conexao->update(Array("enviado" => 0), Array("id" => $contaCorrenteBtc->id));
    }

    public function negarTransacao(ContaCorrenteBtc &$contaCorrenteBtc) {
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $this->conexao->carregar($contaCorrenteBtc);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("transacaoNaoLocalizadaSistema"));
            }
            
            $this->conexao->update(
                    Array(
                        "autorizada" => 2,
                        "executada" => 0
                    ),
                    Array(
                        "id" => $contaCorrenteBtc->id
                    )
                );
            
            $this->conexao->carregar($contaCorrenteBtc);
            
            // estorno do valor e da taxa
            $contaCorrenteTo = new ContaCorrenteBtc();
            $contaCorrenteTo->id = 0;
            $contaCorrenteTo->idCliente = $contaCorrenteBtc->idCliente;
            $contaCorrenteTo->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteTo->descricao = "Estorno Transferência Bitcoin ( BTC )";
            $contaCorrenteTo->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteTo->valor = $contaCorrenteBtc->valor;
            $contaCorrenteTo->transferencia = 0;
            $contaCorrenteTo->enderecoBitcoin = $contaCorrenteBtc->enderecoBitcoin;
            $contaCorrenteTo->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteTo->executada = 1;
            $contaCorrenteTo->idMoeda = $contaCorrenteBtc->idMoeda;
            $contaCorrenteTo->autorizada = 1;
            $this->salvar($contaCorrenteTo, null);
            
            if ($contaCorrenteBtc->valorTaxa > 0) {
                $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
                $contaCorrenteBtcEmpresa->id = 0;
                $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtcEmpresa->descricao = "Estorno Taxa transação {$contaCorrenteBtc->id}.";
                $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::SAIDA;
                $contaCorrenteBtcEmpresa->valor = $contaCorrenteBtc->valorTaxa;
                $contaCorrenteBtcEmpresa->transferencia = 0;
                $contaCorrenteBtcEmpresa->idMoeda = $contaCorrenteBtc->idMoeda;
                $contaCorrenteEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                $contaCorrenteEmpresaRn->salvar($contaCorrenteBtcEmpresa, null);
            }
            
            /*
            if ($this->enviarNotificacao) {
                try {
                    $cliente = new Cliente(Array("id" => $contaCorrenteBtc->idCliente));
                    $clienteRn = new ClienteRn($this->conexao->adapter);
                    $clienteRn->conexao->carregar($cliente);

                    $moeda = new Moeda(Array("id" => $contaCorrenteBtc->idMoeda));
                    $moedaRn = new MoedaRn();
                    $moedaRn->conexao->carregar($moeda);

                    $valor = number_format(($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA ? $contaCorrenteBtc->valor : ($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa)), $moeda->casasDecimais, ".", "");
                    $data = date("d/m/Y - H:i");
                    $msg = $this->idioma->getText("CointradeSaqueCancelado") ."{$moeda->simbolo}, {$contaCorrenteBtc->enderecoBitcoin}, {$data}hs" . $this->idioma->getText("volume") . $valor;

                    $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);
                    $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                    $EnviaSMSResult = $api->EnviaSMS("55{$celular}", $msg);
                } catch (\Exception $ex) {

                }
            }
            */
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function transferirParaEmpresa($valor, $descricao, $idMoeda = 2, $token = null) {
        try {
            $this->conexao->adapter->iniciar();

            $cliente = \Utils\Geral::getCliente();
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
            }
            
            if (!$valor > 0) {
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"));
            }
            
            if (!$idMoeda > 0) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            $moeda = new Moeda(Array("id" => $idMoeda));
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
            }

            if ($moeda->ativo < 1) {
                throw new \Exception($this->idioma->getText("comercioMoedaSuspenso"), 130);
            }
            if ($moeda->statusMercado < 1) {
                throw new \Exception($this->idioma->getText("comercioMoedaTempSuspenso"), 123);
            }
            
            $contaCorrenteFrom = new ContaCorrenteBtc();
            $contaCorrenteFrom->id = 0;
            $contaCorrenteFrom->idCliente = $cliente->id;
            $contaCorrenteFrom->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteFrom->descricao = $descricao;
            $contaCorrenteFrom->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteFrom->valor = number_format($valor, $moeda->casasDecimais, ".", "");
            $contaCorrenteFrom->valorTaxa = 0;
            $contaCorrenteFrom->transferencia = 1;
            $contaCorrenteFrom->idMoeda = $idMoeda;
            $contaCorrenteFrom->enderecoBitcoin = null;
            $contaCorrenteFrom->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteFrom->executada = 1;
            $contaCorrenteFrom->autorizada = 1;

            $saldoConta = $this->calcularSaldoConta($cliente, $idMoeda, false, true);
            if ($saldoConta < $contaCorrenteFrom->valor) {
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            ClienteHasCreditoRn::validar($cliente);
            
            $this->salvar($contaCorrenteFrom, $token);

            $saldo = $this->calcularSaldoConta($cliente, $idMoeda);
            //throw new \Exception("Em manutenção {$saldo}");
            if ($saldo < 0) {
                $this->excluir($contaCorrenteFrom);
                throw new \Exception($this->idioma->getText("voceNaoTemSaldoSuficiente"));
            }
            
            
            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = 0;
            $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresa->descricao = "Transferência de {$cliente->nome}.";
            $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtcEmpresa->valor = $valor;
            $contaCorrenteBtcEmpresa->transferencia = 1;
            $contaCorrenteBtcEmpresa->idMoeda = $idMoeda;
            $contaCorrenteEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $contaCorrenteEmpresaRn->salvar($contaCorrenteBtcEmpresa, $token);
            $this->carregar($contaCorrenteFrom, false, true, true);

            $this->conexao->adapter->finalizar();
            return $contaCorrenteFrom;
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function cobranca(Cliente $cliente, $descricaoCliente, $descricaoEmpresa, $valor, Moeda $moeda) {
        try {
            try{
                $clienteRn = new ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado") );
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
            
            if ($moeda->id < 2) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            try {
                $moedaRn = new MoedaRn($this->conexao->adapter);
                $moedaRn->conexao->carregar($moeda);
            } catch (Exception $ex) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->descricao = $descricaoCliente;
            $contaCorrenteBtc->enderecoBitcoin = null;
            $contaCorrenteBtc->idCliente = $cliente->id;
            $contaCorrenteBtc->orderBook = 0;
            $contaCorrenteBtc->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtc->transferencia = 1;
            $contaCorrenteBtc->valor = number_format($valor, $moeda->casasDecimais, ".", "");
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->idMoeda = $moeda->id;
            
            $this->salvar($contaCorrenteBtc, null);
            
            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->bloqueado = 1;
            $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresa->descricao = $descricaoEmpresa;
            $contaCorrenteBtcEmpresa->id = 0;
            $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtcEmpresa->transferencia = 1;
            $contaCorrenteBtcEmpresa->idMoeda = $moeda->id;
            $contaCorrenteBtcEmpresa->valor = number_format($valor, $moeda->casasDecimais, ".", "");
            
            $contaCorrenteBtcEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa, null);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }

    public function calcularSaldoCurrencies() {
        
        $query = "select "
                . "m.simbolo, cc.tipo, SUM(cc.valor) AS valor, m.id "
                . "FROM conta_corrente_btc cc "
                . "INNER JOIN moedas m  ON (cc.id_moeda = m.id) "
                . "GROUP BY m.simbolo, cc.tipo, m.id";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $dados = Array();
        
        foreach ($result as $d) {
            if (!isset($dados[$d["simbolo"]])) {
                $dados[$d["simbolo"]] = Array(
                    "moeda" => $d["simbolo"],
                    "id" => $d["id"],
                    "entrada" => 0,
                    "saida" => 0,
                    "saldo" => 0
                );
            }
            
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $dados[$d["simbolo"]]["entrada"] = $d["valor"];
                $dados[$d["simbolo"]]["saldo"] = number_format(($dados[$d["simbolo"]]["entrada"] - $dados[$d["simbolo"]]["saida"]), 8, ".", "");
            } else {
                $dados[$d["simbolo"]]["saida"] = $d["valor"];
                $dados[$d["simbolo"]]["saldo"] = number_format(($dados[$d["simbolo"]]["entrada"] - $dados[$d["simbolo"]]["saida"]), 8, ".", "");
            }
        }
        
        return $dados;
    }

    public function getQuantidadeClientesComSaldo($idMoeda) {
        $query = "SELECT
                    COUNT(*) AS qtd
                    FROM clientes c
                    WHERE
                    (
                    (SELECT SUM(valor) FROM conta_corrente_btc WHERE id_moeda = {$idMoeda} AND id_cliente = c.id AND tipo = 'E') - 
                    (SELECT SUM(valor) FROM conta_corrente_btc WHERE id_moeda = {$idMoeda} AND id_cliente = c.id AND tipo = 'S' ) 
                    ) > 0;";
        
        $qtd = 0;
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $qtd = ($dados["qtd"] != null ? $dados["qtd"] : 0);
        }
        return $qtd;
    }

    public function saldoConsolidadoInvestimento($idCliente, $idMoeda, $dataInicial, $dataFinal){
        $dataSql = "";
        $moedaSql = "";
        if($idMoeda != "T"){
            $moedaSql = " id_moeda = {$idMoeda} AND ";
        } else {
            $moedaSql = "";
        }

        if($dataInicial == null){
            $dataSql = "";
        } else {
            $dataSql = "AND data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'";
        }

        $query = "SELECT c.id_moeda, c.descricao, c.data_cadastro, sum(valor) AS totalDia FROM conta_corrente_btc c WHERE
        id_cliente = {$idCliente} AND {$moedaSql} origem = 3 AND tipo = 'E' {$dataSql}
        GROUP BY c.id_moeda, c.descricao, c.data_cadastro
        ORDER BY data_cadastro DESC , id_moeda ASC;";

        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }

    public function ultimosDepositosBtcBrl(Cliente $cliente, $qtdRegitros = "T", $categoria = null) {
        if ($cliente->id == null) {
            throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
        }

        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }

        if($categoria != null){
            $categoriaQuery = " HAVING categoria = {$categoria} ";
        }

        $query = "SELECT data_cadastro as data, valor as volume, (select nome from moedas where id = id_moeda) as moeda,
                hash as comprovante, descricao, (select id_categoria_moeda from moedas where id = id_moeda) as categoria, id_moeda as idMoeda, origem as origem, direcao as direcao, '0' as tipo_deposito, '1' as link FROM conta_corrente_btc
                WHERE id_cliente = {$cliente->id} AND tipo = 'E' AND origem = 0
                {$categoriaQuery}
                UNION
                SELECT data_cadastro as data, valor as volume, (select nome from moedas where id = id_moeda) as moeda,
                origem as comprovante, descricao, (select id_categoria_moeda from moedas where id = id_moeda) as categoria, id_moeda as idMoeda, origem as origem, direcao as direcao, '0' as tipo_deposito, '1' as link FROM conta_corrente_btc
                WHERE id_cliente = {$cliente->id} AND tipo = 'E' AND direcao = 'I' AND origem = 13
                {$categoriaQuery}
                UNION
                SELECT data_cadastro as data, valor as volume, 'Real' as moedas, tipo as comprovante, origem as status, '1' as categoria, '0' as idMoeda, 'E' as origem, '0' as direcao, '0' as tipo_deposito, '1' as link FROM conta_corrente_reais
                WHERE id_cliente = {$cliente->id}  AND origem = 9 AND tipo = 'E' {$categoriaQuery}
                UNION
                SELECT data_solicitacao as data, valor_creditado as volume, 'Real' as moedas, comprovante as comprovante, status as status, '1' as categoria, '0' as idMoeda, 'E' as origem, '0' as direcao, tipo_deposito as tipo_deposito, link_gateway as link FROM depositos
                WHERE id_cliente = {$cliente->id}
                {$categoriaQuery}
                UNION
                SELECT data_confirmacao as data, valor_creditado as volume, 'Atar' as moeda, id_transacao as comprovante, tipo as status, '1' as categoria, '0' as idMoeda, 'E' as origem, '0' as direcao, '0' as tipo_deposito, '1' as link FROM atar_contas
                WHERE id_cliente = {$cliente->id} AND tipo = 'E'
                {$categoriaQuery}
                ORDER BY data DESC {$limit};";

        $result = $this->conexao->adapter->query($query)->execute();

        return $result;
    }

    public function ultimosSaquesBtcBrl(Cliente $cliente, $qtdRegitros = "T", $categoria = null) {
        
        if ($cliente->id == null) {
            throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
        }

        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }
        
        if($categoria != null){
            $categoriaQuery = " HAVING categoria = {$categoria} ";
        }

        $query = "SELECT data_cadastro as data, valor as volume, (select nome from moedas where id = id_moeda) as moeda,
                hash as comprovante, descricao, (select id_categoria_moeda from moedas where id = id_moeda) as categoria, id_moeda as idMoeda, executada as executada, autorizada as autorizada, origem as origem, direcao as direcao FROM conta_corrente_btc
                WHERE id_cliente = {$cliente->id} AND tipo = 'S' AND origem = 0 
                {$categoriaQuery}  
                UNION
                SELECT data_solicitacao as data, valor_sacado as volume, 'Real' as moedas, comprovante as comprovante, status as status, '1' as categoria, '0' as idMoeda, 'exec' as executada, 'auto' as  autorizada, '0' as origem, 'E' as direcao FROM saques
                WHERE id_cliente = {$cliente->id}
                {$categoriaQuery}    
                UNION
                SELECT data_cadastro as data, valor as volume, 'Atar' as moedas, '' as comprovante, '1' as status, '1' as categoria, '0' as idMoeda, tarifa as executada, taxa as  autorizada, '0' as origem, 'E' as direcao FROM atar_contas
                WHERE id_cliente = {$cliente->id} AND tipo = 'S'
                {$categoriaQuery}      
                ORDER BY data DESC {$limit};";
            
        $result = $this->conexao->adapter->query($query)->execute();

        return $result;     
    }
}
?>
