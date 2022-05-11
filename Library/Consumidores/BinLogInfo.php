<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinLogInfo extends Consumer
{
    public $queue_name = 'log_info';

    public function __construct()
    {
        parent::__construct();

    }

    public function exec(array $body)
    {
        $LogQueueRn = new \Models\Modules\Acesso\LogQueueRn();
        $LogQueueRn->registrar($body['id_process'] ?? '', $body['type'] ?? 'Indefinido', $body['queue_name'] ?? 'Indefinido', $body['mensagem'] ?? 'Não existe uma mensagem definida',  json_encode($body['params'] ?? [] ) ) ;
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
            if (!$this->exec($body)) {
                $id_process = isset($body['id_process']) ? $body['id_process'] : null;
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
}

$consumidor = new BinLogInfo();
return $consumidor->pull($consumidor->queue_name);
