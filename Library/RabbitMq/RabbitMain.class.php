<?php

namespace RabbitMq;



class Server
{


    protected $config;


    public $connection;


    public $channel;


    public $show_output;

    public function __construct()
    {

        $this->config = [
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
    }

    private function connect()
    {
        register_shutdown_function(array($this, 'cleanup_connection'));
        $this->connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($this->config['host'], $this->config['port'], $this->config['user'], $this->config['pass'], $this->config['vhost']);
        $this->channel = $this->connection->channel();

    }



    public function push($id_process ,$queue = null, $data = null, $permanent = false, $params = array())
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
            $item = new \PhpAmqpLib\Message\AMQPMessage($data, $params);

            // Publish to the queue
            $this->channel->basic_publish($item, '', $queue);

            // Output
            ($this->show_output) ? rabbitmq_client_output('Pushing "' . $item->body . '" to "' . $queue . '" queue -> OK', null, '+') : true;

            return [
                'sucesso' => true,
                'mensagem' => 'Incluido na fila com sucesso.',
                'id_process' => $id_process
            ];

        }
        catch (\Exception $e){
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
