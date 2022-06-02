<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * 
 *
 * @author willianchiquetto
 */
class LayoutRn {
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Layout());
        } else {
            $this->conexao = new GenericModel($adapter, new Layout());
        }
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
    }
    
    public function salvarOrderBook(Layout &$layout) {
        
        if(empty($layout)){
            throw new \Exception("Layout inválido.");
        }
        
        if($layout->id > 0){
           $this->conexao->update(Array("order_book" => $layout->orderBook), Array("id_cliente" => $layout->idCliente)); 
        } else {
            $this->conexao->salvar($layout);
        }
    }
    
    public function salvarDashboard(Layout &$layout) {
        
        if(empty($layout)){
            throw new \Exception("Layout inválido.");
        }
        
        if($layout->id > 0){
           $this->conexao->update(Array("dashboard" => $layout->dashboard), Array("id_cliente" => $layout->idCliente)); 
        } else {
            $this->conexao->salvar($layout);
        }
    }
    
    public static function getLayout($idCliente){
        $layoutRn = new LayoutRn();
        $result = $layoutRn->conexao->select(" id_cliente = {$idCliente}");
        
        if(sizeof($result) > 0){
            $layout = $result->current();
            return $layout;
        } else {
            return null;
        }
    }
    
}
