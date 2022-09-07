<?php

namespace Modules\ws\Controllers;

class Tokens {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function balanceErc20($params) {
        
       if($this->iniciarTransacao()){           
            $this->callDepositErc20($params);
        }
    }
    
    public function callEthereum($params) {
        
       //if($this->iniciarTransacao(10)){     
            
           $this->depositoErc20Ethereum($params, \Utils\Constantes::REDE_ERC20);
           
           $this->depositoErc20Ethereum($params, \Utils\Constantes::REDE_BEP20);
        //}
    }
    
    public function saldoEmpresa() {
        
       if($this->iniciarTransacao()){          
            $this->saldoEmpresaErc20();
        }
    }
    
    private function iniciarTransacao($op = 0){
        
        $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
        $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
        $dataAux = $configuracao->dataUltimaSincErc20;
        $dataAux->somar(0, 0, 0, 0, 30);
        $inicia = false;
       
        if($configuracao->sincErc20 == 0 || $dataAtual->maiorIgual($dataAux)){
            if($op == 10){
                if($configuracao->depositoEthExecutando == 0){
                    $inicia = true;
                } else {
                   $inicia = false; 
                }
            } else {
                $inicia = true;
            }
        }
        
        return $inicia;
    }
    
    public function atualizarTaxaErc20($params) {
    
        //Recebe a taxa
        $taxa = $this->taxaTransferencia($params);

        if (!empty($taxa) && $taxa > 0) {

            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar(" coin_type = 'ERC20' ", "id ASC");

            $taxaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();

            foreach ($moedas as $moeda) {
                $taxaMoeda = $taxaRn->listar(" id_moeda = {$moeda->id} ", null, null, null, false);
                $taxaMoeda = $taxaMoeda[0];
                $taxaRn->conexao->update(Array("taxa_rede" => $taxa), Array("id" => $taxaMoeda->id));
            }
        }
    }

    public function webhook($params) {
    
        $json = file_get_contents('php://input');        
        $objeto = json_decode($json, false);

        if(!empty($objeto)){
            if(!empty($objeto->wallet) && !empty($objeto->hash) && !empty($objeto->asset) && !empty($objeto->value) && !empty($objeto->confirmations)){

                $this->depositoByWebhook($objeto);
                
                $tokenGatewayLogRn = new \Models\Modules\Cadastro\TokenGatewayLogRn();
                $tokenGatewayLog = new \Models\Modules\Cadastro\TokenGatewayLog();
                $tokenGatewayLog->endereco = $objeto->wallet;
                $tokenGatewayLog->response = $json;
                $tokenGatewayLogRn->salvar($tokenGatewayLog);
            }
        }
    }
    
