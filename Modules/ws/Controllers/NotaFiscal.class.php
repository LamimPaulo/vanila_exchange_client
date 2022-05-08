<?php

namespace Modules\ws\Controllers;

class NotaFiscal {
    
    public function notification($params) {
        
        try {
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, TRUE); //convert JSON into array
            
            //$idnf = \Utils\Post::get($params, "nfeId", NULL);
            $idnf = $input["nfeId"];
            
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            $notaFiscal = $notaFiscalRn->getByNfId($idnf);
            if ($notaFiscal == null) {
                throw new \Exception("Nota fiscal não cadastrada");
            }
            
            
            $dados = \ENotasGW\NotaFiscal::consultar($idnf);
            
            \Models\Modules\Cadastro\NotaFiscalRn::setNotaFiscalFromJson($notaFiscal, $dados);
            
            $notaFiscalRn->salvar($notaFiscal);
            
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        
        exit("ok");
    }
    
}
