<?php
$config_ = include  __DIR__. '/config/configs.php';



define("BDUSER", $config_['db']['user']);
define("BDPASS",  $config_['db']['pass']);
define("BDHOST", $config_['db']['host']);
define("BDNAME", $config_['db']['db']);
define("DBZONE", $config_['db']['time_zone']);
define("URLBASE_CLIENT", $config_['application']['base_url']);
define("IDIOMA", $config_['application']['language']);
define("LOG_ERROR_QUEUE", 'error');
define("LOG_INFO_QUEUE", 'info');


require "Consumer.class.php";
require_once __DIR__ . '/autoload_classmap.php';
