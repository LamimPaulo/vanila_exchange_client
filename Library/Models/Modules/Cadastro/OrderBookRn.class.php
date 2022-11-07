<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade OrderBook
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OrderBookRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    private static $homolog = false;
    private $listOrdersHomolog = Array();
    public  $idioma=null;

    public function __construct(\Io\BancoDados $adapter = null) {
        
        if ($this->idioma == null) {
            $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        }
        
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new OrderBook());
        } else {
            $this->conexao = new GenericModel($adapter, new OrderBook());
        }
    }

    public function setHomolog($homolog = true) {
        self::$homolog = $homolog;
    }

    public function salvar(OrderBook &$orderBook) {
        try {
            
            $this->conexao->adapter->iniciar();
            
            $paridade = new Paridade(Array("id" => $orderBook->idParidade));
            try {
                $paridadeRn = new ParidadeRn();
                $paridadeRn->carregar($paridade);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            $orderBook->id = 0;
            $orderBook->executada = 0;
            $orderBook->cancelada = 0;
            $orderBook->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $orderBook->volumeExecutado = 0;
            $orderBook->valorTaxa = 0;
            $orderBook->valorTaxaExecutada = 0;
            
            $orderBook->idMoedaBook = $paridade->idMoedaBook;
            $orderBook->idMoedaTrade = $paridade->idMoedaTrade;
            $orderBook->symbol = $paridade->symbol;
            $orderBook->symbolMoedaBook = $paridade->moedaBook->simbolo;
            $orderBook->symbolMoedaTrade = $paridade->moedaTrade->simbolo;
            
            $cliente = new Cliente(Array("id" => $orderBook->idCliente));
            
            $configuracao = ConfiguracaoRn::get();
            $taxaRn = new TaxaMoedaRn();
            
            $taxa = $taxaRn->getByMoeda($orderBook->idMoedaBook);
            
            if ($orderBook->volumeCurrency < $taxa->volumeMinimoNegociacao) {
                throw new \Exception($this->idioma->getText("valorMinimoNegRS"). " " . $taxa->volumeMinimoNegociacao . " " . $orderBook->symbolMoedaBook);
            }
            
            if ($paridade->idMoedaTrade == 1) { 
                if (($orderBook->valorCotacao * $orderBook->volumeCurrency) < $configuracao->valorMinimoNegociacaoBrl) {
                    throw new \Exception($this->idioma->getText("valorMinimoNegRS"). " R$ " . number_format($configuracao->valorMinimoNegociacaoBrl, $paridade->moedaTrade->casasDecimais, ",", "."));
                }
            }
            
            if ($paridade->ativo < 1) {
                throw new \Exception($this->idioma->getText("mercadoMoedaSuspenso"));
            }
            
            if ($paridade->statusMercado < 1) {
                throw new \Exception($this->idioma->getText("mercadoTempSuspenso"));
            }
            
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $orderBook->nomeCliente = $cliente->nome;
            
            if ($cliente->statusMercado < 1) {
                throw new \Exception($this->idioma->getText("negBloqParaSuaConta"));
            }
            
            $tipos = Array(
                \Utils\Constantes::ORDEM_COMPRA,
                \Utils\Constantes::ORDEM_VENDA
            );
            
            if (!in_array($orderBook->tipo, $tipos)) {
                throw new \Exception("Tipo de ordem inválida");
            }
            
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();
            $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, false);
            
            
            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) { 
                $orderBook->percentualTaxa = number_format($taxas["compra"], 2, ".", "");
            } else {
                $orderBook->percentualTaxa = number_format($taxas["venda"], 2, ".", "");
            }
            
            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) { 
                $orderBook->valorTaxa = (number_format((($orderBook->percentualTaxa / 100) * $orderBook->volumeCurrency), $paridade->moedaBook->casasDecimais, ".", ""));
            } else {
                $orderBook->valorTaxa = (number_format((($orderBook->percentualTaxa / 100) * ($orderBook->volumeCurrency *$orderBook->valorCotacao) / $orderBook->valorCotacao), $paridade->moedaBook->casasDecimais, ".", ""));
            }
            
            $orderBook->volumeCurrency = number_format(($orderBook->volumeCurrency - $orderBook->valorTaxa), $paridade->moedaBook->casasDecimais, ".", "");
            
            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                $orderBook->volumeBloqueado = number_format(( ($orderBook->volumeCurrency + $orderBook->valorTaxa - $orderBook->volumeExecutado - $orderBook->valorTaxaExecutada) * $orderBook->valorCotacao ), $paridade->moedaBook->casasDecimais, ".", "");
                $orderBook->idMoedaBloqueada = $orderBook->idMoedaTrade;
            } else {
                $orderBook->volumeBloqueado = number_format(($orderBook->volumeCurrency + $orderBook->valorTaxa - $orderBook->volumeExecutado - $orderBook->valorTaxaExecutada), $paridade->moedaBook->casasDecimais, ".", "");
                $orderBook->idMoedaBloqueada = $orderBook->idMoedaBook;
            }
            
            if ($orderBook->valorTaxa < 0 || $orderBook->valorTaxaExecutada < 0 || $orderBook->percentualTaxa < 0 || $orderBook->volumeCurrency < 0) {
                $clienteRn = new ClienteRn($this->conexao->adapter);
                
                $clienteFraude = new Cliente(Array("id" => $orderBook->idCliente));
                $clienteRn->conexao->carregar($clienteFraude);
                $clienteFraude->status = 2;
                $clienteFraude->analiseCliente = 1;    
                
                $clienteRn->alterarStatusCliente($clienteFraude);
                                
                $msg = "Tentativa cliente fraude de taxa negativa - " . $clienteFraude->nome;

                \Utils\Notificacao::notificar($msg, true, true, $clienteFraude);
                
                
                $observacaoCliente = new ObservacaoCliente();
                $observacaoCliente->idCliente = $clienteFraude->id;
                $observacaoCliente->observacoes = "Taxa negativa: POST = " . implode("|", $_POST) . " - GET = " . implode("|", $_GET) . " - _SERVER['QUERY_STRING']: " . $_SERVER['QUERY_STRING'] . " -  _SERVER['HTTP_REFERER']: " . $_SERVER["HTTP_REFERER"];

                $observacaoClienteRn = new ObservacaoClienteRn($this->conexao->adapter);
                $observacaoClienteRn->salvar($observacaoCliente);
                
                throw new \Exception("Tipo de ordem inválida");
            }


            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
                if (strpos($ip, ',') !== false) {
                    $ip = substr($ip, 0, strpos($ip, ','));
                }
            $orderBook->idSession = session_id();
            $orderBook->ipSession = $ip;
            
            unset($orderBook->paridade);
            
            $this->conexao->salvar($orderBook);
            
            
            
            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                
                if ($paridade->idMoedaTrade == 1) { 
                   
                    $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
                    $saldoDisponivel = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
                } else {
                    $contaCorrenteBtcRn = new ContaCorrenteBtcRn();
                    $saldoDisponivel = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $paridade->idMoedaTrade, false);
                }

                if ($saldoDisponivel < 0) {
                    $this->conexao->delete("id = {$orderBook->id}");
                    
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                }
                
            } else {
                
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn();
                $saldoDisponivel = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $paridade->idMoedaBook);
                
                if ($saldoDisponivel < 0) {
                    $this->conexao->delete("id = {$orderBook->id}");
                    
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                }
                
            }
            
            $orderBook->paridade = $paridade;
            $this->executarOrdemPassiva($orderBook, $configuracao);
            
            $this->conexao->adapter->finalizar();
            
            
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }

    public function carregar(OrderBook &$orderBook, $carregar = true, $carregarParidade = true) {
        if ($carregar) {
            $this->conexao->carregar($orderBook);
        }
        
        if ($carregarParidade && $orderBook->idParidade > 0) {
            $paridadeRn = new ParidadeRn();
            $orderBook->paridade = new Paridade(Array("id" => $orderBook->idParidade));
            $paridadeRn->carregar($orderBook->paridade, true, true, true);
        }
    }

    public function listar($where = null, $order = null, $offset = null, $limit = null) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $paridades = Array();
        
        $lista = Array();
        foreach ($result as $orderBook) {
            $paridade = (isset($paridades[$orderBook->idParidade]) ? $paridades[$orderBook->idParidade] : null);
            if ($paridade != null) {
                $orderBook->paridade = $paridade;
            } else {
                $this->carregar($orderBook, false, true);
                $paridades[$orderBook->idParidade] = $orderBook->paridade;
            }
            
            $lista[] = $orderBook;
        }
        return $lista;
    }

    public function calcularPreco($volume, $tipo, $idParidade) {
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $cliente = \Utils\Geral::getCliente();
        
        if (!($volume > 0)) {
            throw new \Exception($this->idioma->getText("volumeInvalido"));
        }
        
        $tipos = Array(
            \Utils\Constantes::ORDEM_COMPRA,
            \Utils\Constantes::ORDEM_VENDA
        );
        
        if (!in_array($tipo, $tipos)) {
            throw new \Exception($this->idioma->getText("tipoMovimentoInvalido"));
        }
        
        $paridade = new Paridade(Array("id" => $idParidade));
        try {
            $paridadeRn = new ParidadeRn();
            $paridadeRn->carregar($paridade, true, true, true);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
        }

        if (!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0) {
            $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
        }
        
        $where = Array();
        
        $tipo = ($tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ORDEM_VENDA : \Utils\Constantes::ORDEM_COMPRA);
        
        $where[] = " tipo = '{$tipo}' ";
        $where[] = " executada = 0 ";
        $where[] = " cancelada = 0 ";
        $where[] = " id_paridade = {$idParidade} ";
        
        if ($cliente != null) {
            $where[] = " id_cliente != {$cliente->id} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $ordenacao = ($tipo == \Utils\Constantes::ORDEM_COMPRA ? " DESC " : " ASC ");
        
        $query = " SELECT * FROM order_book "
                . " {$where}  "
                . " ORDER BY valor_cotacao {$ordenacao}, data_cadastro ASC; ";
               
        
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $array=Array();
        
        $valorRestante = $volume;
        
        foreach ($result as $dados) {
            $ordem = new OrderBook($dados);
            if ($valorRestante > 0) {
                
                
                $vol = number_format(($ordem->volumeCurrency - $ordem->volumeExecutado - ($ordem->valorTaxa - $ordem->valorTaxaExecutada)), $paridade->moedaBook->casasDecimais, ".",  "");
                $total = number_format($vol * $ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".",  "");
                
                
                if (($tipo == \Utils\Constantes::ORDEM_VENDA && $total >= $valorRestante)) {
                    
                    $array[] = Array(
                        "volume" => number_format(($valorRestante / $ordem->valorCotacao), $paridade->moedaBook->casasDecimais, ".", ""),
                        "preco" => number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", "")
                    );
                    
                    $valorRestante = 0;
                } else {
                    
                    $array[] = Array(
                        "volume" => number_format($vol, $paridade->moedaBook->casasDecimais, ".", ""),
                        "preco" => number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", "")
                    );
                    
                    if ($tipo == \Utils\Constantes::ORDEM_VENDA) { 
                        $valorRestante = number_format(($valorRestante - $total), $paridade->moedaBook->casasDecimais, ".", "");
                    } else {
                        $valorRestante = number_format(($valorRestante - $vol), $paridade->moedaBook->casasDecimais, ".", "");
                    }
                }
                
            } else {
                break;
            }
            
        }

        $valorTotal = 0;
        $vol = 0;
        
        $maior = 0;
        $menor = 0;
        
        foreach ($array as $precos) {
            if ($precos["preco"] > $maior) {
                $maior = $precos["preco"];
            }
            
            if ($menor == 0 || $precos["preco"] < $menor) {
                $menor = $precos["preco"];
            }
            
            $valorTotal += number_format(($precos["volume"] * $precos["preco"]), $paridade->moedaTrade->casasDecimais, ".", "");
            $vol = number_format(($vol + $precos["volume"]), $paridade->moedaBook->casasDecimais, ".", "");
        }
        
        $preco = number_format(($valorTotal / $vol), $paridade->moedaTrade->casasDecimais, ".", "");
        $volumeFinal = $volume;
        if ($tipo == \Utils\Constantes::ORDEM_VENDA) {
            $volumeFinal = number_format(($volume / $preco), $paridade->moedaBook->casasDecimais, ".", "");
        }
        
        
        return Array("menor" => $menor, "maior" => $maior, "preco" => $preco, "volume" => $volumeFinal);
    }

    public function registrarOrdemCompra($volume, $preco, Paridade $paridade, $direta = true, $reference = 0, $cliente = null) {
        try {
            
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
               $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
            }
            
            if(!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0){
               $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
            }
            
            
            if ($paridade == null || !($paridade instanceof Paridade)) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            if (!$volume > 0) {
                throw new \Exception($this->idioma->getText("volumeInvalido"));
            }
            
            if (!$preco > 0) {
                throw new \Exception($this->idioma->getText("cotacaoInvalido"));
            }
            
            if ($preco < $paridade->precoMinimo) {
                throw new \Exception(str_replace("{var}", number_format($paridade->precoMinimo, $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo .".", $this->idioma->getText("volumeMinimo")));
            }
            
            if ($cliente == null) {
                $cliente = \Utils\Geral::getCliente();
            }
            
            if ($cliente == null || !($cliente instanceof Cliente)) {
                throw new \Exception($this->idioma->getText("sessaInvalida"));
            }
            
            
            if ($paridade->idMoedaTrade == 1) {
                $contaCorrenteReaisRn = new ContaCorrenteReaisRn();
                $saldoDisponivel = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
            } else {
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn();
                $saldoDisponivel = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $paridade->idMoedaTrade, false, false);
            }
                        
            if ($saldoDisponivel < ($volume * $preco)) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            $orderBook = new OrderBook();
            $orderBook->id = 0;
            $orderBook->idCliente = $cliente->id;
            $orderBook->idParidade = $paridade->id;
            $orderBook->tipo = \Utils\Constantes::ORDEM_COMPRA;
            $orderBook->valorCotacao = number_format($preco, $paridade->moedaTrade->casasDecimais, ".", "");
            $orderBook->volumeCurrency = number_format($volume, $paridade->moedaBook->casasDecimais, ".", "");
            
            if ($direta) {
                $orderBook->direta = 1;
                $orderBook->valorCotacaoReferencia = number_format($reference, $paridade->moedaTrade->casasDecimais, ".", "");
            } else {
                $orderBook->direta = 0;
                $orderBook->valorCotacaoReferencia = 0;
            }
            $this->salvar($orderBook);
                        
            return $orderBook;
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function registrarOrdemVenda($volume, $preco, Paridade $paridade, $direta = true, $cliente = null) {
        try {
            if ($paridade == null || !($paridade instanceof Paridade)) {
                throw new \Exception($this->idioma->getText("moedaInvalida"));
            }
            
            if(!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0){
               $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
            }
            
            if(!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0){
               $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
            }
            
            if (!$volume > 0) {
                throw new \Exception($this->idioma->getText("volumeInvalido"));
            }
            
            if (!$preco > 0) {
                throw new \Exception($this->idioma->getText("cotacaoInvalido"));
            }
            
            if ($preco < $paridade->precoMinimo) {
                throw new \Exception(str_replace("{var}", number_format($paridade->precoMinimo, $paridade->moedaTrade->casasDecimais, ",", ".") . " " . $paridade->moedaTrade->simbolo .".", $this->idioma->getText("volumeMinimo")));
            }
            
            if ($cliente == null) {
                $cliente = \Utils\Geral::getCliente();
            }
            
            if ($cliente == null || !($cliente instanceof Cliente)) {
                throw new \Exception($this->idioma->getText("sessaInvalida"));
            }
            
            $orderBook = new OrderBook();
            $orderBook->id = 0;
            $orderBook->idCliente = $cliente->id;
            $orderBook->idParidade = $paridade->id;
            $orderBook->tipo = \Utils\Constantes::ORDEM_VENDA;
            $orderBook->valorCotacao = number_format($preco, $paridade->moedaTrade->casasDecimais, ".", "");
            $orderBook->volumeCurrency = number_format($volume, $paridade->moedaBook->casasDecimais, ".", "");
            
            if ($direta) {
                $orderBook->direta = 1;
                $orderBook->valorCotacaoReferencia = 0;
            } else {
                $orderBook->direta = 0;
                $orderBook->valorCotacaoReferencia = 0;
            }

            $this->salvar($orderBook);

            return $orderBook;
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function getOrders(Paridade $paridade, $tipo = "T", $executada = "T", $cancelada = "T", $limit = 0, $idCliente = 0, $agrupar = false, $ordemVendaDesc = false, $ordemCompraDesc = false) {
        $where = Array();

        if ($tipo != "T") {
            if ($tipo != \Utils\Constantes::ORDEM_COMPRA && $tipo != \Utils\Constantes::ORDEM_VENDA) {
                throw new \Exception($this->idioma->getText("tipoOrdemInvalida"));
            }
            $where[] = " ob.tipo = '{$tipo}' ";
        }

        if ($executada != "T") {
            if ($executada == "S") {
                $where[] = " ob.executada = 1 ";
            } else {
                $where[] = " ob.executada = 0 ";
            }
        }

        if ($cancelada != "T") {
            if ($cancelada == "S") {
                $where[] = " ob.cancelada = 1 ";
            } else {
                $where[] = " ob.cancelada = 0 ";
            }
        }

        if ($idCliente > 0) {
            $where[] = " ob.id_cliente = {$idCliente} ";
        }

        if ($paridade->id > 0) { 
            $where[] = " ob.id_paridade = {$paridade->id} ";
        }

        $columns = "ob.*";
        $groupby = "";
        $having = "";
        if ($agrupar) {
            if ($tipo == \Utils\Constantes::ORDEM_COMPRA) { 
                $columns = "SUM(ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) AS volume , SUM(ob.volume_executado) AS volume_executado, AVG(ob.valor_cotacao) AS valor_cotacao, ob.tipo, ob.id_paridade, ob.id_cliente, ob.id";
            } else {
                $columns = "SUM(ob.volume_currency - ob.volume_executado) AS volume , SUM(ob.volume_executado) AS volume_executado, AVG(ob.valor_cotacao) AS valor_cotacao, ob.tipo, ob.id_paridade, ob.id_cliente, ob.id";
            }
            $groupby = " GROUP BY ob.valor_cotacao, ob.tipo, ob.id_paridade ";
            $having = " HAVING  volume > 0 ";
        }

        $where = (sizeof($where) > 0 ?  " WHERE "  . implode(" AND ", $where) : "");


        $order = ($tipo != \Utils\Constantes::ORDEM_COMPRA ? "ASC" :  "DESC");

        $query = "SELECT {$columns} FROM order_book ob "
                . " {$where} "
                . " {$groupby} "
                . " {$having} "
                . " ORDER BY ob.tipo ASC,  ob.valor_cotacao ".($tipo != \Utils\Constantes::ORDEM_COMPRA ? "ASC" :  "DESC").", ob.id ";

        if ($limit > 0) {
            $query .= " LIMIT {$limit} ";
        }

        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        $paridadeRn = new ParidadeRn();

        foreach ($result as $dados) {
            $orderBook = new OrderBook($dados);
            if ($agrupar) { 
                $orderBook->volumeCurrency = $dados["volume"];
                $orderBook->id = $dados["id"];
            } else {
                if ( $dados["tipo"] == \Utils\Constantes::ORDEM_COMPRA) { 
                    $orderBook->volumeCurrency = $dados["volume_currency"] - $dados["volume_executado"] + $dados["valor_taxa"] - $dados["valor_taxa_executada"];
                } else {
                    $orderBook->volumeCurrency = $dados["volume_currency"] - $dados["volume_executado"];
                }
            }

            if ($paridade->id > 0) {
                $orderBook->paridade = new Paridade();
                $orderBook->paridade->ativo = $paridade->ativo;
                $orderBook->paridade->id = $paridade->id;
                $orderBook->paridade->idMoedaBook = $paridade->idMoedaBook;
                $orderBook->paridade->idMoedaTrade = $paridade->idMoedaTrade;
                $orderBook->paridade->moedaBook = $paridade->moedaBook;
                $orderBook->paridade->moedaTrade = $paridade->moedaTrade;
                $orderBook->paridade->statusMercado = $paridade->statusMercado;
                $orderBook->paridade->symbol = $paridade->symbol;
            } else {
                $orderBook->paridade = new Paridade(Array("id" => $orderBook->idParidade));
                $paridadeRn->carregar($orderBook->paridade, true, true, true);
            }
            $lista[] = $orderBook;
        }
            
        return $lista;
    }

    public function getValorTotalOrdensReais(Cliente $cliente, Paridade $paridade) {
        
        $query = " SELECT SUM(volume_bloqueado) AS total FROM order_book WHERE id_moeda_bloqueada = {$paridade->idMoedaTrade} AND executada = 0 AND cancelada = 0 AND id_cliente = {$cliente->id};" ;

        $result = $this->conexao->adapter->query($query)->execute();
        
        $saldo = 0;
        
        foreach ($result as $dados) {
            $saldo += ($dados["total"] != null ? $dados["total"] : 0);
        }
        
        return number_format($saldo, $paridade->moedaTrade->casasDecimais, ".", "");
    }

    public function getValorTotalOrdensPorMoeda(Cliente $cliente, $idMoeda) {
        $moeda = MoedaRn::get($idMoeda);
        
        
        $saldo = 0;
        
        $inVenda = " (ob.tipo = '".\Utils\Constantes::ORDEM_VENDA."' AND  ob.id_paridade IN (SELECT id  FROM paridades  WHERE id_moeda_book = {$moeda->id} AND ativo > 0) ) AND ";
        $inCompra = " (ob.tipo = '".\Utils\Constantes::ORDEM_COMPRA."' AND  ob.id_paridade IN (SELECT id FROM paridades WHERE id_moeda_trade = {$moeda->id} AND ativo > 0) ) AND ";

        $query = " SELECT "
            . " SUM(ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) AS total "
            . " FROM order_book ob "
            . " WHERE "
            . " ob.executada = 0 AND "
            . " ob.cancelada = 0 AND "
            . " ob.id_cliente = {$cliente->id} AND "
            . " {$inVenda} "
            . " ob.data_cadastro >= '2018-08-24 18:00:00' "
            . " UNION "
            . " SELECT "
                . " SUM(ob.volume_currency - ob.volume_executado) AS total "
                . " FROM order_book ob "
                . " WHERE "
                . " ob.executada = 0 AND "
                . " ob.cancelada = 0 AND "
                . " ob.id_cliente = {$cliente->id} AND "
                . " {$inVenda} "
                . " ob.data_cadastro < '2018-08-24 18:00:00' "

            . " UNION "

            . " SELECT "
            . " SUM((ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) * ob.valor_cotacao) AS total "
            . " FROM order_book ob "
            . " WHERE "
            . " ob.executada = 0 AND "
            . " ob.cancelada = 0 AND "
            . " ob.id_cliente = {$cliente->id} AND "
            . " {$inCompra} "
            . " ob.data_cadastro >= '2018-08-24 18:00:00' "
            . " UNION "
            . " SELECT "
                . " SUM((ob.volume_currency - ob.volume_executado) * ob.valor_cotacao) AS total "
                . " FROM order_book ob "
                . " WHERE "
                . " ob.executada = 0 AND "
                . " ob.cancelada = 0 AND "
                . " ob.id_cliente = {$cliente->id} AND "
                . " {$inCompra} "
                . " ob.data_cadastro < '2018-08-24 18:00:00' ";

        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $saldo += ($dados["total"] != null ? $dados["total"] : 0);
        }
        
        return number_format($saldo, $moeda->casasDecimais, ".", "");
        
    }

    public function getValorTotalOrdensCurrency(Cliente $cliente, $idParidade) {
        
        $paridade = ParidadeRn::get($idParidade);
        
        $query = "SELECT "
                . " SUM(ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) AS total "
                . " FROM order_book ob "
                . " WHERE "
                . " ob.executada = 0 AND "
                . " ob.cancelada = 0 AND "
                . " ob.tipo = '".\Utils\Constantes::ORDEM_VENDA."' AND "
                . " ob.id_cliente = {$cliente->id} AND "
                . " ob.id_paridade = {$idParidade} AND "
                . " ob.data_cadastro >= '2018-08-24 18:00:00' "
                . " UNION  "
                . " SELECT "
                . " SUM(ob.volume_currency - ob.volume_executado) AS total "
                . " FROM order_book ob "
                . " WHERE "
                . " ob.executada = 0 AND "
                . " ob.cancelada = 0 AND "
                . " ob.tipo = '".\Utils\Constantes::ORDEM_VENDA."' AND "
                . " ob.id_cliente = {$cliente->id} AND "
                . " ob.id_paridade = {$idParidade} AND "
                . " ob.data_cadastro < '2018-08-24 18:00:00' ";
             
        $result = $this->conexao->adapter->query($query)->execute();
        
        $saldo = 0;
        foreach ($result AS $dados) {
            $saldo += ($dados["total"] != null ? $dados["total"] : 0);
        }
        
        
        return number_format($saldo, $paridade->moedaTrade->casasDecimais, ".", "");
    }

    public function cancelar(OrderBook $orderBook) {
        
        try {
            $this->conexao->carregar($orderBook);
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("ordemInvalidaNaoEncontrada"));
        }
        
        $orderBook->cancelada = 1;
        $query = "UPDATE order_book AS ob_w set cancelada = 1 WHERE id = {$orderBook->id};";
        $this->conexao->adapter->query($query)->execute();
        
        $this->calcularSaldoBloqueadoOrdem($orderBook);
        
    }

    public function calcularSaldoBloqueadoOrdem(OrderBook $orderBook) {
        
        if ($orderBook->cancelada > 0 || $orderBook->executada > 0) {
            $update = "UPDATE order_book ob SET  volume_bloqueado = 0 WHERE id = {$orderBook->id};";
        } else if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
            $update = "UPDATE order_book ob SET  volume_bloqueado = ((ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) * ob.valor_cotacao) WHERE id = {$orderBook->id};";
        } else {
            $update = "UPDATE order_book ob SET  volume_bloqueado = (ob.volume_currency - ob.volume_executado + ob.valor_taxa - ob.valor_taxa_executada) WHERE id = {$orderBook->id};";
        }

        $this->conexao->adapter->query($update)->execute();
    }

    public function finalizada(OrderBook $orderBook) {
        try {
            $this->conexao->carregar($orderBook);
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("ordemInvalidaNaoEncontrada"));
        }

        $orderBook->executada = 1;
        $query = "UPDATE order_book AS ob_w set executada = 1 WHERE id = {$orderBook->id};";
        $this->conexao->adapter->query($query)->execute();
    }

    public function executarOrdemPassiva(OrderBook $orderBook, $configuracao = null) {
        
        if ($configuracao == null) {
            $configuracao = ConfiguracaoRn::get();
        }
     
        $cliente = new Cliente(Array("id" => $orderBook->idCliente));
        
        if ($orderBook->paridade == null) {
            $paridade = ParidadeRn::get($orderBook->idParidade);
        } else {
            $paridade = $orderBook->paridade;
        }
        
        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
        }
        
        if (!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0) {
            $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
        }

        $tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ORDEM_VENDA : \Utils\Constantes::ORDEM_COMPRA);
        $ordenacao = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? " ASC " : " DESC ");
        
        $cotacaoReferencia = number_format($orderBook->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", "");
        if ($orderBook->direta > 0 && $orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
            $cotacaoReferencia = number_format($orderBook->valorCotacaoReferencia, $paridade->moedaTrade->casasDecimais, ".", "");
        }
        
        if($cotacaoReferencia <= 0) {
            $cotacaoReferencia = 0;
        }
        
        
        if (self::$homolog) { 
            $ordens = $this->listOrdersHomolog;
        } else {
            $select = new \Zend\Db\Sql\Select();
            $select->from(Array("ob_r" => "order_book"));
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo("tipo", $tipo);

            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                $where->lessThanOrEqualTo("valor_cotacao", number_format($cotacaoReferencia, $paridade->moedaTrade->casasDecimais, ".", ""));
            } else {
                $where->greaterThanOrEqualTo("valor_cotacao", number_format($cotacaoReferencia, $paridade->moedaTrade->casasDecimais, ".", ""));
            }

            $where->notEqualTo("id_cliente", $orderBook->idCliente);
            $where->equalTo("id_paridade", $orderBook->idParidade);
            $where->equalTo("executada", 0);
            $where->equalTo("cancelada", 0);

            $select->where($where);

            $select->order(["valor_cotacao {$ordenacao}", 'data_cadastro ASC']);

            $sql = new \Zend\Db\Sql\Sql($this->conexao->adapter);
            $statement = $sql->prepareStatementForSqlObject($select);

            $ordens = $statement->execute();

        }
        
        $valorInicialTaxaDescontada = number_format(($orderBook->valorTaxa - $orderBook->valorTaxaExecutada), $paridade->moedaBook->casasDecimais, ".", "");
        
        if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) { 
            $volumeRestante = number_format(($orderBook->volumeCurrency - $orderBook->volumeExecutado + ($orderBook->valorTaxa - $orderBook->valorTaxaExecutada)), $paridade->moedaBook->casasDecimais, ".", "");
        } else {
            $volumeRestante = number_format(($orderBook->volumeCurrency - $orderBook->volumeExecutado), $paridade->moedaBook->casasDecimais, ".", "");
        }
        
        $volumeParaSerExecutado = $volumeRestante; // esta variável vai ajudar a cobrar o valor da taxa proporcional
        
        $valorExecutado = 0;
        $ordemExecutadaRn = new OrdemExecutadaRn($this->conexao->adapter);
        
        $valorTotalExecutadoOrdemPlotada = 0;
        if (sizeof($ordens) > 0) {
            foreach ($ordens as $key=>$dados) {
                $ordem = new OrderBook($dados);
                
                if ($volumeRestante > 0) { 
                    $volumeExecutadoOrdem = 0;
                    $ordemExecutada = new OrdemExecutada();
                    
                    $ordemExecutada->id = 0;
                    
                    $ordemExecutada->direta = $orderBook->direta;
                    $ordemExecutada->idParidade = $paridade->id;
                    $ordemExecutada->idMoedaBook = $paridade->idMoedaBook;
                    $ordemExecutada->idMoedaTrade = $paridade->idMoedaTrade;
                    $ordemExecutada->symbol = $paridade->symbol;

                    if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                        
                        $ordemExecutada->idClienteComprador = $orderBook->idCliente;
                        $ordemExecutada->idClienteVendedor = $ordem->idCliente;
                        $ordemExecutada->nomeComprador = $orderBook->nomeCliente;
                        $ordemExecutada->nomeVendedor = $dados["nome_cliente"];
                        
                    } else {
                        
                        $ordemExecutada->idClienteComprador = $orderBook->idCliente;
                        $ordemExecutada->idClienteVendedor = $ordem->idCliente;
                        $ordemExecutada->nomeComprador = $dados["nome_cliente"];
                        $ordemExecutada->nomeVendedor =$orderBook->nomeCliente;
                        
                    }
                    
                    if ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                        // se a ordem a ser executada for de compra eu não posso descontar a taxa antes pois o volume em BTC ainda não existe
                        $volumeDisponivel = number_format(($ordem->volumeCurrency - $ordem->volumeExecutado + ($ordem->valorTaxa - $ordem->valorTaxaExecutada)), $paridade->moedaBook->casasDecimais, ".", "");
                    } else {
                        // se for uma ordem de venda então o volume já está em BTC e a taxa é descontada
                        $volumeDisponivel = number_format(($ordem->volumeCurrency - $ordem->volumeExecutado), $paridade->moedaBook->casasDecimais, ".", "");
                    }
                    
                    //exit("O volume disponivel é {$volumeDisponivel}");
                    if ($volumeDisponivel >= $volumeRestante) {
                        // o volume na ordem passiva é superior ou igual ao volume para execução
                        $volumeExecutadoOrdem = $volumeRestante; // o volume a ser executado se torna o volume executado na ordem passiva
                        $ordemExecutada->volumeExecutado = number_format($volumeRestante, $paridade->moedaBook->casasDecimais, ".", ""); // gravando os dados no histórico da ordem
                        $volumeRestante = 0; // o volume para execução é zerado
                    } else {
                        // aqui o valor a ser executado é superior ao valor disponível na ordem passiva
                        $volumeRestante = number_format(($volumeRestante - $volumeDisponivel), $paridade->moedaBook->casasDecimais, ".", ""); //calculo o volume que ainda falta para zerar a ordem ativa
                        $ordemExecutada->volumeExecutado = number_format($volumeDisponivel, $paridade->moedaBook->casasDecimais, ".", ""); // gravando os dados no histórico da ordem
                        $volumeExecutadoOrdem = $volumeDisponivel; // o valor a ser consumido na ordem passiva é armazenado
                    }
                    
                    $ordemExecutada->valorCotacao = number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""); // armazeno o histórico da execução da ordem
                    $ordemExecutada->tipo = $orderBook->tipo; // armazeno o histórico de execução da ordem

                    if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                        $ordemExecutada->idOrderBookCompra = $orderBook->id;

                        $ordemExecutada->idOrderBookVenda = $ordem->id;
                    } else {
                        $ordemExecutada->idOrderBookCompra = $ordem->id;
                        $ordemExecutada->idOrderBookVenda = $orderBook->id;
                    }

                    if ($volumeExecutadoOrdem > 0) {
                        
                        $ordemExecutadaRn->salvar($ordemExecutada); // salvo o histórico
                        
                        $valorTotalExecutadoOrdemPlotada += number_format(($ordemExecutada->valorCotacao * $ordemExecutada->volumeExecutado), $paridade->moedaTrade->casasDecimais, ".", "");
                        
                        $valorOrdem = number_format(($ordemExecutada->valorCotacao * $ordemExecutada->volumeExecutado), $paridade->moedaTrade->casasDecimais, ".", "");
                        //exit($valorOrdem . " - " . $valorTotalExecutadoOrdemPlotada);
                        $this->calcularExecucaoOrdem($ordem, $ordemExecutada->volumeExecutado, $paridade, $valorOrdem, "Passiva");
                        $this->calcularSaldoBloqueadoOrdem($ordem);
                    }
                } else {
                    break; // quebro o laço quando a ordem ativa é zerada
                }
            } // fim do foreach

            //$volumeExecutadoOrdemAtiva = number_format((($orderBook->volumeCurrency - $orderBook->volumeExecutado) - $volumeRestante), $paridade->moedaBook->casasDecimais, ".", "");
            $volumeExecutadoOrdemAtiva = number_format(($volumeParaSerExecutado - $volumeRestante), $paridade->moedaBook->casasDecimais, ".", "");
            
            if ($volumeExecutadoOrdemAtiva > 0) {
                $valorExecutado = $this->calcularExecucaoOrdem($orderBook, $volumeExecutadoOrdemAtiva, $paridade, $valorTotalExecutadoOrdemPlotada, "Ativa");
            }
            $this->calcularSaldoBloqueadoOrdem($orderBook);
            
        }
    }

    private function calcularExecucaoOrdem(OrderBook &$orderBook, $volumeExecutado, Paridade $paridade, $valorTotal, $tipoOrdem = null) {

            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                $valorTaxa = number_format(($orderBook->percentualTaxa > 0 ? ($volumeExecutado * ($orderBook->percentualTaxa / 100)) : 0), $paridade->moedaBook->casasDecimais, ".", "");

            } else {
                $valorTaxa = number_format(($orderBook->valorTaxa * ($volumeExecutado / $orderBook->volumeCurrency)), $paridade->moedaBook->casasDecimais, ".", "");

            }
            
            //$valorTotal = number_format($volumeExecutado * $orderBook->valorCotacao, 8, ".", "");

            if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                $volumeExecutado = number_format(($volumeExecutado - $valorTaxa), $paridade->moedaBook->casasDecimais, ".", "");

            }

            $volumeSomado = number_format(($volumeExecutado + $orderBook->volumeExecutado), $paridade->moedaBook->casasDecimais, ".", "");

            //exit($volumeSomado . " - " . $orderBook->volumeCurrency . " = " . ($volumeSomado - $orderBook->volumeCurrency));
            if (($volumeSomado >= $orderBook->volumeCurrency) || (($volumeSomado - $orderBook->volumeCurrency) == -0.00000001)) {
                $orderBook->volumeExecutado = $orderBook->volumeCurrency;
                $orderBook->executada = 1;
                $orderBook->valorTaxaExecutada = $orderBook->valorTaxa;
            } else {
                $orderBook->volumeExecutado += $volumeExecutado;
                $orderBook->valorTaxaExecutada += $valorTaxa;
            }

            $this->conexao->update(
                    Array(
                        "volume_executado" => number_format($orderBook->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ""),
                        "executada" => $orderBook->executada,
                        "valor_taxa_executada" => number_format($orderBook->valorTaxaExecutada, $paridade->moedaBook->casasDecimais, ".", "")
                    ),
                    Array("id" => $orderBook->id)
            );
            
            //Verifica Volume de REAU - Evita ordens com menos de 1 volume
            if ($orderBook->idParidade == 28) {
                $volumeAux = number_format(($orderBook->volumeCurrency - $orderBook->volumeExecutado), 8, ".", "");

                if ($volumeAux <= 1) {
                    $this->conexao->update(
                            Array(
                                "volume_executado" => number_format($orderBook->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", ""),
                                "executada" => 1,
                                "volume_bloqueado" => 0,
                            ),
                            Array("id" => $orderBook->id)
                    );
                }
            }


        if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                
                $this->gerarContaCorrente($orderBook, $volumeExecutado, $valorTotal, $paridade);
                
            } else {
                
                $volumeExecutadoSomaTaxa = number_format(($volumeExecutado + $valorTaxa), $paridade->moedaBook->casasDecimais, ".", "");

                $this->gerarContaCorrente($orderBook, $volumeExecutadoSomaTaxa, $valorTotal, $paridade);
            }
            
            $this->registrarTaxaBtc($orderBook, $valorTaxa, $paridade);
    }

    private function gerarContaCorrente(OrderBook $orderBook, $volumeExecutado, $valorReais, Paridade $paridade) {
        
        if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
            $descricao = "Compra de {$paridade->moedaBook->nome}.";
        } else {
            $descricao = "Venda de {$paridade->moedaBook->nome}.";
        }
        
        //Moeda Book
        $contaCorrenteBtc = new ContaCorrenteBtc();
        $contaCorrenteBtc->id = 0;
        $contaCorrenteBtc->autorizada = 1;
        $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteBtc->descricao = $descricao;
        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
        $contaCorrenteBtc->enderecoBitcoin = "";
        $contaCorrenteBtc->executada = 1;
        $contaCorrenteBtc->origem = 1;
        $contaCorrenteBtc->idCliente = $orderBook->idCliente;
        $contaCorrenteBtc->idMoeda = $paridade->idMoedaBook;
        $contaCorrenteBtc->tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ENTRADA : \Utils\Constantes::SAIDA);
        $contaCorrenteBtc->transferencia = 0;
        $contaCorrenteBtc->valor = number_format($volumeExecutado, $paridade->moedaBook->casasDecimais, ".", "");
        $contaCorrenteBtc->valorTaxa = 0;
        $contaCorrenteBtc->orderBook = 1;
         
        $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
        $contaCorrenteBtcRn->gerarContaCorrente($contaCorrenteBtc, NULL);
        
       
        //Moeda Trade
        if ($paridade->idMoedaTrade == 1) {    
            
            //Tramento especial para REAU
            if($orderBook->idParidade != 28){
                $contaCorrenteReais = new ContaCorrenteReais();
                $contaCorrenteReais->id = 0;
                $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteReais->descricao = $descricao;
                $contaCorrenteReais->idCliente = $orderBook->idCliente;
                $contaCorrenteReais->tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::SAIDA : \Utils\Constantes::ENTRADA);
                $contaCorrenteReais->transferencia = 0;
                $contaCorrenteReais->origem = 1;
                $contaCorrenteReais->valor = number_format($valorReais, $paridade->moedaTrade->casasDecimais, ".", "");
                $contaCorrenteReais->orderBook = 1;

                $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
                $contaCorrenteReaisRn->gerarContaCorrente($contaCorrenteReais);
                
            } else {
                //Tramento especial para REAU
                if($orderBook->tipo == \Utils\Constantes::ORDEM_VENDA){
                    
                    $valorAux = number_format(($orderBook->volumeCurrency * $orderBook->valorCotacao), $paridade->casasDecimaisMoedaTrade, ".", "");
                    //exit($valorAux . " - " . $valorReais);
                    if($valorAux >= $valorReais){
                        
                        $contaCorrenteReais = new ContaCorrenteReais();
                        $contaCorrenteReais->id = 0;
                        $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteReais->descricao = $descricao;
                        $contaCorrenteReais->idCliente = $orderBook->idCliente;
                        $contaCorrenteReais->tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::SAIDA : \Utils\Constantes::ENTRADA);
                        $contaCorrenteReais->transferencia = 0;
                        $contaCorrenteReais->origem = 1;
                        $contaCorrenteReais->valor = number_format($valorReais, $paridade->moedaTrade->casasDecimais, ".", "");
                        $contaCorrenteReais->orderBook = 1;

                        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
                        $contaCorrenteReaisRn->gerarContaCorrente($contaCorrenteReais);
                    } else {
                        
                        $clienteRn = new ClienteRn();
                        $cliente = new Cliente(Array("id" => $orderBook->idCliente));
                        $clienteRn->conexao->carregar($cliente);
                        $cliente->status = 0;
                        $clienteRn->alterarStatusCliente($cliente);
                        
                        $mensagem = "MERCADO REAU - Falha execução de Ordem | "
                                . " Cliente: " . $cliente->nome
                                . " - E-mail: " . $cliente->email
                                . " - Status Cliente: Em Espera"
                                . " - Ordem: " . $orderBook->id
                                . " - Tipo: " . $orderBook->tipo
                                . " - Paridade: " . $orderBook->symbol
                                . " - Volume: " . number_format($orderBook->volumeCurrency, $paridade->moedaBook->casasDecimais, ",", ".")
                                . " - Volume Executado: " . number_format($orderBook->volumeExecutado, $paridade->moedaBook->casasDecimais, ",", ".")
                                . " - Volume Executado Operação: " . number_format($volumeExecutado, $paridade->moedaBook->casasDecimais, ",", ".")
                                . " - Valor Ordem R$: " . number_format($valorAux, $paridade->moedaTrade->casasDecimais, ",", ".")
                                . " - Valor Errado R$: " . number_format($valorReais, $paridade->moedaTrade->casasDecimais, ",", ".");
                        
                        \Utils\Notificacao::notificar($mensagem, true, false, $cliente);
                    }
                } else {
                    
                    $contaCorrenteReais = new ContaCorrenteReais();
                    $contaCorrenteReais->id = 0;
                    $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteReais->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteReais->descricao = $descricao;
                    $contaCorrenteReais->idCliente = $orderBook->idCliente;
                    $contaCorrenteReais->tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::SAIDA : \Utils\Constantes::ENTRADA);
                    $contaCorrenteReais->transferencia = 0;
                    $contaCorrenteReais->origem = 1;
                    $contaCorrenteReais->valor = number_format($valorReais, $paridade->moedaTrade->casasDecimais, ".", "");
                    $contaCorrenteReais->orderBook = 1;

                    $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
                    $contaCorrenteReaisRn->gerarContaCorrente($contaCorrenteReais);
                }
            }
            
        } else {
            
            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->descricao = $descricao;
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->enderecoBitcoin = "";
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->origem = 1;
            $contaCorrenteBtc->idCliente = $orderBook->idCliente;
            $contaCorrenteBtc->idMoeda = $paridade->idMoedaTrade;
            $contaCorrenteBtc->tipo = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::SAIDA : \Utils\Constantes::ENTRADA);
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->valor = number_format($valorReais, $paridade->moedaTrade->casasDecimais, ".", "");
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->orderBook = 1;
            $contaCorrenteBtc->moeda = $paridade->idMoedaTrade;
            
            $contaCorrenteBtcRn->gerarContaCorrente($contaCorrenteBtc, NULL);
        }
    }

    private function registrarTaxaBtc(OrderBook $orderBook, $valorTaxa, Paridade $paridade) {
        if ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA) {
            $descricao = "Taxa ordem {$orderBook->id}: Compra de {$paridade->moedaBook->nome}.";
        } else {
            $descricao = "Taxa ordem {$orderBook->id}: Venda de {$paridade->moedaBook->nome}.";
        }

        $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
        $contaCorrenteEmpresaBtc->id = 0;
        $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteEmpresaBtc->descricao = $descricao;
        $contaCorrenteEmpresaBtc->idMoeda = $paridade->idMoedaBook;
        $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteEmpresaBtc->transferencia = 0;
        $contaCorrenteEmpresaBtc->valor = number_format($valorTaxa, $paridade->moedaBook->casasDecimais, ".", "");

        $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
        $contaCorrenteEmpresaBtcRn->gerarContaCorrente($contaCorrenteEmpresaBtc, NULL);

        $cliente = new Cliente(Array("id" => $orderBook->idCliente));
        $clienteRn = new ClienteRn($this->conexao->adapter);
        $clienteRn->conexao->carregar($cliente);

        if ($cliente->idReferencia > 0) {
            $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia, true);
                $data = [
                    'client_id' => $cliente->idReferencia,
                    'level' => 1,
                    'category' => 'orderBook',
                    'id_orderbook' => $orderBook->id,
                ];

                $result = \LambdaAWS\QueueKYC::sendQueue('ex.comissions', $data);
        }

        // if ($cliente->idReferencia > 0) {
        //     $clienteHasComissao = ClienteHasComissaoRn::get($cliente->idReferencia, true);
        //     if ($clienteHasComissao != null) {
        //         $comissao = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? $clienteHasComissao->compra : $clienteHasComissao->venda);

        //         $clienteRecebeComissao = new Cliente(Array("id" => $cliente->idReferencia));
        //         $clienteRn->conexao->carregar($clienteRecebeComissao);

        //         if ($clienteRecebeComissao->documentoVerificado == 1 && $comissao > 0) {
        //             $valorComissao = number_format(($valorTaxa * ($comissao/100)), $paridade->moedaBook->casasDecimais , ".", "");

        //             if ($valorComissao > 0) {
        //                 $contaCorrenteBtc = new ContaCorrenteBtc();
        //                 $contaCorrenteBtc->id = 0;
        //                 $contaCorrenteBtc->autorizada = 1;
        //                 $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        //                 $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
        //                 $contaCorrenteBtc->descricao = "Pagamento de comissão";
        //                 $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
        //                 $contaCorrenteBtc->enderecoBitcoin = "";
        //                 $contaCorrenteBtc->executada = 1;
        //                 $contaCorrenteBtc->origem = 2;
        //                 $contaCorrenteBtc->idReferenciado = $cliente->id;
        //                 $contaCorrenteBtc->idCliente = $cliente->idReferencia;
        //                 $contaCorrenteBtc->idMoeda = $paridade->idMoedaBook;
        //                 $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
        //                 $contaCorrenteBtc->transferencia = 0;
        //                 $contaCorrenteBtc->valor = $valorComissao;
        //                 $contaCorrenteBtc->valorTaxa = 0;
        //                 $contaCorrenteBtc->orderBook = 1;
        //                 $contaCorrenteBtc->moeda = $paridade->moedaBook;
        //                 $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
        //                 $contaCorrenteBtcRn->gerarContaCorrente($contaCorrenteBtc);

        //                 $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
        //                 $contaCorrenteEmpresaBtc->id = 0;
        //                 $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        //                 $contaCorrenteEmpresaBtc->descricao = "Pagamento Comissao Book " . $orderBook->id;
        //                 $contaCorrenteEmpresaBtc->idMoeda = $paridade->idMoedaBook;
        //                 $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
        //                 $contaCorrenteEmpresaBtc->transferencia = 0;
        //                 $contaCorrenteEmpresaBtc->valor = $valorComissao;

        //                 $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
        //             }
                // }
            }
        }
        
        $orderBook->valorTaxaExecutada = number_format(($orderBook->valorTaxaExecutada), $paridade->moedaBook->casasDecimais, ".", "");
        $this->conexao->update(Array("valor_taxa_executada" => number_format($orderBook->valorTaxaExecutada, $paridade->moedaBook->casasDecimais, ".", "")), Array("id" => $orderBook->id));
        
    }

    public function getPrecos($idParidade) {
    
        // $configuracao = new Configuracao(Array("id" => 1));
        // $configuracaoRn = new ConfiguracaoRn();
        // $configuracaoRn->conexao->carregar($configuracao);
    
        // $paridade = new Paridade(Array("id" => $idParidade));
        // $paridadeRn = new ParidadeRn();
        // $paridadeRn->carregar($paridade);
    
        // $casasDecimais = ($paridade->idMoedaTrade == 1 ? $configuracao->qtdCasasDecimais : 8);
    
        // // preço para compra
        // $queryCompra =  " SELECT ob_r.* FROM order_book ob_r "
        //         . " WHERE  "
        //         . " tipo = '".\Utils\Constantes::ORDEM_COMPRA."' AND "
        //         . " id_paridade = {$paridade->id} "
        //         . " AND executada = 0 "
        //         . " AND cancelada = 0 "
        //         . " AND (volume_currency - volume_executado) > 0 "
        //         . " ORDER BY valor_cotacao DESC, data_cadastro ASC"
        //         . " LIMIT 1; ";
            
            
        // // preço para venda
        // $queryVenda =  " SELECT ob_r.* FROM order_book ob_r "
        //         . " WHERE  "
        //         . " tipo = '".\Utils\Constantes::ORDEM_VENDA."' AND "
        //         . " id_paridade = {$paridade->id} "
        //         . " AND executada = 0 "
        //         . " AND cancelada = 0 "
        //         . " AND (volume_currency - volume_executado) > 0 "
        //         . " ORDER BY valor_cotacao ASC, data_cadastro ASC"
        //         . " LIMIT 1; ";
            
        // $tipoCompra = \Utils\Constantes::ORDEM_COMPRA; 
        // $tipoVenda = \Utils\Constantes::ORDEM_VENDA;        
        // $queryUltimoPreco = " SELECT oe.valor_cotacao "
        //         . " FROM ordens_executadas oe "
        //         . " INNER JOIN order_book ob ON ((oe.tipo = '{$tipoVenda}' AND oe.id_order_book_venda = ob.id) OR (oe.tipo = '{$tipoCompra}' AND oe.id_order_book_compra = ob.id) ) "
        //         . " WHERE ob.id_paridade = {$paridade->id} "
        //         . " ORDER BY oe.data_execucao DESC, oe.id DESC "
        //         . " LIMIT 1 ";
    
    
    
        // $compra = 0;
        // $venda = 0;
        // $ultimo = 0;
    
        // $dadosCompra = $this->conexao->executeSql($queryCompra);
        // $dadosVenda = $this->conexao->executeSql($queryVenda);
        // $dadosUltimoPreco = $this->conexao->executeSql($queryUltimoPreco);
    
        // foreach ($dadosCompra as $dc) {
        //     $compra = number_format($dc["valor_cotacao"], $paridade->moedaTrade->casasDecimais, ".", "");
        // }
    
        // foreach ($dadosVenda as $dv) {
        //     $venda = number_format($dv["valor_cotacao"], $paridade->moedaTrade->casasDecimais, ".", "");
        // }
    
    
        // foreach ($dadosUltimoPreco as $du) {
        //     $ultimo = number_format($du["valor_cotacao"], $paridade->moedaTrade->casasDecimais, ".", "");
        // }
    
        // return Array("compra" => $compra, "venda" => $venda, "ultimo" => $ultimo);
    }

    public function getExtrato($idParidade = 0, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = "T", $executada = "S", $cancelada = "T", $limit = 0, $idCliente = 0, $direto = "T") {
       
        $where = Array();
        
        if ($idCliente > 0) {
            $where[] = " ob.id_cliente = {$idCliente} ";
        }
        
        if ($idParidade > 0) {
            $where[] = " id_paridade = {$idParidade} ";
        }
        
        if ($tipo != "T") {
            if ($tipo != \Utils\Constantes::ORDEM_COMPRA && $tipo != \Utils\Constantes::ORDEM_VENDA) {
                throw new \Exception($this->idioma->getText("tipoOrdemInvalida"));
            }
            $where[] = " ob.tipo = '{$tipo}' ";
        }
        
        if ($executada != "T") {
            if ($executada == "S") {
                $where[] = " ob.executada = 1 ";
            } else if ($executada == "N"){
                $where[] = " ob.executada = 0 ";
            } else if ($executada == "M"){
                $where[] = " ob.volume_executado > 0 ";
            }
        }
        
        if ($cancelada != "T") {
            if ($cancelada == "S") {
                $where[] = " ob.cancelada = 1 ";
            } else {
                $where[] = " ob.cancelada = 0 ";
            }
        }
        
        if (!is_string($direto)) {
            if($direto){
                $where[] = " ob.direta > 0 ";
            }
            else {
                $where[] = " ob.direta < 1 ";
            }
        }
        
        if (isset($dataInicial->data) &&  $dataInicial->data != null && isset($dataFinal->data) &&  $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " ob.data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        $where = (sizeof($where) > 0 ?  " WHERE "  . implode(" AND ", $where) : "");
        
        $query = "SELECT ob.* FROM order_book ob "
                . " {$where} "
                . " ORDER BY ob.id DESC ";
                
        if ($limit > 0) {
            $query .= " LIMIT {$limit} ";
        }
        
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $orderBook = new OrderBook($dados);
            $lista[] = $orderBook;
        }
        return $lista;
    }

    public function getExtratoConsolidado(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = "T", $executada = "S", $cancelada = "T", $limit = 0, $idCliente = 0, $direto = "T") {
       
        $where = Array();
        
        if (isset($dataInicial->data) &&  $dataInicial->data != null && isset($dataFinal->data) &&  $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " ob.data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if ($tipo != "T") {
            if ($tipo != \Utils\Constantes::ORDEM_COMPRA && $tipo != \Utils\Constantes::ORDEM_VENDA) {
                throw new \Exception($this->idioma->getText("tipoOrdemInvalida"));
            }
            $where[] = " ob.tipo = '{$tipo}' ";
        }
        
        if ($executada != "T") {
            if ($executada == "S") {
                $where[] = " ob.executada = 1 ";
            } else if ($executada == "N"){
                $where[] = " ob.executada = 0 ";
            } else if ($executada == "M"){
                $where[] = " ob.volume_executado > 0 ";
            }
        }
        
        if ($cancelada != "T") {
            if ($cancelada == "S") {
                $where[] = " ob.cancelada = 1 ";
            } else {
                $where[] = " ob.cancelada = 0 ";
            }
        }
        
        if ($idCliente > 0) {
            $where[] = " ob.id_cliente = {$idCliente} ";
        }
        
        if (!is_string($direto)) {
            if($direto){
                $where[] = " ob.direta > 0 ";
            }
            else {
                $where[] = " ob.direta < 1 ";
            }
        }
        
        //$where[] = " ob.id_moeda = {$moeda->id} ";
        
        $where = (sizeof($where) > 0 ?  " WHERE "  . implode(" AND ", $where) : "");
        
        $query = "SELECT ob.* FROM order_book ob "
                . " {$where} "
                . " ORDER BY ob.data_cadastro DESC ";
                
        if ($limit > 0) {
            $query .= " LIMIT {$limit} ";
        }
       
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $orderBook = new OrderBook($dados);
            $lista[] = $orderBook;
        }
        return $lista;
    }

    public function getExtratoOrdensExecutadas(Paridade $paridade, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = "T", $executada = "T", $cancelada = "T", $limit = 0, $idCliente = 0, $direto = "T") {
       
        $where = Array();
        
        if (isset($dataInicial->data) &&  $dataInicial->data != null && isset($dataFinal->data) &&  $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " ob.data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if ($tipo != "T") {
            if ($tipo != \Utils\Constantes::ORDEM_COMPRA && $tipo != \Utils\Constantes::ORDEM_VENDA) {
                throw new \Exception($this->idioma->getText("tipoOrdemInvalida"));
            }
            $where[] = " ob.tipo = '{$tipo}' ";
        }
        
        if ($executada != "T") {
            if ($executada == "S") {
                $where[] = " ob.executada = 1 ";
            } else {
                $where[] = " ob.executada = 0 ";
            }
        }
        
        if ($cancelada != "T") {
            if ($cancelada == "S") {
                $where[] = " ob.cancelada = 1 ";
            } else {
                $where[] = " ob.cancelada = 0 ";
            }
        }
        
        if ($idCliente > 0) {
            $where[] = " ob.id_cliente = {$idCliente} ";
        }
        
        if (!is_string($direto)) {
            if($direto){
                $where[] = " ob.direta > 0 ";
            }
            else {
                $where[] = " ob.direta < 1 ";
            }
        }
        
        $where[] = " ob.id_paridade = {$paridade->id} ";
        
        $where = (sizeof($where) > 0 ?  " WHERE "  . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " ob.id, "
                . " ob.data_cadastro, "
                . " ob.id_cliente, "
                . " ob.id_cliente, "
                . " ob.volume_currency, "
                . " ob.tipo, "
                . " ob.executada, "
                . " ob.cancelada, "
                . " ob.percentual_taxa, "
                . " ob.valor_taxa, "
                . " ob.valor_taxa_executada, "
                . " ob.direta, "
                . " ob.valor_cotacao_referencia, "
                . " SUM(oe.volume_executado) AS volume_executado, "
                . " AVG(oe.valor_cotacao) AS valor_cotacao "
                . " FROM order_book ob  "
                . " INNER JOIN ordens_executadas oe ON ((ob.tipo = 'V'  AND ob.id = oe.id_order_book_venda) OR  (ob.tipo = 'C'  AND ob.id = oe.id_order_book_compra) )  "
                . " {$where}  "
                . " GROUP BY ob.id, ob.data_cadastro, ob.id_cliente, ob.id_cliente, ob.volume_currency, ob.tipo, ob.executada, ob.cancelada, ob.percentual_taxa, ob.valor_taxa, ob.valor_taxa_executada, ob.direta, ob.valor_cotacao_referencia "
                . " ORDER BY ob.id DESC";
                
        if ($limit > 0) {
            $query .= " LIMIT {$limit} ";
        }
       
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $orderBook = new OrderBook($dados);
            $lista[] = $orderBook;
        }
        return $lista;
    }

    public function getPrimeiroPrecoDia(\Utils\Data $data, Paridade $paridade) {
        $preco = 0;
        
        if (!isset($data->data) || $data->data == null) {
            throw new \Exception($this->idioma->getText("dataInvalidaInformarData"));
        }
        
        if ($paridade == null || !$paridade->id > 0) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        $di = $data->formatar(\Utils\Data::FORMATO_ISO) . " 00:00:00";
        $df = $data->formatar(\Utils\Data::FORMATO_ISO) . " 23:59:59";
        
        $c = \Utils\Constantes::ORDEM_COMPRA;
        
        $query = " SELECT " 
                . " oe.valor_cotacao, " 
                . " oe.data_execucao " 
                . " FROM ordens_executadas oe  "
                . " INNER JOIN order_book ob ON ((ob.tipo = 'V' AND oe.id_order_book_venda = ob.id) OR (ob.tipo = 'C' AND oe.id_order_book_compra = ob.id) )" 
                . " WHERE  " 
                . " oe.data_execucao BETWEEN  '{$di}' AND '{$df}'  "
                . " AND ob.id_paridade = {$paridade->id} "
                . " AND ob.tipo = '{$c}' "  
                . " ORDER BY oe.data_execucao ASC, oe.valor_cotacao ASC " 
                . " LIMIT 1 ";
             
        $result = $this->conexao->adapter->query($query)->execute();
        
        if (sizeof($result) > 0) {
            foreach ($result as $dados) {
                $preco = $dados["valor_cotacao"];
            }
        } else {
            
            $query = " SELECT "
                    . " ob.valor_cotacao "
                    . " FROM order_book ob  "
                    . " WHERE  "
                    . " ob.id_paridade = {$paridade->id} "
                    . " AND ob.executada = 0 "
                    . " AND ob.tipo = '{$c}' "
                    . " ORDER BY ob.valor_cotacao DESC "
                    . " LIMIT 1 ";
            
            $result = $this->conexao->adapter->query($query)->execute();
            if (sizeof($result) > 0) {
                foreach ($result as $dados) {
                    $preco = $dados["valor_cotacao"];
                }
            }
            
        }
        
        return $preco;
        
    }

    public function getPrecoMinMaxDia(\Utils\Data $data, Paridade $paridade) {
        $min = 0;
        $max = 0;
        
        if (!isset($data->data) || $data->data == null) {
            throw new \Exception($this->idioma->getText("dataInvalidaInformarData"));
        }
        
        $di = $data->formatar(\Utils\Data::FORMATO_ISO) . " 00:00:00";
        $df = $data->formatar(\Utils\Data::FORMATO_ISO) . " 23:59:59";
        
        $c = \Utils\Constantes::ORDEM_COMPRA;
        
        $query = " SELECT " 
                . " MAX(oe.valor_cotacao) AS max, " 
                . " MIN(oe.valor_cotacao) AS min " 
                . " FROM ordens_executadas oe  "
                . " INNER JOIN order_book ob ON ((ob.tipo = 'V' AND oe.id_order_book_venda = ob.id) OR (ob.tipo = 'C' AND oe.id_order_book_compra = ob.id) )" 
                . " WHERE  " 
                . " oe.data_execucao BETWEEN  '{$di}' AND '{$df}'  "
                . " AND ob.id_paridade = {$paridade->id} ";
              
        $result = $this->conexao->adapter->query($query)->execute();
        
        if (sizeof($result) > 0) {
            foreach ($result as $dados) {
                $min = $dados["min"];
                $max = $dados["max"];
            }
        } else {
            
            $query = " SELECT "
                    . " ob.valor_cotacao "
                    . " FROM order_book ob  "
                    . " WHERE  "
                    . " ob.id_paridade = {$paridade->id} "
                    . " AND ob.executada = 0 "
                    . " AND ob.tipo = '{$c}' "
                    . " ORDER BY ob.valor_cotacao DESC "
                    . " LIMIT 1 ";
            
            $result = $this->conexao->adapter->query($query)->execute();
            if (sizeof($result) > 0) {
                foreach ($result as $dados) {
                    $min = $dados["valor_cotacao"];
                    $max = $dados["valor_cotacao"];
                }
            }
            
        }
        
        return Array("min" => $min, "max" => $max);
        
    }

    public function setArrayOrdemHomolog(OrderBook $orderBook) {
        
        return Array (
                "cancelada" => $orderBook->cancelada,
                "data_cadastro" => $orderBook->dataCadastro,
                "direta" => $orderBook->direta,
                "id" => $orderBook->id,
                "id_cliente" => $orderBook->idCliente,
                "id_paridade" => $orderBook->idParidade,
                "percentual_taxa" => $orderBook->percentualTaxa,
                "tipo" => $orderBook->tipo,
                "valor_cotacao" => $orderBook->valorCotacao,
                "valor_cotacao_referencia" => $orderBook->valorCotacaoReferencia,
                "valor_taxa_executada" => $orderBook->valorTaxaExecutada,
                "volume_currency" => $orderBook->volumeCurrency,
                "volume_executado" => $orderBook->volumeExecutado,
                "executada" => $orderBook->executada,
                "valor_taxa" => $orderBook->valorTaxa,
            );
    }

    public function generateOrdersHomolog($tipo, $minValue, $maxValue, $minCotValue = 0, $maxCotValue = 0, $qtd = 0, $idParidade = 1, $ordensForcadas = Array()) {
        
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $this->listOrdersHomolog = Array();
        $perTaxa = 0.2;
        
        if (sizeof($ordensForcadas) > 0) { 
            foreach ($ordensForcadas as $key=>$o) {
                $taxa = number_format($o["volume"] * ($perTaxa / 100), 8, ".", "");
                $volume = number_format($o["volume"] - $taxa, 8, ".", "");
                
                $cot = number_format($o["cotacao"] * 10000, 0, ".", "");
                
                $dados = Array (
                    "cancelada" => 0,
                    "data_cadastro" => new \Utils\Data(date("d/m/Y H:i:s")),
                    "direta" => 0,
                    "id" => ($key),
                    "id_cliente" => 2,
                    "id_paridade" => $idParidade,
                    "percentual_taxa" => $perTaxa,
                    "tipo" => $tipo,
                    "valor_cotacao" => number_format($o["cotacao"], $configuracao->qtdCasasDecimais, ".", ""),
                    "valor_cotacao_referencia" => 0,
                    "valor_taxa_executada" => 0,
                    "volume_currency" => $volume,
                    "volume_executado" => 0,
                    "executada" => 0,
                    "valor_taxa" => $taxa,
                );

                
                
                $this->listOrdersHomolog[$cot] = $dados;
            }
        }
        
        if ($qtd < 1) {
            $qtd = rand(0, 100);
        }
        
        $minValue = ($minValue > 0 ? ($minValue * 100000000) : 2000000);
        $maxValue = ($maxValue > 0 ? ($maxValue * 100000000) : 100000000);
        
        $minCotValue = ($minCotValue > 0 ? ($minCotValue * 100) : 9000);
        $maxCotValue = ($maxCotValue > 0 ? ($maxCotValue * 100) : 10000);
        
        for ($i = 0; $i < $qtd; $i++) {
            
            $vol = rand($minValue, $maxValue);
            
            $volume = number_format(($vol / 100000000), 8, ".", "");
            $taxa = number_format($volume * ($perTaxa / 100), 8, ".", "");
            $volume = number_format($volume - $taxa, 8, ".", "");
            
            $cot = rand($minCotValue, $maxCotValue);
            $cotacao = number_format(($cot / 100), $configuracao->qtdCasasDecimais, ".", "");
            
            $dados = Array (
                "cancelada" => 0,
                "data_cadastro" => new \Utils\Data(date("d/m/Y H:i:s")),
                "direta" => 0,
                "id" => ($qtd + $i),
                "id_cliente" => $i,
                "id_paridade" => $idParidade,
                "percentual_taxa" => $perTaxa,
                "tipo" => $tipo,
                "valor_cotacao" => $cotacao,
                "valor_cotacao_referencia" => 0,
                "valor_taxa_executada" => 0,
                "volume_currency" => $volume,
                "volume_executado" => 0,
                "executada" => 0,
                "valor_taxa" => $taxa,
            );
            
            $this->listOrdersHomolog[$cot] = $dados;
            
        }
        
        if ($tipo == \Utils\Constantes::ORDEM_COMPRA) {
            krsort($this->listOrdersHomolog);
        } else {
            ksort($this->listOrdersHomolog);
        }
        
    }

    public function getBookHomolog() {
        return $this->listOrdersHomolog;
    }

    public function extratoConsolidado(Cliente $cliente) {
        
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        $query = " SELECT * FROM conta_corrente_reais WHERE id_cliente = {$cliente->id} AND order_book = 1 ORDER BY data;";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }

    public function rankingMensal(Paridade $paridade, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where=Array();
        
        
        if ($paridade == null || $paridade->id < 1) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            $where[] = " oe.data_execucao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        } else {
           
            $dataInicial = date("Y-m-01 00:00:00");
            
            $dataFinal = date("Y-m-t 23:59:59");
            
           $where[] = " oe.data_execucao BETWEEN '{$dataInicial}' AND '{$dataFinal}' ";
        }
                
        $where[] = " ob.id_paridade = {$paridade->id} ";
        
        //$dataInicial->subtrair(1);
        
        $where = (sizeof($where) > 0 ?  " WHERE "  . implode(" AND ", $where) : "");
        

        $query = "SELECT 
                c.id,c.nome,
                m.id AS id_paridade,
                m.id_moeda_book,
                m.id_moeda_trade,
                mb.nome AS moeda_book,
                mt.nome AS moeda_trade,
                SUM(oe.volume_executado) AS volume
                FROM order_book ob
                INNER JOIN ordens_executadas oe ON ((oe.id_order_book_venda = ob.id) OR (oe.id_order_book_compra = ob.id))
                INNER JOIN clientes c ON (ob.id_cliente = c.id)
                INNER JOIN paridades m ON (ob.id_paridade = m.id)
                INNER JOIN moedas mb ON (m.id_moeda_book = mb.id)
                INNER JOIN moedas mt ON (m.id_moeda_trade = mt.id)
                {$where}
                GROUP BY c.nome, c.id, m.id_moeda_book, m.id_moeda_trade
                ORDER BY volume DESC";
        //exit($query);
        $lista = Array();
        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $lista[] = $dados;
        }
        
        $rankingRn = new RankingClienteMensalRn($this->conexao->adapter);
        $rankingRn->salvar($lista, $paridade);
    }
}

?>
