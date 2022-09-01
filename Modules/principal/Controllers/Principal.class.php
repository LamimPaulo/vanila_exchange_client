<?php

namespace Modules\principal\Controllers;



class Principal {

    function index($_parameters) {        
        Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
    }

    function mudarIdioma($params) {
        $idioma = \Utils\Post::get($params, "lang", "pt-BR");
        $expire = (time() + 7 * 24 * 60 * 60);
        $path = "/";
        setcookie("ncidiom", $idioma, $expire, $path);

        $uri = $_SERVER["REQUEST_URI"];
        if (substr($uri, 0, 1)) {
            $uri = substr($uri, 1);
        }
    }

    static function getIdioma() {
        $idioma = isset($_COOKIE["ncidiom"]) ?  $_COOKIE["ncidiom"] : "pt-BR";
        return $idioma;
    }

    public function online() {
        try {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->setUltimaAtividade();
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);

            //exit(print_r(\Utils\Geral::getCliente()));

            if (!\Utils\Geral::isLogado() || $cliente->status != 1) {
               //\Utils\Session::close();
               //\Utils\Geral::redirect(URLBASE_CLIENT);
                $navegadorRn = new \Models\Modules\Cadastro\NavegadorRn();

                $navegadorSessao = $navegadorRn->conexao->listar(" id_cliente = {$cliente->id}", "id DESC", null, 1);
                $navegadorSessao = $navegadorSessao->current();
                $session_id_to_destroy = \Utils\Criptografia::decriptyPostId($navegadorSessao->idSession);

                session_id($session_id_to_destroy);
                session_start();
                session_destroy();
                session_commit();

                //\Utils\Geral::setAutenticado(false);
                $json["mensagem"] = "Sessão encerrada.";
                $json["url"] = URLBASE_CLIENT . \Utils\Rotas::R_LOGIN;
                $json["sucesso"] = false;
            } else {
                $json["sucesso"] = true;
            }
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["url"] = URLBASE_CLIENT . \Utils\Rotas::R_LOGOUT;
        }        
        print json_encode($json);
    }

    public function init($params) {
        try {
            //throw new \Exception( "erro");
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);

            $paridade = Principal::getParity();
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridadeRn->carregar($paridade, true, true, true);

            if (!\Utils\Geral::isLogado()) {
                $json["redirect"] = true;
                $json["url"] = URLBASE_CLIENT . \Utils\Rotas::R_LOGIN;
                throw new \Exception("Sessão expirada");
            }

            $casasCurrency = \Utils\Post::get($params, "casasCurrency", 8);
            $casasDecimais = ($paridade->idMoedaTrade == 1 ? $configuracao->qtdCasasDecimais : 8);

            //throw new \Exception();
            $cliente = \Utils\Geral::getCliente();

            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->setUltimaAtividade();
            } catch (\Exception $ex) {
                //
            }

            //$dados = Array("saldo" => 0, "bloqueado" => 0);
            if ($cliente !== null) {
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn(null, false);
                if ($paridade->idMoedaTrade == 1) { 
                    $dados = $contaCorrenteReaisRn->calcularSaldoConta(new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id)), true);
                } else {
                    $dados = $contaCorrenteBtcRn->calcularSaldoConta(new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id)), $paridade->idMoedaTrade, true);
                }

                $json["saldobrl"] = number_format($dados["saldo"], $casasDecimais, ",", ".");
                $json["saldobrlbloqueado"] = number_format($dados["bloqueado"], $casasDecimais, ",", ".");

                $dados = $contaCorrenteBtcRn->calcularSaldoConta(new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id)), $paridade->idMoedaBook, true);

                $sSaldo = number_format($dados["saldo"], 8, ".", "");
                $sSaldoBloqueado = number_format($dados["bloqueado"], 8, ".", "");

                $json["saldobtc"] = substr($sSaldo, 0, (strlen($sSaldo) - (8-$casasCurrency)));
                $json["saldobtcbloqueado"] = substr($sSaldoBloqueado, 0, (strlen($sSaldoBloqueado) - (8-$casasCurrency)));
                
                
                $json["fullbtc"] = number_format($dados["saldo"], 8, ".", "");
                $json["fullbtcbloqueado"] = number_format($dados["bloqueado"], 8, ".", "");
                
                
            } else {
                
                $json["saldobrl"] = number_format(0, $casasDecimais, ",", "");
                $json["saldobrlbloqueado"] = number_format(0, $casasDecimais, ",", "");
                $json["saldobtc"] = number_format(0, $casasCurrency, ",", "");
                $json["saldobtcbloqueado"] = number_format(0, $casasCurrency, ",", "");
                
                $json["fullbtc"] = number_format(0, 8, ".", "");
                $json["fullbtcbloqueado"] = number_format(0, 8, ".", "");
                
            }
            
            
            if (\Utils\Geral::isUsuario()) {
                $online = $clienteRn->getNumeroClientesOnline();
            } else {
                $online = 0;
            }
            
            $json["clientesOnline"] = $online;
            $json["compra"] = number_format($paridade->precoCompra, $casasDecimais, ",", "");
            $json["venda"] = number_format($paridade->precoVenda, $casasDecimais, ",", "");
            $json["volume"] = number_format($paridade->volume, $casasCurrency, ".", "");
            
            $json["compraf"] = number_format($paridade->precoCompra, $casasDecimais, ",", ".");
            $json["vendaf"] = number_format($paridade->precoVenda, $casasDecimais, ",", ".");
            $json["volumef"] = number_format($paridade->volume, $casasCurrency, ".", ".");
            
            $json["compram"] = number_format($paridade->maiorPreco, $casasCurrency, ".", ".");
            $json["vendam"] = number_format($paridade->menorPreco, $casasCurrency, ".", ".");

            $json["sucesso"] = true;
            
            
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function testeEmail($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
             //\Email\EmailMain::send($cliente, 5, null, "10.000,00", "não identificado pelo banco");
             
            //$sms = new \Utils\Sms($cliente->ddi, $cliente->celular, "Teste de SMS online");
            //$sms->enviar();  
            $dados = Array();
            
            $dados[1] = "Bitcoin";
            $dados[2] = "R$ 35.990,00";
            $dados[3] = "1.55004400";        
            
            $notificacao = new \Utils\NotificationManager($cliente, 6, "Depósito de Bitcoin", $dados);
            $notificacao->enviar();
            
            $json["sucesso"] = true;
            $json["mensagem"] = "E-mail enviado";
            
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getListaCotacoes() {
        try {
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaTodasParidades(true);
            $cotacoesParidades = Array();
            
            foreach ($paridades as $parity) {
                if ($parity->ativo > 0) { 
                    //$parity = new \Models\Modules\Cadastro\Paridade();
                    
                    $casasDecimais = ($parity->idMoedaTrade == 1 ? $configuracao->qtdCasasDecimais : $parity->moedaTrade->casasDecimais);
                    
                    $cotacoesParidades[] = Array(
                        "code" => $parity->id,
                        "buy" => number_format($parity->precoCompra, $casasDecimais, ",", "."),
                        "sell" => number_format($parity->precoVenda, $casasDecimais, ",", "."),
                        "volume" => number_format($parity->volume, $parity->moedaBook->casasDecimais, ",", "."),
                    );
                } else {
                    $cotacoesParidades[] = Array(
                        "code" => $parity->id,
                        "buy" => number_format(0, $casasDecimais, ",", "."),
                        "sell" => number_format(0, $casasDecimais, ",", "."),
                        "volume" => number_format(0, $parity->moedaBook->casasDecimais, ",", "."),
                    );
                }
            }
            
            $json["cotacoes"] = $cotacoesParidades;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public static function sincCofre() {
        try {
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $cofreRn->cobrarTaxaTodosOsClientes();
            
            $result = $cofreRn->getClientesComResgateSolicitado();
            foreach ($result as $dados) {
                $cliente = $dados["cliente"];
                $moeda = $dados["moeda"];
                
                $cofreRn->retirar($cliente, $moeda);
            }
            
        } catch (\Exception $ex) {
            if(AMBIENTE == "desenvolvimento") {
                //exit(print_r($ex));
            }
        }
    }
    
    
    public static function setCurrency($params) {
        try {
            setcookie('nccurrency', '', time() - 300); 
            $idParidade = \Utils\Post::getEncrypted($params, "codigo", null);

            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            if(empty($idParidade)){

                $cliente->status = 0;
                $clienteRn->alterarStatusCliente($cliente);

                $mensagem = "Paridade não encontrada - setCurrency - {$cliente->email}";
                \Utils\Notificacao::notificar($mensagem, true, false, null, false);
                
                throw new \Exception("Verificação de cliente"); 
            } else {
                
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                
                $paridade = $paridadeRn->get($idParidade);

                $clienteRn->conexao->update(Array("id_moeda_atual" => $paridade->id), Array("id" => $cliente->id));
            }
        } catch (\Exception $ex) {
            throw new \Exception("Verificação cliente");
        }
        
        $json = Array("sucesso" => true);
        print json_encode($json);
    }
    
    public static function getParity($idParidade = null) {
        try {
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $idCliente = \Utils\Geral::getLogado();
        $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente->id));
        $clienteRn->conexao->carregar($cliente);

        if (empty($idParidade)) {
            $idParidade = $cliente->idMoedaAtual;
        }

            if(!empty($idParidade)){
                $paridade = $paridadeRn->get($idParidade);
            } else {
                $cliente->status = 0;
                $clienteRn->alterarStatusCliente($cliente);

                $mensagem = "Paridade não encontrada - getParity - {$cliente->email}";
                \Utils\Notificacao::notificar($mensagem, true, false, $cliente);

                throw new \Exception("Verificação de cliente"); 
            }

            return $paridade;
        } catch (\Exception $ex) {
            throw new \Exception("Verificação de clienteeee");
        }
    }
    
    public static function getCurrency($idParidade = null) {
        
        try {
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $idCliente = \Utils\Geral::getLogado();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente->id));
            $clienteRn->conexao->carregar($cliente);
            
            if (empty($idParidade)) {      
                 $idParidade = $cliente->idMoedaAtual;               
            }

            if(!empty($idParidade)){
                $paridade = $paridadeRn->get($idParidade);
            } else {

                $cliente->status = 0;
                $clienteRn->alterarStatusCliente($cliente);

                $mensagem = "Paridade não encontrada - GetCurrency - {$cliente->email}";
                \Utils\Notificacao::notificar($mensagem, true, false, $cliente);

                throw new \Exception("Verificação de cliente"); 
            }

            return $paridade->moedaBook;
        } catch (\Exception $ex) {

            throw new \Exception("Verificação de cliente"); 
            
        }
    }
    
    public function setCurrencyAndRedirect($params) {
        try {
            $sigla = \Utils\Get::get($params, 0, NULL);
            $destino = \Utils\Get::get($params, 1, NULL);
            if ($sigla != null && $destino != null) {

                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moeda = $moedaRn->getBySimbolo($sigla);

                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                
                $paridade = $paridadeRn->find($moeda->id, 1);
                
                $cliente = \Utils\Geral::getLogado();
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->update(Array("id_moeda_atual" => $paridade->id), Array("id" => $cliente->id));
                
                $url = "";
                
                switch (strtolower($destino)) {
                    case "d":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_DEPOSITOS;
                        break;
                    case "t":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_TRANSFERENCIAS;
                        break;
                    case "b":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_BOOK;
                        break;
                    case "v":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_MERCADO;
                        break;
                    case "c":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_CARTEIRAS;
                        break;
                    case "f":
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_COFRE;
                        break;

                    default:
                        $url = URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD;
                }
                
                if (!empty($url)) {
                    \Utils\Geral::redirect($url);
                } 
                
            }
        } catch (\Exception $e) {
            
        }
    }    
    
    
    public static function validarAcessoCliente($params, $thowsException = false) {
        if (\Utils\Geral::isLogado()) {
            if (!\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);

            }
        }
    }
    
    
    public function getTasksPendentes($params) {
        try {
            $json["cods"] = Array();
            
            $nomeClienteBolRem = null;
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $statusDepositoSaque = \Utils\Constantes::STATUS_DEPOSITO_PENDENTE;
            $statusRemessaBoleto = \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO;
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
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
                if ($deposito->status === \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                    $listaReais["Z-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"] = $deposito;
                } else {
                    $listaReais["A-{$deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$deposito->id}"] = $deposito;
                }
            }
            foreach ($saques as $saque) {
                if ($saque->status === \Utils\Constantes::STATUS_SAQUE_PENDENTE) {
                    $listaReais["Z-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"] = $saque;
                } else {
                    $listaReais["A-{$saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$saque->id}"] = $saque;
                }
            }
            
            foreach ($boletos as $boleto) {
                if ($boleto->status === \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO) {
                    $listaReais["Z-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"] = $boleto;
                } else {
                    $listaReais["A-{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$boleto->id}"] = $boleto;
                }
            }
            
            foreach ($remessas as $remessa) {
                if ($remessa->status === \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO) {
                    $listaReais["Z-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"] = $remessa;
                } else {
                    $listaReais["A-{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}-{$remessa->id}"] = $remessa;
                }
            }
            
            krsort($listaReais);
            
            ob_start();
            $i = 0;
            foreach ($listaReais as $object) {
                
                if($object instanceof \Models\Modules\Cadastro\BoletoCliente || $object instanceof \Models\Modules\Cadastro\RemessaDinheiro){
                    $clienteBolRem = new \Models\Modules\Cadastro\Cliente();
                    $clienteBolRem->id = $object->idCliente;
                    $clienteRn->conexao->carregar($clienteBolRem);
                    $nomeClienteBolRem = $clienteBolRem->nome;
                }
                           
                
                $class = "text-warning";
                if ($object->status == \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO) {
                    $class = "text-success";
                } else if ($object->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) {
                    $class = "text-danger";
                } else {
                    if ($object instanceof \Models\Modules\Cadastro\Deposito) {
                        $json["cods"][] = "DBRL-" . $object->id;
                    } else {
                        $json["cods"][] = "SBRL-" . $object->id;
                    }
                }
                
                $icon = "fa-arrow-up";
                $classIcon = "text-danger";
                if ($object instanceof \Models\Modules\Cadastro\Deposito) {
                    $icon = "fa-arrow-down";
                    $classIcon = "text-info";
                }
                
                ?>
                <tr class="<?php echo $class ?>">
                    <td class="text-center <?php echo $classIcon ?>">
                        <i class="fa <?php echo $icon ?>"></i>
                    </td>
                    <td class="text-center">
                        <?php echo $object->id ?>
                    </td>
                    <td class="text-center">
                        <?php if ($object instanceof \Models\Modules\Cadastro\Deposito || $object instanceof \Models\Modules\Cadastro\Saque) { ?>
                            <?php echo $object->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\BoletoCliente || $object instanceof \Models\Modules\Cadastro\RemessaDinheiro) {  ?>
                            <?php echo $object->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        <?php } ?>
                    </td>
                    <td class="text-center"> 
                        <?php if ($object instanceof \Models\Modules\Cadastro\Deposito || $object instanceof \Models\Modules\Cadastro\Saque) {
                            echo $object->cliente->nome ?>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\BoletoCliente || $object instanceof \Models\Modules\Cadastro\RemessaDinheiro) {
                            echo $nomeClienteBolRem;
                        } ?>
                    </td>
                    <td class="text-center">
                        <?php if ($object instanceof \Models\Modules\Cadastro\Deposito) { ?>
                            <?php echo number_format($object->valorDepositado, $configuracao->qtdCasasDecimais, ",", ".")?>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\Saque) {  ?>
                            <?php echo number_format($object->valorSacado, $configuracao->qtdCasasDecimais, ",", ".")?>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\BoletoCliente) {  ?>
                            <?php echo number_format(($object->valor - $object->valorTaxa), $configuracao->qtdCasasDecimais, ",", ".")?>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\RemessaDinheiro) {  ?>
                            <?php echo number_format(($object->valor - $object->valorTaxa - $object->tarifaTed), $configuracao->qtdCasasDecimais, ",", ".");
                        } ?>
                    </td>
                    <td class="text-center">
                        <?php echo $object->getStatus(); ?>
                    </td>
                    <td class="text-center">
                       <?php if (($object instanceof \Models\Modules\Cadastro\Deposito) && ($object->tipoDeposito != \Utils\Constantes::GERENCIA_NET)) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_DEPOSITOS ?>">Depósito</a>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\Saque) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_SAQUES ?>">Saque</a>
                        <?php } if ($object instanceof \Models\Modules\Cadastro\BoletoCliente) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_BOLETOS ?>">Boleto</a>
                        <?php }  if ($object instanceof \Models\Modules\Cadastro\RemessaDinheiro) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_REMESSAS ?>">Remessa</a>
                        <?php }  if (($object instanceof \Models\Modules\Cadastro\Deposito) && ($object->tipoDeposito == \Utils\Constantes::GERENCIA_NET)) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_DEPOSITOS ?>">Dep. Boleto</a>
                        <?php } ?>
                            
                    </td>
                </tr>
                <?php
                $i++;
                
                if ($i == 20) {
                    break;
                }
            }
            $movimentosReais = ob_get_contents();
            ob_end_clean();
            
            $dataAtual = date("Y-m-d");
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $depositosCurrencies = $contaCorrenteBtcRn->lista(
                    " (tipo = 'S' AND order_book = 0 AND transferencia = 1 AND executada = 0 AND autorizada < 2 AND origem != 3)  OR "
                                                                
                    . " (data_cadastro BETWEEN '{$dataAtual} 00:00:00' AND '{$dataAtual} 23:59:59' AND ((executada = 1 AND order_book < 1 AND origem != 3) OR (tipo = 'S' AND origem = 3)) ) "
                    , "data desc", null, 50, true, true);
            $moedaCripRn = new \Models\Modules\Cadastro\MoedaRn();
            
            ob_start();
            
            foreach ($depositosCurrencies as $cc) {
               $moedaCrip = new \Models\Modules\Cadastro\Moeda();
               $moedaCrip->id = $cc->idMoeda;
               $moedaCripRn->carregar($moedaCrip);
               //exit(print_r($moedaCrip->getUrlExplorer($cc->hash)));
                
               
                $tipo = $cc->getDescricaoOrigem();
                
                $class = "text-warning";
                if ($cc->autorizada > 1) {
                    $class = "text-danger";
                } else if ($cc->executada > 0) {
                    $class = "text-success";
                } else {
                    $json["cods"][] = "DCRY-" . $cc->id;
                }
                
                $icon = "fa-arrow-up";
                $classIcon = "text-danger";
                if ($cc->tipo == \Utils\Constantes::ENTRADA) {
                    $icon = "fa-arrow-down";
                    $classIcon = "text-info";
                }
                
                ?>
                <tr class="<?php echo $class ?>">
                    <td class="text-center <?php echo $classIcon ?>" style="vertical-align: middle;">
                        <i class="fa <?php echo $icon ?>"></i>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $cc->moeda->icone ?>" style="max-height: 18px; max-width: 18px" />
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <a href="<?php echo $moedaCrip->getUrlExplorer($cc->hash) ?>" target="EXPLORER_<?php echo $moedaCrip->simbolo ?>"><?php echo $cc->id ?></a>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $cc->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;"> 
                        <?php echo $cc->cliente->nome ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo number_format($cc->valor, $moedaCrip->casasDecimais, ",", ".")?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo ($cc->autorizada > 1 ? "Negada" : ($cc->executada > 0 ? "Executada" : "Pendente")); ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $tipo; ?>
                    </td>
                </tr>
                <?php
            }
            $htmlCurrencies = ob_get_contents();
            ob_end_clean();
            
            
            $clientes = $clienteRn->filtrar("T", null, "H", 0, "D", null, null, "a", "T");
            
            $clientesPendentes = sizeof($clientes);
            
            ob_start();
            foreach ($clientes as $cliente) {
                $json["cods"][] = "CLI-" . $cliente->id;
                
                if (empty($cliente->foto)) {
                    if (strtolower($cliente->sexo) == "m") {
                        $foto = TEMA . "dist/img/avatar5.png";
                    } else {
                        $foto = TEMA . "dist/img/avatar2.png";
                    }
                } else  {
                    $foto = URLBASE_CLIENT . UPLOADS . $cliente->foto;
                }
                
                $classeSelfie = ($cliente->fotoClienteVerificada > 0 ? "btn-primary" : (empty($cliente->fotoCliente) ? "btn-danger" : "btn-warning"));
                $classeDocumento = ($cliente->fotoDocumentoVerificada > 0 ? "btn-primary" : (empty($cliente->fotoDocumento) ? "btn-danger" : "btn-warning"));
                $classeResidencia = ($cliente->fotoResidenciaVerificada > 0 ? "btn-primary" : (empty($cliente->fotoResidencia) ? "btn-danger" : "btn-warning"));
                $corLinha = $cliente->analiseCliente == 1 ? "#FFDFD4":"";
                ?>
                <tr style="background-color: <?php echo $corLinha?>">
                    <td class="text-center" style="vertical-align: middle;">
                        <img src="<?php echo $foto ?>" style="max-height: 30px; max-width: 30px" />
                    </td>
                    <td style="vertical-align: middle;" >
                        <?php echo $cliente->nome ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $cliente->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-circle <?php echo $classeDocumento?>">D</button>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-circle <?php echo $classeResidencia?>">R</button>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-circle <?php echo $classeSelfie?>">S</button>
                    </td>
                </tr>
                <?php
            }
            $htmlClientes = ob_get_contents();
            ob_end_clean();
            
            
            $online = $clienteRn->getQuantidadeClientesOnline();
            ob_start();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            
            foreach ($online as $dadosCliente) {
                $cliente = new \Models\Modules\Cadastro\Cliente($dadosCliente);
                //$cliente->ipUltimoAcesso = "25.145.45.23";
                $sigla = \Utils\IPUtils::getCountryByIp($cliente->ipUltimoAcesso);
                
                $flag = "";
                if (!empty($sigla)) {
                    $flag = \Models\Modules\Cadastro\PaisRn::getBandeiraBySigla($sigla);
                }
                
                if (empty($cliente->foto)) {
                    if (strtolower($cliente->sexo) == "m") {
                        $foto = TEMA . "dist/img/avatar5.png";
                    } else {
                        $foto = TEMA . "dist/img/avatar2.png";
                    }
                } else  {
                    $foto = URLBASE_CLIENT . UPLOADS . $cliente->foto;
                }
                
                $atividade = \Models\Modules\Acesso\LoginSistemaRn::calcularTempoLogado($cliente);
                $moeda = new \Models\Modules\Cadastro\Moeda(Array("id"=>$cliente->idMoedaAtual));
                $moedaRn->conexao->carregar($moeda);
                $corLinhaOnline = $cliente->analiseCliente == 1 ? "#FFDFD4":"";
                
                ?>
                <tr style="background-color: <?php echo $corLinhaOnline ?>">
                    <td class="text-center" style="vertical-align: middle;">
                        <?php if (!empty($flag)) { ?>
                        <img src="<?php echo $flag ?>" style="max-height: 20px; max-width: 40px" />
                        <?php } ?>
                    </td>
                    <td style="vertical-align: middle;" class='text-center'>
                        <?php echo $sigla ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <img src="<?php echo $foto ?>" style="max-height: 30px; max-width: 30px" />
                    </td>
                    <td style="vertical-align: middle;">
                        <?php echo $cliente->nome ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $cliente->ipUltimoAcesso ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $atividade["stringShort"] ?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="max-width: 20px; max-height: 20px;" />
                    </td>
                </tr>
                <?php
            }
            $htmlOnline = ob_get_contents();
            ob_end_clean();
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $ordens = $ordemExecutadaRn->getHistoricoExecucoesIdentificadas();
            
            ob_start();
            foreach ($ordens as $ordem) {
                $data = new \Utils\Data(substr($ordem["data_execucao"], 0, 19));
                
                if (empty($ordem["vendedor"])) {
                    $ordem["vendedor"] = "OTC";
                }
                if (empty($ordem["comprador"])) {
                    $ordem["comprador"] = "OTC";
                }
                
                $paridade = new \Models\Modules\Cadastro\Paridade(Array("id" => $ordem["id_paridade"]));
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridadeRn->carregar($paridade);
                ?>
                <tr >
                    <td style="vertical-align: middle;" class='text-center'>
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $paridade->moedaBook->icone ?>" style="max-width: 40px; max-height: 30px;" />
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php 
                        if ($ordem["tipo"] == \Utils\Constantes::ORDEM_VENDA) {
                            echo $ordem["vendedor"];
                        } else {
                            echo $ordem["comprador"];
                        }
                        ?>
                    </td>
                    <td style="vertical-align: middle;" class='text-center'>
                        <?php 
                        if ($ordem["tipo"] == \Utils\Constantes::ORDEM_VENDA) {
                            echo "Vendeu";
                        } else {
                            echo "Comprou";
                        }
                        ?>
                    </td>
                    
                    <td class="text-center" style="vertical-align: middle;">
                        <?php 
                        if ($ordem["tipo"] == \Utils\Constantes::ORDEM_VENDA) {
                            echo $ordem["comprador"];
                        } else {
                            echo $ordem["vendedor"];
                        }
                        ?>
                    </td>
                    <td style="vertical-align: middle;" class='text-center'>
                        <?php echo number_format($ordem["volume_executado"], $paridade->moedaBook->casasDecimais, ".", "")?>
                    </td>
                    <td class="text-center" style="vertical-align: middle; min-width: 100px;">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $paridade->moedaTrade->icone ?>" style="max-width: 15px; max-height: 15px;" /> <?php echo number_format($ordem["valor_cotacao"], $paridade->moedaTrade->casasDecimais, ".", "")?>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <?php echo $data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                    </td>
                </tr>
                <?php
            }
            $htmlOrdens = ob_get_contents();
            ob_end_clean();
            
           
            //Dados de cadastro nos meses            
            $lista = array();
            $lista = $clienteRn->getQuantidadeClientesCadastradosPorMes();

            
            
            //Dados de cadastro nos dias
            $listaDia = array();
            $listaDia = $clienteRn->getQuantidadeClientesCadastradosPorDia();
            //exit(print_r(sizeof($listaDia)));
            /*for ($i = 0; $i <= 6; $i++) {
            $result = $clienteRn->conexao->listar("weekofyear(data_cadastro) = weekofyear(current_date()) AND YEAR(data_cadastro) = YEAR(curdate()) AND dayname(data_cadastro)  =  {$i} order by dayofweek(data_cadastro) ");
            $listaDia[$i] = sizeof($result);
             
            }*/
            
            $clienteRn->getQuantidadeClientesCadastradosPorMes();
            
            $cadastrados = $clienteRn->getQuantidadeClientesCadastrados();
            $cadastradosIco = $clienteRn->getQuantidadeClientesCadastradosIco();
            $dados = $clienteRn->getQuantidadeClientesPorStatus();
            $clientesVerificados = $clienteRn->getQuantidadeClientesVerificadosSistema();
            $clientesVerificadosPorc = "| " . number_format(((($clientesVerificados * 1) / $cadastrados) * 100), 0, ",",".") . "%";
            //exit(print_r($clientesVerificadosPorc));
            
            
            $json["qtdPendentes"] = $clientesPendentes;
            $json["qtdCadastros"] = $cadastrados - $cadastradosIco;
            $json["qtdCadastrosIco"] = $cadastradosIco;
            $json["totalCadastros"] = $cadastrados;
            $json["qtdVerificados"] = $clientesVerificados;
            $json["qtdVerificadosPorc"] = $clientesVerificadosPorc;
            $json["qtdOnline"] = sizeof($online);
            $json["qtdBloqueados"] = $dados["inativos"];
            $json["qtdAtivos"] = $dados["inativos"];
            $json["qtdEspera"] = $dados["aguardando"];
            $json["cadastroClientes"] = $this->dadosPorSemana();
            $json["dadosCadastroMes"] = $this->htmlDadosMes($lista);
            $json["dadosCadastroDia"] = $this->htmlDadosDia($listaDia);
            
            $json["orderBook"] = $htmlOrdens;
            $json["clientesAguardando"] = $htmlClientes;
            $json["movimentosReais"] = $movimentosReais;
            $json["clientesOnline"] = $htmlOnline;
            $json["htmlCriptocurrencies"] = $htmlCurrencies;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlDadosMes($lista) {
        
        ob_start();
        $i = 1;
        $qtdIco = 0;
        if (sizeof($lista) > 0) {
            $mesAnterior = null;
            $mesAnteriorIco = null;
            foreach ($lista as $mes) {
                
                //exit(print_r($mes));
                if($mes["qtdIco"] == null){
                    $qtdIco = 0;
                } else {
                    $qtdIco = $mes["qtdIco"];
                }            
                
                $this->htmlItemMeses($mes["qtdSite"], $mes["mes"], $mesAnterior, $qtdIco, $mesAnteriorIco);
                $mesAnterior = $mes["qtdSite"];
                $mesAnteriorIco = $qtdIco;
                $i++;
            }   
        } 
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemMeses($mes, $i, $mesAnterior, $qtdIco, $mesAnteriorIco) {
        
        $data = new \Utils\Data("01/".($i < 10 ? "0{$i}" : $i)."/2018");
        $monthName = $data->getNomeMes(true);
        //strftime("%b", mktime(0, 0, 0, $i + 1, 0));
        $mesAnterPorc = ($mes > 0 ? (100 - (($mesAnterior * 1 / $mes) * 100)) : 0);
        
        $mesAnterPorcIco = ($qtdIco > 0 ? (100 - (($mesAnteriorIco * 1 / $qtdIco) * 100)) : 0);
        

        //if($i > 0) {
        ?>            
            <div class="col-lg-1">
                <div class="widget <?php echo ($mes > $mesAnterior ? "navy-bg" : ($mesAnterior == 0 ? "blue-bg" : ($mes == 0 ? "blue-bg" : "yellow-bg"))) ?> p-xxs no-margin text-center">
                    <div class="row">
                        <div class="col-xs-12">
                            <span style="font-size: 14px;"></i><?php echo $monthName ?></span><br>
                            <span class="font-bold" style="font-size: 10px;">S - <?php echo $mes . " | " . ($mes == 0 ? "0%" : number_format($mesAnterPorc , 0, ",", "."). "%")?></span></br>
                            <?php if($qtdIco > 0) { ?>
                              <span class="font-bold" style="font-size: 10px;">I - <?php echo $qtdIco . " | " . ($qtdIco == 0 ? "0%" : number_format($mesAnterPorcIco , 0, ",", "."). "%")?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div> 
        <?php
        //}
    }
    
    private function htmlDadosDia($listaDia) {
        
        ob_start();
        $i = 0;
        $diaAnterior = null;
        if (sizeof($listaDia) > 0) {
            foreach ($listaDia as $dia) {
                
                $this->htmlItemDias($dia["qtdSite"], $i, $diaAnterior, $dia["qtdIco"]);
                $diaAnterior = $dia["qtdSite"];
                $i++;
            }   
        } 
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemDias($dia, $i, $diaAnterior, $qtdIco) {
        
        $nomeDia = array("Seg", "Ter", "Qua", "Qui", "Sex", "Sab", "Dom");//strftime("%a", mktime(0, 0, 0, 0, 0));
        $diaAnterPorc = ($dia > 0 ? (100 - (($diaAnterior * 1 / $dia) * 100)) : 0);
        //. " | " . ($dia == 0 ? "0%" : number_format($diaAnterPorc , 0, ",", "."). "% Para imprimir a diferença

        
        ?>            
            <div class="col-lg-1">
                <div class="widget <?php echo ($dia > 0 ? "navy-bg" : "blue-bg") ?> p-xxs no-margin text-center">
                    <div class="row">
                        <div class="col-xs-12">
                            <span style="font-size: 14px;"><?php echo $nomeDia[$i] ?></span><br>
                            <span class="font-bold" style="font-size: 10px;">S - <?php echo $dia ?></span></br>
                            <?php if($qtdIco > 0) { ?> 
                            <span class="font-bold" style="font-size: 10px;">I - <?php echo $qtdIco ?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div> 
        <?php
        
    }
    
    private function dadosPorSemana(){
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $result = "";
            //Dados de cadastro no DIA / SEMANA / MES 
            ob_start();

            $result = $clienteRn->conexao->listar("weekofyear(data_cadastro) = weekofyear(current_date()) - 1 AND YEAR(data_cadastro) = YEAR(curdate()) AND origem_cadastro = 'site'");
            $cadastroSemanaAnteriorSite = sizeof($result);            
            
            $result = $clienteRn->conexao->listar("weekofyear(data_cadastro) = weekofyear(current_date()) - 1 AND YEAR(data_cadastro) = YEAR(curdate()) AND origem_cadastro = 'newctoken'");
            $cadastroSemanaAnteriorNewcToken = sizeof($result);
           
            $result = $clienteRn->conexao->listar("weekofyear(data_cadastro) = weekofyear(current_date()) AND YEAR(data_cadastro) = YEAR(curdate()) AND origem_cadastro = 'site'");
            $cadastroSemanaSite = sizeof($result);
            
            $result = $clienteRn->conexao->listar("weekofyear(data_cadastro) = weekofyear(current_date()) AND YEAR(data_cadastro) = YEAR(curdate()) AND origem_cadastro = 'newctoken'");
            $cadastroSemanaNewcToken = sizeof($result);

            ?>

                    <div class=" col-lg-2 text-center">
                        <div class="widget style1 lazur-bg h6">Cad. na Semana: <span>Site - <?php echo $cadastroSemanaSite ?></span> | <span>Ico - <?php echo $cadastroSemanaNewcToken ?></span>                               
                        </div>
                        
                    </div>
                    <div class=" col-lg-3 text-center">
                        <div class="widget style1 navy-bg h6">Cad. Semana Anterior: <span>Site - <?php echo $cadastroSemanaAnteriorSite ?></span> | <span>Ico - <?php echo $cadastroSemanaAnteriorNewcToken ?></span>                                
                        </div>
                    </div>
             <?php
            $cadastroClientes = ob_get_contents();
            ob_end_clean();

            return $cadastroClientes;
    }
    

}