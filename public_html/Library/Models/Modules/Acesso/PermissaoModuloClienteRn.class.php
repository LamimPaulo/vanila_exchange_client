<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class PermissaoModuloClienteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PermissaoModuloCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new PermissaoModuloCliente());
        }
    }
    
    public function salvar(\Models\Modules\Cadastro\Cliente $cliente, $permissoes) {
        $this->conexao->delete("id_cliente = {$cliente->id}");
        if (sizeof($permissoes) > 0) {
            foreach ($permissoes as $idModuloHasAcao) {
                $permissaoModuloCliente = new PermissaoModuloCliente();
                $permissaoModuloCliente->idModuloHasAcao = $idModuloHasAcao;
                $permissaoModuloCliente->idCliente = $cliente->id;
                
                $this->conexao->salvar($permissaoModuloCliente);
            }
        }
        
    }
    
    public function addToCliente($idModuloHasAcao, $idCliente) {
        
        $result = $this->conexao->select(Array("id_cliente" => $idCliente, "id_modulo_has_acao" => $idModuloHasAcao));
        if (!sizeof($result) > 0) {
            
            $permissaoModuloCliente = new PermissaoModuloCliente();
            $permissaoModuloCliente->idCliente = $idCliente;
            $permissaoModuloCliente->idModuloHasAcao = $idModuloHasAcao;
            $permissaoModuloCliente->id = 0;
            
            
            $this->conexao->salvar($permissaoModuloCliente);
        }
        
    }
    
    
    public function addToAllClient($idModuloHasAcao) {
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $clientes = $clienteRn->conexao->listar("status = 1 AND email_confirmado = 1 ", "id", NULL, NULL);
        foreach ($clientes as $cliente) {
            $this->addToCliente($idModuloHasAcao, $cliente->id);
        }
        
    }
    
}

?>