<?php

namespace Modules\financeiro\Controllers;

use Utils\Geral;
use Utils\Layout;

class Operacoes {

    public function __construct($_parameters) {
        //$this->index($_parameters);
    }

    public function index($parameters) {
        Layout::view("operacoes", $parameters);
    }

}
