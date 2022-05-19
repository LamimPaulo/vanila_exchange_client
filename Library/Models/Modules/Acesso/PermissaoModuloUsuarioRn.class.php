<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class PermissaoModuloUsuarioRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PermissaoModuloUsuario());
        } else {
            $this->conexao = new GenericModel($adapter, new PermissaoModuloUsuario());
        }
    }
    
    public function salvar(\Models\Modules\Cadastro\Usuario $usuario, $permissoes) {
        $this->conexao->delete("id_usuario = {$usuario->id}");
        if (sizeof($permissoes) > 0) {
            foreach ($permissoes as $idModuloHasAcao) {
                $permissaoModuloUsuario = new PermissaoModuloUsuario();
                $permissaoModuloUsuario->idModuloHasAcao = $idModuloHasAcao;
                $permissaoModuloUsuario->idUsuario = $usuario->id;
                
                $this->conexao->salvar($permissaoModuloUsuario);
            }
        }
        
    }
    
}

?>