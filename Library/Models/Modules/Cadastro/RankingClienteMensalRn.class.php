<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estado
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RankingClienteMensalRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new RankingClienteMensal());
        } else {
            $this->conexao = new GenericModel($adapter, new RankingClienteMensal());
        }
    }
    
    
    public function salvar($lista, Paridade $paridade) {
        
        $this->conexao->delete("id_paridade = {$paridade->id}");
        try {
            $i = 0;
            foreach ($lista as $dados) {
                $i++;
                
                $rankingMensal =  new RankingClienteMensal();
                $rankingMensal->id = 0;
                $rankingMensal->idCliente = $dados["id"];
                $rankingMensal->idParidade = $dados["id_paridade"];
                $rankingMensal->nomeCliente = $dados["nome"];
                $rankingMensal->nomeMoedaBook = $dados["moeda_book"];
                $rankingMensal->nomeMoedaTrade = $dados["moeda_trade"];
                $rankingMensal->posicao = $i;
                $rankingMensal->volume = $dados["volume"];
                
                $this->conexao->salvar($rankingMensal);
            }
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $pais) {
            $lista[] = $pais;
        }
        return $lista;
    }
    
    
    public static function getByCliente(Cliente $cliente, Paridade $paridade) {
        $rankingClienteMensalRn = new RankingClienteMensalRn();
        $result = $rankingClienteMensalRn->conexao->select(Array("id_cliente" => $cliente->id, "id_paridade" => $paridade->id));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
}

?>