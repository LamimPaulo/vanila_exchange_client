<?php

namespace Modules\testes\Controllers;

class TesteRetornoCallback {
    
    public function index($params) {
        
        print_r($params["_GET"]);
        
    }
    
}