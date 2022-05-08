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
class EstadoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Estado());
        } else {
            $this->conexao = new GenericModel($adapter, new Estado());
        }
    }
    
    public function salvar(Estado &$estado) {
        if (strlen($estado->nome) <= 0) {
            throw new \Exception($this->idioma->getText("necessarioInformarNomeEstado"));
        }
        
        if (strlen($estado->sigla) != 2) {
            throw new \Exception($this->idioma->getText("siglaEstadoInvalida"));
        }
        
        $this->conexao->salvar($estado);
    }
    
    public static function get($idEstado) {
        $estado = new Estado(Array("id" => $idEstado));
        $estadoRn = new EstadoRn();
        $estadoRn->conexao->carregar($estado);
        return $estado;
    }
    
    
}

?>