<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinUserNew extends Consumer
{

    public $queue_name = 'user_new';

    protected $configIporto;

    public function __construct()
    {

        parent::__construct();
        $this->configIporto = include 'config/configs.php';
    }

    private function requestIport(string $method, array $params)
    {
        try {

            $validEmail = false;

            if ($validEmail === false) {
                return [
                    'sucesso' => true,
                    'message' => 'Serviço de validação de e-mail desligado'
                ];
            }

            if (empty($params)) {
                return [
                    'sucesso' => false,
                    'message' => 'Não existem parametros informados'
                ];
            }

            $url = sprintf('https://api.iporto.com.br/api/panel/pkg/email-validation/http/check?email=%s', $params['email']);
            $headers = [
                "Accept: application/json",
                sprintf("Authorization: Bearer %s", $this->configIporto['iporto']['key'])
            ];

            $ch = \curl_init();
            \curl_setopt($ch, CURLOPT_URL, $url);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if (strtoupper($method) === 'POST') {
                \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $response = curl_exec($ch);
            $info = curl_getinfo($ch);


            if ($info['http_code'] >= 300) {

                return [
                    'sucesso' => false,
                    'message' => sprintf('Falha cUrl | %s %s ', $info['http_code'], $ch ?? ''),
                ];
            }

            if (curl_error($ch)) {
                return [
                    'sucesso' => false,
                    'message' => sprintf('Falha cUrl | %s ', curl_error($ch) ?? '')
                ];
            }


            curl_close($ch);

            $response_ = json_decode($response, true);
            if ($response_['data']['is_valid'] == 2) {
                return [
                    'sucesso' => false,
                    'message' => 'E-mail invalido.',
                ];
            }
            return [
                'sucesso' => true,
                'message' => sprintf('E-mail %s autorizado para cadastro', $params['email']),
            ];


        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    public function saveCustomer(array $params = [])
    {
        try {

            $controllerCliente = new  \Modules\acesso\Controllers\Cadastro();
            $cadastrar = $controllerCliente->criarNovoCliente(json_encode($params));
            return $cadastrar;

        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'message' => sprintf('Falha %s', $e->getMessage())
            ];
        }
    }

    public function exec(array $body)
    {
        try {
            $params = $body;
            $process = $this->requestIport('GET', $params);
            if ($process['sucesso']) {
                $addCustomer = $this->saveCustomer($params);
                $this->log_(
                    $body['id_process'] ?? uniqid(),
                    LOG_INFO_QUEUE,
                    $this->queue_name,
                    isset($addCustomer) ? $addCustomer : []
                );
                if ($addCustomer['sucesso']) {
                    $dados = $addCustomer['dados'];
                    $rabbitmq = new Consumer();
                    $rabbitmq->push(
                        uniqid(),
                        'notificacoes',
                        json_encode([
                            'nome' => $dados['cliente_nome'],
                            'email' => $dados['cliente_email'],
                            'template_name' => 'system.user.validaccount',
                            'params' => [
                                'cliente_nome' => $dados['cliente_nome'],
                                'cliente_email' => $dados['cliente_email'],
                                'cliente_url' => $dados['cliente_url'],
                            ]
                        ]),
                        true
                    );
                }
            } else {
                //EMAIL DE NAO APROVADO
                $rabbitmq = new Consumer();
                $rabbitmq->push(
                    uniqid(),
                    'notificacoes',
                    json_encode([
                        'nome' => $params['nome'],
                        'email' => $params['email'],
                        'template_name' => 'system.user.accountreproved',
                        'params' => $params['params'] ?? []
                    ]),
                    true
                );

            }
            return $process['sucesso'] ?? false;
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

$consumidor = new BinUserNew();
return $consumidor->pull($consumidor->queue_name);
