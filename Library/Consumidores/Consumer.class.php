<?php

error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo' );

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../../Core/Dduo.class.php";

class Consumer
{

    protected $config;
    public $connection;
    public $channel;
    public $show_output;

    public function __construct()
    {
        $config_ = include  'config/configs.php';
        $this->config = [
            'host' => $config_['rabbit']['host'],
            'port' => $config_['rabbit']['port'],
            'user' => $config_['rabbit']['user'],
            'pass' => $config_['rabbit']['pass'],
            'vhost' => '/',
            'prefix' => $config_['rabbit']['prefix'],
            'allowed_methods' => '',
            'non_blocking' => false,
            'timeout' => 0
        ];

        if(isset($config_['application']['ambiente'])){
            define("AMBIENTE", $config_['application']['ambiente']);
        }
    }

    public function connect()
    {
        register_shutdown_function(array($this, 'cleanup_connection'));
        $this->connection = new PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);
        $this->channel = $this->connection->channel();
    }

    public function exec(array $body)
    {
        echo sprintf('Rceived body consumer principal -> %s' . PHP_EOL, json_encode($body));

        return true;

    }

    public function pull(string $queue = null)
    {

        if (empty($queue)) {
            rabbitmq_client_output('You did not specify the [queue] parameter', 'error', 'x');
            throw new Exception("You did not specify the [queue] parameter");
        }
        $queue = sprintf('%s.%s', $this->config['prefix'], $queue);
        $this->connect();

        // Declaring the queue again
        $this->channel->queue_declare($queue, false, true, false, false);
        // Limit the number of unacknowledged
        $this->channel->basic_qos(null, 1, null);
        $callback = function ($message) use ($queue) {
            $body = $message->body ? json_decode($message->body, true) : null;
            $id_process = $body['id_process'] ?? uniqid();
            $this->log_(
                $id_process,
                'info',
                $queue,
                isset($body) ? $body : [],
                'Recebimento de dados (Pull)'
            );


            if (!$this->exec($body)) {

                $this->log_(
                    $id_process,
                    'error',
                    $queue,
                    isset($body) ? $body : []
                );
                $this->lock($message);
            } else {
                $this->unlock($message);
            }


        };

        // Define consuming with 'process' callback
        $this->channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function log_(string $id_process, string $type, string $queue_name, array $params, string $message = null)
    {
        $dataLog = [
            'id_process' => $id_process,
            'type' => $type,
            'queue_name' =>  sprintf('%s.%s', $this->config['prefix'], $queue_name),
            'mensagem' => $message ?? 'Não existe uma mensagem definida',
            'params' => $params
        ];
        $rabbitmq = new Consumer();
        $rabbitmq->push(
            $id_process,
            sprintf('log_%s', $type),
            json_encode($dataLog),
            true,
            array('delivery_mode' => 2)
        );
    }

    public function push($id_process, $queue = null, $data = null, $permanent = false, $params = array())
    {
        try {
            // We check if the queue is not empty then we declare the queue
            if (empty($queue)) {
                rabbitmq_client_output('You did not specify the [queue] parameter', 'error', 'x');
                throw new Exception("You did not specify the [queue] parameter");
            }

            // Connect to rabbit
            $this->connect();

            $queue = sprintf('%s.%s', $this->config['prefix'], $queue);

            // We declare the queue
            $this->channel->queue_declare($queue, false, $permanent, false, false, false, null, null);

            // If the information given are in an array, we convert it in json format
            $data = (is_array($data)) ? json_encode($data) : $data;

            // Create a new instance of message then push it into the selected queue
            $item = new PhpAmqpLib\Message\AMQPMessage($data, $params);

            // Publish to the queue
            $this->channel->basic_publish($item, '', $queue);

            // Output
            ($this->show_output) ? rabbitmq_client_output('Pushing "' . $item->body . '" to "' . $queue . '" queue -> OK', null, '+') : true;

            return [
                'sucesso' => true,
                'mensagem' => 'Pushing "' . $item->body . '" to "' . $queue . '" queue -> OK',
                'id_process' => $id_process
            ];

        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }

    }

    public function lock($message)
    {
        $this->channel->basic_nack($message->delivery_info['delivery_tag'], false, true);
    }


    public function unlock($message)
    {
        $this->channel->basic_ack($message->delivery_info['delivery_tag']);

    }


    public function purge($queue = '')
    {
        // Connect to rabbit
        $this->connect();

        // Purge queue if exist
        if (!empty($queue)) {
            $this->channel->queue_purge($queue);
        }
    }


    public function cleanup_connection()
    {
        try {
            // Close the channel
            if (!empty($this->channel)) {
                $this->channel->close();
                $this->channel = null;
            }

            // Close the connexion
            if (!empty($this->connexion)) {
                $this->connexion->close();
                $this->connexion = null;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    public function __destruct()
    {
        $this->cleanup_connection();
    }
}
