<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinNotificacoes extends Consumer
{
    private $rabbit;

    public $queue_name = 'notificacoes';


    public function __construct()
    {
        parent::__construct();
        $this->rabbit = new Consumer();
    }

    public function exec(array $body)
    {
        $id_proccess = isset($body['id_process']) ? $body['id_process'] : uniqid();
        $sendSendGrid = $this->rabbit->push(
            $id_proccess,
            'sendgrid',
            json_encode($body),
            true
        );

        if ($sendSendGrid['sucesso']) {
            return true;
        }
        return true;

    }


}

$consumidor = new BinNotificacoes();
return $consumidor->pull($consumidor->queue_name);
