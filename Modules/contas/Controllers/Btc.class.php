<?php

namespace Modules\contas\Controllers;

class Btc {
    
    private  $codigoModulo = Array("transferencias", "depositos");
    private $idioma = null;

    
    public function __construct(&$params) {
        
        //\Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("transferencia_btc", IDIOMA);
        
        if (\Utils\Geral::isLogado()) {
            
            if (!(\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR)) {
                
                if (isset($get[0])) {
                    $id = \Utils\Get::getEncrypted($params, 0, 0);

                    if (\Utils\Geral::getCliente()->id !== $id) {
                        \Utils\Geral::redirect(URLBASE_CLIENT. \Utils\Rotas::R_CONTACORRENTEBTC . "/". \Utils\Criptografia::encriptyPostId(\Utils\Geral::getCliente()->id));
                    }
                }
            }
        }
    }
    
    public function index($params) {
        
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        if (!$adm) {
            $cliente = \Utils\Geral::getCliente();
            \Utils\Geral::redirect(URLBASE_CLIENT . "contas/reais/lancamentos/{$cliente->id}");
        } else {
            try {

            } catch (\Exception $ex) {

            }
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0", "principal DESC, simbolo ASC", null, null);
            $params["moedas"] = $moedas;
            \Utils\Layout::view("btc", $params);
        }
    }
    
    public function resumo($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", null);
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", 0);
            $mostrarZeradas = \Utils\Post::getBoolean($params, "contasZeradas", true);
            $mostrarPositivas = \Utils\Post::getBoolean($params, "contasPositivas", true);
            $mostrarNegativas = \Utils\Post::getBoolean($params, "contasNegativas", true);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $lista = $contaCorrenteBtcRn->resumo($filtro, $idMoeda);
            
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
                        <strong><?php echo $idioma->getText("liCliente") ?></strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong><?php echo $idioma->getText("liEntrada") ?></strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong><?php echo $idioma->getText("liSaida") ?></strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong><?php echo $idioma->getText("liVolume") ?></strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong><?php echo $idioma->getText("liAbrir") ?> </strong>
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
                            <?php echo number_format($dados["entrada"], 8, ",", ".")?>
                        </div>
                        <div class="col col-lg-2 text-center">
                            <?php echo number_format($dados["saida"], 8, ",", ".")?>
                        </div>
                        <div class="col col-lg-2 text-center">
                            <?php echo number_format($saldo, 8, ",", ".")?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            <a class="btn btn-primary" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_CONTACORRENTEBTC ?>/<?php echo \Utils\Criptografia::encriptyPostId($dados["id"]) ?>">
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
                        <?php echo $this->idioma->getText("nenhumaContaEncontrada") ?>
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
            
            if ($cliente->id > 0) {
                try {
                    $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                    $clienteRn->conexao->carregar($cliente);
                } catch (\Exception $ex) {
                    throw new \Exception($this->idioma->getText("clienteNaoLoc"));
                }
            } else {
                $cliente = \Utils\Geral::getCliente();
            }
            
            $params["cliente"] = $cliente;
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0", "principal DESC, simbolo ASC", null, null);
            $params["moedas"] = $moedas;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("conta_corrente_btc", $params);
    }
    
