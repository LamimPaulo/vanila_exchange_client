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
class LicencaSoftwareHasRecursoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LicencaSoftwareHasRecurso());
        } else {
            $this->conexao = new GenericModel($adapter, new LicencaSoftwareHasRecurso());
        }
    }
    
    public function atribuir(LicencaSoftwareHasRecurso $licencaSoftwareHasRecurso) {
        
        $recurso = $this->conexao->listar("id_licenca_software = {$licencaSoftwareHasRecurso->idLicencaSoftware} AND id_recurso_licenca = {$licencaSoftwareHasRecurso->idRecursoLicenca}");
        if (sizeof($recurso) <= 0) {
            
            $this->conexao->insert(Array(
                "id_licenca_software" => $licencaSoftwareHasRecurso->idLicencaSoftware,
                "id_recurso_licenca" => $licencaSoftwareHasRecurso->idRecursoLicenca
            ));
            
        }
    }
    
    
    public function remover(LicencaSoftwareHasRecurso $licencaSoftwareHasRecurso) {
        $this->conexao->delete("id_licenca_software = {$licencaSoftwareHasRecurso->idLicencaSoftware} AND id_recurso_licenca = {$licencaSoftwareHasRecurso->idRecursoLicenca}");
    }
    
}

?>