<?php

namespace Modules\financeiro\Controllers;

use Utils\Geral;
use Utils\Layout;

class Processos {

    function __construct($_parameters) {
        $this->index($_parameters);
    }
    
    function index($_parameters) {
        \Utils\Layout::view("processos", $params);
    }

}
