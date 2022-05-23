<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/

require_once __DIR__ . '/autoload_consumers.php';


class BinSaveDepositToken extends Consumer
{

    public $queue_name = 'deposit_save_token';

    public function __construct()
    {
        parent::__construct();
    }

    public function saveDepositToken(array $dados)
    {
        try {
            if(!empty($dados)){

                $objeto = (object) $dados;

                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $taxasMoedasRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
                $carteirasRn = new \Models\Modules\Cadastro\CarteiraRn();
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $valor = $objeto->value . "";
                $confirmacao = $objeto->confirmations . "";

                $dados = $carteirasRn->listar(" endereco = '{$objeto->wallet}' AND inutilizada = 0 ", "prioridade DESC", null, null, true);

                if (sizeof($dados) > 0) {

                    $carteira = $dados[0];

                    if($objeto->asset == "ETH"){
                        $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 3));
                        $moedaRn->carregar($moeda, true);
                    } else {
                        $moedas = $moedaRn->listar("contrato = '{$objeto->asset}'");
                        $moeda = $moedas[0];
                    }

                    $valida = $contaCorrenteBtcRn->lista("endereco_bitcoin = '{$carteira->endereco}' AND hash = '{$objeto->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                    if (sizeof($valida) > 0) {
                        throw new \Exception("Tentativa depósito duplicado.", 500);
                    } else {
                        if($moeda->ativo == 1 && $moeda->statusDeposito == 1){

                            $taxa = $taxasMoedasRn->getByMoeda($moeda->id);

                            $valor = number_format(str_replace(",", ".", $valor), 8, ".", "");

                            if ($confirmacao >= $taxa->minConfirmacoes && $valor >= $taxa->valorMinimoDeposito) {

                                $registrar = true;

                                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                                $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $carteira->idCliente));
                                $clienteRn->conexao->carregar($cliente);

                                //Verifica se a moeda é stablecoin e valida se o usuário está verificado
                                if($moeda->idCategoriaMoeda == 2 && $cliente->documentoVerificado != 1){
                                    $registrar = false;
                                }

                                if ($registrar && $moeda->ativo == 1 && $moeda->statusDeposito == 1) {

                                    //Creditar Conta Corrente BTC
                                    $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();

                                    $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                                    $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                                    $contaCorrenteBtc->descricao = "Deposito de " . $moeda->simbolo;
                                    $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                    $contaCorrenteBtc->enderecoBitcoin = $carteira->endereco;
                                    $contaCorrenteBtc->executada = 1;
                                    $contaCorrenteBtc->hash = $objeto->hash;
                                    $contaCorrenteBtc->idCliente = $carteira->idCliente;
                                    $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                                    $contaCorrenteBtc->transferencia = 0;
                                    $contaCorrenteBtc->idMoeda = $moeda->id;
                                    $contaCorrenteBtc->valor = $valor;
                                    $contaCorrenteBtc->valorTaxa = 0;
                                    $contaCorrenteBtc->autorizada = 1;
                                    $contaCorrenteBtc->origem = 0;
                                    $contaCorrenteBtc->enderecoEnvio = "";
                                    $contaCorrenteBtc->nomeMoeda = $moeda->nome;
                                    $contaCorrenteBtc->moeda = $moeda->nome;
                                    $contaCorrenteBtc->symbol = $moeda->simbolo;

                                    $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

                                    if (!empty($moeda->idMoedaConversao)) {
                                        if ($moeda->idMoedaConversao > 0) {
                                            \Utils\ConversaoMoeda::conversao($moeda->id, $moeda->idMoedaConversao, $carteira->idCliente, $contaCorrenteBtc->valor, $taxa->taxaConversao);
                                        }
                                    }

                                    //Notificar Cliente
//                                    $dados = ["moeda_nome" => $moeda->nome,
//                                        "volume" => $contaCorrenteBtc->valor,
//                                        "status" => "Autorizada",
//                                        "hash_endereco" => str_replace("{hash}", $contaCorrenteBtc->hash, $moeda->urlExplorer),
//                                        "hash" => $contaCorrenteBtc->hash,
//                                        "wallet" => $contaCorrenteBtc->enderecoBitcoin];
//
//                                    \LambdaAWS\LambdaNotificacao::notificar($cliente, true, 16, false, $dados);
                                } else {
                                    throw new \Exception("Depósito não registrado, verificar KYC do do cliente.", 500);
                                }
                            }
                        } else {
                            throw new \Exception("Depósito desativado.", 500);
                        }
                    }
                } else {
                    throw new \Exception("Carteria do cliente não encontrada.", 500);
                }

                return [
                    'sucesso' => true,
                    'message' => 'Deposito cadastrado com sucesso'
                ];
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
            $return = $this->saveDepositToken($params);

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

$consumidor = new BinSaveDepositToken();
return $consumidor->pull($consumidor->queue_name);
