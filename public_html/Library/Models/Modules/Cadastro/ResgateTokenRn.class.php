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
class ResgateTokenRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ResgateToken());
        } else {
            $this->conexao = new GenericModel($adapter, new ResgateToken());
        }
    }
    
    public function salvar(ResgateToken &$resgateToken) {
        
        $cliente = \Utils\Geral::getCliente();
        
        if ($cliente == null || !($cliente instanceof Cliente)) {
            throw new \Exception("É necessário estar logado para concluir a operação");
        }
        
        if (empty($resgateToken->wallet)) {
            
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
    
    public static function getBandeiraBySigla($sigla) {
        $sigla = strtolower($sigla);
        return IMAGES . "countries/{$sigla}.svg";
        
    }
    
}

?>