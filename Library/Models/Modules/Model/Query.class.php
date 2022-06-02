<?php

namespace Models\Modules\Model;

class Query {
    
    public function get($object) {
        
        $tabelaParidade = new Table(new \Models\Modules\Cadastro\Paridade());
        
        //exit($tabelaParidade->toQuery(Array("id", "symbol", "moedaBook.nome", "moedaBook.simbolo", "moedaTrade.nome", "moedaTrade.simbolo")));
        
        
        $arguments = Array(
            "ativo" => true,
            "AND" => Array("symbol" => Array("LIKE" => "b"), "ativo" => true),
            "OR" => Array("teste" => "b", "teste" => Array("GT" => 1, "LT" => 2))
        );
       
        $where = new Where($arguments);
        exit($where->toWhereString());
    }
    
    
    
}