<?php

namespace Modules\testes\Controllers;

class TesteArvoreComissao {
    
    public function index($params) {
        try {
            
            //$this->atribuirPermissaoRotinas();
            //exit("deu certo");
            
            /*$file = "uploads/public/votacao/4104459afc448607dbba2efd8293ad6c3b8830ea.png";
            \GoogleDriver\GoogleDriverUpload::upload($file);*/
            
            /* 8451682006603284546 */
            //\GoogleDriver\GoogleDriverUpload::getFile("1lQ5T7Ag1KpHx9M8iyBCmLNCFpJEyoV5i");
            
            //exit("ok");
            //$this->sendPush();
            
            //$this->pagarBonusParaUmCliente();
            
            /*
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" =>15093064537956));
            $amount = 20;
            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 2));
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $saqueIcoRn->sacarCurrency($cliente, $amount, $moeda);
            */
            
            
            //$this->pagarForkBBK();
            //$this->atribuirPermissaoModulo();
            //$this->atribuirPermissaoRotinas();
            
            /*
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar("status = 1");
            
            $msg = "Ganhe NEWC Tokens compartilhando posts em suas redes sociais! Acesse https://painel.newctoken.io/marketing";
            $i = 0;
            foreach ($clientes as $cliente) {
                if (!empty($cliente->celular)) {
                    $cel = str_replace(Array(" ", "-", "(", ")"), "", $cliente->celular);


                    $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                    $api->EnviaSMS("{$cliente->ddi}{$cel}", $msg);
                    $i++;
                    echo "{$i} - Enviou para {$cliente->nome} <br>";
                }
            }
            
            
            //$cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => "15093064537956")); // pangare 10
            
            /*$cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => "15093064537967")); // pangare 22
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            $fila = $distribuicaoTokenRn->getArvoreComissao($cliente);

            print_r($fila);
            */
            /*$arvore = $clienteRn->getArvoreReferencias($cliente);
            print_r($arvore);*/
            
            /*
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();

            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar("status = 1 and data_cadastro >= '2018-11-30 17:00:00' AND nome not like '%PANGERE%' ");
         
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn($clienteRn->conexao->adapter, false);
            
            
            $i = 1;
            foreach ($clientes as $cliente) {
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 33, false);
                
                if ($cliente->emailConfirmado > 0) {
                    
                    if ($saldo < 50) {
                        
                        $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                        $contaCorrenteBtc->id = 0;
                        $contaCorrenteBtc->autorizada = 1;
                        $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtc->descricao = "Bonus de Cadastro ICO NEWC Token";
                        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                        $contaCorrenteBtc->enderecoBitcoin = "";
                        $contaCorrenteBtc->executada = 1;
                        $contaCorrenteBtc->origem = 5;
                        $contaCorrenteBtc->idCliente = $cliente->id;
                        $contaCorrenteBtc->idMoeda = 33;
                        $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteBtc->transferencia = 0;
                        $contaCorrenteBtc->valor = number_format(50, 8, ".", "");
                        $contaCorrenteBtc->valorTaxa = 0;
                        $contaCorrenteBtc->orderBook = 0;
                        $contaCorrenteBtcRn->salvar($contaCorrenteBtc, true);
                        
                        echo "{$i} - {$cliente->id} - Pagou para {$cliente->nome}      - {$cliente->origemCadastro}<br><br>";

                        $distribuicaoToken =  new \Models\Modules\ICO\DistribuicaoToken();
                        $distribuicaoToken->id = 0;
                        $distribuicaoToken->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $distribuicaoToken->idFase = 1;
                        $distribuicaoToken->idCliente = $cliente->id;
                        $distribuicaoToken->volumeToken = number_format(50, 8, ".", "");
                        $distribuicaoToken->valorTotal = number_format(50 * 0.00001550, 8, ".", "");
                        $distribuicaoToken->idMoeda = 2;
                        $distribuicaoToken->idIco = 1;
                        $distribuicaoToken->preco =  number_format(0.00001550, 8, ".", "");
                        $distribuicaoToken->tipo = 2;

                        $distribuicaoTokenRn->salvar($distribuicaoToken);
                        $i++;
                    }
                }
            }
            
            */
            
            /*
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar("status = 1");
            //$clientes = $clienteRn->conexao->listar("status = 1 AND foto_cliente_verificada > 0 AND foto_documento_verificada > 0 AND foto_residencia_verificada > 0");
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
            
            foreach ($clientes as $cliente) {
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 33, false);
                
                if ($saldo > 0) {
                    
                    
                    
                    echo "Pagou para {$cliente->nome} <br><br>";
                }
            }*/
                    
        } catch (\Exception $ex) {
            echo "Exceção: <br><br>";
            print_r($ex);
        }
    }
    
    
    private function sendPush() {
        $firebase = new \Firebase\Firebase();
        $push = new \Firebase\FirebasePush();
 
        // optional payload
        $payload = array();
        $payload['team'] = 'India';
        $payload['score'] = '5.6';
 
        // notification title
        $title = "Testando 2 Exchange";
         
        // notification message
        $message = "Estamos 2 testando";
         
        // push type - single user / topic
        $push_type = "topic";
         
        // whether to include to image or not
        $include_image = true;
 
 
        $push->setTitle($title);
        $push->setMessage($message);
        if ($include_image) {
            $push->setImage('https://api.androidhive.info/images/minion.jpg');
        } else {
            $push->setImage('');
        }
        $push->setIsBackground(false);
        $push->setPayload($payload);
 
 
        $json = '';
        $response = '';
 
        if ($push_type == 'topic') {
            $json = $push->getPush();
            $response = $firebase->sendToTopic('global', $json);
        } else if ($push_type == 'individual') {
            $json = $push->getPush();
            $regId = "dtm7YOydQb0:APA91bHs4tKiXs8S5JmEvWMtmi12_hD17J9bz2_AcHod5U66z1mjsMwkVt0bczHR2K2MyksuwWiAmxcPa0HzVGLrbPR-sxuybn8u40dq9Xy5_9C9QJBzapXczX-9by_KKisLXCPNZrCE";
            $response = $firebase->send($regId, $json);
        }
        
        print_r($response);
    }
    
    
    private function pagarForkBBK() {
        
        try {
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar();
            
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            foreach ($clientes  as $cliente) {
                
                $saldoBbk = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 25, false);
                
                if ($saldoBbk > 0) {
                    
                    $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                    
                    $contaCorrenteBtc->id = 0;
                    $contaCorrenteBtc->autorizada = 1;
                    $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteBtc->descricao = "SWAP Bitblocks";
                    $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                    $contaCorrenteBtc->enderecoBitcoin = "";
                    $contaCorrenteBtc->enderecoEnvio = "";
                    $contaCorrenteBtc->executada = 1;
                    $contaCorrenteBtc->hash = "";
                    $contaCorrenteBtc->idCliente = $cliente->id;
                    $contaCorrenteBtc->idMoeda = 25;
                    $contaCorrenteBtc->idReferenciado = null;
                    $contaCorrenteBtc->orderBook = 0;
                    $contaCorrenteBtc->origem = 10;
                    $contaCorrenteBtc->seed = "";
                    $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                    $contaCorrenteBtc->transferencia = 0;
                    $contaCorrenteBtc->valor = number_format($saldoBbk, 8, ".", "");
                    $contaCorrenteBtc->valorTaxa = 0;
                    
                    $contaCorrenteBtcRn->salvar($contaCorrenteBtc, null);
               
                    /*$msg = "Cointrade Informa: SWAP BBK completo. Você deve gerar uma nova carteira para realizar depósitos na plataforma.";
                    $cel1 = str_replace(Array(" ", "-", "(", ")"), "", $cliente->celular);
                    $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                    $api->EnviaSMS("55{$cel1}", $msg);
                    */
                    echo "{$cliente->nome} tinha {$saldoBbk} e recebeu {$contaCorrenteBtc->valor}. <br>";
                }
                
            }
            
            
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    
    public function atribuirPermissaoModulo() {
        
        try {
            
            $where = "tipo IN ('C', 'UC') AND id_modulo = 23";
            
            $permissaoRn = new \Models\Modules\Acesso\PermissaoModuloClienteRn();
            
            $moduloHasAcaoRn = new \Models\Modules\Acesso\ModuloHasAcaoRn();
            $modulosHasAcoes = $moduloHasAcaoRn->listar($where, null, null, null, true);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar();
            
            foreach ($clientes as $cliente) {
                foreach ($modulosHasAcoes as $mha) {
                    $permissaoRn->addToCliente($mha->id, $cliente->id);
                }
            }
            
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
        
    }
    
    
    public function atribuirPermissaoRotinas() {
        
        try {
            $where = "tipo IN ('C', 'UC') AND id_rotina = 60 ";
            
            $permissaoRn = new \Models\Modules\Acesso\PermissaoClienteRn();
            
            $rotinaHasAcaoRn = new \Models\Modules\Acesso\RotinaHasAcaoRn();
            $rotinasHasAcoes = $rotinaHasAcaoRn->listar($where, null, null, NULL, false);
        
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar();
            
            foreach ($clientes as $cliente) {
                foreach ($rotinasHasAcoes as $rha) {
                    $permissaoRn->addToCliente($rha->id, $cliente->id);
                }
            }
            
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
        
    }
    
    
    public function pagarBonusParaUmCliente() {
        
        //$cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064536678)); // vagner
        $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064540392));
        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn(null, true);
        $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
        $contaCorrenteBtc->id = 0;
        $contaCorrenteBtc->autorizada = 1;
        $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteBtc->descricao = "Bonus de Cadastro ICO NEWC Token";
        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
        $contaCorrenteBtc->enderecoBitcoin = "";
        $contaCorrenteBtc->executada = 1;
        $contaCorrenteBtc->origem = 5;
        $contaCorrenteBtc->idCliente = $cliente->id;
        $contaCorrenteBtc->idMoeda = 33;
        $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteBtc->transferencia = 0;
        $contaCorrenteBtc->valor = number_format(50, 8, ".", "");
        $contaCorrenteBtc->valorTaxa = 0;
        $contaCorrenteBtc->orderBook = 0;
        $contaCorrenteBtcRn->salvar($contaCorrenteBtc, true);
        echo "Pagou para {$cliente->nome} <br><br>";

        $distribuicaoToken =  new \Models\Modules\ICO\DistribuicaoToken();
        $distribuicaoToken->id = 0;
        $distribuicaoToken->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $distribuicaoToken->idFase = 1;
        $distribuicaoToken->idCliente = $cliente->id;
        $distribuicaoToken->volumeToken = number_format(50, 8, ".", "");
        $distribuicaoToken->valorTotal = number_format(50 * 0.00001550, 8, ".", "");
        $distribuicaoToken->idMoeda = 2;
        $distribuicaoToken->idIco = 1;
        $distribuicaoToken->preco =  number_format(0.00001550, 8, ".", "");
        $distribuicaoToken->tipo = 2;
        
        $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
        $distribuicaoTokenRn->salvar($distribuicaoToken);
        
    }
    
}