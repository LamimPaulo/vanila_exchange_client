<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LicencaSoftwareHasRecurso {

    /**
     *
     * @var Integer 
     */
    public $idLicencaSoftware;

    
    /**
     *
     * @var Integer 
     */
    public $idRecursoLicenca;
    
    
    
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->idLicencaSoftware = ((isset($dados ['id_licenca_software'])) ? ($dados ['id_licenca_software']) : (null));
        $this->idRecursoLicenca = ((isset($dados ['id_recurso_licenca'])) ? ($dados ['id_recurso_licenca']) : (null));
    }
    
    public function getTable() {
        return "licenca_software_has_recurso";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LicencaSoftwareHasRecurso();
    }


}

?>