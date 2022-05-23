<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinKyc extends Consumer
{

    public $queue_name = 'user_kyc_request';
    protected $confCaf;

    public function __construct()
    {
        parent::__construct();
        $this->confCaf = include 'config/configs.php';

    }


    public function exec(array $body)
    {
        try {
            $Caf = new \Caf\Caf($this->confCaf['caf']['token']);
            $requestLink = $Caf->createLinkOnboard($body);
            if (!$requestLink['sucesso']) {
                $this->log_(
                    $body['id_process'] ?? uniqid(),
                    LOG_ERROR_QUEUE,
                    $this->queue_name,
                    isset($body) ? $body : [],
                    sprintf('%s', $requestLink['mensagem'])
                );
                return true;
            }

            if(!isset($requestLink['data']['_id'])){
                $this->log_(
                    $body['id_process'] ?? uniqid(),
                    LOG_ERROR_QUEUE,
                    $this->queue_name,
                    isset($body) ? $body : [],
                    sprintf('%s', $requestLink['mensagem'])
                );
                return true;
            }
            $dataReturn = $requestLink['data'];
            $kyc = new \Models\Modules\Cadastro\Kyc();
            unset($kyc->id);
            unset($kyc->updated_at);
            $kyc->caf_id = $dataReturn['_id'];
            $kyc->report_id = $dataReturn['templateId'];
            $kyc->federal_document = $body['federal_document'];
            $kyc->url = $dataReturn['url'];
            $kyc->status = $dataReturn['status'];


            try {
                $kycRn = new \Models\Modules\Cadastro\KycRn();
                $kycRn->save($kyc);
            }catch (\Exception $e){
                $this->log_(
                    $body['id_process'] ?? uniqid(),
                    LOG_ERROR_QUEUE,
                    $this->queue_name,
                    isset($body) ? $body : [],
                    sprintf('Erro ao gravar registro de KYC %s', $e->getMessage())
                );
                return true;
            }

            //EMAIL DE NAO APROVADO
            $rabbitmq = new Consumer();
            $rabbitmq->push(
                uniqid(),
                'notificacoes',
                json_encode([
                    'nome' => $body['nome'],
                    'email' => $body['email'],
                    'template_name' => 'system.user.verificacaoonboard',
                    'params' => [
                        'cliente_url' => $dataReturn['url']
                    ]
                ]),
                true
            );

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

$consumidor = new BinKyc();
return $consumidor->pull($consumidor->queue_name);
