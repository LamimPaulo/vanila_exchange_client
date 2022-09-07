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
                ['host' => '177.38.215.101', 'port' => 5672, 'user' => 'admin', 'password' => 'N@videv1', 'vhost' => '/'],
            ],
            // [
            //     'insist' => false,
            //     'login_method' => 'AMQPLAIN',
            //     'login_response' => null,
            //     'locale' => 'en_US',
            //     'connection_timeout' => 3.0,
            //     'read_write_timeout' => 10.0,
            //     'context' => null,
            //     'keepalive' => false,
            //     'heartbeat' => 5
            // ]
        );

            $channel = $connection->channel();
            // $channel->exchange_declare($exName, 'direct', false, false, false);
            $channel->queue_declare($exName, false, true, false, false);

            // $channel->queue_bind($exName, $exName, $exName.'.key');

            $msg = new AMQPMessage(json_encode($json));
            $channel->basic_publish($msg, '', $exName);

            $channel->close();
            $connection->close();
            // exit(print_r('finish'));


            // $clientSQS = new \Aws\Sqs\SqsClient([
            //     'credentials' => [
            //         'key' => $_ENV["EnvLambdaKey"],
            //         'secret' => $_ENV["EnvLambdaSecret"],
            //     ],
            //     'region' => "us-east-1",
            //     'version' => '2012-11-05'
            // ]);
            // $paramsSQS = [
            //     'MessageDeduplicationId' => strtotime(date("Y-m-d H:i:s")),
            //     'MessageGroupId' => 'group1',
            //     'MessageBody' => $json,
            //     'QueueUrl' => $urlFila
            // ];

            // $result = $clientSQS->sendMessage($paramsSQS);

            if(AMBIENTE == "desenvolvimento"){
                var_dump($result);
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
