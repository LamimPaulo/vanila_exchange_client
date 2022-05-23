<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasParidadeFavoritaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasParidadeFavorita());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasParidadeFavorita());
        }
    }
    
    public function salvar($idCliente, $idParidade) {
        
        if ($idCliente > 0 && $idParidade > 0) {
            
            $this->conexao->insert(Array("id_cliente" => $idCliente, "id_paridade" => $idParidade));
            
        }
    }
    
    
    public function remover($idCliente, $idParidade) {
        $this->conexao->delete("id_cliente = {$idCliente} AND id_paridade = {$idParidade}");
    }
    
    
    public function getParidadesFavoritas(Cliente $cliente) {
        
        $result = $this->conexao->listar("id_cliente = {$cliente->id}", "id", null, NULL);
        $lista = Array();
        foreach ($result as $dados) {
            $lista[$dados->idParidade] = $dados;
        }
        return $lista;
    }
    
    
}

?>