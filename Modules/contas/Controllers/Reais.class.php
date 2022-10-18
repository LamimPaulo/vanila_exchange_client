<?php

namespace Modules\contas\Controllers;

class Reais {
    
    private  $codigoModulo = "transferencias";
    private $idioma = null;
    
    public function __construct(&$params) {
        $this->idioma = new \Utils\PropertiesUtils("transferencia_btc", IDIOMA);
       // \Utils\Validacao::acesso($this->codigoModulo);
        
        if (\Utils\Geral::isLogado()) {
            if (!(\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR)) {
                $get = $params["_parameters"];
                if (isset($get[0])) {
                    $id = \Utils\Get::getEncrypted($params, 0, 0);

                    if (\Utils\Geral::getCliente()->id !== $id) {
                        \Utils\Geral::redirect(URLBASE_CLIENT. \Utils\Rotas::R_CONTACORRENTEREAIS ."/". \Utils\Criptografia::encriptyPostId(\Utils\Geral::getCliente()->id));
                    }
                }
            }
        }
    }
    
    public static function validarAcessoCliente($params, $thowsException = false) {
        if (\Utils\Geral::isLogado()) {
            if (!\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
                if ($cliente->fotoDocumentoVerificada < 1 || $cliente->fotoClienteVerificada < 1 || $cliente->fotoResidenciaVerificada < 1) {
                    if ($thowsException) {
                        throw new \Exception("Existem pendências na sua conta. O acesso ao recuso está bloqueado. Por favor, contate-nos.");
                    } else {
                        \Utils\Layout::append("inspina/necessario_validar_documentos", $params);
                        exit();
                    }
                }
            }
        }
    }
    
