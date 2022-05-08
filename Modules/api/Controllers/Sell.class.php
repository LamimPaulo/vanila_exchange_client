<?php

namespace Modules\api\Controllers;

class Sell {
    
    public function index($params) {
        
        $headers = apache_request_headers();

        foreach ($headers as $header => $value) {
            echo "$header: $value <br />\n";
        }
        
    }
    
}