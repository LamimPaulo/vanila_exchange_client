<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinSendgrid extends Consumer
{
    public $queue_name = 'sendgrid';

    protected $confiSendGrid;

    public function __construct()
    {
        $this->confiSendGrid =  include  'config/configs.php';
        parent::__construct();

    }

    public function requestSendGrid(array $params = [])
    {
        try {
       /*     $emailManager = \Models\Modules\Cadastro\EmailManagerRn::get(16);
            if(empty($emailManager->idTemplate))
            {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Template não informada'
                ];
            }*/


            $templates = [
                'teste' => 'd-e44c82ff97eb42dd9096234367994452',
                'system.security.logacesso' => 'd-75d19d43e3354748843467bbc9ac8597',
                'system.security.twofa' => 'd-27c70cc1cc644dc99d5fa2fb4e3abd20',
                'system.security.newpassword' => 'd-27c70cc1cc644dc99d5fa2fb4e3abd20',
                'system.security.recoverypassword' => 'd-bea7d3f70061419db07a7f32f08dffca',
                'system.user.validaccount' => 'd-19ec55d72a1a4b5bb28ea13a2e5c0377',
                'system.user.accountreproved' => ' d-c439a5948a3f49dbbf817f91395e7491',
                'system.log.process' => 'd-e44c82ff97eb42dd9096234367994452',
                'system.user.verificacaoonboard' => 'd-c99943b37e3c4a078abbe757bfbd5954',
                'system.user.verificacaoaprovada' => 'd-977ed28f7398430090222fc5fe7fda1a',
                'system.user.verificacaoreprovado' => 'd-323ed48ff00c4bec9b6665a0c7f28074',

            ];

            if(!isset($params['email'])){
                return [
                    'sucesso' => false,
                    'mensagem' => 'Não foi informado e-mail do destinatário'
                ];
            }

            if(!isset($templates[$params['template_name']])){

                return [
                    'sucesso' => false,
                    'mensagem' => sprintf('A template %s não foi configurada', $params['template_name'])
                ];
            }

            //DEFAULT PARAMS
            $params['params']['lang'] = 'en';
            $params['params']['social_youtube_url'] = true;
            $params['params']['social_instagram_url'] = true;
            $params['params']['social_facebook_url'] = true;
            $params['params']['social_icons'] = true;
            $params['params']['home_url'] = 'https://newcash.com.br';
            $params['params']['template_name'] = $templates[$params['template_name']];

            if(isset($params['params']['cliente_nome'])){
                $nome = explode(' ', $params['params']['cliente_nome'])[0] ?? '';
                unset($params['params']['cliente_nome']);
                $params['params']['cliente_nome'] = $nome;
            }


            $params['params']['json'] = json_encode($params);

            $sendData = [
                'from' => [
                    'name' => $this->confiSendGrid['sendgrid']['from']['name'] ,
                    'email' => $this->confiSendGrid['sendgrid']['from']['email']
                ],
                'personalizations' =>[[
                    'to' => [[
                        'name' => $params['nome'] ? isset(explode(' ', $params['nome'] )[0]) ? explode(' ', $params['nome'] )[0] :  $params['nome'] :  '',
                        'email' => $params['email']
                    ]],
                    'dynamic_template_data' => isset($params['params']) ? $params['params'] :  []
                ]],
                'template_id' => $templates[$params['template_name']]
            ];

            if (empty($params)) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Não existem parametros informados'
                ];
            }

            $url = sprintf('https://api.sendgrid.com/v3%s', '/mail/send');
            $headers = [
                "Content-Type: application/json",
                sprintf( "Authorization: Bearer %s", $this->confiSendGrid['sendgrid']['key'])
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($sendData),
                CURLOPT_HTTPHEADER => $headers,
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return [
                    'sucesso' => false,
                    'info' => isset($info) ? $info :  'Sem informacoes adicionais',
                    'mensagem' => $err,
                    'params' => $params
                ];
            } else {
                return [
                    'sucesso' => true,
                    'mensagem' => 'E-mail enviado com sucesso.',
                    'data' => $response
                ];
            }


        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => sprintf('Fatal error %s', $e->getMessage())
            ];
        }

    }

    public function exec(array $body)
    {
        $params = [
            'template_name' =>  isset($body['template_name']) ? $body['template_name'] : 'teste',
            'email' => $body['email'],
            'nome' => $body['nome'],
            'params' => isset($body['params']) ? $body['params'] :  [
                'texto' => sprintf('Texto de teste gerado em %s', date('d-m-Y H:o:s'))
            ]
        ];
        $process = $this->requestSendGrid($params);

        if($process['sucesso'] === false){

            $process_id = $body['id_process'] ?? uniqid();
            $this->log_(
                $process_id,
                LOG_ERROR_QUEUE,
                $this->queue_name,
                isset($body) ? $body : [],
                $process['mensagem']
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
                $id_proccess = isset($body['id_process']) ? $body['id_process'] : uniqid();
                $this->log_(
                    $id_proccess,
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
$consumidor = new BinSendgrid();
return $consumidor->pull($consumidor->queue_name);
