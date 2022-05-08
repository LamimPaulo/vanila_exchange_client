<?php

namespace LambdaAWS;
/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class LambdaNotificacao {

    public static function notificar(\Models\Modules\Cadastro\Cliente $cliente, $email = true, $idEmail = null, $sms = false, $dados = null) {
        try {

            $emailManager = \Models\Modules\Cadastro\EmailManagerRn::get($idEmail);

            $empresa = \Models\Modules\Cadastro\EmpresaRn::getEmpresa();
            $idioma = substr(\Modules\principal\Controllers\Principal::getIdioma(), 0, 2);
            
            $separacao = explode(' ', $cliente->nome);
            $nome = !empty($separacao[0]) ? $separacao[0] : $cliente->nome;

            $urlQueue = 'https://sqs.us-east-1.amazonaws.com/293963835247/notificacoes_prod.fifo'; //PROD

                        
            $clientSQS = new \Aws\Sqs\SqsClient([
                'credentials' => [
                    'key' => getenv("EnvLambdaKey"),
                    'secret' => getenv("EnvLambdaSecret"),
                ],
                'region' => "us-east-1",
                'version' => '2012-11-05'
            ]);

            //Preparacao do JSON a ser enviado ao Lambda
            $object = ["user_id" => "{$cliente->id}"];
                                
            if ($email) {
                $emailArray = ["email" => [
                        "para" => $cliente->email,
                        "texto" => "",
                        "templateId" => $emailManager->idTemplate,
                        "parametros" => [
                            "lang" => $idioma,
                            "cliente_nome" => $nome,
                            "cliente_email" => $cliente->email,
                            "plataforma_nome" => $empresa->nomeFantasia,
                            "plataforma_email_seguros" => $empresa->emailsSeguros,
                            "plataforma_rodape" => "{$empresa->nomeFantasia} | {$empresa->logradouro}, {$empresa->numero} - {$empresa->complemento} - {$empresa->bairro} | {$empresa->municipio} - {$empresa->cep} | {$empresa->pais}",
                        ]
                    ]
                ];
                            
                $object += $emailArray;

                //Verificar redes sociais
                if ($emailManager->redesSociais) {
                    $arraySocial = ["social_icons" => true,
                        "social_facebook_url" => "https://www.facebook.com/" . $empresa->facebook,
                        "social_youtube_url" => "https://www.youtube.com/" . $empresa->youtube,
                        "social_twitter_url" => "https://twitter.com/" . $empresa->twitter,
                        "social_instagram_url" => "https://www.instagram.com/" . $empresa->instagram,
                        "social_telegram_url" => "https://t.me/" . $empresa->telegram,
                        "social_blog_url" => $empresa->blog
                    ];

                    $object["email"]["parametros"] += $arraySocial;
                }
                
                $jsonEmailManager = json_decode($emailManager->parametrosJson);

                //Verifica Frase de Seguranca
                if (isset($jsonEmailManager->frase_seguranca) && $jsonEmailManager->frase_seguranca) {
                    $fraseSeguranca = ["frase_seguranca" => $cliente->fraseSeguranca];
                    $object["email"]["parametros"] += $fraseSeguranca;
                }

                //Verifica Cliente URL
                if (isset($jsonEmailManager->cliente_url) && $jsonEmailManager->cliente_url) {
                    $clienteUrl = ["cliente_url" => $dados["cliente_url"]];
                    $object["email"]["parametros"] += $clienteUrl;
                }

                //Verifica Home URL
                if (isset($jsonEmailManager->home_url) && $jsonEmailManager->home_url) {
                    $homeUrl = ["home_url" => $dados["home_url"]];
                    $object["email"]["parametros"] += $homeUrl;
                }
                
                //Verifica Cliente 2FA
                if (isset($jsonEmailManager->cliente_2fa) && $jsonEmailManager->cliente_2fa) {
                    $cliente2fa = ["cliente_2fa" => $dados["codigo"]];
                    $object["email"]["parametros"] += $cliente2fa;
                }
                
                //Verifica Senha Cliente
                if (isset($jsonEmailManager->senha) && $jsonEmailManager->senha) {
                    $senha = ["senha_cliente" => $dados["senha"]];
                    $object["email"]["parametros"] += $senha;
                }
                
                //Mensagem de notificação
                if (isset($jsonEmailManager->mensagem) && $jsonEmailManager->mensagem) {
                    $mensagem = ["mensagem" => $dados["mensagem"]];
                    $object["email"]["parametros"] += $mensagem;
                }
                
                //Verifica Fiat - Depósito e Saque
                if (isset($jsonEmailManager->fiat) && $jsonEmailManager->fiat) {
                    $fiat = ["fiat" => $dados];
                    $object["email"]["parametros"] += $fiat;
                }
                
                //Verifica Fiat - Depósito e Saque
                if (isset($jsonEmailManager->criptomoeda) && $jsonEmailManager->criptomoeda) {
                    $criptomoeda = ["criptomoeda" => $dados];
                    $object["email"]["parametros"] += $criptomoeda;
                }
                
                //Verifica Fiat - Depósito e Saque
                if (isset($jsonEmailManager->acesso) && $jsonEmailManager->acesso) {
                    $navegador = ["navegador" => $dados];
                    $object["email"]["parametros"] += $navegador;
                }

                //Hash recuperar senha
                if (isset($jsonEmailManager->hash) && $jsonEmailManager->hash) {
                    $hash = ["hash" => $dados["hash"]];
                    $object["email"]["parametros"] += $hash;
                }
                
                //Status da coanta
                if (isset($jsonEmailManager->status_conta) && $jsonEmailManager->status_conta) {
                    $statusConta = ["status_conta" => $dados];
                    $object["email"]["parametros"] += $statusConta;
                }

                //Solicitação de API
                if (isset($jsonEmailManager->api_key) && $jsonEmailManager->api_key) {
                    $clientID = ["client_id" => $dados["client_id"]];
                    $apiKey = ["api_key" => $dados["api_key"]];
                    $object["email"]["parametros"] += $clientID;
                    $object["email"]["parametros"] += $apiKey;
                }
            }
            
            //Verifica SMS
            if ($sms) {
                /*$smsArray = ["sms" =>
                        ["para" => "{$cliente->celular}",
                        "texto" => "Teste SMS",
                        "monitorar" => "false"]
                ];
                        
                $object += $smsArray;*/
            }
            
            $paramsSQS = [
                'MessageDeduplicationId' => strtotime(date("Y-m-d H:i:s")),
                'MessageGroupId' => 'group1',
                'MessageBody' => json_encode($object),
                'QueueUrl' => $urlQueue
            ];

            $result = $clientSQS->sendMessage($paramsSQS);

            if(AMBIENTE == "desenvolvimento"){
                //var_dump($result);
            }
            
        } catch (\Aws\Exception\AwsException $e) {
             if(AMBIENTE == "desenvolvimento"){
               // var_dump($e->getMessage());
            }
        }
    }

}
