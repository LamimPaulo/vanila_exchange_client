<?php

namespace Modules\acesso\Controllers;


class Login {
    
    public function index($params) {
        \Utils\Layout::view("login", $params);
    }
    
}