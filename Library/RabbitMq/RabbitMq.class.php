<?php

namespace RabbitMq;

class Client
{
    private static function log_(string $process_id, string $type, string $queue_name, array $params)
    {

        $config = [
            'host' => getenv("RmqHost"),
            'port' => getenv("RmqPort"),
            'user' => getenv("RmqUser") ,
            'pass' => getenv("RmqPass") ,
            'vhost' =>  '/',
            'prefix' =>  getenv("RmqPrefix") ,
            'allowed_methods' => '',
            'non_blocking' => false,
            'timeout' => 0
        ];


        $dataLog = [
            'id_process' => $process_id,
            'type' => $type,
            'queue_name' =>  sprintf('%s.%s', $config['prefix'], $queue_name),
            'mensagem' => $message ?? 'Envio de dados (Push)',
            'params' => $params
        ];
        $rabbitmq = new \RabbitMq\Server();
        $rabbitmq->push(
            $process_id,
            sprintf('log_%s', $type),
            json_encode($dataLog),
            true,
            array('delivery_mode' => 2)
        );

    }

    public  function sendQueue(string $queue_name = null, array $params = [])
    {

        if (empty($queue_name)) {
            throw new Exception("You did not specify the [queue] parameter");
        }


        $id = uniqid();
        $params = array_merge($params, [
            'id_process' => $id
        ]);

        $rabbit = new \RabbitMq\Server();
        $push =  $rabbit->push(
            $id,
            $queue_name,
            json_encode($params),
            true

        );

        $this->log_(
            $id,
            $push['sucesso'] ? 'info' : 'error',
            $queue_name,
            $params
        );

        return $push;
    }
}
