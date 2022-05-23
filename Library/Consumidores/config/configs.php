<?php
return [
    'application' => [
        'base_url' => 'http://broker.exchange.local/',
        'language' => 'pt-BR'
    ],
    'db' => [
        'host' => '177.38.215.105',
        'user' => 'dev',
        'pass' => 'N@videv1',
        'db' => 'exchange',
        'time_zone' => 'America/Sao_Paulo'
    ],
    'rabbit' => [
        'host' => '177.38.215.101',
        'port' => 5672,
        'user' => 'admin',
        'pass' => 'admin',
        'vhost' => '/',
        'prefix' => 'ex',
        'allowed_methods' => '',
        'non_blocking' => false,
        'timeout' => 0
    ],
    'sendgrid' => [
        'key' => '',
        'from' => [
            'name' => 'Local Exchange',
            'email' => 'broker@exchange.local'
        ],
        'params' => []
    ],
    'iporto' => [
        'key' => ''
    ],
    'ipapi' => [
        'key' => ''
    ],
    'caf' => [
        'token' => ''
    ]

];
