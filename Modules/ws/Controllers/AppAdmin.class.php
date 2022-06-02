<?php

namespace Modules\ws\Controllers;



class AppAdmin {

    /*public function getTasksPendentes($params) {
        try {

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $statusDepositoSaque = \Utils\Constantes::STATUS_DEPOSITO_PENDENTE;
            $statusRemessaBoleto = \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO;
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositos = $depositoRn->listar("date(data_solicitacao) = curdate() or status = '{$statusDepositoSaque}' or date(data_confirmacao) = curdate() or date(data_cancelamento) = curdate()", "data_solicitacao DESC", null, 20, FALSE, false, true);
            
            $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
            $saques = $saqueRn->listar("date(data_solicitacao) = curdate() or status = '{$statusDepositoSaque}' or date(data_deposito) = curdate() or date(data_cancelamento) = curdate()", "data_solicitacao DESC", null, 20, FALSE, false, true);

            $boletoRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $boletos = $boletoRn->conexao->listar("date(data_cadastro) = curdate() or status = '{$statusRemessaBoleto}' or date(data_pagamento) = curdate()", "data_cadastro DESC", null, 20, false, false, true);
            
            $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $remessas = $remessaRn->conexao->listar("date(data_cadastro) = curdate() or status = '{$statusRemessaBoleto}' or date(data_pagamento) = curdate() or date(data_cancelamento) = curdate()", "data_cadastro DESC", null, 20, false, false, true);
            
            
            $listaReais = Array();
            foreach ($depositos as $deposito) {
                if(!empty($deposito->idContaBancariaEmpresa)){
                    $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
                    $contaBancaria = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
                    $contaBancaria->id = $deposito->idContaBancariaEmpresa;
                    $contaBancariaRn->carregar($contaBancaria, true, true);                    
                }                
                
                if ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["id"] = $deposito->id;
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["tipo"] = "Depósito" . !empty($deposito->idContaBancariaEmpresa) ? " - " . $contaBancaria->banco->nome : "" ; 
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["status"] = ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_PENDENTE ? "Pendente" : ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO ? "Confirmado" : "Cancelado"));
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["valor"] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["cliente"] = $deposito->cliente->nome;
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["data"] = $deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                } else {
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["id"] = $deposito->id;
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["tipo"] = "Depósito" . !empty($deposito->idContaBancariaEmpresa) ? " - " . $contaBancaria->banco->nome : "" ; 
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["status"] = ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_PENDENTE ? "Pendente" : ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO ? "Confirmado" : "Cancelado"));
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["valor"] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["cliente"] = $deposito->cliente->nome;
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"]["data"] = $deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                }
            }
            foreach ($saques as $saque) {
                if ($saque->status === \Utils\Constantes::STATUS_SAQUE_PENDENTE) {
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["id"] = $saque->id;
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["tipo"] = "Saque";
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["status"] = ($saque->status === \Utils\Constantes::STATUS_SAQUE_PENDENTE ? "Pendente" : ($saque->status === \Utils\Constantes::STATUS_SAQUE_CONFIRMADO ? "Confirmado" : "Cancelado"));
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["valor"]= "R$ " . number_format($saque->valorSacado, 2, ",", ".");
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["cliente"] = $saque->cliente->nome;
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["data"] = $saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                } else {
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["id"] = $saque->id;
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["tipo"] = "Saque";
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["status"] = ($saque->status === \Utils\Constantes::STATUS_SAQUE_PENDENTE ? "Pendente" : ($saque->status === \Utils\Constantes::STATUS_SAQUE_CONFIRMADO ? "Confirmado" : "Cancelado"));
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["valor"]= "R$ " . number_format($saque->valorSacado, 2, ",", ".");
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["cliente"] = $saque->cliente->nome;
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"]["data"] = $saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                }
            }
            
            foreach ($boletos as $boleto) {
                $cliente->id = $boleto->idCliente;
                $clienteRn->conexao->carregar($cliente);
                if ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO) {
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["id"] = $boleto->id;
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["tipo"] = "Boleto";
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["status"] = ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO ? "Pendente" : ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO? "Confirmado" : "Cancelado"));
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["valor"] = "R$ " . number_format($boleto->valor, 2, ",", ".");
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["cliente"] = $cliente->nome;
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["data"] = $boleto->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                } else {
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["id"] = $boleto->id;
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["tipo"] = "Boleto";
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["status"] = ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO ? "Pendente" : ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO? "Confirmado" : "Cancelado"));
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["valor"] = "R$ " . number_format($boleto->valor, 2, ",", ".");
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["cliente"] = $cliente->nome;
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"]["data"] = $boleto->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                }
            }
            
            foreach ($remessas as $remessa) {
                $cliente->id = $remessa->idCliente;
                $clienteRn->conexao->carregar($cliente);
                if ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO) {
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["id"] = $remessa->id;
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["tipo"] = "Remessa";
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["status"] = ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO ? "Pendente" : ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO ? "Confirmado" : "Cancelado"));
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["valor"] = "R$ " . number_format($remessa->valor, 2, ",", ".");
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["cliente"] = $cliente->nome;
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["data"] = $remessa->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);                        
                } else {
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["id"] = $remessa->id;
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["tipo"] = "Remessa";
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["status"] = ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO ? "Pendente" : ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO ? "Confirmado" : "Cancelado"));
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["valor"] = "R$ " . number_format($remessa->valor, 2, ",", ".");
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["cliente"] = $cliente->nome;
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"]["data"] = $remessa->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                }
            }
            
            krsort($listaReais);
            $listaJson = Array();
            foreach ($listaReais as $obj){
                $listaJson[] = $obj;
            }            
            
            $json["listaReais"] = $listaJson;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function sendPush($params) {
        
        $titulo = \Utils\Post::get($params, "tipo", null);
        $valor = \Utils\Post::get($params, "valor", NULL);
        
        $firebase = new \Firebase\Firebase();
        $push = new \Firebase\FirebasePush();
        
       // $usuarios = Array();
        //$usuarios[0] = "fYG_u8JJvqw:APA91bEirXFe0L7ZKKYz39uxM6WQIX0tzAlpH1nPJ7IRd0oPgLiUjygHndxdWjEInLZVq0sO2-XHxMF-QWYq0pwB0h4j74n5rqK0kYfo03_lRwyq2QHsvgPSikwSoWLOcEZ6UDxcibg8"; //Sony Willian
        //$usuarios[1] = "e-eXTwA0i8g:APA91bGJAXKCVwbvQ0FdCQK0Rdj-3MyHJ2OgbXu8wNG6gr9tKXR-E7hqlfj5KAbGeYUEnEXzFHLZ7GKzeQzNmge1UMUJ2egWEFmKnRJahnp_dblUyCwfmK16R9hZLpaF5GTNoTwkQNks"; // Vagner
        $usuarios[2] = "ch7KIxQScBI:APA91bF9AV7guyZIB_GqWcoRMZkbPs5X6jPzUrBPxBhZzINTjxs3xjXpSyKP5NyPf3Kav8-jzkkHBl-wSTtBGvwNH6cQv_1pX-4gVGC-Dp5TZc-D0trWY83n8EnldqQSPyjh523Gsfex"; // Moto G4
        $usuarios[3] = "eVEOiytkVCc:APA91bFDBVN0IzpWUgN99yNJ-_wtAtMlbQ1XCtP-rfZk_SQ1rYv1gPfO6-rToPj7CG0xskYQMZYUtImAy3WbyyStJygW4wXtgL1b2tqdybfXXwe4ylvrFL6exHN7ltSIavSssTtb0irr";//Samsung Willian
        //$usuarios[4] = "fHV2p3s5lCs:APA91bHc1L4glhoCsHk6OzlqDV744FbvvGilCKGXaHfBguh-6Ibeu17b1y4Iy1izW0PmPs3x2KPZINzM39nyzDee1ctiwoPFpZsR8433HcI-r-29xtA1mrExzLPX3xuwmVbRH5-jOMdT"; //Gerson
        //$usuarios[5] = "ePZE5wZJ0w4:APA91bERiusGXkF01oWmptnz81HW2fpOtJ-zgQ5YXnf3nRtMIwDHi6z3HGP5L6aeYffOU7UFlQDotEJ2gkpIAaXo47IODDH6Sit44UniPJDiFV0Hw9xFg4b11I4Hrj9H9YepDLbXAkNU";//Android Virtual
       // $usuarios[6] = "eQhMnmk8cng:APA91bHDo_dTKtYtuh832d3-QvEjp20NG5m_E6ahO2grBidsfpgDThYGpGQPOM4FBBaQjdBOIPNMHa-DNZqTCvkaMPY2lI0DQkWoCo1AbDaprWfd1apPhMFsIlGMppU_knqtobuK0VaP"; // Renato

        // optional payload
        $payload = array();
        $payload['team'] = 'India';
        $payload['score'] = '5.6';
 
        // notification title
        $title = $titulo;
         
        // notification message
        $message = $titulo . " no valor de R$ " . $valor;
         
        // push type - single user / topic
        $push_type = "admin";
         
        // whether to include to image or not
        $include_image = FALSE;
 
 
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
            $regId = "d0mmrL5rkc0:APA91bGZ6B9Xx5jqAJePGAifHnnNGNtU4W_v6gMg563NdPpm9fgmBMqkP-FH5gYldoNTnqiigveim4azBDBNL5gQZ4es1N9uATDk4eRfL0Pi5j9i1dv0crnTarVXTC4m_RRzqS2eUlYd";
            $response = $firebase->send($regId, $json);
        } else if ($push_type == "admin") {
            $json = $push->getPush();
            foreach ($usuarios as $admin){
                $response = $firebase->send($admin, $json); 
            }
        }
        
        print_r($response);
    }*/
    
    
}