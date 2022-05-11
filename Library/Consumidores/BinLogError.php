<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinLogError extends Consumer
{
    public $queue_name = 'log_error';
    protected $configApi;

    public function __construct()
    {
        parent::__construct();
        $this->configApi =  include  'config/configs.php';

    }

    public function exec(array $body)
    {
        try {
            $LogQueueRn = new \Models\Modules\Acesso\LogQueueRn();
            $LogQueueRn->registrar($body['id_process'] ?? '', $body['type'] ?? 'Indefinido', $body['queue_name'] ?? 'Indefinido', $body['mensagem'] ?? 'Não existe uma mensagem definida',  json_encode($body['params'] ?? [] ) ) ;

            $emails_alert = [];

            if($this->configApi['application']['ambiente'] == 'desenvolvimento'){
                array_push($emails_alert, [
                    'nome' => 'Willian',
                    'email' => 'willianchiquetto@gmail.com'
                ]);
            } else {
                array_push($emails_alert, [
                    'nome' => 'Danilo',
                    'email' => 'danilo.fransiozi@newcash.com.br'
                ]);

                array_push($emails_alert, [
                    'nome' => 'Renato',
                    'email' => 'renato.oliva@newcash.com.br'
                ]);

                array_push($emails_alert, [
                    'nome' => 'Willian',
                    'email' => 'willian.chiquetto@newcash.com.br'
                ]);
            }

            $rabbitmq = new Consumer();
            foreach ($emails_alert as $value) {
                $rabbitmq->push(
                    uniqid(),
                    'notificacoes',
                    json_encode([
                        'nome' => $value['nome'],
                        'email' => $value['email'],
                        'template_name' => 'system.log.process',
                        'params' => [
                            'body' => $body,
                        ]
                    ]),
                    true
                );
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }

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
                $process_id = isset($body['process_id']) ? $body['process_id'] : null;
                $this->log_(
                    $process_id,
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

$consumidor = new BinLogError();
return $consumidor->pull($consumidor->queue_name);
