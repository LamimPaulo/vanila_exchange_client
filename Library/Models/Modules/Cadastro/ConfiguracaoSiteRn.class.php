<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class ConfiguracaoSiteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ConfiguracaoSite());
        } else {
            $this->conexao = new GenericModel($adapter, new ConfiguracaoSite());
        }
    }
    
    public function salvar(ConfiguracaoSite &$configuracaoSite) {
        
        if ($configuracaoSite->id > 0) {
            $aux = new ConfiguracaoSite(Array("id" => $configuracaoSite->id));
            $this->conexao->carregar($aux);
            
            if (empty($configuracaoSite->imagemCabase)) {
                $configuracaoSite->imagemCabase = $aux->imagemCabase;
            }
            
            if (empty($configuracaoSite->imagemCacentro)) {
                $configuracaoSite->imagemCacentro = $aux->imagemCacentro;
            }
        }
        
        $this->conexao->salvar($configuracaoSite);
    }
    
    /**
     * 
     * @return \Models\Modules\Cadastro\ConfiguracaoSite
     */
    public static function get() {
        $configuracaoSiteRn = new ConfiguracaoSiteRn();
        $configuracaoSite = new ConfiguracaoSite(Array("id" => 1));
        $configuracaoSiteRn->conexao->carregar($configuracaoSite);
        return $configuracaoSite;
    }
}

?>