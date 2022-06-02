<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class ModuloHasAcaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ModuloHasAcao());
        } else {
            $this->conexao = new GenericModel($adapter, new ModuloHasAcao());
        }
    }
    
    
    
    
    public function montarArvoreRotinas(Modulo $modulo, $logado) {
        $tipos = ($logado instanceof \Models\Modules\Cadastro\Usuario ? "'U', 'UC'" : "'C', 'UC'");
        $rotinaRn = new RotinaRn();
        $rotinaHasAcaoRn = new RotinaHasAcaoRn();
        $result = $rotinaRn->conexao->listar("tipo IN ({$tipos}) AND id_modulo = {$modulo->id} AND ativo > 0", "ordem");
        $rotinas = Array();    
        foreach ($result as $rotina) {
            
            $rotinaHasAcoes = $rotinaHasAcaoRn->listar("id_rotina = {$rotina->id}", "id_acao");
           
            $rotinas[] = Array(
                "rotina" => $rotina,
                "acoes" => $rotinaHasAcoes
            );
        }
        return $rotinas;
    }
    
    
    public function carregar(ModuloHasAcao &$moduloHasAcao, $carregar = true, $carregarAcao = true) {
        if ($carregar) {
            $this->conexao->carregar($moduloHasAcao);
        }
        
        if ($carregarAcao && $moduloHasAcao->idAcao > 0) {
            $moduloHasAcao->acao = new Acao(Array("id" => $moduloHasAcao->idAcao));
            $acaoRn = new AcaoRn();
            $acaoRn->conexao->carregar($moduloHasAcao->acao);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarAcao = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $moduloHasAcao) {
            $this->carregar($moduloHasAcao, false, $carregarAcao);
            $lista[] = $moduloHasAcao;
        }
        return $lista;
    }
}

?>