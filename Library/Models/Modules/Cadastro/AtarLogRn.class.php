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
class AtarLogRn {
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new AtarLog());
        } else {
            $this->conexao = new GenericModel($adapter, new AtarLog());
        }
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
    }
    
    public function salvar(AtarLog &$atarLog) {
        
        $atarLog->data = new \Utils\Data(date("Y-m-d H:i:s"));
        
        $this->conexao->salvar($atarLog);
        
    }
    
}
