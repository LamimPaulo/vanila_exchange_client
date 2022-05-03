<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class ClienteHasComissaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasComissao());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasComissao());
        }
    }
    
    public function salvar(ClienteHasComissao &$clienteHasComissao) {
        
        if ($clienteHasComissao->idCliente > 0) {
            $result = $this->conexao->select(Array("id_cliente" => $clienteHasComissao->idCliente));
            if (sizeof($result) > 0) {
                $aux = $result->current();
                $clienteHasComissao->id = $aux->id;
            } else {
                $clienteHasComissao->id = 0;
            }
        } else {
            $clienteHasComissao->id = 1;
        }
        
        $this->conexao->salvar($clienteHasComissao);
    }
    
    /**
     * 
     * @param Integer $idCliente
     * @return ClienteHasComissao
     */
    public static function get($idCliente = 0, $somenteEmUso = false) {
        $clienteHasComissaoRn = new ClienteHasComissaoRn();
        if ($idCliente > 0) {
            $result = $clienteHasComissaoRn->conexao->select(Array("id_cliente" => $idCliente));
            if (sizeof($result) > 0) {
                $clienteHasComissao = $result->current();
                if ($somenteEmUso) {
                    if ($clienteHasComissao->utilizar > 0) {
                        return $clienteHasComissao;
                    }
                } else {
                    return $clienteHasComissao;
                }
                
            }
        }
        
        $result = $clienteHasComissaoRn->conexao->select("id_cliente is null");
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
    /**
     * 
     * @param Integer $idCliente
     * @return ClienteHasComissao
     */
    public static function getByCliente($idCliente = 0) {
        $clienteHasComissaoRn = new ClienteHasComissaoRn();
        if ($idCliente > 0) {
            $result = $clienteHasComissaoRn->conexao->select(Array("id_cliente" => $idCliente));
            if (sizeof($result) > 0) {
                return $result->current();
            }
        }
        
        return null;
    }
    
}

?>