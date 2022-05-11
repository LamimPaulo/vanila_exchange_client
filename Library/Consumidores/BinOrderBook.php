<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/

require_once __DIR__ . '/autoload_consumers.php';


class BinOrderBook extends Consumer
{

    public $queue_name = 'order_book';

    public function __construct()
    {
        parent::__construct();
    }

    public function saveOrder(array $dados)
    {
        try {
            if(!empty($dados)){

                $tipoOrdens = Array(\Utils\Constantes::ORDEM_COMPRA, \Utils\Constantes::ORDEM_VENDA);

                //Validar tipo de ordem
                if(isset($dados["paridade"]) && $dados["paridade"] instanceof \Models\Modules\Cadastro\Paridade){
                    if(isset($dados["tipo"]) && in_array($dados["tipo"], $tipoOrdens, true)){
                        if(isset($dados["volume"]) && is_numeric($dados["volume"]) && $dados["volume"] > 0){
                            if(isset($dados["preco"]) && is_numeric($dados["preco"]) && $dados["preco"] > 0){
                                if(isset($dados["direta"]) && is_bool($dados["direta"])){
                                    if(isset($dados["cliente"]) && $dados["cliente"] instanceof \Models\Modules\Cadastro\Cliente){

                                        $precoReferencia = 0;
                                        if($dados["direta"] == true){
                                            if(isset($dados["precoReferencia"]) && is_numeric($dados["precoReferencia"]) && $dados["precoReferencia"] > 0){
                                                $precoReferencia = $dados["precoReferencia"];
                                            } else {
                                                return [
                                                    'sucesso' => false,
                                                    'message' => 'Preco referencia invalido'
                                                ];
                                            }
                                        }

                                        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
                                        if($dados["tipo"] == \Utils\Constantes::ORDEM_COMPRA){
                                            $orderBookRn->registrarOrdemCompra($dados["volume"], $dados["preco"], $dados["paridade"], $dados["direta"], $precoReferencia, $dados["cliente"]);

                                        } else if ($dados["tipo"] == \Utils\Constantes::ORDEM_VENDA){
                                            $orderBookRn->registrarOrdemVenda($dados["volume"], $dados["preco"], $dados["paridade"], $dados["direta"], $dados["cliente"]);
                                        }

                                        return [
                                            'sucesso' => true,
                                            'message' => 'Ordem registrada com sucesso'
                                        ];
                                    } else {
                                        return [
                                            'sucesso' => false,
                                            'message' => 'Cliente nao valido'
                                        ];
                                    }
                                } else {
                                    return [
                                        'sucesso' => false,
                                        'message' => "Ordem direta nao valida"
                                    ];
                                }
                            } else {
                                return [
                                    'sucesso' => false,
                                    'message' => 'Preco não valido'
                                ];
                            }
                        } else {
                            return [
                                'sucesso' => false,
                                'message' => 'Volume não valido'
                            ];
                        }
                    } else {
                        return [
                            'sucesso' => false,
                            'message' => 'Tipo de ordem não valido'
                        ];
                    }
                } else {
                    return [
                        'sucesso' => false,
                        'message' => 'Paridade invalida'
                    ];
                }
            }
            return [
                'sucesso' => false,
                'message' => 'Dados inválidos'
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
            $return = $this->saveOrder($params);

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

$consumidor = new BinOrderBook();
return $consumidor->pull($consumidor->queue_name);
