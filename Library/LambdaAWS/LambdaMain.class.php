<?php

namespace LambdaAWS;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class LambdaMain {

    public static function enviar($exName, $json) {
        try {

            $connection =  AMQPStreamConnection::create_connection([
                ['host' => $_ENV['AMQP_HOST'], 'port' => $_ENV['AMQP_PORT'], 'user' => $_ENV['AMQP_USER'], 'password' => $_ENV['AMQP_PASSWORD'], 'vhost' => '/'],
            ]);

            $channel = $connection->channel();
            // $channel->exchange_declare($exName, 'direct', false, false, false);
            $channel->queue_declare($exName, false, true, false, false);

            // $channel->queue_bind($exName, $exName, $exName.'.key');

            $msg = new AMQPMessage(json_encode($json));
            $channel->basic_publish($msg, '', $exName);

            $channel->close();
            $connection->close();

            if(AMBIENTE == "desenvolvimento"){
                // var_dump($result);
            }

            return true;
        } catch (\Aws\Exception\AwsException $e) {
            if(AMBIENTE == "desenvolvimento"){
                var_dump($e->getMessage());
            }
            return false;
        }
    }

}
