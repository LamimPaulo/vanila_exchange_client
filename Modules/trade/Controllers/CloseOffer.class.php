<?php

namespace Modules\trade\Controllers;

class CloseOffer {

public function index($params) {
        try {             
           
            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);                

            \Utils\Layout::view("pratique");

        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
       
    }
}