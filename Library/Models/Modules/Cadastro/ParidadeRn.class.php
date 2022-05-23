<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Cadastro\Moeda;
use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Moeda
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ParidadeRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Paridade());
        } else {
            $this->conexao = new GenericModel($adapter, new Paridade());
        }
         
    }

    public function carregar(Paridade &$paridade, $carregar = true, $carregarMoedaBook = true, $carregarMoedaTrade = true) {
        
        $columnsParidade = Paridade::getLazingColumns("p.", 0);
        
        $columnsMoedaBook = "";
        $joinMoedaBook = "";
        if ($carregarMoedaBook) {
            $columnsMoedaBook = "," . Moeda::getLazingColumns("mb.", 0);
            $joinMoedaBook = " INNER JOIN moedas mb ON (mb.id = p.id_moeda_book) ";
        }
        
        $columnsMoedaTrade = "";
        $joinMoedaTrade = "";
        if ($carregarMoedaTrade) {
            $columnsMoedaTrade = "," . Moeda::getLazingColumns("mt.", 1);
            $joinMoedaTrade = " INNER JOIN moedas mt ON (p.id_moeda_trade = mt.id) ";
        }
        
        $query = " SELECT {$columnsParidade} {$columnsMoedaBook} {$columnsMoedaTrade} "
                . "FROM paridades p {$joinMoedaBook} "
                . " {$joinMoedaTrade} "
                . " WHERE p.id = {$paridade->id};";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        
        foreach ($result as $dados) {
            $paridade = new Paridade($dados, true, 0);
            if ($carregarMoedaTrade) {
                $moedaTrade = new Moeda($dados, true, 1);
                $paridade->moedaTrade = $moedaTrade;
            }
            if ($carregarMoedaBook) {
                $moedaBook = new Moeda($dados, true, 0);
                $paridade->moedaBook = $moedaBook;
            }
            
            return $paridade;
        }
        
        return null;
    }

    public static function get($idParidade = null) {
        $paridadeRn = new ParidadeRn();
        $paridade = new Paridade(Array("id" => $idParidade));
        
        $paridadeRn->carregar($paridade, true, true, true);
        return $paridade;
    }

    public static function getSymbol($idParidade = null) {
        $paridadeRn = new ParidadeRn();
        $paridade = new Paridade(Array("symbol" => $idParidade));

        $paridadeRn->carregar($paridade, true, true, true);
        return $paridade;
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarMoedaBook = true, $carregarMoedaTrade = true) {
      $result = $this->conexao->listar($where, $order, $offset, $limit);
      $lista = Array();
      
      foreach ($result as $paridade) {
          $this->carregar($paridade, false, $carregarMoedaBook, $carregarMoedaTrade);
          
          $lista[] = $paridade;
      }
      return $lista;
    }

    public function getListaIdsParidadesByMoeda(Moeda $moeda, $mostrarTodos = true, $statusMercado = true) {
        $wAtivo = ($mostrarTodos ? " " : " AND p.ativo = 1 ");
        $mercadoAtivo = ($statusMercado ? " " : " AND p.status_mercado = 1 ");
        
        $query = " SELECT p.id FROM paridades p WHERE p.id_moeda_trade = {$moeda->id} {$wAtivo} {$mercadoAtivo} ORDER BY p.ordem";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridades[] = $dados["id"];
        }
        
        return $paridades;
    }
    
    public function getListaParidadesByMoeda(Moeda $moeda, $mostrarTodos = true, $statusMercado = false) {

        $wAtivo = ($mostrarTodos ? " " : " AND p.ativo = 1 ");
        
        if($statusMercado){
            $queryMercado = " AND p.status_mercado = 1 ";
        } else {
            $queryMercado = "";
        }
        
        $columnsParidade = Paridade::getLazingColumns("p.", 0);
        $columnsMoedaBook = Moeda::getLazingColumns("mb.", 0);
        $columnsMoedaTrade = Moeda::getLazingColumns("mt.", 1);
        
        $query = " SELECT {$columnsParidade}, {$columnsMoedaBook}, {$columnsMoedaTrade} "
                . " FROM paridades p INNER JOIN moedas mb ON (mb.id = p.id_moeda_book) "
                . " INNER JOIN moedas mt ON (p.id_moeda_trade = mt.id) "
                . " WHERE p.id_moeda_trade = {$moeda->id} {$wAtivo} {$queryMercado} ORDER BY p.ordem";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridade = new Paridade($dados, true, 0);
            $moedaBook = new Moeda($dados, true, 0);
            $moedaTrade = new Moeda($dados, true, 1);
            
            $paridade->moedaBook = $moedaBook;
            $paridade->moedaTrade = $moedaTrade;
            $paridades[] = $paridade;
        }
        
        return $paridades;
    }
    
    public function getListaTodasParidades($mostrarTodos = true) {
        #code OLD
        $wAtivo = ($mostrarTodos ? " " : " WHERE p.ativo = 1 ");
        
        $columnsMoeda = Moeda::getLazingColumns("m.", 0);
        
        $query = " SELECT DISTINCT(m.id) AS dist, {$columnsMoeda} "
                . "FROM paridades p INNER JOIN moedas m ON (m.id = p.id_moeda_trade) "
                . " {$wAtivo} ORDER BY  m.id, m.nome ";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $moedaTrade = new Moeda($dados, true, 0);
            
            $paridades[] = $moedaTrade;
        }
        
        return $paridades;

    }

    #created André 23/07/2019
    public function getListAllParity($mostrarTodos = true){

        
        $wAtivo = ($mostrarTodos ? " " : " p.ativo = 1 AND ");
        
        $columnsParidade = Paridade::getLazingColumns("p.", 0);
        $columnsMoedaBook = Moeda::getLazingColumns("mb.", 0);
        $columnsMoedaTrade = Moeda::getLazingColumns("mt.", 1);
        
        $query = " SELECT {$columnsParidade}, {$columnsMoedaBook}, {$columnsMoedaTrade} "
                . "FROM paridades p INNER JOIN moedas mb ON (mb.id = p.id_moeda_book) "
                . " INNER JOIN moedas mt ON (p.id_moeda_trade = mt.id) "
                . " WHERE {$wAtivo}  (mt.id = 1 OR mt.ativo > 0) AND (mt.id = 1 OR mt.status_mercado > 0) AND mb.ativo > 0 AND mb.status_mercado > 0 ORDER BY p.ordem";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridade = new Paridade($dados, true, 0);
            $moedaBook = new Moeda($dados, true, 0);
            $moedaTrade = new Moeda($dados, true, 1);
            
            $paridade->moedaBook = $moedaBook;
            $paridade->moedaTrade = $moedaTrade;
            $paridades[] = $paridade;
        }
        
        return $paridades;
    }
    
    public function getListaTodasParidadesByMoeda(Moeda $moeda, $mostrarTodos = true) {
        $wAtivo = ($mostrarTodos ? " " : " AND p.ativo = 1 ");
        
        $columnsParidade = Paridade::getLazingColumns("p.", 0);
        $columnsMoedaBook = Moeda::getLazingColumns("mb.", 0);
        $columnsMoedaTrade = Moeda::getLazingColumns("mt.", 1);
        
        $query = " SELECT {$columnsParidade}, {$columnsMoedaBook}, {$columnsMoedaTrade} "
                . "FROM paridades p INNER JOIN moedas mb ON (mb.id = p.id_moeda_book) "
                . " INNER JOIN moedas mt ON (p.id_moeda_trade = mt.id) "
                . " WHERE (p.id_moeda_book = {$moeda->id} OR p.id_moeda_trade = {$moeda->id}) {$wAtivo} ORDER BY p.ordem";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridade = new Paridade($dados, true, 0);
            $moedaBook = new Moeda($dados, true, 0);
            $moedaTrade = new Moeda($dados, true, 1);
            
            $paridade->moedaBook = $moedaBook;
            $paridade->moedaTrade = $moedaTrade;
            $paridades[] = $paridade;
        }
        
        return $paridades;
    }
    
    
    public function getListaIdsParidadesComMoeda(Moeda $moeda, $mostrarTodos = true) {
        $wAtivo = ($mostrarTodos ? " " : " AND p.ativo = 1 ");
        $query = " SELECT p.id  FROM paridades p   WHERE p.id_moeda_book = {$moeda->id} {$wAtivo} ORDER BY p.ordem";
        
                //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridades[] = $dados["id"];
        }
        
        return $paridades;
    }
    
    public function getParidadesComMoeda(Moeda $moeda, $mostrarTodos = true, $statusMercado = false) {
        $wAtivo = ($mostrarTodos ? " " : " AND p.ativo = 1 ");
        
        if($statusMercado){
            $queryMercado = " AND p.status_mercado = 1 ";
        } else {
            $queryMercado = "";
        }
        
        $columnsParidade = Paridade::getLazingColumns("p.", 0);
        $columnsMoedaBook = Moeda::getLazingColumns("mb.", 0);
        $columnsMoedaTrade = Moeda::getLazingColumns("mt.", 1);
        
        $query = " SELECT {$columnsParidade}, {$columnsMoedaBook}, {$columnsMoedaTrade} "
                . "FROM paridades p INNER JOIN moedas mb ON (mb.id = p.id_moeda_book) "
                . " INNER JOIN moedas mt ON (p.id_moeda_trade = mt.id) "
                . " WHERE p.id_moeda_book = {$moeda->id} {$wAtivo} {$queryMercado} ORDER BY p.ordem";
        
        
        $result = $this->conexao->adapter->query($query)->execute();
        $paridades = Array();
        foreach ($result as $dados) {
            $paridade = new Paridade($dados, true, 0);
            $moedaBook = new Moeda($dados, true, 0);
            $moedaTrade = new Moeda($dados, true, 1);
            
            $paridade->moedaBook = $moedaBook;
            $paridade->moedaTrade = $moedaTrade;
            $paridades[] = $paridade;
        }
        
        return $paridades;
    }
    
    public function find($idMoedaBook, $idMoedaTrade) {
        $result = $this->conexao->select(Array(
            "id_moeda_book" => $idMoedaBook,
            "id_moeda_trade" => $idMoedaTrade
        ));
        return $result->current();
    }
    
    public function getBySymbol($symbol) {
        
        $result = $this->conexao->select(Array(
            "symbol" => $symbol
        ));
        
        $paridade = $result->current();
        if ($paridade != null) {
            $this->carregar($paridade);
        }
        
        return  $paridade;
    }
    
    public static function getListaMoedasQueSaoParidades() {
        $paridadeRn = new ParidadeRn();
        $query = "SELECT DISTINCT(m.id), m.* FROM moedas m INNER JOIN paridades p ON (p.id_moeda_trade = m.id) WHERE  m.id = 1 OR (m.ativo = 1 AND p.ativo = 1) ORDER BY m.id";
        $result = $paridadeRn->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $lista[] = new Moeda($dados);
        }
        return $lista;
    }
    
    
    public function getPrecoAberturaUltimas24Horas(Paridade $paridade) {
        $dataInicio = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataFinal->subtrair(0, 0, 0, 24);
        
        $query = "SELECT 
                    oe.valor_cotacao
                    FROM ordens_executadas oe 
                    INNER JOIN order_book ob ON ((oe.tipo = 'C' AND oe.id_order_book_compra = ob.id) OR (oe.id_order_book_venda = ob.id AND oe.tipo = 'V'))
                    WHERE oe.data_execucao BETWEEN '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' AND '{$dataInicio->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' AND ob.id_paridade = {$paridade->id}
                    ORDER BY ob.id DESC
                    LIMIT 1";
   
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {           
            return $dados["valor_cotacao"];
        }
        
        return 0;
    }
}
