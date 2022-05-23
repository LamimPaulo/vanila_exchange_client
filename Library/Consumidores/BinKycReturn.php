<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinKycReturn extends Consumer
{

    public $queue_name = 'user_kyc_response';
    protected $confCaf;

    public function __construct()
    {
        parent::__construct();
        $this->confCaf = include 'config/configs.php';

    }


    private function ReprovarCliente(\Models\Modules\Cadastro\Cliente &$cliente, $status)
    {
        try {
            $ClienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $ClienteRn->kycUpdateStatus($cliente, $status);

            $rabbitmq = new Consumer();
            $rabbitmq->push(
                uniqid(),
                'notificacoes',
                json_encode([
                    'nome' => $cliente->nome,
                    'email' => $cliente->email,
                    'template_name' => 'system.user.verificacaoreprovado'

                ]),
                true
            );

        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    private function AprovarCliente(\Models\Modules\Cadastro\Cliente &$cliente, $status)
    {
        try {
            $ClienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $ClienteRn->kycUpdateStatus($cliente, $status);

            $rabbitmq = new Consumer();
            $rabbitmq->push(
                uniqid(),
                'notificacoes',
                json_encode([
                    'nome' => $cliente->nome,
                    'email' => $cliente->email,
                    'template_name' => 'system.user.verificacaoaprovada'

                ]),
                true
            );
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }

    public function exec(array $body)
    {
        try {
            $kycRn = new \Models\Modules\Cadastro\KycRn();
            $getKYC = $kycRn->carregar([
                'caf_id' => $body['onboardingId']
            ]);

            if (isset($getKYC->id)) {

                $Cliente = new \Models\Modules\Cadastro\Cliente();
                $ClienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $Cliente = $ClienteRn->getByCpf($getKYC->federal_document);

                /*   echo sprintf('Data -> %s' . PHP_EOL, json_encode($Cliente));*/

                if (!isset($Cliente->id)) {
                    echo sprintf('Cliente não existe' . PHP_EOL);
                    $process_id = $body['id_process'] ?? uniqid();
                    $this->log_(
                        $process_id,
                        LOG_ERROR_QUEUE,
                        $this->queue_name,
                        isset($body) ? $body : [],
                        sprintf('Processamento KYC  | %s', 'Não foi encontrado CPF/CNPJ no cadastro de clientes ')
                    );
                    return true;
                }

                if (intval($Cliente->documentoVerificado) === 2) {
                    echo sprintf('Cliente já reprovado' . PHP_EOL);
                    $process_id = $body['id_process'] ?? uniqid();
                    $this->log_(
                        $process_id,
                        LOG_ERROR_QUEUE,
                        $this->queue_name,
                        isset($body) ? $body : [],
                        sprintf('Processamento KYC  | %s', 'Status do cliente já se encontra como não autorizado')
                    );
                    return true;
                }

                if ($Cliente->documentoVerificado == 3) {
                    echo sprintf('Testando STATUS' . PHP_EOL);
                    switch ($body['status']) {
                        case 'APROVADO':
                            echo sprintf('APROVADO' . PHP_EOL);
                            $this->AprovarCliente($Cliente, "1");
                            break;
                        case 'REPROVADO':
                            echo sprintf('PROCESSANDO' . PHP_EOL);
                            $this->AprovarCliente($Cliente, "2");
                            break;
                        case 'PROCESSANDO':
                            echo sprintf('PROCESSANDO' . PHP_EOL);
                            break;
                        case 'PENDENTE':
                            echo sprintf('PENDENTE' . PHP_EOL);
                            break;
                        default:
                            echo sprintf('STATUS NAO DEFINIDO' . PHP_EOL);

                    }
                }
                return true;
            } else {
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

$consumidor = new BinKycReturn();
return $consumidor->pull($consumidor->queue_name);