    public function depositoByWebhook($objeto) {
        try {
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
                
                
                $valida = $contaCorrenteBtcRn->lista(" hash = '{$objeto->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                if (sizeof($valida) > 0) {
                    //echo "Creditado " . $objeto->hash . " - Val:" . number_format($objeto->value, 8, ".") . "<br>";
                } else {
                    //echo "Cart: " . $objeto->wallet . " - Cli: " . $carteira->idCliente . " - Val:" . number_format($objeto->value + 1, 8, ".") . " <br>";

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

                                if ($moeda->id == 3) {
                                    if ($contaCorrenteBtc->valor >= $taxa->valorMinimoDeposito) {
                                        $contaCorrenteBtcRn->salvar($contaCorrenteBtc);
                                    }
                                } else {
                                    $contaCorrenteBtcRn->salvar($contaCorrenteBtc);
                                }

                                if (!empty($moeda->idMoedaConversao)) {
                                    if ($moeda->idMoedaConversao > 0) {
                                        \Utils\ConversaoMoeda::conversao($moeda->id, $moeda->idMoedaConversao, $carteira->idCliente, $contaCorrenteBtc->valor, $taxa->taxaConversao);
                                    }
                                }

                                //Incremento de prioridade
                                $carteirasRn->conexao->update(Array("prioridade" => ($carteira->prioridade + 1)), Array("id" => $carteira->id));

                                //Notificar Cliente
                                $dados = ["moeda_nome" => $moeda->nome,
                                    "volume" => $contaCorrenteBtc->valor,
                                    "status" => "Autorizada",
                                    "hash_endereco" => str_replace("{hash}", $contaCorrenteBtc->hash, $moeda->urlExplorer),
                                    "hash" => $contaCorrenteBtc->hash,
                                    "wallet" => $contaCorrenteBtc->enderecoBitcoin];

                                \LambdaAWS\LambdaNotificacao::notificar($cliente, true, 16, false, $dados);
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }
    
    public function criarWalletEth($params) {
        if (AMBIENTE == "producao") {
            
            $qtdCarteiras = \Utils\Get::get($params, 0, 10);
            $carteiraGeradaRn = new \Models\Modules\Cadastro\CarteiraGeradaRn();
            
            for ($i = 0; $i < $qtdCarteiras; $i++) {

                $bitWalletApi = new \BitWalletAPI();
                $novoEndereco = $bitWalletApi->get_new_address();
                
                exit(print_r($novoEndereco));
                
                if ($novoEndereco["success"]) {
                    $novaCarteira = new \Models\Modules\Cadastro\CarteiraGerada();

                    $novaCarteira->idMoeda = 3;
                    $novaCarteira->address = $novoEndereco["address"];

                    $carteiraGeradaRn->salvar($novaCarteira);
                }
            }
        } else {
            echo "Somente em produção.";
        }
    }

    public function saldoEmpresaErc20() {
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        try {
            
            $configuracaoRn->conexao->update(Array("sinc_erc20" => 7), Array("id" => 1));

            $converter = new \Utils\Converter();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0 AND carteira_empresa IS NOT NULL AND coin_type = 'ERC20' ", "id ASC");

            $statusCoreRn = new \Models\Modules\Cadastro\StatusCoreRn();
            
            $etherscan = new \Utils\Ethereum();
            
            foreach ($moedas as $moeda) {
                
                $statusCore = $statusCoreRn->getByIdMoeda($moeda);
                
                if(empty($statusCore)){
                    $statusCore = new \Models\Modules\Cadastro\StatusCore();
                    $statusCore->idMoeda = $moeda->id;
                }
                
                if ($moeda->id == 3) {
                    
                    $eth = $etherscan->getBalanceEthereum($moeda->carteiraEmpresa, "balance");
                    
                    $dados = json_decode($eth);
                    
                    if ($dados->message == "OK" && $dados->status == 1) {
                        $valor = $converter->fromWei($dados->result, "ether");

                        $statusCore->balance = number_format($valor, 8, ".", "");
                        $statusCore->dataUltimaAtualizacao = date("Y-m-d H:i:s");
                        $statusCore->dataUltimaAtualizacaoCore = date("Y-m-d H:i:s");
                    }
                } else {
                    $eth = $etherscan->getBalanceErc20($moeda->contrato, $moeda->carteiraEmpresa, "tokenbalance");

                     if ($dados->message == "OK" && $dados->status == 1) {
                        $dados = json_decode($eth);
                        $valor = substr_replace($dados->result, ".", $moeda->infoDecimal * -1, 0);

                        $statusCore->balance = number_format($valor, 8, ".", "");
                        $statusCore->dataUltimaAtualizacao = date("Y-m-d H:i:s");
                        $statusCore->dataUltimaAtualizacaoCore = date("Y-m-d H:i:s");
                    }
                }
                
                if(!empty($statusCore->balance)){
                  $statusCoreRn->salvar($statusCore); 
                }
                sleep(2);
            }

            $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
        } catch (Exception $ex) {
            $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
        }
    }
    
    public function ethereumBalance($params) {
        
        $carteiraSpecial = \Utils\Get::get($params, 0, null);
        
        $sqlSpecial = "";
        if(!empty($carteiraSpecial)){
            $sqlSpecial = " AND endereco = '{$carteiraSpecial}' ";
        }
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moedas = $moedaRn->listar("id = 3 AND ativo = 1 AND sinc_deposito = 1");
        $moeda = null;

        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        
        if (sizeof($moedas) > 0) {
            foreach ($moedas as $dados) {
                $moeda = $dados;
            }
            if ($moeda->sincDeposito == 1) {

                try {
                    $configuracaoRn->conexao->update(Array("sinc_erc20" => 3), Array("id" => 1));                    
                    $taxasMoedasRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
                    $taxa = $taxasMoedasRn->getByMoeda(3); // Id Ethereum
                    $ethereum = new \Utils\Ethereum();
                    $carteirasRn = new \Models\Modules\Cadastro\CarteiraRn();
                    $dados = $carteirasRn->listar(" id_moeda = 3 AND inutilizada = 0 {$sqlSpecial}", "prioridade DESC", null, null, true);
                    $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                    $arraySize = 0;
                    $arrayCarteira = Array();
                    $arrayChamadas = Array();
                    $converter = new \Utils\Converter();
                    
                    foreach ($dados as $carteira) {

                        $arrayChamadas[] = $ethereum->getBalanceEthereum($carteira->endereco);
                        
                        if(!empty($carteiraSpecial)){
                            usleep(200000);
                            $arrayChamadas[] = $ethereum->getBalanceEthereum($carteira->endereco, "txlistinternal");
                        }
                        
                        foreach ($arrayChamadas as $json) {

                            $object = json_decode($json);

                            if ($object->status == 1 && $object->message == "OK") {
                                $arraySize = sizeof($object->result);

                                if ($arraySize > 0) {

                                    $arrayCarteira = $object->result;

                                    for ($i = 0; $i < $arraySize; $i++) {
                                        
                                        if (strtoupper($arrayCarteira[$i]->to) == strtoupper($carteira->endereco)) {
                                           
                                            $valida = $contaCorrenteBtcRn->lista(" hash = '{$arrayCarteira[$i]->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                                            if (sizeof($valida) > 0) {
                                                if(!empty($carteiraSpecial)){
                                                    echo "Já Creditado - " . $arrayCarteira[$i]->hash . " - Vol: " . $converter->fromWei($arrayCarteira[$i]->value, "ether") . "<br>";
                                                }
                                            } else {
                                                if ($arrayCarteira[$i]->confirmations >= $taxa->minConfirmacoes || $arrayCarteira[$i]->type == "call") {

                                                    if (!empty($carteiraSpecial)) {
                                                        echo "Novo credito - Cart: " . $arrayCarteira[$i]->to . " - Cli: " . $carteira->idCliente . " - Val:" . $converter->fromWei($arrayCarteira[$i]->value, "ether") . "<br>";
                                                    }
                                                    
                                                    //Creditar Conta Corrente BTC
                                                    $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();

                                                    $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s", $arrayCarteira[$i]->timeStamp));
                                                    $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                                                    $contaCorrenteBtc->descricao = "Deposito de " . $carteira->moeda->simbolo;
                                                    $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                                    $contaCorrenteBtc->enderecoBitcoin = $carteira->endereco;
                                                    $contaCorrenteBtc->executada = 1;
                                                    $contaCorrenteBtc->hash = $arrayCarteira[$i]->hash;
                                                    $contaCorrenteBtc->idCliente = $carteira->idCliente;
                                                    $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                                                    $contaCorrenteBtc->transferencia = 0;
                                                    $contaCorrenteBtc->idMoeda = $carteira->moeda->id;
                                                    $contaCorrenteBtc->valor = $converter->fromWei($arrayCarteira[$i]->value, "ether");
                                                    $contaCorrenteBtc->valorTaxa = 0;
                                                    $contaCorrenteBtc->autorizada = 1;
                                                    $contaCorrenteBtc->origem = 0;
                                                    $contaCorrenteBtc->enderecoEnvio = $arrayCarteira[$i]->from;
                                                    $contaCorrenteBtc->nomeMoeda = $carteira->moeda->nome;
                                                    $contaCorrenteBtc->moeda = $carteira->moeda->nome;
                                                    $contaCorrenteBtc->symbol = $carteira->moeda->simbolo;



                                                    $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

                                                    //Incremento de prioridade
                                                    $carteirasRn->conexao->update(Array("prioridade" => ($carteira->prioridade + 1)), Array("id" => $carteira->id));


                                                    if ($moeda->id == $carteira->moeda->id) {
                                                        if ($moeda->lastBlock < $arrayCarteira[$i]->blockNumber) {
                                                            $moedaRn->conexao->update(Array("last_block" => $arrayCarteira[$i]->blockNumber), Array("id" => $carteira->moeda->id));
                                                        }
                                                    }
                                                    
                                                    $this->moverSaldo(null, $carteira->endereco);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        $arrayChamadas = null;
                        usleep(300000); //Permitido 5 requisições por segundo         
                    }
                    $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
                } catch (Exception $ex) {
                    $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
                }
            }
            
            $configuracaoRn->conexao->update(Array("data_ultima_sinc_erc20" => date("Y-m-d H:i:s")), Array("id" => 1));
        }
        
    }

    //Função desativada do CRON
    public function callDepositErc20($params) {
        
        $carteiraEspecial = \Utils\Post::get($params, "wallet", null);

        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moedas = $moedaRn->listar("contrato IS NOT NULL AND id_moeda_principal = 3 AND sinc_deposito = 1 AND status_deposito = 1 ");


        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();

        try {
            $configuracaoRn->conexao->update(Array("sinc_erc20" => 5), Array("id" => 1));

            $taxasMoedasRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $ethereum = new \Utils\Ethereum();
            $carteirasRn = new \Models\Modules\Cadastro\CarteiraRn();
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $arraySize = 0;
            $arrayCarteira = Array();
            
            if(!empty($carteiraEspecial)){
                $dados = $carteirasRn->listar(" endereco = {$carteiraEspecial} ", "prioridade DESC", null, null, true);
            } else {
                $dados = $carteirasRn->listar(" id_moeda IN (SELECT m.id FROM moedas m WHERE contrato IS NOT NULL AND id_moeda_principal = 3 AND sinc_deposito = 1 AND status_deposito = 1) ", "prioridade DESC", null, null, true);

            }

            if (sizeof($dados) > 0) {
                foreach ($dados as $carteira) {

                    $json = $ethereum->getBalanceErc20($carteira->moeda->contrato, $carteira->endereco);

                    $object = json_decode($json);

                    if ($object->status == 1 && $object->message == "OK") {
                        $arraySize = sizeof($object->result);

                        if ($arraySize > 0) {

                            $arrayCarteira = $object->result;

                            for ($i = 0; $i < $arraySize; $i++) {

                                if (($arrayCarteira[$i]->to == $carteira->endereco) && ($arrayCarteira[$i]->contractAddress == $carteira->moeda->contrato)) {

                                    $valida = $contaCorrenteBtcRn->lista(" hash = '{$arrayCarteira[$i]->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                                    if (sizeof($valida) > 0) {
                                        echo "Creditado " . $arrayCarteira[$i]->hash . " - Val:" . substr_replace($arrayCarteira[$i]->value, ".", $arrayCarteira[$i]->tokenDecimal * -1, 0) . "<br>";
                                    } else {
                                        echo "Cart: " . $arrayCarteira[$i]->to . " - Cli: " . $carteira->idCliente . " - Val:" . substr_replace($arrayCarteira[$i]->value, ".", $arrayCarteira[$i]->tokenDecimal * -1, 0) . "<br>";

                                        $taxa = $taxasMoedasRn->getByMoeda($carteira->moeda->id);

                                        if ($arrayCarteira[$i]->confirmations >= $taxa->minConfirmacoes) {
                                            //Creditar Conta Corrente BTC

                                            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                                            
                                            $valor = substr_replace($arrayCarteira[$i]->value, ".", $arrayCarteira[$i]->tokenDecimal * -1, 0);

                                            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s", $arrayCarteira[$i]->timeStamp));
                                            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                                            $contaCorrenteBtc->descricao = "Deposito de " . $carteira->moeda->simbolo;
                                            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                            $contaCorrenteBtc->enderecoBitcoin = $carteira->endereco;
                                            $contaCorrenteBtc->executada = 1;
                                            $contaCorrenteBtc->hash = $arrayCarteira[$i]->hash;
                                            $contaCorrenteBtc->idCliente = $carteira->idCliente;
                                            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                                            $contaCorrenteBtc->transferencia = 0;
                                            $contaCorrenteBtc->idMoeda = $carteira->moeda->id;
                                            $contaCorrenteBtc->valor = $valor;
                                            $contaCorrenteBtc->valorTaxa = 0;
                                            $contaCorrenteBtc->autorizada = 1;
                                            $contaCorrenteBtc->origem = 0;
                                            $contaCorrenteBtc->enderecoEnvio = $arrayCarteira[$i]->from;
                                            $contaCorrenteBtc->nomeMoeda = $carteira->moeda->nome;
                                            $contaCorrenteBtc->moeda = $carteira->moeda->nome;
                                            $contaCorrenteBtc->symbol = $carteira->moeda->simbolo;

                                            $contaCorrenteBtcRn->salvar($contaCorrenteBtc);
                                            
                                            if (!empty($carteira->moeda->idMoedaConversao)) {
                                                if ($carteira->moeda->idMoedaConversao) {
                                                    \Utils\ConversaoMoeda::conversao($carteira->moeda->id, $carteira->moeda->idMoedaConversao, $carteira->idCliente, $valor, $taxa->taxaConversao);
                                                }
                                            }

                                            //Incremento de prioridade
                                            $carteirasRn->conexao->update(Array("prioridade" => ($carteira->prioridade + 1)), Array("id" => $carteira->id));

                                            foreach ($moedas as $moeda) {
                                                if ($moeda->id == $carteira->moeda->id) {
                                                    if ($moeda->lastBlock < $arrayCarteira[$i]->blockNumber) {
                                                        $moedaRn->conexao->update(Array("last_block" => $arrayCarteira[$i]->blockNumber), Array("id" => $carteira->moeda->id));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    usleep(400000); //Permitido 5 requisições por segundo         
                }
                
                $configuracaoRn->conexao->update(Array("data_ultima_sinc_erc20" => date("Y-m-d H:i:s")), Array("id" => 1));
            }

            $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
        } catch (Exception $ex) {
            $configuracaoRn->conexao->update(Array("sinc_erc20" => 0), Array("id" => 1));
        }
    }
    
    //Função ativa no CRON
    public function depositoErc20Ethereum($params, $rede = null){
       
        $carteiraSpecial = \Utils\Get::get($params, 0, null);
        
        $sqlSpecial = "";
        if(!empty($carteiraSpecial)){
            $sqlSpecial = " AND endereco = '{$carteiraSpecial}' ";
        }
        
        if($rede == null || $rede == \Utils\Constantes::REDE_ERC20){
            $rede = \Utils\Constantes::REDE_ERC20;
            $ethereum = new \Utils\Ethereum(\Utils\Constantes::REDE_ERC20);
        } else {
            $rede = \Utils\Constantes::REDE_BEP20;
            $ethereum = new \Utils\Ethereum(\Utils\Constantes::REDE_BEP20);
        }
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moedas = $moedaRn->listar("coin_type = 'ERC20' AND sinc_deposito = 1 AND status_deposito = 1");
       
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
       
        if (sizeof($moedas) > 0) {
            try {
                $configuracaoRn->conexao->update(Array("sinc_erc20" => 10, "deposito_eth_executando" => 1), Array("id" => 1));
                $taxasMoedasRn = new \Models\Modules\Cadastro\TaxaMoedaRn();               
                
                $carteirasRn = new \Models\Modules\Cadastro\CarteiraRn();
                $dados = $carteirasRn->listar(" id_moeda = 3 AND inutilizada = 0 {$sqlSpecial}", "prioridade DESC", null, null, true);
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $arraySize = 0;
                $arrayCarteira = Array();
                $arrayChamadas = Array();
                $converter = new \Utils\Converter();
              
                foreach ($dados as $carteira) {

                    foreach ($moedas as $moeda) {

                        if (($moeda->id == 3 && $rede == \Utils\Constantes::REDE_ERC20) || ($moeda->id == 42 && $rede == \Utils\Constantes::REDE_BEP20)) {

                            $taxa = $taxasMoedasRn->getByMoeda($moeda->id); // Id Ethereum

                            $arrayChamadas[] = $ethereum->getBalanceEthereum($carteira->endereco);

                            foreach ($arrayChamadas as $json) {

                                $object = json_decode($json);

                                if ($object->status == 1 && $object->message == "OK") {

                                    $arraySize = sizeof($object->result);

                                    if ($arraySize > 0) {

                                        $arrayCarteira = $object->result;

                                        for ($i = 0; $i < $arraySize; $i++) {

                                            if (strtoupper($arrayCarteira[$i]->to) == strtoupper($carteira->endereco)) {

                                                $valida = $contaCorrenteBtcRn->lista(" hash = '{$arrayCarteira[$i]->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                                                if (sizeof($valida) > 0) {
                                                    //if (!empty($carteiraSpecial)) {
                                                    echo "Já Creditado - " . $arrayCarteira[$i]->hash . " - Vol: " . $converter->fromWei($arrayCarteira[$i]->value, "ether") . " <br>";
                                                    //}
                                                } else {
                                                    if ($arrayCarteira[$i]->confirmations >= $taxa->minConfirmacoes || $arrayCarteira[$i]->type == "call") {

                                                        $valor = $converter->fromWei($arrayCarteira[$i]->value, "ether");

                                                        if ($valor >= $taxa->valorMinimoDeposito) {
                                                            
                                                            //Creditar Conta Corrente BTC
                                                            echo "Novo credito - Cart: " . $arrayCarteira[$i]->to . " - Cli: " . $carteira->idCliente . " - Val:" . $valor . " <br>";


                                                            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();

                                                            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s", $arrayCarteira[$i]->timeStamp));
                                                            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                                                            $contaCorrenteBtc->descricao = "Deposito de " . $moeda->simbolo;
                                                            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                                            $contaCorrenteBtc->enderecoBitcoin = $carteira->endereco;
                                                            $contaCorrenteBtc->executada = 1;
                                                            $contaCorrenteBtc->hash = $arrayCarteira[$i]->hash;
                                                            $contaCorrenteBtc->idCliente = $carteira->idCliente;
                                                            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                                                            $contaCorrenteBtc->transferencia = 0;
                                                            $contaCorrenteBtc->idMoeda = $moeda->id;
                                                            $contaCorrenteBtc->valor = $valor;
                                                            $contaCorrenteBtc->valorTaxa = 0;
                                                            $contaCorrenteBtc->autorizada = 1;
                                                            $contaCorrenteBtc->origem = 0;
                                                            $contaCorrenteBtc->enderecoEnvio = $arrayCarteira[$i]->from;
                                                            $contaCorrenteBtc->nomeMoeda = $moeda->nome;
                                                            $contaCorrenteBtc->moeda = $moeda->nome;
                                                            $contaCorrenteBtc->symbol = $moeda->simbolo;
                                                            
                                                            $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

                                                            if (!empty($moeda->idMoedaConversao)) {
                                                                if ($moeda->idMoedaConversao > 0) {
                                                                    \Utils\ConversaoMoeda::conversao($moeda->id, $moeda->idMoedaConversao, $carteira->idCliente, $valor, $taxa->taxaConversao);
                                                                }
                                                            }
                                                            //Incremento de prioridade
                                                            $carteirasRn->conexao->update(Array("prioridade" => ($carteira->prioridade + 1)), Array("id" => $carteira->id));


                                                            if ($moeda->lastBlock < $arrayCarteira[$i]->blockNumber) {
                                                                $moedaRn->conexao->update(Array("last_block" => $arrayCarteira[$i]->blockNumber), Array("id" => $moeda->id));
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $arrayChamadas = null;
                        } else {
                            
                            $json = $ethereum->getBalanceErc20($moeda->contrato, $carteira->endereco);

                            $object = json_decode($json);

                            if ($object->status == 1 && $object->message == "OK") {
                                $arraySize = sizeof($object->result);

                                if ($arraySize > 0) {

                                    $arrayCarteira = $object->result;

                                    for ($i = 0; $i < $arraySize; $i++) {
                                        
                                        if($moeda->id == 43){
                                            $valor = $converter->fromWei($arrayCarteira[$i]->value, "ether");
                                        } else {
                                            $valor = substr_replace($arrayCarteira[$i]->value, ".", $arrayCarteira[$i]->tokenDecimal * -1, 0);
                                        }

                                        if ((strtoupper($arrayCarteira[$i]->to) == strtoupper($carteira->endereco)) && (strtoupper($arrayCarteira[$i]->contractAddress) == strtoupper($moeda->contrato))) {

                                            $valida = $contaCorrenteBtcRn->lista(" hash = '{$arrayCarteira[$i]->hash}' AND id_cliente = '{$carteira->idCliente}' ", null, null, null, false, false);

                                            if (sizeof($valida) > 0) {
                                                echo "Já Creditado " . $arrayCarteira[$i]->hash . " - Val: " . $valor . "<br>";
                                            } else {

                                                $taxa = $taxasMoedasRn->getByMoeda($moeda->id);

                                                if ($arrayCarteira[$i]->confirmations >= $taxa->minConfirmacoes) {
                                                    //Creditar Conta Corrente BTC
                                                    
                                                    if ($valor >= $taxa->valorMinimoDeposito) {
                                                        
                                                        $registrar = true;

                                                        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                                                        $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $carteira->idCliente));
                                                        $clienteRn->conexao->carregar($cliente);
                                                        
                                                        //Verifica se a moeda é stablecoin e valida se o usuário está verificado
                                                        if($moeda->idCategoriaMoeda == 2 && $cliente->documentoVerificado != 1){
                                                            $registrar = false;
                                                        }                                                        
                                                        
                                                        if ($registrar) {
                                                            
                                                            echo "Novo credito: " . $arrayCarteira[$i]->to . " - Cli: " . $carteira->idCliente . " - Val: " . $valor . "<br>";
                                                            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();

                                                            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s", $arrayCarteira[$i]->timeStamp));
                                                            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                                                            $contaCorrenteBtc->descricao = "Deposito de " . $moeda->simbolo;
                                                            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                                            $contaCorrenteBtc->enderecoBitcoin = $carteira->endereco;
                                                            $contaCorrenteBtc->executada = 1;
                                                            $contaCorrenteBtc->hash = $arrayCarteira[$i]->hash;
                                                            $contaCorrenteBtc->idCliente = $carteira->idCliente;
                                                            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                                                            $contaCorrenteBtc->transferencia = 0;
                                                            $contaCorrenteBtc->idMoeda = $moeda->id;
                                                            $contaCorrenteBtc->valor = $valor;
                                                            $contaCorrenteBtc->valorTaxa = 0;
                                                            $contaCorrenteBtc->autorizada = 1;
                                                            $contaCorrenteBtc->origem = 0;
                                                            $contaCorrenteBtc->enderecoEnvio = $arrayCarteira[$i]->from;
                                                            $contaCorrenteBtc->nomeMoeda = $moeda->nome;
                                                            $contaCorrenteBtc->moeda = $moeda->nome;
                                                            $contaCorrenteBtc->symbol = $moeda->simbolo;

                                                            $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

                                                            if (!empty($moeda->idMoedaConversao)) {
                                                                if ($moeda->idMoedaConversao > 0) {
                                                                    \Utils\ConversaoMoeda::conversao($moeda->id, $moeda->idMoedaConversao, $carteira->idCliente, $valor, $taxa->taxaConversao);
                                                                }
                                                            }

                                                            $carteirasRn->conexao->update(Array("prioridade" => ($carteira->prioridade + 1)), Array("id" => $carteira->id));

                                                            if ($moeda->lastBlock < $arrayCarteira[$i]->blockNumber) {
                                                                $moedaRn->conexao->update(Array("last_block" => $arrayCarteira[$i]->blockNumber), Array("id" => $moeda->id));
                                                            }

                                                            //Notificar Cliente
                                                            $dados = ["moeda_nome" => $moeda->nome,
                                                                "volume" => $contaCorrenteBtc->valor,
                                                                "status" => "Autorizada",
                                                                "hash_endereco" => str_replace("{hash}", $contaCorrenteBtc->hash, $moeda->urlExplorer),
                                                                "hash" => $contaCorrenteBtc->hash,
                                                                "wallet" => $contaCorrenteBtc->enderecoBitcoin];

                                                            \LambdaAWS\LambdaNotificacao::notificar($cliente, true, 16, false, $dados);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        usleep(400500); //Permitido 5 requisições por segundo
                    }
                }
                $configuracaoRn->conexao->update(Array("sinc_erc20" => 0, "deposito_eth_executando" => 0), Array("id" => 1));
                } catch (Exception $ex) {
                    $configuracaoRn->conexao->update(Array("sinc_erc20" => 0, "deposito_eth_executando" => 0), Array("id" => 1));
                    echo $ex->getMessage();
                }
            
            
            $configuracaoRn->conexao->update(Array("data_ultima_sinc_erc20" => date("Y-m-d H:i:s")), Array("id" => 1));
        }
    }    
    
    public function moverSaldo($params = null, $carteiraMove = null) {
        
        try {
            
            $carteira = null;
            
            if(empty($carteiraMove)){
               $carteira = \Utils\Get::get($params, "wallet", null);  
            }            
            $moeda = \Utils\Get::get($params, "moeda", null);

            $bitWalletApi = new \BitWalletAPI();
            
            $valoresMinimos = Array();
            $tokens = Array();
            $tokensSaldos = Array();

            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();

            $moedas = $moedaRn->listar(" move_balance = 1 AND contrato IS NOT NULL AND id_moeda_principal = 3 ");
           
            //$tokensSaldos["3"] = "ETH";
            
            foreach ($moedas as $moeda) {
                $taxa = $taxaMoedaRn->getByMoeda($moeda->id);
                $valoresMinimos[$moeda->simbolo] = $taxa->minMoverSaldo;
                
                if($moeda->id == 3){
                   // $tokensSaldos["ETH"] = $moeda->simbolo;
                   // $tokens[$moeda->simbolo] = "ETH";
                } else {
                    $tokensSaldos[$moeda->contrato] = $moeda->simbolo;
                    $tokens[$moeda->simbolo] = $moeda->contrato;
                }
            }

            //exit(print_r($moedas));
           
            if(empty($carteira)){
                
                //$carteira = Array();                
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                
                $result = $contaCorrenteBtcRn->lista(" id_moeda IN(SELECT id FROM moedas WHERE move_balance = 1 AND contrato IS NOT NULL AND id_moeda_principal = 3 OR id = 3) AND direcao = 'E' AND tipo = 'E' AND endereco_bitcoin IS NOT NULL "
                        . "GROUP BY endereco_bitcoin", "id DESC", null, null, false, false);

                //exit(print_r($result));
                    
                foreach ($result as $conta) {
                    
                    $retorno = $bitWalletApi->moverSaldoCointrade($valoresMinimos, $conta->enderecoBitcoin, $tokens, $tokensSaldos);
                    
                    if($retorno){
                        
                        $contaCorrenteBtcRn->conexao->update(Array("saldo_movido" => 1), Array("endereco_bitcoin" => $conta->enderecoBitcoin));
                        
                        echo "Wallet: " . $conta->carteiraBitcoin . " - Saldo movido";
                    } else {
                        echo "Wallet: " . $conta->carteiraBitcoin . " - Não movido";
                    }
                }
                
            } else if(!empty($carteiraMove)){                
                $bitWalletApi->moverSaldoCointrade($valoresMinimos, $carteiraMove, $tokens, $tokensSaldos);
                
            } else {
                
                $bitWalletApi->moverSaldoCointrade($valoresMinimos, $carteira, $tokens, $tokensSaldos);
            }
            
        } catch (Exception $ex) {
            throw new \Exception($ex);
        }
    }

    public function testeJava($params) {

        $json = file_get_contents('php://input');
        //$objeto = json_decode($json, false);

        $tokenGatewayLogRn = new \Models\Modules\Cadastro\TokenGatewayLogRn();
        $tokenGatewayLog = new \Models\Modules\Cadastro\TokenGatewayLog();
        $tokenGatewayLog->endereco = "testejava";
        $tokenGatewayLog->response = $json;
        $tokenGatewayLogRn->salvar($tokenGatewayLog);

    }
    
    
    /**
    * Metodo para verificar a taxa de transferencia ERC20
    *
    *Você pode passar na url (GET - 0) os paramestros
    * low, average, fast, fastest - Default average
    */
    public function taxaTransferencia($params) {
        
        $priority = \Utils\Get::get($params, 0, "average");
        
        $bitWalletApi = new \BitWalletAPI();
        
        $taxa = $bitWalletApi->gas_price($priority);
        
        if($taxa["success"] == 1 && $taxa["data"] > 0){
            return $taxa["data"];
        } else {
            return null;
        }
    }
    
    
    
    
    

//    public function salvarLog($carteira, $response){
//        $tokenGatewayLogRn = new \Models\Modules\Cadastro\TokenGatewayLogRn();
//        $tokenGatewayLog = new \Models\Modules\Cadastro\TokenGatewayLog();
//        
//        $tokenGatewayLog->endereco = $carteira;
//        $tokenGatewayLog->response = $response;
//        
//        $tokenGatewayLogRn->salvar($tokenGatewayLog);
//    }
//
//    public function callBack() {
//        
//        $httpResponse = new \Modules\apiv2\Controllers\HttpResult();
//        
//        try {
//            $json = file_get_contents('php://input');
//            $object = json_decode($json);
//            
//            $httpResponse->setSuccessful(\Modules\apiv2\Controllers\HTTPResponseCode::$CODE200);
//        } catch (\Exception $ex) {
//            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
//        }
//         $httpResponse->printResult();
//        
//    }
    
    
//    public function registrar() {
//        try {
//            
//            $ethereum = new \Utils\Ethereum();
//            $carteirasRn = new \Models\Modules\Cadastro\CarteiraRn();
//            $dados = $carteirasRn->listar(" id_moeda IN (SELECT m.id FROM moedas m WHERE contrato IS NOT NULL) AND callback_deposito IS NULL ", null, null, null, true);
//            $i = 0;
//            
//            foreach ($dados as $carteira){
//                
//                print $carteira->endereco . " - " . $carteira->moeda->nome . " - " .++$i . "<br>";
//                
//                if(AMBIENTE == "producao"){
//
//                    $validacao = $ethereum->subscribeAddress($carteira->moeda, $carteira->endereco);
//                    if($validacao){
//                        $carteirasRn->marcarCallback($carteira->id);
//                        print "Feito <br>";
//                    }
//                    
//                }
//                sleep(1);
//            }
//            
//            
//        } catch (\Exception $ex) {
//            exit(print_r($ex));
//        }
//    }

}