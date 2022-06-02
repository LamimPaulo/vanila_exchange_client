<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Cadastro\Moeda;
use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Moeda
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class StatusCoreRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new StatusCore());
        } else {
            $this->conexao = new GenericModel($adapter, new StatusCore());
        }
    }

    
    /**
     * 
     * @param Moeda $moeda
     * @return StatusCore
     */
    public function getByIdMoeda(Moeda $moeda) {
        $result = $this->conexao->select(
                Array("id_moeda" => $moeda->id)
            );
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    public function salvar(StatusCore &$statusCore) {
        
        if (!$statusCore->idMoeda > 0) {
            throw new \Exception("Moeda inválida");
        }
        
        if(empty($statusCore->id)){
           $s = $this->getByIdMoeda(new Moeda(Array("id" => $statusCore->idMoeda)));
           $statusCore->id = ($s != null ? $s->id : 0); 
        }
        
        $statusCore->dataUltimaAtualizacao = new \Utils\Data(date("d/m/Y H:i:s"));
        
        $this->conexao->salvar($statusCore);
    }
    
}
