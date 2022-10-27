<?php
ob_start();
require_once __DIR__ . '/Library/vendor/autoload.php';
require_once __DIR__ . '/Library/autoload_classmap.php';
require_once 'vendor/autoload.php';


use Utils\Session;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
//Definição do ambiente de execução do sistema

$ambiente = $_SERVER['AMBIENTE'] ?? getenv('AMBIENTE');
$empresa = $_SERVER['EMPRESA'] ?? getenv('EMPRESA');
$titulo = $_SERVER['TITULO'] ?? getenv('TITULOP');
$grafico = $_SERVER['GRAFICO'] ?? getenv('GRAFICO');
$erro = $_SERVER['ERRO'] ?? getenv('ERRO');
$urlBase = $_ENV["EnvUrlProd"] ?? getenv('EnvUrlProd');
$bdBook = $_ENV["EnvBdUrlBookName"] ?? getenv('EnvBdUrlBookName');
$bdGrafico = $_ENV["EnvBdUrlGraficoName"] ?? getenv('EnvBdUrlGraficoName');

define("AMBIENTE", $ambiente);
define("EMPRESA", $empresa);
define("TITULO", $titulo);
define("GRAFICO", $grafico);
define("BDBOOK", $bdBook);
define("BDGRAFICO", $bdGrafico);

if ($ambiente == 'desenvolvimento') {
    error_reporting(E_ERROR);
    /*ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);*/

} else if ($ambiente == 'producao') {
    //Se não, os erros são omitidos
    error_reporting(E_ALL);
}


if (AMBIENTE == "producao" && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

if(AMBIENTE != "producao"){
    define('DATAFEED_URL', 'https://sandbox.coinage.trade/api/getcandles');
}else{
    define('DATAFEED_URL', 'https://coinage.trade/api/getcandles');
}

//Incluo os caminhos de todas as bibliotecas e arquivos de include do sistema
set_include_path('./Library/' . PATH_SEPARATOR . './Core/' . PATH_SEPARATOR . './Modules/');
//Defino o locale do sistema
setlocale(LC_ALL, "pt_BR", "ptb_bra");
date_default_timezone_set('America/Sao_Paulo' );
//Único require feito manualmente, responsável pelo autoload de todas as outras classes


require_once 'Dduo.class.php';
//require_once './Library/PHPMailer/vendor/autoload.php';
//require_once './Library/GerenciaNet/vendor/autoload.php';

define('DIR_MODULES', 'Modules/');
define('DIR_LAYOUTS', 'layouts/');
define('DIR_PUBLIC', 'public/');
define('DIR_TV', 'tradingview/');

$dduo = new Dduo();

date_default_timezone_set($dduo->getTimeZone());


//Definição de urls para a inclusão de scripts JS, CSS, arquivos de imagens, ícones, etc.
define('IDIOMA', Modules\principal\Controllers\Principal::getIdioma());
define('URL', $urlBase . $_SERVER['REQUEST_URI']);
define('URLBASE', $urlBase);
define('CSS', $urlBase . '/resources/css/');
define('JS', $urlBase . '/resources/js/');
define('BOOK', $urlBase . '/resources/book/');
define('LIB', JS . 'lib/');
define('TEMPLATE', $urlBase . '/resources/templates/');
define('NC', TEMPLATE . 'default/');
define('TEMA', TEMPLATE . 'inspina/');
define('SITE', TEMPLATE . 'appic/');
define('NEWCASH', TEMPLATE . 'default/');
define('IMAGES', $urlBase . '/resources/images/');
define('AUDIO', $urlBase . '/resources/sounds/');
define('ICONS', IMAGES . 'icons/');

//Tema Cointrade
define('TEMACOINTRADE', TEMPLATE . 'Cointrade/');
define('TEMAJS', TEMPLATE . 'Cointrade/js/');
define('TEMACSS', TEMPLATE . 'Cointrade/css/');
define('TEMAIMG', TEMPLATE . 'Cointrade/img/');

if(AMBIENTE == "producao"){
    define('UPLOADS', "../../../../efs/arquivosclientes/");
    define('IMG_PUBLIC', "../../../../efs/img-public/");
} else {
    define('UPLOADS', "arquivosclientes/");
    define('IMG_PUBLIC', "uploads/public/");
}

define('QRCODES', "qrcodes/");
define('PUBLIC_IMAGES', "uploads/public/");


$parametros = $dduo->checaURL(URL);
define('URLBASE_CLIENT', URLBASE . '/' );

define('CLIENTE', (!empty($parametros['_parameters']['_client']) ? $parametros['_parameters']['_client'] . '/' : null));

$dduo->route($parametros);


?>
