<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/

require_once __DIR__ . '/autoload_consumers.php';


class BinRequestWallet extends Consumer
{

    public $queue_name = 'wallet_new';

    public function addWallet($idMoeda)
    {
        try {
            $carteiraGeradaRn = new  \Models\Modules\Cadastro\CarteiraGeradaRn();
            $dados = $carteiraGeradaRn->contarCarteiras($idMoeda);

            if($dados["gerar_carteiras"]){
                $moeda = \Models\Modules\Cadastro\MoedaRn::get($idMoeda);

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "http://127.0.0.1:8500/wallets?moeda={$moeda->simbolo}&qtd={$dados['qtd']}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    throw new \Exception("Falha para solicitar carteira - Moeda: {$moeda->simbolo} - Qtd.: {$dados['qtd']}", 500);
                } else {
                    return [
                        'sucesso' => true,
                        'message' => "Solicitacao de carteiras - Moeda: {$moeda->simbolo} - Qtd.: {$dados['qtd']}"
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function exec(array $body)
    {
        if(!empty($body)) {
            $requestWallet = $this->addWallet($body["id_moeda"]);

            if($requestWallet["sucesso"]){
                $typeLog = LOG_INFO_QUEUE;
            } else {
                $typeLog = LOG_ERROR_QUEUE;
            }

            $this->log_(
                $body['id_process'] ?? uniqid(),
                $typeLog,
                $this->queue_name,
                isset($body) ? $body : [],
                $requestWallet["message"]
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

$consumidor = new BinRequestWallet();
return $consumidor->pull($consumidor->queue_name);
