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
class TokenGatewayLogRn {
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TokenGatewayLog());
        } else {
            $this->conexao = new GenericModel($adapter, new TokenGatewayLog());
        }
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
    }
    
    public function salvar(TokenGatewayLog &$tokenLog) {
        
        $tokenLog->data = new \Utils\Data(date("Y-m-d H:i:s"));
        
        $this->conexao->salvar($tokenLog);
        
    }
    
}
