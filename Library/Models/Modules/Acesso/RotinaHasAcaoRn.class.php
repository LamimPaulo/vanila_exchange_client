<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class RotinaHasAcaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new RotinaHasAcao());
        } else {
            $this->conexao = new GenericModel($adapter, new RotinaHasAcao());
        }
    }
    
    public function montarArvorePermissoes($logado, Modulo $modulo = null) {
        
        $arvore = Array();
        
        $tipos = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'");
        $whereModulo = ($modulo != null ? " AND id_modulo = {$modulo->id} " : " AND id_modulo IS NULL ");
        
        $moduloRn = new ModuloRn();
        $moduloHasAcaoRn = new ModuloHasAcaoRn();
        
        $modulos = $moduloRn->conexao->listar("tipo IN ({$tipos}) {$whereModulo} AND ativo > 0", "ordem");
        
        foreach ($modulos as $modulo) {
            
            $acoes = $moduloHasAcaoRn->listar(" id_modulo = {$modulo->id} AND tipo IN ({$tipos}) ");
            
            $rotinas = $this->montarArvoreRotinas($modulo, $logado);
            
            $outrosNiveis = $this->montarArvorePermissoes($logado, $modulo);
            
            $arvore[] = Array(
                "modulo" => $modulo,
                "rotinas" => $rotinas,
                "modulos" => $outrosNiveis,
                "acoes" => $acoes
            );
        }
        
        
        return $arvore;
    }
    
    
    public function montarArvoreRotinas(Modulo $modulo, $logado) {
        $tipos = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'");
        $rotinaRn = new RotinaRn();
        $rotinaHasAcaoRn = new RotinaHasAcaoRn();
        $result = $rotinaRn->conexao->listar("tipo IN ({$tipos}) AND id_modulo = {$modulo->id} AND ativo > 0", "ordem");
        $rotinas = Array();    
        foreach ($result as $rotina) {
            
            $rotinaHasAcoes = $rotinaHasAcaoRn->listar("id_rotina = {$rotina->id} AND tipo IN ({$tipos}) ", "id_acao");
           
            if (sizeof($rotinaHasAcoes) > 0) {
                $rotinas[] = Array(
                    "rotina" => $rotina,
                    "acoes" => $rotinaHasAcoes
                );
            }
        }
        return $rotinas;
    }
    
    
    public function carregar(RotinaHasAcao &$rotinaHasAcao, $carregar = true, $carregarAcao = true) {
        if ($carregar) {
            $this->conexao->carregar($rotinaHasAcao);
        }
        
        if ($carregarAcao && $rotinaHasAcao->idAcao > 0) {
            $rotinaHasAcao->acao = new Acao(Array("id" => $rotinaHasAcao->idAcao));
            $acaoRn = new AcaoRn();
            $acaoRn->conexao->carregar($rotinaHasAcao->acao);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarAcao = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $rotinaHasAcao) {
            $this->carregar($rotinaHasAcao, false, $carregarAcao);
            $lista[] = $rotinaHasAcao;
        }
        return $lista;
    }
    
}

?>