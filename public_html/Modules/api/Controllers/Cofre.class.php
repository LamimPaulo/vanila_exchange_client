<?php

namespace Modules\api\Controllers;

class Cofre {
    
    public function sinc() {
        try {
            
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
    }
    
}