<?php

require_once __DIR__ . '/autoload_consumers.php';


class BinOrderBookExecute extends Consumer
{

    public $queue_name = 'order_book_execute';

    public function __construct()
    {
        parent::__construct();
    }

    public function executeOrder(array $order)
    {
        try {

            if(!empty($order)){
                $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
                $orderBook = new \Models\Modules\Cadastro\OrderBook();

                $order["dataCadastro"] = null;

                foreach ($order as $key => $value) {
                    $orderBook->$key = $value;
                }

                $orderBook->dataCadastro = new \Utils\Data(date("Y-m-d H:i:s"));
                unset($orderBook->paridade);

                $orderBookRn->conexao->salvar($orderBook);
                $orderBookRn->carregar($orderBook, true, true);

                if(!empty($orderBook)){
                    $orderBookRn->executarOrdemPassiva($orderBook);
                } else {
                    throw new \Exception("Ordem não encontrada - {$orderBook->id}");
                }

                return [
                    'sucesso' => true,
                    'message' => "Ordem executada - {$orderBook->id}"
                ];
            } else {
                throw new \Exception("Dados inválidos - Objeto vazio");
            }
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'message' => $e->getMessage() . " - " . $e->getFile()
            ];
        }
    }

    public function exec(array $params)
    {

        $order = $params["order"];

        if(!empty($order)) {

            $return = $this->executeOrder($order);

            if($return["sucesso"]){
                $typeLog = LOG_INFO_QUEUE;
            } else {
                $typeLog = LOG_ERROR_QUEUE;
            }

            $this->log_(
                $params['id_process'] ?? uniqid(),
                $typeLog,
                $this->queue_name,
                isset($params) ? $params : [],
                $return["message"]
            );
        }

        return true;
    }

    public function pull($queue = '')
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

$consumidor = new BinOrderBookExecute();
return $consumidor->pull($consumidor->queue_name);
