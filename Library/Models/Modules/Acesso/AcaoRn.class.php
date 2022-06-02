<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class AcaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Acao());
        } else {
            $this->conexao = new GenericModel($adapter, new Acao());
        }
    }
    
}

?>