    public function index($params) {
        
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        if (!$adm) {
            $cliente = \Utils\Geral::getCliente();
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_CONTACORRENTEREAIS . "/{$cliente->id}");
        } else {
            try {



            } catch (\Exception $ex) {

            }

            \Utils\Layout::view("reais", $params);
        }
    }
    
    public function resumo($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            $mostrarZeradas = \Utils\Post::getBoolean($params, "contasZeradas", TRUE);
            $mostrarPositivas = \Utils\Post::getBoolean($params, "contasPositivas", TRUE);
            $mostrarNegativas = \Utils\Post::getBoolean($params, "contasNegativas", TRUE);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $lista = $contaCorrenteReaisRn->resumo($filtro);
            
            $html = $this->htmlListaResumo($lista, $mostrarZeradas, $mostrarPositivas, $mostrarNegativas);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaResumo($lista, $mostrarZeradas, $mostrarPositivas, $mostrarNegativas) {
        ob_start();
        if (sizeof($lista)) {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-5">
                        <strong>Cliente</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Entrada</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Saída</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Saldo</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Abrir</strong>
                    </div>
                </div>
            </li>
            <?php
            foreach ($lista as $dados) {
                
                $saldo = $dados["entrada"] - $dados["saida"];
                
                $mostrar = true;
                
                if ((!$mostrarNegativas) && $saldo < 0) {
                    $mostrar = false;
                }
                if ((!$mostrarPositivas) && $saldo > 0) {
                    $mostrar = false;
                }
                if ((!$mostrarZeradas) && $saldo == 0) {
                    $mostrar = false;
                }
                if ($mostrar) {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-5">
                            <?php echo $dados["nome"] ?>
                        </div>
                        <div class="col col-lg-2 text-center">
                            <?php echo number_format($dados["entrada"], 2, ",", ".")?>
                        </div>
                        <div class="col col-lg-2 text-center">
                            <?php echo number_format($dados["saida"], 2, ",", ".")?>
                        </div>
                        <div class="col col-lg-2 text-center">
                            <?php echo number_format($saldo, 2, ",", ".")?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            <a class="btn btn-primary" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_CONTACORRENTEREAIS ?>/<?php echo \Utils\Criptografia::encriptyPostId($dados["id"]) ?>">
                                <i class="fa fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </li>
                <?php
                }
            }
            
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Nenhuma conta corrente encontrada
                    </div>
                </div>
            </li>
            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function lancamentos($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Get::getEncrypted($params, 0, 0);
            
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            $params["cliente"] = $cliente;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("conta_corrente_reais", $params);
    }
    
    public function filtrar($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            
            $idCliente = \Utils\Post::getEncrypted($params, "idCliente", 0);
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $transferencia = \Utils\Post::get($params, "transferencia", "T");
            $nresultado = \Utils\Post::get($params, "nresultado", "T");
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $dados = $contaCorrenteReaisRn->filtrar($idCliente, $dataInicial, $dataFinal, $tipo, $filtro, $transferencia, $nresultado);
            
            $html = $this->htmlListaContaCorrente($dados);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function filtrarsaldo($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            if ($cliente !== null) {
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $dados = $contaCorrenteReaisRn->calcularSaldoConta(new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id)));
                $json["saldobrl"] = $dados;
            } else {
                $json["saldobrl"] = 0;
            }
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function htmlListaContaCorrente($dados) {
        $total = 0;
        $lista = $dados["lista"];
        ob_start();
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $contaCorrenteReais) {
                if ($contaCorrenteReais->tipo == \Utils\Constantes::ENTRADA) {
                    $total += $contaCorrenteReais->valor;
                } else {
                    $total -= $contaCorrenteReais->valor;
                }
                $this->itemListaContaCorrente($contaCorrenteReais);
            }
        }
        
        ?>
        <tr>
            <td colspan='2'>
                <strong>
                    Valor total: 
                </strong>
            </td>
            <td colspan='3' style='text-align: right;'>
                <strong>
                    R$ <?php echo number_format($total, 2, ",", ".")?>
                </strong>
            </td>
        </tr> 
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaContaCorrente(\Models\Modules\Cadastro\ContaCorrenteReais $contaCorrenteReais) {
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        ?>
        <tr style="color:<?php echo ($contaCorrenteReais->tipo == \Utils\Constantes::ENTRADA ? "#1ab394" : "#ed5565")?> ">
            <td><?php echo $contaCorrenteReais->id ?></td>
            <td><?php echo ($contaCorrenteReais->tipo == \Utils\Constantes::ENTRADA ? "Depósito" : "Saque") ?></td>
            <td><?php echo $contaCorrenteReais->descricao ?></td>
            <td><?php echo $contaCorrenteReais->data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
            <td><?php echo number_format($contaCorrenteReais->valor, 2, ",", ".")?> </td>
        </tr>
        <?php
    }
    
    
    public function cadastro($params) {
        try {
            $contaCorrenteReais = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteReais->id = \Utils\Post::get($params, "id", 0);
            
            if ($contaCorrenteReais->id > 0) {
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $contaCorrenteReaisRn->conexao->carregar($contaCorrenteReais);
            }
            
            $contaCorrenteReais->data = ($contaCorrenteReais->data != null ? $contaCorrenteReais->data->formatar(\Utils\Data::FORMATO_PT_BR) : date("d/m/Y"));
            $contaCorrenteReais->valor = number_format($contaCorrenteReais->valor, 2, ",", "");
            
            $json["conta"] = $contaCorrenteReais;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            $contaCorrenteReais = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteReais->id = \Utils\Post::get($params, "id", 0);
            
            
            
            $contaCorrenteReais->data = \Utils\Post::getData($params, "data", null, "00:00:00");
            $contaCorrenteReais->descricao = \Utils\Post::get($params, "descricao", NULL);
            $contaCorrenteReais->idCliente = \Utils\Post::get($params, "idCliente", 0);
            $contaCorrenteReais->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteReais->valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $contaCorrenteReais = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteReais->id = \Utils\Post::get($params, "id", 0);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReaisRn->excluir($contaCorrenteReais);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function imprimir($params) {
        try {
            
            $d = \Utils\Get::get($params, 0, 0);
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));
            
            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ?
                    new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ?
                    new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $idCliente = isset($a[4]) ? $a[4] : 0;
            $tipo = isset($a[2]) ? $a[2] : "T";
            $filtro = isset($a[3]) ? $a[3] : "T";
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $dados = $contaCorrenteReaisRn->filtrar($idCliente, $dataInicial, $dataFinal, $tipo, $filtro);
            
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente));
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            $params["cliente"] = $cliente;
            $params["entrada"] = $dados["entradas"];
            $params["saida"] = $dados["saidas"];
            $params["lancamentos"] = $dados["lista"];
            
            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_reais", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_conta_corrente_rs.pdf", "D", false, false, false);
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1>Ops...</h1>
                    <h3><?php echo \Utils\Excecao::mensagem($ex) ?></h3>
                </body>
            </html>
            <?php
        }
    }
    
    
    
    public function transferencia($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Get::getEncrypted($params, 0, 0);
            
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
            
            $params["configuracao"] = $configuracao;
            $params["cliente"] = $cliente;
            $params["saldo"] = $saldo;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("transferencia_reais", $params);
    }
    
    
    public function transferir($params) {
        try {
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $clienteFrom = new \Models\Modules\Cadastro\Cliente();
            $clienteFrom->id = \Utils\Geral::getCliente()->id;
            $clienteTo = new \Models\Modules\Cadastro\Cliente();
            $clienteTo->email = \Utils\Post::get($params, "email", null);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $clienteRn->conexao->carregar($clienteFrom);
            
            \Utils\ValidarSeguranca::validar($clienteFrom);
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($clienteFrom);
            
            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);
            
            \Utils\ValidarLimiteOperacional::validar($clienteFrom, $moeda, \Utils\Constantes::SAQUE, $valor, true);   
             
            $clienteTo = $clienteRn->getByEmail($clienteTo->email);
             
            if($clienteTo->id == null){               
                 throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            if($clienteFrom->id == $clienteTo->id){
                throw new \Exception("Transferência para o mesmo cliente não é permitido.");
            }
            
            // if ($clienteFrom->documentoVerificado != 1 || $clienteFrom->status != 1) {
            //     throw new \Exception("Por favor, faça a verificação da sua conta no menu Meu Perfil.");
            // }
            
            if ($clienteTo->documentoVerificado != 1 || $clienteTo->status != 1) {
                throw new \Exception("Transferência não permitida. Cliente destino não habilitado.");
            }

            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReaisRn->transferir($clienteFrom, $clienteTo, $valor, null);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("transfSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function filtrarTransferencias($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $nresultado = \Utils\Post::get($params, "nregistros", "T");
            
            $cliente = \Utils\Geral::getCliente();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $dados = $contaCorrenteReaisRn->filtrar(($cliente != null ? $cliente->id : 0), $dataInicial, $dataFinal, "T", $filtro, "S", $nresultado);
            
            $html = $this->htmlListaTransferencias($dados);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    private function htmlListaTransferencias($dados) {
        $total = 0;
        $lista = $dados["lista"];
        ob_start();
        
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $contaCorrenteReais) {
                $total += $contaCorrenteReais->valor;
                $this->itemListaTransferencia($contaCorrenteReais);
            }
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaTransferencia(\Models\Modules\Cadastro\ContaCorrenteReais $contaCorrenteReais) {
        $cor = ($contaCorrenteReais->tipo == \Utils\Constantes::SAIDA ? "red" : "#1ab394");
        ?>
        <tr style="color: <?php echo $cor ?>;">

            <?php if (\Utils\Geral::isUsuario()) { ?>
            <td><?php echo $contaCorrenteReais->cliente->nome ?></td>
            <?php } ?>
            <td><?php echo $contaCorrenteReais->id ?></td>
            <td><?php echo $contaCorrenteReais->data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
            <td class="text-center">
                <a tabindex="0" class="btn btn-xs btn-info transferencia-reais-descricao text-center" role="button" data-descricao='<?php echo $contaCorrenteReais->descricao ?>'  data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 10px">
                    <?php echo $this->idioma->getText("descricao1") ?>
                </a>
            </td>
            <td><?php echo number_format($contaCorrenteReais->valor, 2, ",", ".")?></td>
            <td><?php echo number_format($contaCorrenteReais->valorTaxa, 2, ",", ".")?></td>
            <td><?php echo ($contaCorrenteReais->clienteDestino != null ? $contaCorrenteReais->clienteDestino->nome : "") ?></td>

        </tr>
        <?php
    }
    
    public function imprimirtransferencias($params) {
        try {
            $d = \Utils\Get::get($params, 0, 0);
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));
            
            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ?
                    new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ?
                    new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $idCliente = isset($a[3]) ? $a[3] : 0;
            $filtro = isset($a[2]) ? $a[2] : "T";
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $dados = $contaCorrenteReaisRn->filtrar($idCliente, $dataInicial, $dataFinal, "T", $filtro, "S");
            
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente));
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            $params["cliente"] = $cliente;
            $params["entrada"] = $dados["entradas"];
            $params["saida"] = $dados["saidas"];
            $params["lancamentos"] = $dados["lista"];
            
            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_transferencias_reais", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_transferencias_rs.pdf", "D", false, false, false);
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1>Ops...</h1>
                    <h3><?php echo \Utils\Excecao::mensagem($ex) ?></h3>
                </body>
            </html>
            <?php
        }
    }
    
    
    
    public function token($params) {
        try {
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $valor += $configuracao->taxaTransferenciaInternaReais;
            
            $cliente = \Utils\Geral::getLogado();
            
            $auth = new \Models\Modules\Cadastro\Auth();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            
            if ($saldo < $valor) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);
            
            $tipo = "";
            $email = "";
            $telefone = "";
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $email = $usuario->email;
                $telefone = $usuario->celular;
                $auth->idUsuario = $usuario->id;
                $tipo = $usuario->tipoAutenticacao;
            } else {
                $cliente = \Utils\Geral::getCliente();
                
                if (empty($cliente->pin)) {
                    throw new \Exception($this->idioma->getText("precisaCadPin1"));
                }
                
                $email = $cliente->email;
                $telefone = $cliente->celular;
                $auth->idCliente = $cliente->id;
                $tipo = $cliente->tipoAutenticacao;
            }
            
            // if($cliente->documentoVerificado != 1 ){
            //     throw new \Exception("Por favor, faça a verificação da sua conta no menu Meu Perfil.");
            // }
                        
            $clienteTo = new \Models\Modules\Cadastro\Cliente();
            $clienteTo->id = \Utils\Post::getEncrypted($params, "idClienteTo", 0);
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($clienteTo);
                
                if ($clienteTo->documentoVerificado != 1 || $clienteTo->status != 1) {
                    throw new \Exception("Transferência não permitida. Cliente destino não habilitado.");
                }
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLoc"));
            }
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth);
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["meio"] = $this->idioma->getText("") . $email. $this->idioma->getText("porFavorInsiraToken1");
            } 
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_SMS){
                $json["meio"] = $this->idioma->getText("foiEnviadoSMS1") . $telefone . $this->idioma->getText("porFavorInsiraToken1");
            }
            
            if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                $json["meio"] = $this->idioma->getText("useGoogle1");
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function findByWallet($params) {
        try {
            $wallet = \Utils\Post::get($params, "carteira", "");
            
            if (empty($wallet)) {
                throw new \Exception($this->idioma->getText("necessarioInfCarteira"));
            }
            
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $result = $carteiraRn->conexao->listar("endereco = '{$wallet}' AND inutilizada < 1", null, null, null);
            
            if (sizeof($result) > 0) {
                $carteira = $result->current();
                
                $c = \Utils\Geral::getCliente();
                if ($c->id == $carteira->idCliente) {
                    throw new \Exception($this->idioma->getText("voceNaoPodeTransContaCorrente"));
                }
                
                $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $carteira->idCliente));
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } else {
                throw new \Exception($this->idioma->getText("cartInformadaNaoNc"));
            }
            
            $json["cliente"] = $cliente;
            $json["id"] = \Utils\Criptografia::encriptyPostId($cliente->id);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}