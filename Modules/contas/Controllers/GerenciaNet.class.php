<?php

namespace Modules\contas\Controllers;

class GerenciaNet {
    
    private $idioma;
    
    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("deposito", IDIOMA);
    }
    
    public function gerarBoleto($params) {
        
        $deposito = null;
        try {
            
            $token = \Utils\Post::get($params, "token", null);
            $pin = \Utils\Post::get($params, "pin", null);
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $configuracoes = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $deposito->id = \Utils\Post::getEncrypted($params, "deposito", 0);
            $cliente = \Utils\Geral::getCliente();
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            try {
                $depositoRn->carregar($deposito, true, false, false, true);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("depositoInvalidoOuNaoEncontrado"));
            }
            
            $nomeCliente = \Utils\Validacao::limparString($deposito->cliente->nome, false);
            
            if (empty($token)) {
                throw new \Exception($this->idioma->getText("tokenInvalido"));
            }

            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }

            if ($deposito->cliente->pin != $pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }

            $authRn->validar($token, $cliente);

            if (strlen($nomeCliente) < 8) {
                throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
            }
            
            if(!\Utils\Validacao::verificarNomeCompleto($nomeCliente)){
                throw new \Exception("Nome inválido. Atualize seu nome no menu Meu Perfil, aba Meus Dados.");
            }
            
            if (empty($deposito->idGateway) || $deposito->dataVencimentoGateway == null || $deposito->dataVencimentoGateway->menor(new \Utils\Data(date("d/m/Y H:i:s")))) { 
                $itens = Array(
                    Array (
                        'name' => 'Depósito de crédito', // nome do item, produto ou serviço
                        'amount' => 1, // quantidade
                        'value' => intval(number_format($deposito->valorDepositado, 2, "", "")) // valor (1000 = R$ 10,00)
                    )
                );

                $body = Array(
                    "items" => $itens,
                    "metadata" => Array(
                        "notification_url" => URLBASE_CLIENT . "ws/gerencianet/notification",
                        "custom_id" => $deposito->id
                    )
                );
                
                if(AMBIENTE == "desenvolvimento"){
                   $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$optionsDev);
                } else {
                   $api = new \Gerencianet\Gerencianet(\GerenciaNet\Credentials::$options);
                }
                $charge = $api->createCharge([], $body);
                
                $deposito->idGateway = $charge["data"]["charge_id"];
                $deposito->statusGateway = $charge["data"]["status"];

                $depositoRn->conexao->update(Array("id_gateway" => $deposito->idGateway, "status_gateway" => $deposito->statusGateway), Array("id" => $deposito->id));
            
            
                $dataVencimento = new \Utils\Data(date("d/m/Y H:i:s"));
                $dataVencimento->somar(0, 0, 3);
                $billingOptions = Array(
                    'payment' => Array(
                        'banking_billet' => Array(
                            'expire_at' => $dataVencimento->formatar(\Utils\Data::FORMATO_ISO), // data de vencimento do boleto (formato: YYYY-MM-DD)
                            'customer' => Array(
                                'name' => $nomeCliente, // nome do cliente
                                'cpf' => str_replace(Array(".", "-", "/", " "), "", $deposito->cliente->documento), // cpf válido do cliente
                                'phone_number' => str_replace(Array("(", ")", "-", " "), "", $deposito->cliente->celular) // telefone do cliente
                            )
                        )
                    )
                );

                $result= $api->payCharge(Array('id' => intval($deposito->idGateway)), $billingOptions);

                $deposito->barcodeGateway = $result["data"]["barcode"];
                $deposito->linkGateway = $result["data"]["link"];
                $deposito->dataVencimentoGateway = new \Utils\Data($result["data"]["expire_at"] . " 23:59:59", \Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
                $deposito->statusGateway = $result["data"]["status"];

                $depositoRn->conexao->update(
                        Array(
                            "barcode_gateway" => $deposito->barcodeGateway, 
                            "link_gateway" => $deposito->linkGateway, 
                            "data_vencimento_gateway" => $deposito->dataVencimentoGateway->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO), 
                            "status_gateway" => $deposito->statusGateway
                        ), 
                        Array(
                            "id" => $deposito->id
                        )
                    );
            }
            
            //$barcode = \Utils\Barcode::gerarCodigoBarras(str_replace(Array(".", " "), "", $deposito->barcodeGateway));
            
            $barras = new \boletosPHP();
            $barras->setIpte($deposito->barcodeGateway);
            $barcode = \Utils\Barcode::gerarCodigoBarras(str_replace(Array(".", " ", ","), "", $barras->getBarras()));
            
            $valorCreditar = $deposito->valorDepositado - ($deposito->valorDepositado * ($configuracoes->taxaDepositoBoleto / 100)) - $configuracoes->tarifaDepositoBoleto;
            
            //exit( $valorCreditar . "-" .  $configuracoes->taxaDepositoBoleto . " tarifa " . $configuracoes->tarifaDepositoBoleto);
            $json["vencimento"] = $deposito->dataVencimentoGateway->formatar(\Utils\Data::FORMATO_PT_BR);
            $json["barras"] = $deposito->barcodeGateway;
            $json["img"] = $barcode;
            $json["valor"] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
            $json["link"] = $deposito->linkGateway;
            $json["valorCreditar"] = "R$ " . number_format($valorCreditar, 2, ",", ".");
            $json["comissao"] = number_format($configuracoes->taxaDepositoBoleto, 2, ",", ".") . "%";
            $json["taxa"] = "R$ " . number_format($configuracoes->tarifaDepositoBoleto, 2, ",", ".");
            $json["sucesso"] = true;
        } catch (\Gerencianet\Exception\GerencianetException $ex) {
            $json["sucesso"] = false;
            
            if (AMBIENTE == "desenvolvimento") {
                $json["mensagem"] = "$ex->code - $ex->error - ";
                if (is_array($ex->errorDescription)) {
                    foreach ($ex->errorDescription as $value) {
                        $json["mensagem"] .= $value . ".";
                    }
                } else {
                    $json["mensagem"] .= $ex->errorDescription . ".";
                }
            } else {
              $json["mensagem"] = "Falha para gerar o boleto.";  
            }
            $this->deletaDeposito($deposito);
        } catch (\Gerencianet\Exception\AuthorizationException $ex) {
            $json["sucesso"] = false;
            
            if (AMBIENTE == "desenvolvimento") {
                $json["mensagem"] = "$ex->code - $ex->error - ";
                if (is_array($ex->errorDescription)) {
                    foreach ($ex->errorDescription as $value) {
                        $json["mensagem"] .= $value . ".";
                    }
                } else {
                    $json["mensagem"] .= $ex->errorDescription . ".";
                }
            } else {
                $json["mensagem"] = "Falha para gerar o boleto.";
            }
            $this->deletaDeposito($deposito);
        }  catch (\Exception $ex) {
            $this->deletaDeposito($deposito);
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function deletaDeposito(\Models\Modules\Cadastro\Deposito &$deposito) {
    
        try{            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositoRn->conexao->excluir($deposito);
        } catch (Exception $ex) {
            throw new \Exception("Falha para gerar o boleto.");
        }        
    }
    
    public function token($params) {
        try {
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $cliente = \Utils\Geral::getCliente();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $valor = \Utils\Post::getNumeric($params, "valor", 0);

            $nomeCliente = \Utils\Validacao::limparString($cliente->nome, false);

            if (strlen($nomeCliente) < 8) {
                throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
            }

            if(!\Utils\Validacao::verificarNomeCompleto($nomeCliente)){
                throw new \Exception("Nome inválido. Atualize seu nome no menu Meu Perfil, aba Meus Dados.");
            }

            if($valor > $configuracao->valorMaxBoleto){
                throw new \Exception("Valor não permitido.");
            }

            $auth = new \Models\Modules\Cadastro\Auth();
            $auth->idCliente = $cliente->id;
            $authRn->salvar($auth);


            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoEmail1") . " " . $cliente->email . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoSMS1") . " " . $cliente->celular . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $json["mensagem"] = $this->idioma->getText("useGoogle1");
            }
            
            $valorCreditar = $valor - ($valor * ($configuracao->taxaDepositoBoleto / 100)) - $configuracao->tarifaDepositoBoleto;
            
            $json["creditarBoleto"] = "R$ " . number_format($valorCreditar, 2, ",", ".");
            $json["totalBoleto"] = "R$ " . number_format($valor, 2, ",", ".");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}