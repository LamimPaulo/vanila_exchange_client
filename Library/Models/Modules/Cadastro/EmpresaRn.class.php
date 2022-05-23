<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estado
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class EmpresaRn{
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Empresa());
        } else {
            $this->conexao = new GenericModel($adapter, new Empresa());
        }
    }
    
    public static function getEmpresa() {
        $empresaRn = new EmpresaRn();
        $empresa = EMPRESA;
        
        $result = $empresaRn->conexao->listar(" id = {$empresa} ");
        $dados = $result->current();
        
        return $dados;
    }
}

?>