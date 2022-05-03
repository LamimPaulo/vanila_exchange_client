<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class RotinaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Rotina());
        } else {
            $this->conexao = new GenericModel($adapter, new Rotina());
        }
    }
    
    
    public function getRotinas($logado, Modulo $modulo = null) {
        
        $whereModulo = "";
        if ($modulo != null) {
            $whereModulo = " AND r.id_modulo = {$modulo->id} ";
        }
        $tipo = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'" );
        
        $rotinas = Array();
        
        $result = null;
        if ($logado instanceof \Models\Modules\Cadastro\Usuario) {
            
            $query = " SELECT r.* "
                    . " FROM rotinas r "
                    . " INNER JOIN rotinas_has_acoes rha ON (r.id = rha.id_rotina) "
                    . " INNER JOIN permissoes_usuarios pu ON (rha.id = pu.id_rotina_has_acao) "
                    . " WHERE "
                    . " rha.id_acao = 1 AND "
                    . " r.tipo IN ({$tipo}) AND "
                    . " rha.tipo IN ({$tipo}) AND "
                    . " pu.id_usuario = {$logado->id} AND "
                    . " r.ativo > 0 "
                    . " {$whereModulo} "
                    . " ORDER BY r.ordem; ";
                    
            
            $result = $this->conexao->adapter->query($query)->execute();
            
        } else if ($logado instanceof \Models\Modules\Cadastro\Cliente) {
            
            $query = " SELECT r.* "
                    . " FROM rotinas r "
                    . " INNER JOIN rotinas_has_acoes rha ON (r.id = rha.id_rotina) "
                    . " INNER JOIN permissoes_clientes pc ON (rha.id = pc.id_rotina_has_acao) "
                    . " WHERE "
                    . " rha.id_acao = 1 AND "
                    . " r.tipo IN ({$tipo}) AND "
                    . " rha.tipo IN ({$tipo}) AND "
                    . " pc.id_cliente = {$logado->id}  AND "
                    . " r.ativo > 0 "
                    . " {$whereModulo} "
                    . " ORDER BY r.ordem; ";
                    
            $result = $this->conexao->adapter->query($query)->execute();
        }
        
        if (sizeof($result) > 0) {
            foreach ($result as $dados) {
                $rotina = new Rotina($dados);
                $rotinas[] = $rotina;
            }
        }
        
        return $rotinas;
    }
    
    
    
    public static function validar($codigo, $idAcao) {
        
        $logado = \Utils\Geral::getLogado();
        $tipo = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'" );
        $rotinaRn = new RotinaRn();
        $existe = $rotinaRn->conexao->listar("codigo = '{$codigo}'");
        
        if (sizeof($existe) > 0) { 
            $result = null;
            if ($logado instanceof \Models\Modules\Cadastro\Usuario) {

                $query = " SELECT r.* "
                        . " FROM rotinas r "
                        . " INNER JOIN rotinas_has_acoes rha ON (r.id = rha.id_rotina) "
                        . " INNER JOIN permissoes_usuarios pu ON (rha.id = pu.id_rotina_has_acao) "
                        . " WHERE "
                        . " rha.id_acao = {$idAcao} AND "
                        . " r.tipo IN ({$tipo}) AND "
                        . " rha.tipo IN ({$tipo}) AND "
                        . " pu.id_usuario = {$logado->id} AND "
                        . " r.codigo = '{$codigo}' "
                        . " ORDER BY r.ordem; ";
 
                $result = $rotinaRn->conexao->adapter->query($query)->execute();

            } else if ($logado instanceof \Models\Modules\Cadastro\Cliente) {

                $query = " SELECT r.* "
                        . " FROM rotinas r "
                        . " INNER JOIN rotinas_has_acoes rha ON (r.id = rha.id_rotina) "
                        . " INNER JOIN permissoes_clientes pc ON (rha.id = pc.id_rotina_has_acao) "
                        . " WHERE "
                        . " rha.id_acao = {$idAcao} AND "
                        . " rha.tipo IN ({$tipo}) AND "
                        . " r.tipo IN ({$tipo}) AND "
                        . " pc.id_cliente = {$logado->id} AND "
                        . " r.codigo = '{$codigo}' "
                        . " ORDER BY r.ordem; ";

                $result = $rotinaRn->conexao->adapter->query($query)->execute();
            }
            
            if (sizeof($result) <= 0) {
                return false;
            }
        
        } 
        
        return true;
    }
}

?>