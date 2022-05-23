<?php

namespace Modules\financeiro\Controllers;

use Utils\Geral;
use Utils\Layout;

class Deposito {

    function __construct($_parameters) {
        $this->index($_parameters);
    }
    
    function index($_parameters) {
        \Utils\Layout::view("deposito", $params);
    }

}
