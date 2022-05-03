<?php

namespace Modules\financeiro\Controllers;

use Utils\Geral;
use Utils\Layout;

class Moedas {

    function __construct($parameters) {
        $this->index($parameters);
    }

    function index($parameters) {
      $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
      // echo "<h2>Depois</h2>\n";
      $moeda = $moedaRn->conexao->listar();
      // $moeda = new Moeda();
      $parameters['moedas'] = $moeda;
      \Utils\Layout::view("moedas", $parameters);
    }

}
