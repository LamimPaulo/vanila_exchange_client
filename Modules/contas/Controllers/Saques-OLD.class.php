<?php

namespace Modules\contas\Controllers;

class Saques {

    private $codigoModulo = "saques";
    private $idioma = null;

    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        $cliente = \Utils\Geral::getCliente();
        
        if (\Utils\Geral::isCliente() && $cliente->utilizaSaqueDepositoBrl < 1) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        
        $this->idioma = new \Utils\PropertiesUtils("saque", IDIOMA);
    }

    public function index($params) {

        try {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);


            # 30/07/2019 - Caique feature/t-33-info-mov-broker
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaTodasParidades(false);
            
            # end 

            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $bancos = $contaBancariaRn->getBancosEmUso();
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $todosOsBancos = $bancoRn->conexao->listar(null, "nome");

            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            $comissao = 0;
            if ($cliente->considerarTaxaSaqueCliente) {
                $comissao = $cliente->taxaComissaoSaque;
            } else {
                $comissao = $configuracao->taxaSaque;
            }
                        
            $contasBancarias = $contaBancariaRn->listar("id_cliente = {$cliente->id} AND ativo > 0", "id", null, null, true);            

            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $bancosEmpresa = $contaBancariaEmpresaRn->getIdsBancosEmpresa();
            
            $contasSemTaxaSaque = Array();
            foreach ($contasBancarias as $contaBancaria) {
                if (in_array($contaBancaria->idBanco, $bancosEmpresa)) {
                    $contasSemTaxaSaque[] = $contaBancaria->id;
                }
            }
            
            
            if($configuracao->atarAtivo == 1){                
                $atarContasRn = new \Models\Modules\Cadastro\AtarContasRn();                
                $params["atar"] = $atarContasRn->saldoAtarDisponivel($configuracao);
            }

            
            
            $params["contasSemTaxa"] = $contasSemTaxaSaque;
            $params["contas"] = $contasBancarias;
            $params["configuracao"] = $configuracao;
            $params["cliente"] = $cliente;
            $params["bancos"] = $bancos;
            $params["todosOsBancos"] = $todosOsBancos;
            $params["comissao"] = $comissao;
            $params["tarifaTed"] = $configuracao->tarifaTed;            
            $params["paridades"]  = $paridades;
            

            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_saques", $params);

    }
    
    public function listar($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $categoriaMoedaRn = new \Models\Modules\Cadastro\CategoriaMoedaRn();
            $categorias = $categoriaMoedaRn->conexao->listar("ativo = 1");
            $mostrarSaqueReais = $clienteRn->clienteVerificado($cliente);
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();            
            
            $moedasStablecoin = $moedaRn->conexao->listar("id_categoria_moeda = 2 AND ativo = 1 AND status_saque = 1", null, null, null);
            ob_start();
            foreach ($categorias as $categoriaCarteira) {
                if($categoriaCarteira->id == 1){
                    if($configuracao->statusDepositoBrl > 0 && $mostrarSaqueReais){ ?>
                       <a href="javascript:ativaUltimosSaques('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("bancos") ?></h5></a>
                    <?php }
                } else {
                    if($categoriaCarteira->id == 2 && sizeof($moedasStablecoin) > 0){ ?>
                      <a href="javascript:ativaUltimosSaques('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("stablecoins") ?></h5></a>  
                   <?php }
                    if($categoriaCarteira->id == 3){ ?>
                      <a href="javascript:ativaUltimosSaques('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("criptomoeda") ?></h5></a>  
                   <?php }
                }
                ?>   
                <ul class="folder-list m-b-md" style="padding: 0">
                <?php
                if ($categoriaCarteira->id == 1 && $configuracao->statusDepositoBrl > 0 && $mostrarSaqueReais) { ?>
                        <li>
                            <a  href="javascript:showCoin('<?php echo null ?>', 'b');"> 
                                <img src="<?php echo IMAGES ?>currencies/BRL.png" style="width: 20px; height: 20px;" /> Real
                            </a>
                        </li>
                        <?php
                    
                } else if ($categoriaCarteira->id > 1) { 
                    $moedas = $moedaRn->conexao->listar("id_categoria_moeda = {$categoriaCarteira->id} AND ativo = 1 AND status_saque = 1", "principal DESC, nome", null, null);
                    foreach ($moedas as $moeda) {
                        ?>
                        <li>
                            <a  href="javascript:showCoin('<?php echo \Utils\Criptografia::encriptyPostId($moeda->id) ?>', 'c')"> 
                                <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="width: 20px; height: 20px;" /> <?php echo $moeda->nome ?>
                            </a>
                        </li>
                        <?php
                    }
                } 
                ?>
                </ul>
                <?php
                
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function listarReais($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $data = \Utils\Post::get($params, "data", "semana");
            $idBanco = \Utils\Post::get($params, "idContaBancaria", 0);
            $status = \Utils\Post::get($params, "status", "T");
            $filtro = \Utils\Post::get($params, "filtro", null);
            $nresultado = \Utils\Post::get($params, "nresultado", "T");            
            
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

            $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
            $lista = $saqueRn->filtrar($cliente->id, $dataInicial, $dataFinal, $idBanco, $status, $filtro, $nresultado);

            $dados = $this->htmlLista($lista);

            $json["html"] = $dados["html"];
            $json["popover"] = $dados["popover"];

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function htmlLista($lista) {
        $popover = Array();
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $saque) {
                $this->itemHtmlLista($saque);
            }
        } else {
            ?>
            <tr> 
                <td colspan='12' class='text-center'>
                   <?php echo $this->idioma->getText("nenhumSaqueC") ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "popover" => $popover);
    }

    public function itemHtmlLista(\Models\Modules\Cadastro\Saque $saque) {

        ?>


        <tr style='text-align: center; background-color: #fff'>

            <td><?php echo $saque->id; ?></td>
            <td><?php echo $saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
            <td>
                <?php if($saque->contaBancaria != null){  ?>
                   <a tabindex="0" class="saque-motivo" role="button" data-controle='<?php echo $saque->id ?>' data-motivo='<?php echo $this->idioma->getText("codC") ?>: <?php echo $saque->contaBancaria->banco->codigo ?> <br> <?php echo $this->idioma->getText("bancoC") ?>: <?php echo $saque->contaBancaria->banco->nome ?>
                      <br><?php echo $this->idioma->getText("agenciaC") ?>: <?php echo $saque->contaBancaria->agencia . ($saque->contaBancaria->agenciaDigito != null ? "-".$saque->contaBancaria->agenciaDigito : "")?> <br><?php echo $this->idioma->getText("contaC") ?>: <?php echo $saque->contaBancaria->conta . ($saque->contaBancaria->contaDigito != null ? "-".$saque->contaBancaria->contaDigito : "") ?>'
                      data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                      <i style="font-size: 15px;" class="fa fa-info-circle"></i>
                </a> 
                <?php }?> 
            </td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($saque->valorSaque, 2, ",", ".") ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($saque->valorComissao, 2, ",", ".") ?></td>
            <td><?php echo number_format($saque->taxaComissao, 2, ",", ".") ?>%</td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($saque->tarifaTed, 2, ",", ".") ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($saque->valorSacado, 2, ",", ".") ?></td>
            <td>
                <?php if (!empty($saque->comprovante)) { ?>
                <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/" . \Utils\Criptografia::encriptyPostId($saque->comprovante) ?>" target="_BLANK_<?php echo $saque->id ?>">
                        <i class="fa fa-file-archive-o"></i>
                    </a>
                <?php } else {
                    echo "-";
                }?>
            </td>
            <td>
                <?php if ($saque->aceitaNota && !empty($saque->notaFiscal)) { ?>
                    <a href="<?php echo URLBASE_CLIENT . UPLOADS . $saque->notaFiscal ?>" target="_BLANK_<?php echo $saque->id ?>">
                        <i class="fa fa-file-pdf-o"></i> <?php echo $this->idioma->getText("cliqueDownloadC") ?>
                    </a>
                <?php } ?>
            </td>

            <td>
                <?php echo $saque->getStatus(); ?>
                <?php
                if ($saque->status == \Utils\Constantes::STATUS_SAQUE_CANCELADO && !empty($saque->motivoCancelamento)) {
                    ?>
                    <a tabindex="0" class="saque-motivo" role="button" data-controle='<?php echo $saque->id ?>' data-motivo='<?php echo $saque->motivoCancelamento ?>' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px ">
                         <i style="font-size: 15px;"class="fa fa-info-circle"></i>
                    </a>
                    <?php } ?>
            </td>
        </tr>

        <?php
    }

    public function solicitar($params) {

        if (!\Utils\Geral::isCliente()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_SAQUES);
        }

        try {

            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_SAQUES, \Utils\Constantes::CADASTRAR)) {
                throw new \Exception($this->idioma->getText("naoTemPermissaoSaqueC"));
            }
            $cliente = \Utils\Geral::getCliente();

            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("necessarioLogadoC"));
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);

            $comissao = 0;
            if ($cliente->considerarTaxaSaqueCliente) {
                $comissao = $cliente->taxaComissaoSaque;
            } else {
                $comissao = $configuracao->taxaSaque;
            }

            $id = str_replace("ID_SOLICITACAO-", "", \Utils\SQLInjection::clean(base64_decode(\Utils\Post::get($params, 0, 0))));

            $saque = new \Models\Modules\Cadastro\Saque(Array("id" => $id));
            if ($saque->id > 0) {
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saqueRn->conexao->carregar($saque);
            }

            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();

            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contasBancarias = $contaBancariaRn->listar("id_cliente = {$cliente->id}", "id", NULL, null, true);


            $params["contas"] = $contasBancarias;
            $params["saldo"] = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
            $params["saque"] = $saque;
            $params["comissao"] = $comissao;
            $params["tarifaTed"] = $configuracao->tarifaTed;

            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("solicitar_saque", $params);
    }

    public function getDadosContaBancaria($params) {
        try {
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->id = \Utils\Post::get($params, "idContaBancaria", 0);

            try {
                $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                $contaBancariaRn->carregar($contaBancaria, true, true);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("contaNaoEncontradaC"));
            }
            $contaBancaria->agencia = "".$contaBancaria->agencia.($contaBancaria->agenciaDigito != null ? "-".$contaBancaria->agenciaDigito : "");
            $contaBancaria->conta = "".$contaBancaria->conta.($contaBancaria->contaDigito != null ? "-".$contaBancaria->contaDigito : "");
            $contaBancaria->tipoConta = ($contaBancaria->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? $this->idioma->getText("contaCorrenteC") : $this->idioma->getText("contaPoupancaC"));
            $json["conta"] = $contaBancaria;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    
    public function salvar($params) {
        try {
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $saque = new \Models\Modules\Cadastro\Saque();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            $saque->idContaBancaria = \Utils\Post::get($params, "idContaBancaria", 0);
            $saque->valorSaque = \Utils\Post::getNumeric($params, "valorReais", 0);            
            $processo = \Utils\Post::getEncrypted($params, "processo", 0);
            $token = \Utils\Post::get($params, "token", null);
            $pin = \Utils\Post::get($params, "pin", null);
            
            if($cliente->documentoVerificado != 1){
                throw new \Exception("Por favor, informe seu CPF no menu Meu Pefil, aba Meus Dados.");
            }
            
            if($processo == 1){
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
                
                if($saldo < $saque->valorSaque){
                    throw new \Exception($this->idioma->getText("saldoInsuficiente"));
                }                
                
                $auth = new \Models\Modules\Cadastro\Auth();
                $auth->idCliente = $cliente->id;                
                $authRn->salvar($auth);
                
                $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
                $contaBancaria->id = $saque->idContaBancaria;
                
                try {
                    $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                    $contaBancariaRn->carregar($contaBancaria, true, true);
                } catch (\Exception $ex) {
                    throw new \Exception($this->idioma->getText("contaNaoEncontradaC"));
                }
                
                $comissao = 0;
                if ($cliente->considerarTaxaSaqueCliente) {
                    $comissao = $cliente->taxaComissaoSaque;
                } else {
                    $comissao = $configuracao->taxaSaque;
                }
                
                $valorSaque = ($saque->valorSaque - ($saque->valorSaque * ($comissao / 100))) - ($configuracao->tarifaTed);
                
                
                $json["nomeTitular"] = $contaBancaria->nomeCliente;
                $json["documentoCPF"] = $contaBancaria->documentoCliente;
                $json["bancoInf"] = $contaBancaria->banco->codigo . " - " . $contaBancaria->banco->nome;
                $json["agencia"] = $contaBancaria->agencia . " - " . $contaBancaria->agenciaDigito;
                $json["conta"] = $contaBancaria->conta . " - " . $contaBancaria->contaDigito;
                
                $json["valorSolicitado"] = "R$ " . number_format($saque->valorSaque, 2, ",", ".");
                $json["valorSaque"] = "R$ " . number_format($valorSaque, 2, ",", ".");
                
                if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoEmail1") . " " . $cliente->email . " " . $this->idioma->getText("porFavorInsiraToken1");
                } 

                if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS){
                    $json["mensagem"] = $this->idioma->getText("foiEnviadoSMS1") . " " . $cliente->celular . " " . $this->idioma->getText("porFavorInsiraToken1");;
                }

                if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                    $json["mensagem"] = $this->idioma->getText("useGoogle1");
                }                
                
            } else if($processo == 2){
                
                if(empty($token)){
                    throw new \Exception($this->idioma->getText("tokenInvalido"));
                }
                
                if(empty($pin)){
                   throw new \Exception($this->idioma->getText("pinInvalido")); 
                }
                
                if($cliente->pin != $pin){
                    throw new \Exception($this->idioma->getText("pinInvalido"));
                }
                
                $authRn->validar($token, $cliente);
                
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saqueRn->solicitarSaque($saque);
                
                $json["mensagem"] = $this->idioma->getText("saqueSucesso");
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    
    public function ultimosSaques($params) {
        try{
            $cliente = \Utils\Geral::getCliente();
            $categoria = \Utils\Post::getEncrypted($params, "categoria", null);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $dados = $contaCorrenteBtcRn->ultimosSaquesBtcBrl($cliente, 'T', $categoria);
            
            $json["html"] = $this->htmlListaUltimosSaques($dados);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaUltimosSaques($dados) {        
        ob_start();

        if (sizeof($dados) > 0) {
            foreach ($dados as $contaCorrenteBtc) {
                $this->itemListaUltimosSaques($contaCorrenteBtc);                
            }
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function itemListaUltimosSaques($dados) {
        $data = new \Utils\Data(date($dados["data"]));
        $hora = new \Utils\Data(date($dados["data"])); 
        
        if($dados["categoria"] > 1){
        $moeda = new \Models\Modules\Cadastro\Moeda();
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moeda->id = $dados["idMoeda"];
        $moedaRn->carregar($moeda);
        }
        
        ?>
        <tr style="text-decoration: <?php echo $dados["descricao"] == \Utils\Constantes::STATUS_SAQUE_CANCELADO ? "line-through;" : "none;" ?>">
            <td class="text-center"><?php echo $data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
            <td class="text-center"><?php echo $hora->formatar(\Utils\Data::FORMATO_HORA) ?></td>
            <td class="text-center"><?php echo $dados["moeda"] ?></td>
            <td class="text-center">
                <?php if ($dados["categoria"] == "1") { 
                    if($dados["moeda"] == "Atar"){
                        echo "R$ " . number_format($dados["volume"], 2, ",", ".");
                    } else {?>
                    R$ <?php echo number_format($dados["volume"], 2, ",", ".")?>
                <?php } 
                
                    } else { ?>
                <?php echo number_format($dados["volume"], 8, ",", ".") . " " . $moeda->simbolo ?> 
                <?php } ?>                
            </td>
            <td style='text-align: center;'>
                <?php if ($dados["categoria"] == "1") {
                    if($dados["moeda"] == "Atar"){
                        echo "-";
                    } else {
                    if($dados["descricao"] == \Utils\Constantes::STATUS_SAQUE_CANCELADO) { ?>
                                 -
                <?php } else if($dados["descricao"] == \Utils\Constantes::STATUS_SAQUE_PENDENTE){ ?>
                            <i style="color: #333 !important;" class="fa fa-file-pdf-o"></i>
                <?php } else {
                            if(!empty($dados["comprovante"])){?>
                        <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/". \Utils\Criptografia::encriptyPostId($dados["comprovante"]) ?>" target="COMPROVANTE">
                            <i style="color: #d00000 !important;" class="fa fa-file-pdf-o"></i>
                        </a>
                            <?php  } else { echo "-";} } }?>                
                <?php } else {
                    if(!empty($dados["comprovante"])){ ?>
                        <a href="<?php echo $moeda->getUrlExplorer($dados["comprovante"])?>" target="EXPLORER"><i style="color: #1c84c6 !important;" class="fa fa-link"></i></a>
                <?php } else {
                            if($dados["direcao"] == "I") { 
                                echo "Transf. Interna";
                            } else {
                                echo "-";
                            }                                
                }
                
                            }?>
            </td>
            <td><?php if ($dados["categoria"] == "1") {
                    if($dados["moeda"] == "Atar"){
                        echo $this->idioma->getText("confirmado"); 
                    } else {
                    if($dados["descricao"]  == \Utils\Constantes::STATUS_SAQUE_CONFIRMADO){
                       echo $this->idioma->getText("confirmado"); 
                    } else if ($dados["descricao"]  == \Utils\Constantes::STATUS_SAQUE_PENDENTE) {
                        echo $this->idioma->getText("pendente");
                    } else if ($dados["descricao"]  == \Utils\Constantes::STATUS_SAQUE_CANCELADO) {
                        echo $this->idioma->getText("cancelado");
                    }
                    
                    }
                } else { 
                    if(($dados["autorizada"] == "1" && $dados["executada"] == "1") || ($dados["autorizada"] == "1" && $dados["origem"] == "0")){ ?>
                      <?php echo $this->idioma->getText("confirmado") ?> 
                    <?php } else if ($dados["autorizada"] == "1" || $dados["autorizada"] == "0" && $dados["executada"] == "0") { ?>
                        <?php echo $this->idioma->getText("pendente") ?> 
                    <?php } ?>
           <?php }?></td>
        </tr>
        <?php
    }
    
    public function showDados($params) {
        try {
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            
            $taxa = "";
            
            $id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $tipo = \Utils\Post::get($params, "tipo", null);
          
            if (!in_array(strtolower($tipo), Array("b", "c"))) {
                throw new \Exception("Código inválido");
            }
            
            if($tipo == "c"){
                $moeda = \Models\Modules\Cadastro\MoedaRn::get($id);
                $taxa = $taxaMoedaRn->getByMoeda($moeda->id);
                
                $json["taxa"] =  number_format($taxa->taxaTransferencia, 8, ".", "");
               
                $json["useCarteira"] =  str_replace("{var}", $moeda->nome, $this->idioma->getText("insiraSomente"));
                $json["nomeMoeda"] =  $moeda->nome;
                $json["simbolo"] = $moeda->simbolo;
                $json["icone"] = IMAGES . "currencies/{$moeda->icone}";
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function saldoClienteCripto($params) {
        try{
            $id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $saldoRn = new \Models\Modules\Cadastro\SaldoClienteRn();
            $cliente = \Utils\Geral::getCliente();
            
            $dados = $saldoRn->getSaldo($cliente->id, $id, true, false);
            
            ob_start(); ?>

            <td>R$ <?php echo number_format($dados["saldo"] + $dados["bloqueado"], 8, ",", ".") ?></td>
            <td>R$ <?php echo number_format($dados["saldo"], 8, ",", ".") ?></td>
            <td>R$ <?php echo number_format($dados["bloqueado"], 8, ",", ".") ?></td>
            
        <?php
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["saldoDisp"] = number_format($dados["saldo"], 8, ",", ".");
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    public function saldoCliente($params) {
        try{
            $cliente = \Utils\Geral::getCliente();            
            $id = \Utils\Post::getEncrypted($params, "id", 1);
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $cripto = false;
            
            if($id > 1){ // Criptomoeda
                
                $contaCorrenteRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $dados = $contaCorrenteRn->calcularSaldoConta($cliente, $id, true, false);
                
                $cripto = true;
                $moeda->id = $id;
                $moedaRn->carregar($moeda);
                
                ob_start(); ?>
            <td><?php echo number_format($dados["saldo"] + $dados["bloqueado"], 8, ".", "")  . " " . $moeda->simbolo ?></td>
            <td><?php echo number_format($dados["saldo"], 8, ".", "") . " " . $moeda->simbolo ?></td>
            <td><?php echo number_format($dados["bloqueado"], 8, ".", "") . " " . $moeda->simbolo ?></td>            
        <?php
            $json["saldoDisp"] = number_format($dados["saldo"], 8, ".", "");
            } else { // Reais  
                
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldoReais = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true, false);
                $cripto = false;
                
                ob_start(); ?>
            <td>R$ <?php echo number_format($saldoReais["saldo"] + $saldoReais["bloqueado"], 2, ",", ".") ?></td>
            <td>R$ <?php echo number_format($saldoReais["saldo"], 2, ",", ".") ?></td>
            <td>R$ <?php echo number_format($saldoReais["bloqueado"], 2, ",", ".") ?></td>            
        <?php
            $json["saldoDisp"] = number_format($saldoReais["saldo"], 2, ",", ".");
            }
            
            $html = ob_get_contents();
            ob_end_clean();   
            
            $json["cripto"] = $cripto;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }

    
    public function findByEmail($params) {
        try{   
            
            $cliente = \Utils\Geral::getCliente();
            $email = \Utils\Post::get($params, "email", null);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            if(empty($email) || $email == $cliente->email){
                throw new \Exception("E-mail inválido");
            }
            
            $clienteDestino = $clienteRn->getByEmail($email);
            
            if($clienteDestino ==! null){
                $json["nome"] = $clienteDestino->nome;
                $json["found"] = true;
            } else {
                $json["found"] = false;
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    

}
