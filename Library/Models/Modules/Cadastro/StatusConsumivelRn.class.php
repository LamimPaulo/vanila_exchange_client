<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade StatusConsumivel
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class StatusConsumivelRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new StatusConsumivel());
        } else {
            $this->conexao = new GenericModel($adapter, new StatusConsumivel());
        }
    }
    
    public function updateStatusSms($quantidadeSms, \Utils\Data $validadeSms = null) {
        
        $set = Array();
        $set["quantidade_sms"] = ($quantidadeSms > 0 ? $quantidadeSms : 0);
        if ($validadeSms != null) {
            $set["validade_sms"] = $validadeSms->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        }
        
        $this->conexao->update($set);
    }
    
    
    public function upateStatusConsultaDocumentos($quantidadeConsultas) {
        $this->conexao->update(Array("creditos_consulta_documentos" => ($quantidadeConsultas > 0 ? $quantidadeConsultas : 0)));
    }
    
    
    public function getStatus() {
        $dados = $this->conexao->listar();
        return $dados->current();
    }
}

?>