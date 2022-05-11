<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/

require_once __DIR__ . '/autoload_consumers.php';


class BinSaveWallet extends Consumer
{

    public $queue_name = 'wallet_save';

    public function __construct()
    {
        parent::__construct();
    }

    public function saveWallet(array $dados)
    {
        try {
            if(!empty($dados)){
                $carteiras = $dados["wallets"];
                $moedaSimbolo = $dados["moeda"];

                $carteiraGeradaRn = new  \Models\Modules\Cadastro\CarteiraGeradaRn();
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moeda = $moedaRn->getBySimbolo($moedaSimbolo);

                foreach ($carteiras as $address){
                    $carteira = new \Models\Modules\Cadastro\CarteiraGerada();

                    $carteira->idMoeda = $moeda->id;
                    $carteira->address = $address;

                    $carteiraGeradaRn->salvar($carteira);
                }
            }
            return [
                'sucesso' => true,
                'message' => 'Cadastrado com sucesso'
            ];

        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function exec(array $params)
    {
        if(!empty($params)) {
            $return = $this->saveWallet($params);

            if($return["sucesso"]){
                $typeLog = LOG_INFO_QUEUE;
            } else {
                $typeLog = LOG_INFO_QUEUE;
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

$consumidor = new BinSaveWallet();
return $consumidor->pull($consumidor->queue_name);
