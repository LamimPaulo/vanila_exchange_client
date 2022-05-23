<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class RecursoLicencaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new RecursoLicenca());
        } else {
            $this->conexao = new GenericModel($adapter, new RecursoLicenca());
        }
    }
    
    
    public function salvar(RecursoLicenca &$recursoLicenca) {
        
        if (empty($recursoLicenca->descricao)) {
            throw new \Exception($this->idioma->getText("nomeRecursoInvalido"));
        }
        
        if ($recursoLicenca->ordem <= 0) {
            throw new \Exception($this->idioma->getText("ordemRecursoInvalido"));
        }
        
        $this->conexao->salvar($recursoLicenca);
    }
    
    public function excluir(RecursoLicenca &$recursoLicenca) {
        
        try {
            $this->conexao->carregar($recursoLicenca);
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("recursoNaoEncontrado"));
        }
        
        $licencaSoftwareHasRecursoRn = new LicencaSoftwareHasRecursoRn();
        $licencaSoftwareHasRecursoRn->conexao->delete(" id_recurso_licenca = {$recursoLicenca->id} ");
        
        
        $this->conexao->excluir($recursoLicenca);
    }
    
    
}

?>