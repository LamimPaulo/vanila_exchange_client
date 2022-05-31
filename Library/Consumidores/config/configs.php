<?php
return [
    'application' => [
        'base_url' => 'http://177.38.215.100/',
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
        'port' => 15672,
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
    ],
    'mandrill' => [
        'host' => 'smtp.mandrillapp.com',
        'port' => 587,
        'user' => 'any username will work - try "navi" for example',
        'pass' => 'fjHTcoqqNAe45zJgqWkCQQ',
        'from' => [
            'mail' => 'no-reply@navi.inf.br',
            'name' => 'Nav inf'
        ]

    ]
];
