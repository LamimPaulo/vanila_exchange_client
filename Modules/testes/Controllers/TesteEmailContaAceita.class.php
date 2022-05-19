<?php

namespace Modules\testes\Controllers;

class TesteEmailContaAceita {
    
    public function testEmailContaAceita($params) {
       
        $cliente = new \Models\Modules\Cadastro\Cliente(Array(
            "id" =>  "15093064536678",
            "nome" => "Vagner F. Carvalho",
            "email" => "vagnercarvalho.vfc@gmail.com"
        ));
        
        
        $contaAceita = new \Email\ContaAceita();
        $contaAceita->send($cliente);
    }
    
    
    public function testEmailContaRejeitada($params) {
        $clienteConvidadoRn = new \Models\Modules\Cadastro\ClienteConvidadoRn();
        $result = $clienteConvidadoRn->conexao->listar("cadastrou < 1", "data_convite");
        
        
        
        foreach ($result as $convite) {
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $convite->idCliente));
            
            echo $convite->email . "<br><br>";
            
            //\Email\EmailConvite::send($cliente, $convite->email);
        }
        
        
    }
}