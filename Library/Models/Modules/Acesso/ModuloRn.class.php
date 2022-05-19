<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class ModuloRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Modulo());
        } else {
            $this->conexao = new GenericModel($adapter, new Modulo());
        }
    }
    
    public function getModulosByPermissao($logado, $idModulo = null) {
        // flag para permitir a chamada recursvia a fim de obter os próximos níveis de módulos
        $whereModulo = ($idModulo > 0 ? " m.id_modulo = {$idModulo} " : " m.id_modulo IS NULL ");
        $tipo = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'" );
        $modulos = Array();
        
        $result = null;
        if ($logado instanceof \Models\Modules\Cadastro\Usuario) {
            
            $queryModulos = "SELECT DISTINCT(m.id), m.* "
                . " FROM modulos m "
                . " INNER JOIN modulos_has_acoes mha ON (mha.id_modulo = m.id) "
                . " INNER JOIN permissoes_modulos_usuarios pmu ON (mha.id = pmu.id_modulo_has_acao) "
                . " WHERE "
                . " m.ativo > 0 AND "
                . " mha.id_acao = 1 AND "
                . " m.tipo IN ({$tipo}) AND "
                . " mha.tipo IN ({$tipo}) AND "
                . " pmu.id_usuario = {$logado->id} AND "
                . " {$whereModulo} "
                . " ORDER BY m.ordem;";
            
            $result = $this->conexao->adapter->query($queryModulos)->execute();
            
        } else if ($logado instanceof \Models\Modules\Cadastro\Cliente) {
            
            $queryModulos = "SELECT DISTINCT(m.id), m.* "
                . " FROM modulos m "
                . " INNER JOIN modulos_has_acoes mha ON (mha.id_modulo = m.id) "
                . " INNER JOIN permissoes_modulos_clientes pmc ON (mha.id = pmc.id_modulo_has_acao) "
                . " WHERE "
                . " m.visivel_cliente > 0 AND "
                . " m.ativo > 0 AND "
                . " mha.id_acao = 1 AND "
                . " m.tipo IN ({$tipo}) AND "
                . " mha.tipo IN ({$tipo}) AND "
                . " pmc.id_cliente = {$logado->id} AND "
                . " {$whereModulo} "
                . " ORDER BY m.ordem;";
            
            $result = $this->conexao->adapter->query($queryModulos)->execute();
            
        }
        
        if (sizeof($result) > 0) {
            foreach ($result as $dados) {
                $modulo = new Modulo($dados);
                
                // chamada recursiva para pegar os próximos níveis de módulos
                $m = $this->getModulosByPermissao($logado, $modulo->id);
                $modulo->modulos = $m;
                
                $modulos[] = $modulo;
            }
        }
        
        
        
        return $modulos;
    }
    
    public function validarAcesso($codigo) {
        $logado = \Utils\Geral::getLogado();
        
        $tipo = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'" );
        
        $moduloRn = new ModuloRn();
        
        if (is_array($codigo)) { 
            $sCodigo = implode( "', '", $codigo );
            $existe = $moduloRn->conexao->listar("codigo IN ('{$sCodigo}') ");
        } else {
            $existe = $moduloRn->conexao->listar("codigo = '{$codigo}'");
        }
        
        if (sizeof($existe) > 0) { 
            
            $queryCodigo = "";
            if (is_array($codigo)) { 
                $queryCodigo = " AND  m.codigo IN ('". implode("', '", $codigo)."') ";
            } else {
                $queryCodigo = " AND  m.codigo = '{$codigo}'";
            }
            
            $result = null;
            if ($logado instanceof \Models\Modules\Cadastro\Usuario) {

                $queryModulos = "SELECT DISTINCT(m.id), m.* "
                    . " FROM modulos m "
                    . " INNER JOIN modulos_has_acoes mha ON (mha.id_modulo = m.id) "
                    . " INNER JOIN permissoes_modulos_usuarios pmu ON (mha.id = pmu.id_modulo_has_acao) "
                    . " WHERE "
                    . " m.ativo > 0 AND "
                    . " mha.id_acao = 1 AND "
                    . " m.tipo IN ({$tipo}) AND "
                    . " mha.tipo IN ({$tipo}) AND "
                    . " pmu.id_usuario = {$logado->id} "
                    . " {$queryCodigo} "
                    . " ORDER BY m.ordem;";

                $result = $this->conexao->adapter->query($queryModulos)->execute();

            } else if ($logado instanceof \Models\Modules\Cadastro\Cliente) {

                $queryModulos = "SELECT DISTINCT(m.id), m.* "
                    . " FROM modulos m "
                    . " INNER JOIN modulos_has_acoes mha ON (mha.id_modulo = m.id) "
                    . " INNER JOIN permissoes_modulos_clientes pmc ON (mha.id = pmc.id_modulo_has_acao) "
                    . " WHERE "
                    . " m.ativo > 0 AND "
                    . " mha.id_acao = 1 AND "
                    . " m.tipo IN ({$tipo}) AND "
                    . " mha.tipo IN ({$tipo}) AND "
                    . " pmc.id_cliente = {$logado->id} "
                    . " {$queryCodigo} "
                    . " ORDER BY m.ordem;";

                $result = $this->conexao->adapter->query($queryModulos)->execute();

            }

            if (sizeof($result) <= 0) {
                return false;
            }
        }
        
        
        return true;
    }
    
    
    public static function validar($codigo, $idAcao) {
        
        $logado = \Utils\Geral::getLogado();
        $tipo = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'" );
        $moduloRn = new ModuloRn();
        $existe = $moduloRn->conexao->listar("codigo = '{$codigo}'");
        
        if (sizeof($existe) > 0) { 
            $result = null;
            if ($logado instanceof \Models\Modules\Cadastro\Usuario) {

                $query = " SELECT m.* "
                        . " FROM modulos m "
                        . " INNER JOIN modulos_has_acoes mha ON (m.id = mha.id_modulo) "
                        . " INNER JOIN permissoes_modulos_usuarios pmu ON (mha.id = pmu.id_modulo_has_acao) "
                        . " WHERE "
                        . " mha.id_acao = {$idAcao} AND "
                        . " m.tipo IN ({$tipo}) AND "
                        . " mha.tipo IN ({$tipo}) AND "
                        . " pmu.id_usuario = {$logado->id} AND "
                        . " m.codigo = '{$codigo}' "
                        . " ORDER BY m.ordem; ";
 
                $result = $moduloRn->conexao->adapter->query($query)->execute();

            } else if ($logado instanceof \Models\Modules\Cadastro\Cliente) {

                $query = " SELECT m.* "
                        . " FROM modulos m  "
                        . " INNER JOIN modulos_has_acoes mha ON (m.id = mha.id_modulo) "
                        . " INNER JOIN permissoes_modulos_clientes pmc ON (mha.id = pmc.id_modulo_has_acao) "
                        . " WHERE "
                        . " m.ativo > 0 > 0 AND "
                        . " mha.id_acao = {$idAcao} AND "
                        . " mha.tipo IN ({$tipo}) AND "
                        . " m.tipo IN ({$tipo}) AND "
                        . " pmc.id_cliente = {$logado->id} AND "
                        . " m.codigo = '{$codigo}' "
                        . " ORDER BY m.ordem; ";

                $result = $moduloRn->conexao->adapter->query($query)->execute();
            }
            
            if (sizeof($result) <= 0) {
                return false;
            }
        
        } 
        
        return true;
    }
}

?>