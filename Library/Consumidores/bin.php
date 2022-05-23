<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once 'Consumer.class.php';
require_once "BinNotificacoes.php";
require_once "BinSendGrid.php";

$queues = [
    'notificacoes' => new BinNotificacoes()
];
$channelCount = 0;
echo sprintf('Processando ...'.PHP_EOL);

foreach ($queues as $queue => $value) {
   echo sprintf('Processando ...'.PHP_EOL);
    // echo sprintf('Iniciando fila -> %s'.PHP_EOL, $queue);
   /* \call_user_func([new $value($queue), 'pull']);*/
}