    public function filtrar($params) {
        try {  
            
            $idCliente = \Utils\Geral::getCliente()->id;
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $data = \Utils\Post::get($params, "data", "todos");            
            $nresultado = \Utils\Post::get($params, "nresultado", "T");
            $referencia = \Utils\Post::getBoolean($params, "referencia", false);
            $idMoeda = \Utils\Post::getEncrypted($params, "idMoeda", null);
            
            switch ($data) {
                case "dia":                        
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;
                case "todos":
                    $dataInicial =  new \Utils\Data(date("01/04/2018") . " 00:00:00");//Data que iniciou o sistema
                    $dataFinal = new \Utils\Data(date("d/m/Y") . " 23:59:59");
                    break;
            }
            
            if($idMoeda != null){
                $moeda = new \Models\Modules\Cadastro\Moeda();
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moeda->id = $idMoeda;
                $moedaRn->carregar($moeda);
            } else {
                $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            }
            
            if(empty($idCliente)){
                throw new \Exception("Cliente inválido.");
            }
           
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $dados = $contaCorrenteBtcRn->filtrar($idCliente, $dataInicial, $dataFinal, $tipo, $filtro, "T", $moeda->id, $nresultado, false, $referencia);
            
            $html = $this->htmlListaContaCorrente($dados, $moeda);
            
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
            
            $idMoeda = 2; // Mostrando sempre o saldo em BTC da conta
            
            if ($cliente !== null) {
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $dados = $contaCorrenteBtcRn->calcularSaldoConta(new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id)), $idMoeda);
                $json["saldobtc"] = $dados;
            } else {
                $json["saldobtc"] = 0;
            }
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaContaCorrente($dados, \Models\Modules\Cadastro\Moeda $moeda) {
        
        $total = 0;
        $lista = $dados["lista"];
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        ob_start();
        
        ?>

        <?php
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $contaCorrenteBtc) {
                if ($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                    $total += $contaCorrenteBtc->valor;
                } else {
                    $total -= $contaCorrenteBtc->valor;
                }
                $this->itemListaContaCorrente($contaCorrenteBtc, $moeda);
            }
            ?>
            </div>
            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaContaCorrente(\Models\Modules\Cadastro\ContaCorrenteBtc $contaCorrenteBtc, \Models\Modules\Cadastro\Moeda $moeda) {
        //$moeda = \Modules\principal\Controllers\Principal::getCurrency();
        //$adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        $url = "";
        
        if((!empty($moeda->idMoedaConversao) && $moeda->idMoedaConversao > 0) && ($contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA 
                && !empty($contaCorrenteBtc->hash) && !empty($contaCorrenteBtc->enderecoBitcoin))){
         
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaConversao = new \Models\Modules\Cadastro\Moeda(Array("id" => $moeda->idMoedaConversao));
            $moedaRn->conexao->carregar($moedaConversao);
            
            $url = $moedaConversao->getUrlExplorer($contaCorrenteBtc->hash);
            
            
        } else {
            $url = $moeda->getUrlExplorer($contaCorrenteBtc->hash);
        }
        
        ?>
        <tr>
            <td><?php echo $contaCorrenteBtc->id ?></td>
            <td><?php echo number_format($contaCorrenteBtc->valor, $moeda->casasDecimais, ".", "")?></td>
            <td style='text-align: center;'>
                <?php if (!empty($contaCorrenteBtc->hash)) { ?>
                <a href="<?php echo $url ?>" target="EXPLORER_<?php echo $moeda->simbolo ?>"><i class="fa fa-link"></i></a>
                <?php } ?>
            </td>
            <td><?php echo $contaCorrenteBtc->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
            <td><?php echo $contaCorrenteBtc->enderecoBitcoin ?></td>
        </tr>
        <?php
    }
    
    
    
    
    public function transferencia($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            
            $cliente->id = \Utils\Get::getEncrypted($params, 0, 0);
            
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            if ($cliente->id > 0) { 
                try {
                    $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                    $clienteRn->conexao->carregar($cliente);
                } catch (\Exception $ex) {
                    throw new \Exception($this->idioma->getText("clienteNaoLoc"));
                }
            } else {
                $cliente = \Utils\Geral::getLogado();
            }
            
            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
            }
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
                       
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);
            $taxa = $taxaMoeda->taxaTransferencia;
            
            if ($cliente instanceof \Models\Modules\Cadastro\Cliente) { 
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente);
            } else {
                $saldo = 0;
            }
            
            $params["taxa"] = $taxa;
            $params["configuracao"] = $configuracao;
            $params["cliente"] = $cliente;
            $params["saldo"] = $saldo;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("transferencia_btc", $params);
    }
    
    
    public function transferir($params) {
        try {
            $enderecoBitcoin = \Utils\Post::get($params, "enderecoBitcoin", null);
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $token = \Utils\Post::get($params, "token", NULL);
            $pin = \Utils\Post::get($params, "pin", NULL);
            $rede = \Utils\Post::get($params, "rede", NULL);
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda->id = \Utils\Post::getEncrypted($params, "moeda", null);
            $moedaRn->carregar($moeda);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteFrom = new \Models\Modules\Cadastro\Cliente();
            $clienteFrom->id = \Utils\Geral::getCliente()->id;
            $clienteRn->conexao->carregar($clienteFrom);
            
            if(empty($clienteFrom->id)){
                throw new \Exception("Cliente inválido.");
            }
            
            \Utils\ValidarSeguranca::validar($clienteFrom);
            
            \Utils\ValidarLimiteOperacional::validar($clienteFrom, $moeda, \Utils\Constantes::SAQUE, $valor, true);

            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token);

            if ($clienteFrom != null) {
                if (empty($clienteFrom->pin)) {
                    throw new \Exception($this->idioma->getText("precisaCadPin1"));
                }
                if ($clienteFrom->pin != $pin) {
                    throw new \Exception($this->idioma->getText("pinInvalido1"));
                }
            }

            if ($moeda->coinType == \Utils\Constantes::REDE_ERC20 && trim($rede) == \Utils\Constantes::REDE_BEP20) {
                if (!empty($rede)) {
                    $validar = false;
                    $redes = json_decode($moeda->redesSaque);
                    $validarKey = key_exists($rede, (array)$redes);
                    if (!$validarKey) {
                        throw new \Exception("Rede inválida.");
                    }
                }
            } else {
                $rede = null;
            }

            if($valor > 0) {
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

                $contaCorrenteBtcRn->transferir($clienteFrom, $enderecoBitcoin, $valor, null, $moeda->id, null, $rede);

                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($clienteFrom, $moeda->id);

                $json["saldo"] = number_format($saldo, $moeda->casasDecimais, ",", "");
                $json["sucesso"] = true;
                $json["mensagem"] = $this->idioma->getText("transSucesso");
            } else {
                $clienteFrom->status = 2;
                $clienteRn->alterarStatusCliente($clienteFrom);
            }            
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function filtrarTransferencias($params) {
        try {
            
            $dataInicial = null;
            $dataFinal = null;
            $moeda = \Utils\Post::getEncrypted($params, "moeda", null);
            $cliente = \Utils\Geral::getCliente();            
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $data = \Utils\Post::get($params, "data", "todos"); 
            $nresultado = \Utils\Post::get($params, "nregistros", "T");
            
            switch ($data) {
                case "dia":                        
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;
                case "todos":
                    $dataInicial =  new \Utils\Data(date("01/04/2018") . " 00:00:00");//Data que iniciou o sistema
                    $dataFinal = new \Utils\Data(date("d/m/Y") . " 23:59:59");
                    break;
            }
            
            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
            }
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $dados = $contaCorrenteBtcRn->filtrar(($cliente != null ? $cliente->id : 0), $dataInicial, $dataFinal, \Utils\Constantes::SAIDA, $filtro, "S", $moeda, $nresultado);
            
            $html = $this->htmlListaTransferencias($dados, $moeda);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    private function htmlListaTransferencias($dados, $idMoeda) {
        $moeda = new \Models\Modules\Cadastro\Moeda();
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moeda->id = $idMoeda;
        $moedaRn->carregar($moeda);
        $total  = 0;
        $lista = $dados["lista"];
        
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $contaCorrenteBtc) {
                $this->itemListaTransferencia($contaCorrenteBtc);
            }
        } else {
            ?>
            <tr class="">
                <td  colspan='6'>
                    <?php echo $this->idioma->getText("nenhumaContaEncontrada") ?>
                </td>
            </tr>

            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaTransferencia(\Models\Modules\Cadastro\ContaCorrenteBtc $contaCorrenteBtc) { 
        
        $status = "";
        $cor = "";
        $simboloTaxa = "";
        
        if ($contaCorrenteBtc->autorizada == 2) {
            $status = $this->idioma->getText("statusNaoAutorizado");
            $cor = "#CC0000";
        } else if ($contaCorrenteBtc->autorizada == 0) {
            $status = $this->idioma->getText("statusEmAnalise");
            $cor = "#CC9900";
        } else if ($contaCorrenteBtc->autorizada == 1 && $contaCorrenteBtc->executada < 1) {
            $status = $this->idioma->getText("statusEmProcessamento");
            $cor = "#FF9900";
        } else if ($contaCorrenteBtc->autorizada == 1 && $contaCorrenteBtc->executada > 0) {
            $status = $this->idioma->getText("statusConcluido");
            $cor = "#1ab394";
        }
        
        if(!empty($contaCorrenteBtc->idMoedaTaxa)){
            if($contaCorrenteBtc->idMoedaTaxa != $contaCorrenteBtc->idMoeda){
                $moedaTaxa = \Models\Modules\Cadastro\MoedaRn::get($contaCorrenteBtc->idMoedaTaxa);
                $simboloTaxa = $moedaTaxa->simbolo;
            } 
        } 
        
        
        ?>
            <tr>        
                <td><?php echo $contaCorrenteBtc->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ?></td>
                <td><?php echo number_format($contaCorrenteBtc->valor, $contaCorrenteBtc->moeda->casasDecimais, ".", "")?></td>
                <td><?php echo number_format($contaCorrenteBtc->valorTaxa, $contaCorrenteBtc->moeda->casasDecimais, ".", "") ?> <?php echo $simboloTaxa; ?></td>
                <td><?php echo $contaCorrenteBtc->enderecoBitcoin ?>   </td>  
                <td>
                    <?php if ($contaCorrenteBtc->direcao == \Utils\Constantes::TRANF_EXTERNA) { ?>  
                        <?php if (!empty($contaCorrenteBtc->hash)) { ?>
                    <a href="<?php echo $contaCorrenteBtc->moeda->getUrlExplorer($contaCorrenteBtc->hash)?>" target="EXPLORER_<?php echo $contaCorrenteBtc->moeda->simbolo ?>"><i class="fa fa-link"></i></a>
                <?php }
                    } else { ?>
                       <?php echo $this->idioma->getText("cartNewCash") ?>
                    <?php }?>
                </td>
                 <td><?php echo $status ?></td>
            </tr>
            
        <?php
    }
    
    public function token($params) {
        try {
            
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", null);
            $enderecoBitcoin = \Utils\Post::get($params, "enderecoBitcoin", null);
            $rede = \Utils\Post::get($params, "rede", null);

            $cliente = \Utils\Geral::getCliente();  
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            $auth = new \Models\Modules\Cadastro\Auth();
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda->id = $idMoeda;
            $moedaRn->conexao->carregar($moeda);
            
            \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::SAQUE, $valor, true);
            
            if(empty($enderecoBitcoin)){
               throw new \Exception($this->idioma->getText("carteiraInvalida")); 
            }
            
            if($moeda->coinType == \Utils\Constantes::REDE_ERC20){
                
                if(empty($moeda->redesSaque)){
                    $rede = \Utils\Constantes::REDE_ERC20;
                } else {
                    $saques = json_decode($moeda->redesSaque);
                    
                    if($saques->ERC20 == true && $saques->BEP20 == true){
                        throw new \Exception("Por favor, selecione a rede onde será efetuado o saque.");
                        
                    } else if($saques->ERC20 == false && $saques->BEP20 == true){
                        $rede = \Utils\Constantes::REDE_BEP20;
                        
                    } else if($saques->ERC20 == true && $saques->BEP20 == false){
                        $rede = \Utils\Constantes::REDE_ERC20;
                    }
                }
                
                if(empty($rede) && $rede != \Utils\Constantes::REDE_ERC20 && $rede != \Utils\Constantes::REDE_BEP20){
                    throw new \Exception("Por favor, selecione a rede onde será efetuado o saque.");
                }
            }
            
            // if(!\Utils\Validacao::validaCarteira($enderecoBitcoin, $moeda)){
            //     throw new \Exception("Insira uma carteira válida da moeda " . $moeda->nome . "."); 
            // }
            
            if ($valor <= 0) {
                throw new \Exception("Valor inválido.");
            }
            
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            
            $carteira = $carteiraRn->getByEndereco($enderecoBitcoin, $moeda->id, false, $cliente);
            
            if (!empty($carteira)) {
                if ($carteira->idCliente == $cliente->id) {
                    throw new \Exception("Transferência interna não permitida.");
                }
            }

            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();

            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

            if ($rede == \Utils\Constantes::REDE_BEP20 && $moeda->idMoedaSaque > 0){

                //Moeda Saque
                $moedaSaque = new \Models\Modules\Cadastro\Moeda(Array("id" => $moeda->idMoedaSaque));
                $moedaRn->conexao->carregar($moedaSaque);

                //Taxa da moeda saque
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moedaSaque->id);

            } else {
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);
            }
            
            if ($taxaMoeda != null) {
                //Verifica se a taxa é cobrada em outra moeda
                if(empty($taxaMoeda->idMoedaTaxa)){
                    //Taxa cobrada na mesma moeda
                    $taxa = $taxaMoeda->taxaTransferencia;
                    
                    //Verifica se o cliente tem taxa especial.
                    if($cliente->considerarTaxaTransferenciaCurrency == 1) {
                        $taxa = $cliente->taxaComissaoTransfenciaCurrency;
                    } else {
                        $taxa = $taxaMoeda->taxaTransferencia;
                    }
                    
                    $saldoEmconta = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false, true);
                    $valorTransferencia = number_format($valor + $taxa, $moeda->casasDecimais, ".", "");

                    if ($saldoEmconta < $valorTransferencia) {
                        throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                    }
                    
                    if ($taxa > $saldoEmconta) {
                        throw new \Exception($this->idioma->getText("valorDaTranferenciaMaior") . " " . number_format($taxa, $moeda->casasDecimais, ",", "") . " {$moeda->simbolo}");
                    }
                    
                } else {
                    //Taxa cobrada em outra moeda

                    //Verifica se taxa para essa transferencia não está vazia, caso sim, busca a taxa da moeda de transferencia;
                    if(!empty($taxaMoeda->taxaMoedaTransferencia) && $taxaMoeda->taxaMoedaTransferencia > 0){
                        $taxa = $taxaMoeda->taxaMoedaTransferencia;

                    } else {
                        $taxaMoedaTransf = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                        $taxa = $taxaMoedaTransf->taxaTransferencia;
                    }

                    //Verifica se o cliente tem taxa especial.
                    if($cliente->considerarTaxaTransferenciaCurrency == 1) {
                        $taxa = $cliente->taxaComissaoTransfenciaCurrency;
                    }

                    $moedaTaxa = new \Models\Modules\Cadastro\Moeda(Array("id" => $taxaMoeda->idMoedaTaxa));
                    $moedaRn->carregar($moedaTaxa);

                    $saldoMoedaTaxa = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moedaTaxa->id, false, true);

                    if($saldoMoedaTaxa < $taxa){
                        throw new \Exception("Você precisa ter em seu saldo " . number_format($taxa, $moedaTaxa->casasDecimais, ",", "") . " {$moedaTaxa->nome} para fazer o saque.");
                    }

                    $saldoEmconta = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false, true);
                    $valorTransferencia = number_format($valor, $moeda->casasDecimais, ".", "");

                    if ($saldoEmconta < $valorTransferencia) {
                        throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                    }
                }
            } else {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }

            if ($valor < 0 || $taxa < 0) {
                \Utils\Notificacao::notificar("Saque: valor ou taxa negativos.", true, true, $cliente);

                $cliente->status = 2;
                $clienteRn->alterarStatusCliente($cliente);
            }

            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);

            if (empty($cliente->pin)) {
                throw new \Exception($this->idioma->getText("precisaCadPin1"));
            }

            $email = $cliente->email;
            $telefone = $cliente->celular;
            $auth->idCliente = $cliente->id;
            $tipo = $cliente->tipoAutenticacao;

            if (empty($enderecoBitcoin)) {
                throw new \Exception($this->idioma->getText("enderecoInvalido"));
            }

            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth);

            if ($tipo == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoEmail1") . " " . $email . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($tipo == \Utils\Constantes::TIPO_AUTH_SMS){
                $json["mensagem"] = $this->idioma->getText("foiEnviadoSMS1") . " " . $telefone . " " . $this->idioma->getText("porFavorInsiraToken1");;
            }

            if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                $json["mensagem"] = $this->idioma->getText("useGoogle1");
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}