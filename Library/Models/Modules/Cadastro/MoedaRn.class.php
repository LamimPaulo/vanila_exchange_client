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
class MoedaRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Moeda());
        } else {
            $this->conexao = new GenericModel($adapter, new Moeda());
        }
    }

    public function carregar(Moeda &$moeda, $carregar = true) {
        if ($carregar) {
            $this->conexao->carregar($moeda);
        }
    }

    public function listar($where = null, $order = null, $offset = null, $limit = null) {
      $result = $this->conexao->listar($where, $order, $offset, $limit);
      $lista = Array();
      foreach ($result as $moeda) {
          $this->carregar($moeda, false);
          $lista[] = $moeda;
      }
      return $lista;
    }

    
    public function getBySimbolo($simbolo) {
        $result = $this->conexao->select(Array(
            "simbolo" => $simbolo
        ));
        
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
    public function update(Moeda $moeda) {
        $this->conexao->update(
                Array(
                    "qtd_maxima_carteiras" => $moeda->qtdMaximaCarteiras
                ), 
                Array(
                    "id" => $moeda->id
                )
            );
    }
    
    
    public static function get($idMoeda = 2) {
        $moeda = new Moeda(Array("id" => $idMoeda));
        $moedaRn = new MoedaRn();
        $moedaRn->carregar($moeda);
        return $moeda;
    }
    
    public static function find($simbolo) {
        $moedaRn = new MoedaRn();
        $result = $moedaRn->conexao->select(Array(
            "simbolo" => $simbolo
        ));
        
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
}
