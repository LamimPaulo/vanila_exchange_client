<?php

namespace Modules\principal\Controllers;

use Utils\Layout;
use Utils\Validacao;

class Manual {

    function __construct($_parameters) {
        Validacao::acesso($_parameters);
    }

    function index($_parameters) {
        Layout::view('manual', $_parameters);
    }

    

}