<?php // 
namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade OrderBook
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OrdemExecutadaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new OrdemExecutada());
        } else {
            $this->conexao = new GenericModel($adapter, new OrdemExecutada());
        }
    }
    
    public function salvar(OrdemExecutada &$ordemExecutada) {
        try {
            $ordemExecutada->id = 0;
            $ordemExecutada->dataExecucao = new \Utils\Data(date("d/m/Y H:i:s"));
            
            if (!$ordemExecutada->idOrderBookCompra > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarOrdemCompra"));
            }
            
            if (!$ordemExecutada->idOrderBookVenda > 0) {
                throw new \Exception($this->idioma->getText("necessarioInformarOrdemCompra"));
            }
            
            $tipos = Array(
                \Utils\Constantes::ORDEM_COMPRA,
                \Utils\Constantes::ORDEM_VENDA
            );
            
            if (!in_array($ordemExecutada->tipo, $tipos)) {
                throw new \Exception($this->idioma->getText("tipoOrdemInvalida"));
            }
            
            if (!$ordemExecutada->valorCotacao > 0) {
                throw new \Exception($this->idioma->getText("valorCotacaoInvalido"));
            }
            
            
            if (!$ordemExecutada->volumeExecutado > 0) {
                throw new \Exception($this->idioma->getText("volumeExecutadoInvalido"));
            }
            
            if (!is_numeric($ordemExecutada->direta)) {
                $ordemExecutada->direta = 0;
            }
            
            $this->conexao->salvar($ordemExecutada);
        } catch(\Exception $e) {
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
        
        
    }
    
    
    public function calculosTicker(Paridade $paridade) {
        
        $dataIni = new \Utils\Data(date("Y-m-d H:i:s"));
        $dataIni->subtrair(0, 0, 0, 24);
        $dataFim = new \Utils\Data(date("Y-m-d H:i:s"));
        
        $di = $dataIni->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        $df = $dataFim->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        
        $query = "call calcularTicker({$paridade->id}, '{$di}', '{$df}'); ";
        
        $this->conexao->adapter->query($query)->execute();
    }
    
    
    public function filtrar(Paridade $paridade = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = "T", $direto = "T", $idCliente = 0, $limit = 0, $OTC = "T") {
        
        $where = Array();
        
        if ($paridade != null) {
            $where[] = " oe.id_paridade = {$paridade->id} ";
        }
        
        if ($tipo != 'T') {
            $where[] = " oe.tipo = '{$tipo}' ";
        }
        
        if (!is_string($direto)) {
            if($direto){
                $where[] = " oe.direta > 0 ";
            }
            else {
                $where[] = " oe.direta < 1 ";
            }
        }
        
        if ($idCliente > 0) {
                $where[] = " (oe.id_cliente_comprador = {$idCliente} OR oe.id_cliente_vendedor = {$idCliente}) ";
        }

        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataInicial->data) && $dataInicial->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            
            $where[] = " oe.data_execucao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if ($OTC != "T") { 
            if ($OTC) {
                $where[] = " ((oe.id_order_book_compra IS NOT NULL AND oe.id_order_book_venda IS NULL) OR  (oe.id_order_book_compra IS NULL AND oe.id_order_book_venda IS NOT NULL) ) ";
            } else {
                $where[] = " oe.id_order_book_compra IS NOT NULL AND oe.id_order_book_venda IS NOT NULL";
            }
        }
        
        $whereString = (sizeof($where) > 0 ? (" WHERE " . implode(" AND ", $where)) : "");
        $limitString = ($limit > 0 ? " LIMIT {$limit} " : "");
        $query = " SELECT oe.* "
                . " FROM ordens_executadas oe "
                . " {$whereString} "
                . " ORDER BY oe.id DESC"
                . " {$limitString}; ";
             
                //exit($query);
        $result = $this->conexao->executeSql($query);
        
        $lista = Array();
        foreach ($result as $dados) {
            $ordemExecutada = new OrdemExecutada($dados);
            $lista[] = $ordemExecutada;
        }
        
        return $lista;
    }
    
    public function getUltimaOrdemExecutada(Paridade $paridade) {
        
        $query = "SELECT oe.* FROM ordens_executadas oe "
                . " WHERE oe.id_paridade = {$paridade->id} ORDER BY oe.id DESC LIMIT 1;";
                
        $dados = $this->conexao->adapter->query($query)->execute();
        if (sizeof($dados) > 0) {
            $d = $dados->current();
            return new OrdemExecutada($d);
        }
        
        return null;
    }
    
    
    public function getUltimaOrdemExecutadaByMoeda(Moeda $moeda) {
        
        $query = "SELECT oe.* FROM ordens_executadas oe "
                . " WHERE oe.id_paridade IN (SELECT id FROM paridades WHERE id_moeda_book = {$moeda->id} OR id_moeda_trade = {$moeda->id}) ORDER BY oe.id DESC LIMIT 1;";
                
        $dados = $this->conexao->adapter->query($query)->execute();
        if (sizeof($dados) > 0) {
            $d = $dados->current();
            return new OrdemExecutada($d);
        }
        
        return null;
    }

    public function getValorMedioOrdem(OrderBook $orderBook) {
        try {
            $orderBookRn = new OrderBookRn();
            $orderBookRn->conexao->carregar($orderBook);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("ordemNaoEncontrada"));
        }
    
        $whereTipoOrdem = ($orderBook->tipo == \Utils\Constantes::ORDEM_COMPRA ? "id_order_book_compra" : "id_order_book_venda");
        $query = "SELECT SUM(valor_cotacao * volume_executado) AS valor_executado, SUM(volume_executado) AS volume_executado FROM ordens_executadas WHERE {$whereTipoOrdem} = {$orderBook->id};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $precoMedio = 0;
        
        foreach ($result as $d) {
            $precoMedio = ($d["valor_executado"] / $d["volume_executado"]);
        }
        
        return $precoMedio;
    }
    
    public function calcularVolumeParidade($idParidade, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $paridade = new Paridade(Array("id" => $idParidade));
        
        try {
            $paridadeRn = new ParidadeRn();
            $paridadeRn->carregar($paridade);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        $where = Array();
        if ($dataInicial != null && $dataFinal != null) {
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("dataInicialInvalida"));
            }
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("dataFinalInvalida"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " oe.data_execucao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        
        $where[] = " ob.id_paridade = {$paridade->id} ";
        $wheres  = (implode("AND", $where));
        
        $query = "SELECT "  
                    . " SUM(oe.volume_executado)  AS volume_currency, "
                    . " SUM(oe.volume_executado * oe.valor_cotacao) AS volume_reais "

                    . " FROM ordens_executadas  oe "
                    . " INNER JOIN order_book ob ON ((oe.tipo = 'V' AND oe.id_order_book_venda = ob.id) OR (oe.tipo = 'C' AND oe.id_order_book_compra = ob.id)) "
                    . " WHERE "
                    . " {$wheres} ;";
               
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $volumeCurrency = 0;
        $volumeReais = 0;
        foreach ($dados as $d) {
            
            $volumeCurrency = number_format($d["volume_currency"], $paridade->moedaBook->casasDecimais, ".", "");
            $volumeReais = number_format($d["volume_reais"], $paridade->moedaTrade->casasDecimais, ".", "");
        }
        
        if ($paridade->id == 1) {
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            $volumeCurrency += number_format($historicoTransacaoReferenciaRn->calcularVolumeEntradasSaidas($dataInicial, $dataFinal, "T", $paridade->idMoedaBook), $paridade->moedaBook->casasDecimais, ".", "");
        }
        
        return Array("currency" => $volumeCurrency, "reais" => $volumeReais);
    }
    
    
    public function calcularVolumeMoeda($idMoeda, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $moeda = new Moeda(Array("id" => $idMoeda));
        
        try {
            $moedaRn = new MoedaRn();
            $moedaRn->carregar($moeda);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        
        
        $where = Array();
        if ($dataInicial != null && $dataFinal != null) {
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("dataInicialInvalida"));
            }
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("dataFinalInvalida"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " oe.data_execucao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        
        $wheres  = (implode("AND", $where));
        
        
        $query = "SELECT 
                    SUM(oe.volume_executado) AS volume
                    FROM ordens_executadas oe 
                    INNER JOIN order_book ob ON ( (oe.tipo = 'C' AND oe.id_order_book_compra = ob.id ) OR (oe.tipo = 'V' AND  oe.id_order_book_venda = ob.id) )
                    WHERE ob.id_paridade IN (SELECT id FROM paridades WHERE id_moeda_book = {$moeda->id})
                    AND {$wheres} 

                    UNION

                    SELECT 

                    SUM(oe.volume_executado * oe.valor_cotacao) AS volume
                    FROM ordens_executadas oe 
                    INNER JOIN order_book ob ON ( (oe.tipo = 'C' AND oe.id_order_book_compra = ob.id ) OR (oe.tipo = 'V' AND  oe.id_order_book_venda = ob.id) )
                    WHERE ob.id_paridade IN (SELECT id FROM paridades WHERE id_moeda_trade = {$moeda->id})
                    AND {$wheres} ;";
        
        $dados = $this->conexao->adapter->query($query)->execute();
        
        
        
        $volumeCurrency = 0;
        foreach ($dados as $d) {
            $volumeCurrency += number_format(($d["volume"] != null ? $d["volume"] : 0), $moeda->casasDecimais, ".", "");
        }
        
        if ($moeda->id == 2) {
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            $volumeCurrency += number_format($historicoTransacaoReferenciaRn->calcularVolumeEntradasSaidas($dataInicial, $dataFinal, "T", $moeda->id), $moeda->casasDecimais, ".", "");
        }
        
        return $volumeCurrency;
    }
    
    
    public function calcularReais(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $where = Array();
        if ($dataInicial != null && $dataFinal != null) {
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("dataInicialInvalida"));
            }
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("dataFinalInvalida"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " oe.data_execucao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        
        $wheres  = (sizeof($where) > 0 ? " WHERE " .  implode("AND", $where) : "");
        
        $query = "SELECT "  
                    . " SUM(oe.volume_executado * oe.valor_cotacao) AS volume_reais "
                    . " FROM ordens_executadas  oe "
                    . " {$wheres} ;";
                    
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $volume = 0;
        foreach ($dados as $d) {
            $volume = number_format($d["volume_reais"], $configuracao->qtdCasasDecimais, ".", "");
        }
        
        return number_format($volume, $configuracao->qtdCasasDecimais, ".", "");
    }
    
    
    public function getHistoricoExecucoesIdentificadas() {
        
        $query = "SELECT " 
                . " (SELECT c.nome FROM clientes c INNER JOIN order_book ob2 ON (c.id = ob2.id_cliente) WHERE ob2.id = oe.id_order_book_compra) AS comprador, " 
                . " (SELECT c.nome FROM clientes c INNER JOIN order_book ob3 ON (c.id = ob3.id_cliente) WHERE ob3.id = oe.id_order_book_venda) AS vendedor, " 
                . " ob.id_paridade, oe.*  " 
                . " FROM ordens_executadas oe  "                 
                . " INNER JOIN order_book ob ON ((oe.tipo = 'C' AND ob.id = oe.id_order_book_compra) OR (oe.tipo = 'V' AND ob.id = oe.id_order_book_venda))  "
                . " WHERE date(data_execucao) = curdate() "
                . " ORDER BY oe.data_execucao DESC ";
                //. " LIMIT {$limit} ";
        
        
        $result = $this->conexao->adapter->query($query)->execute();
        return $result;
    }
    
    public function getDadosGrafico($idParidade, $offset, $unidade) {
        
        try {
            $paridade = new Paridade(Array("id" => $idParidade));
            $paridadeRn = new ParidadeRn();
            $paridadeRn->carregar($paridade);
            
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("moedaInvalida"));
        }
        
        $whereData = \Modules\apiv2\Controllers\BookUtils::dateInterval($unidade, $offset);
           
        $sql = "SELECT 
                oe.id,
                oe.data_execucao,
                oe.valor_cotacao AS valor,
                oe.volume_executado AS volume,
                oe.tipo
                FROM ordens_executadas oe 
                INNER JOIN order_book ob ON ((oe.tipo = 'C' AND ob.id = oe.id_order_book_compra) OR (oe.tipo = 'V' AND ob.id = oe.id_order_book_venda)) 
                WHERE ob.id_paridade = {$paridade->id} {$whereData}
                ORDER BY oe.id ;
                ";
                      
        $result = $this->conexao->adapter->query($sql)->execute();
        return $result;
        
    }
    
    public function getLasPriceUDFChartOHLC(\Utils\Data $dataLimite, $paridade) {
        $sDt = $dataLimite->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP);
        //$sDt = substr($sDt, 0, -2).'00';
        
        $compra = \Utils\Constantes::ORDEM_COMPRA;
        $venda = \Utils\Constantes::ORDEM_COMPRA;
        $query = "SELECT oe.valor_cotacao "
                . " FROM ordens_executadas oe "
                /*. " INNER JOIN order_book ob ON ((oe.tipo = '{$venda}'  AND oe.id_order_book_venda = ob.id) OR (oe.tipo = '{$compra}'  AND oe.id_order_book_compra = ob.id)) "*/
                . " WHERE oe.id_paridade = {$paridade->id} AND oe.data_execucao < '{$sDt}'"
                . " ORDER BY oe.id DESC LIMIT 1; ";

        $result = $this->conexao->adapter->query($query)->execute();
        
        $preco = 0;
        if (sizeof($result) > 0) {
            $d = $result->current();
            $preco = $d["valor_cotacao"];
        }
        
        return $preco;
    }
    
    public function getUDFChartOHLC(\Utils\Data $dtIni, \Utils\Data $dtFin, Paridade $paridade) {
        
        $sDtIni = $dtIni->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP);
        $sDtFin = $dtFin->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP);
        
        $compra = \Utils\Constantes::ORDEM_COMPRA;
        $venda = \Utils\Constantes::ORDEM_VENDA;
        
        $query = "SELECT 
                    oe.data_execucao, 
                    MAX(oe.valor_cotacao) AS h, 
                    MIN(oe.valor_cotacao) AS l, 
                    SUM(oe.volume_executado) AS v, 
                    oe.valor_cotacao as o,
                    oe.valor_cotacao as c

                    FROM 

                    ordens_executadas oe 
                    WHERE oe.data_execucao BETWEEN '{$sDtIni}' AND '{$sDtFin}' AND oe.id_paridade = {$paridade->id} GROUP BY oe.data_execucao ORDER BY id ASC; ";
        
                    //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        
        
        $lista = Array();
        foreach ($result as $d) {
            //$d["data_execucao"] = new \Utils\Data(substr($d["data_execucao"], 0, 21));
            $lista[] = $d;
            //exit(print_r($d));
        }
        //exit(print_r($lista));
        return $lista;
        
    }
}

?>
