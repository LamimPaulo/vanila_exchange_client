<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';
class BinKycResend extends Consumer
{

    public $queue_name = 'user_kyc_resend';
    protected $confCaf;

    public function __construct()
    {
        parent::__construct();
        $this->confCaf = include 'config/configs.php';

    }

    public function exec(array $body)
    {
        try {

            echo sprintf('Data return -> %s' . PHP_EOL, json_encode($body));
            echo sprintf('OnBoardId -> %s' . PHP_EOL, json_encode($body['onboardingId']));

            $kycRn = new \Models\Modules\Cadastro\KycRn();
            $getKYC = $kycRn->carregar([
                'federal_document' => $body['federal_document']
            ]);

            if (isset($getKYC->id)) {
                echo sprintf('Dados do KYC %s' . PHP_EOL, json_encode($getKYC));

                $rabbitmq = new Consumer();
                $rabbitmq->push(
                    uniqid(),
                    'notificacoes',
                    json_encode([
                        'nome' => $body['nome'],
                        'email' => $body['email'],
                        'template_name' => 'system.user.verificacaoonboard',
                        'params' => [
                            'cliente_url' => $getKYC->url
                        ]
                    ]),
                    true
                );


                return true;
            } else {
                echo sprintf('Não existe informações de KYC a serem recuperadas %s' . PHP_EOL, json_encode($body));
                $process_id = $body['id_process'] ?? uniqid();
                $this->log_(
                    $process_id,
                    LOG_ERROR_QUEUE,
                    $this->queue_name,
                    isset($body) ? $body : [],
                    sprintf('Falha ao recuperar informações de KYC  | %s', 'Não existem dados para o onboardingId especifico ')
                );
            }

            return true;
        } catch (\Exception $e) {

            $process_id = $body['id_process'] ?? uniqid();
            $this->log_(
                $process_id,
                LOG_INFO_QUEUE,
                $this->queue_name,
                isset($body) ? $body : [],
                sprintf('Falha no processo de cadastro de cliente  | %s', $e->getMessage())
            );
            return true;
        }
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

            $process_id = $body['id_process'] ?? uniqid();
            $this->log_(
                $process_id,
                LOG_INFO_QUEUE,
                $queue,
                isset($body) ? $body : []
            );
            if (!$this->exec($body)) {

                $this->log_(
                    $process_id,
                    LOG_INFO_QUEUE,
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

$consumidor = new BinKycResend();
return $consumidor->pull($consumidor->queue_name);
