<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class SeguimentoComercialRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new SeguimentoComercial());
        } else {
            $this->conexao = new GenericModel($adapter, new SeguimentoComercial());
        }
    }
    
    
    public function salvar(SeguimentoComercial &$segmentoComercial) {
        
        if ($segmentoComercial->id > 0) {
            $aux = new SeguimentoComercial(Array("id" => $segmentoComercial->id));
            $this->conexao->carregar($aux);
            
            $segmentoComercial->ativo = $aux->ativo;
        } else {
            $segmentoComercial->ativo = 1;
        }
        
        if (empty($segmentoComercial->nome)) {
            throw new \Exception("O nome do Segmento comercial deve ser informado");
        }
        
        $this->conexao->salvar($segmentoComercial);
        
    }
    
    
    public function excluir(SeguimentoComercial &$segmentoComercial) {
        $comercioRn = new ComercioRn();
        
        $comercios = $comercioRn->conexao->listar("id_segmento_comercio = {$segmentoComercial->id}", null, null, 1);
        if (sizeof($comercios) > 0) {
            throw new \Exception("O segmento comercial não pode ser excluído por que existem comércios vinculados ao mesmo");
        }
        
        
        $this->conexao->excluir($segmentoComercial);
    }
    
    
    
    public function alterarStatusAtivo(SeguimentoComercial &$segmentoComercial) {
        try {
            $this->conexao->carregar($segmentoComercial);
        } catch (\Exception $ex) {
            throw new \Exception("Segmento não encontrado");
        }
        
        $segmentoComercial->ativo = ($segmentoComercial->ativo > 0 ? 0  : 1);
        $this->conexao->update(Array("ativo" => ($segmentoComercial->ativo > 0 ? "1" : "0")), Array("id" => $segmentoComercial->id));
    }
    
    
}

?>