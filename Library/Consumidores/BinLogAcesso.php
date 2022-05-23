<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/
require_once __DIR__ . '/autoload_consumers.php';

class BinLogAcesso extends Consumer
{
    private $rabbit;

    public $queue_name = 'log_acesso';
    protected $configApi;


    public function __construct()
    {

        parent::__construct();

        $this->rabbit = new Consumer();
        $this->configApi =  include  'config/configs.php';


    }

    private function requestIpApi(array $params = [])
    {
        try {


            $url = sprintf('https://ipapi.co/%s/json/?key=%s', $params['ip'], $this->configApi['ipapi']['key']);
            $headers = [
                "Content-Type: application/json"
            ];


            $ch = \curl_init();
            \curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            \curl_setopt($ch, CURLOPT_URL, $url);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = \curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                return [
                    'sucesso' => false,
                    'mensagem' => sprintf('Problemas ao consultar %s', $err),
                ];
            } else {

                $return_ = json_decode($response, true);
                if (isset($return_['error'])) {

                    return [
                        'sucesso' => false,
                        'mensagem' => sprintf('Problemas ao consultar %s .', $return_['message'] ?? 'Sem informações adicionais'),
                    ];
                }

                return [
                    'sucesso' => true,
                    'message' => 'Processado com sucesso.',
                    'data' =>  $return_
                ];

            }

        } catch (\Exception $e) {
            print_r($e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    public function exec(array $body)
    {
        $id_proccess = isset($body['id_process']) ? $body['id_process'] : uniqid();

        if(!isset($body['params']['ip'])){
            $this->log_(
                $id_proccess,
                LOG_INFO_QUEUE,
                $this->queue_name,
                isset($body) ? $body : [],
                'Não foi informado Ip para consulta   '
            );
            return  true;
        }

        $rangeIpIgnore = ['::1', '127.0.0.1','localhost'];
        if(in_array($body['params']['ip'], $rangeIpIgnore)){
            $this->log_(
                $id_proccess,
                LOG_INFO_QUEUE,
                $this->queue_name,
                isset($body) ? $body : [],
                sprintf('Ip livre de consulta  | %s', $body['params']['ip'])
            );
            return  true;
        }

        $consultarIp = $this->requestIpApi([
            'ip' => $body['params']['ip']
        ]);

        $this->log_(
            $id_proccess,
            LOG_INFO_QUEUE,
            $this->queue_name,
            isset($body) ? $body : [],
            sprintf('Detalhe da consulta de IP  | %s', json_encode($consultarIp))
        );

        if(!$consultarIp['sucesso']){
            $this->log_(
                $id_proccess,
                LOG_ERROR_QUEUE,
                $this->queue_name,
                isset($body) ? $body : [],
                sprintf('Falha ao consultar IP  | %s', $consultarIp['mensagem'])
            );
        }
        $dataReturn = $consultarIp['data'];

        $localizacao = sprintf('%s - %s - %s',
            $dataReturn['city'],
            $dataReturn['region'],
            $dataReturn['country']
        );
        $rabbitmq = new Consumer();
        $rabbitmq->push(
            uniqid(),
            'notificacoes',
            json_encode([
                'nome' => $body['nome'],
                'email' => $body['email'],
                'params' => [
                    "cliente_data" => date('d/m/Y'),
                    "cliente_hora" => date('H:i:s'),
                    "cliente_nome" => $body["nome"],
                    "cliente_ip" => $body['params']['ip'],
                    "cliente_localizacao" => $localizacao,
                    "cliente_sistema" => $body['params']['sistema_operacional'],
                    "cliente_navegador" => $body['params']['navegador'],
                    "cliente_url" => $body['params']['link_revogar']

                ],
                'template_name' => 'system.security.logacesso'
            ]),
            true
        );



        $navegador = new \Models\Modules\Cadastro\Navegador();
        $navegador->ipUltimoAcesso = $body['params']['ip'];
        $navegador->idCliente = $body['params']['id_cliente'];
        $navegador->navegador = $body['params']["navegador"];
        $navegador->idSession = $body['params']['id_session'];
        $navegador->sistemaOperacional = $body['params']["sistema_operacional"];
        $navegador->localizacao = $localizacao;

        $navegadorRn = new \Models\Modules\Cadastro\NavegadorRn();
        $navegadorRn->salvar($navegador);

        return true;

    }


}
$consumidor = new BinLogAcesso();
return $consumidor->pull($consumidor->queue_name);
