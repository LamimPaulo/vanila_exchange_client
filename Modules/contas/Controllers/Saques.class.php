<?php

namespace Modules\contas\Controllers;

class Saques {

    private $codigoModulo = "saques";
    private $idioma = null;

    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);

        $this->idioma = new \Utils\PropertiesUtils("saque", IDIOMA);
    }

    public function index($params) {
        try {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);
            
            if (\Utils\Geral::isCliente() && $cliente->utilizaSaqueDepositoBrl < 1) {
                \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
            }

            if($cliente->analiseCliente == 1 && $cliente->documentoVerificado != 1){
                \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_MEUSDADOS);
            }

            $clienteVerificado = $cliente->documentoVerificado == 1 ? true : false;

            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();

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

        //    $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
        //    $bancosEmpresa = $contaBancariaEmpresaRn->getIdsBancosEmpresa();

        //    $contasSemTaxaSaque = Array();
        //    foreach ($contasBancarias as $contaBancaria) {
        //        if (in_array($contaBancaria->idBanco, $bancosEmpresa)) {
        //            $contasSemTaxaSaque[] = $contaBancaria->id;
        //        }
        //    }

            if($configuracao->atarAtivo == 1){
                $atarContasRn = new \Models\Modules\Cadastro\AtarContasRn();
                $params["atar"] = $atarContasRn->saldoAtarDisponivel($configuracao);
            }

            $atarCliente = null;
            $atarClientesRn = new \Models\Modules\Cadastro\AtarClientesRn();
            $result = $atarClientesRn->conexao->listar(" id_cliente = {$cliente->id} ");

            if (sizeof($result) > 0) {
                foreach ($result as $atar) {
                    $atarCliente = $atar;
                }
            }

            if($cliente->documentoVerificado == 1){
                $params["operacao"] = $this->operacao($cliente, $configuracao);
                $params["contas"] = $contasBancarias;
            }

            $params["atar"] = $atarCliente;
            $params["moedaFavorita"] = \Utils\Criptografia::encriptyPostId($cliente->moedaFavorita);

            $params["moedas"] = $this->listar($cliente, $clienteVerificado);

            $params["configuracao"] = $configuracao;
            $params["comissao"] = $comissao;

            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_saques", $params);

    }

    private function operacao(\Models\Modules\Cadastro\Cliente &$cliente, \Models\Modules\Cadastro\Configuracao &$configuracao) {
        try {

            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contasBancarias = $contaBancariaRn->listar("id_cliente = {$cliente->id} AND ativo > 0", "id", null, null, true);    

            $arrayContas = null;

            $jsonContas = (object)null;;
            $jsonAtar = (object)null;;
            $jsonCointrade = (object)null;;

            $jsonContas->text = "Contas Bancárias";

            $jsonExchange = (object)null;
            $jsonExchange->text = "Exchange";

            //Atar
            if ($configuracao->atarAtivo == 1 ) {
                $jsonAtar->text = "Atar";

                $object = (object)null;
                $object->id = 2;
                $object->text = "Atar";
                $object->icone = IMAGES . "bancos/atar.png";

                $jsonAtar->children[] = $object;
                $arrayContas[] = $jsonAtar;
            }

            //Transf interna.
            $object = (object)null;;
            $object->id = 1;
            $object->text = $this->idioma->getText("transfInterna");
            $object->icone = IMAGES . "transferencia.png";

            $jsonCointrade->children[] = $object;
            $arrayContas[] = $jsonCointrade;

            foreach ($contasBancarias as $contaBancaria) {

                //Texto
                $tipoConta = strlen(\Utils\Validacao::limparString($contaBancaria->documentoCliente)) == 14 ? " / Empresa" : "";
                $digito = empty($contaBancaria->agenciaDigito) ? "" : "-{$contaBancaria->agenciaDigito}";
                $texto = "{$contaBancaria->banco->nome}    |    AG: {$contaBancaria->agencia}{$digito} / CC: {$contaBancaria->conta}-{$contaBancaria->contaDigito} {$tipoConta}";

                //Icone
                if(!empty($contaBancaria->banco->logo)){
                    $icone = IMAGES . "bancos/" . $contaBancaria->banco->logo;
                } else {
                    $icone = IMAGES . "currencies/BRL.png";
                }

                    $object = (object)null;;
                    $object->id = $contaBancaria->id;
                    $object->text = $texto;
                    $object->icone = $icone;

                    $jsonContas->children[] = $object;
            }

            $arrayContas[] = $jsonContas;

            return json_encode($arrayContas);
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function listar(\Models\Modules\Cadastro\Cliente &$cliente, $clienteVerificado) {
        try {

            $categoriaMoedaRn = new \Models\Modules\Cadastro\CategoriaMoedaRn();
            $categorias = $categoriaMoedaRn->conexao->listar("ativo = 1");
            $mostrarAbaDepositoReais = $clienteVerificado;
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();

            $arrayMoedas = null;
            $jsonMoedas = (object)null;;
            $jsonContas = (object)null;;
            $jsonStablecoin = (object)null;;

            $jsonMoedas->text = "Criptomoedas";

            if(empty($cliente->moedaFavorita)){
                $cliente->moedaFavorita = 2;
            }

            if ($cliente->documentoVerificado == 1) {

                $jsonStablecoin->text = "Stablecoins";
                $jsonContas->text = "Reais";

                $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                $contasBancarias = $contaBancariaRn->listar("id_cliente = {$cliente->id} AND ativo > 0", "id", null, null, true);

                $jsonAtar = (object)null;;
                $jsonCointrade = (object)null;;

                //Atar
                if ($configuracao->atarAtivo == 1 && $cliente->documentoVerificado == 1) {

                    $jsonExchange = (object)null;;
                    $jsonExchange->text = "Exchange";

                    $jsonAtar->text = "Atar";
                    $object = (object)null;;
                    $object->id = 2;
                    $object->op = \Utils\Criptografia::encriptyPostId(1); //Real
                    $object->text = "Atar";
                    $object->icone = IMAGES . "bancos/atar.png";
                    $object->tag = "real";
                    $object->tipo = "b";

                    $jsonAtar->children[] = $object;
                }

                if ($cliente->documentoVerificado == 1) {
                    //Transf interna.
                    $object = (object)null;;
                    $object->id = 1;
                    $object->op = \Utils\Criptografia::encriptyPostId(1); //Real 
                    $object->text = $this->idioma->getText("transfInterna");
                    $object->icone = IMAGES . "transferencia.png";
                    $object->tag = "real";
                    $object->tipo = "b";

                    $jsonCointrade->children[] = $object;
                }


                foreach ($contasBancarias as $contaBancaria) {

                    //Texto
                    $tipoConta = strlen(\Utils\Validacao::limparString($contaBancaria->documentoCliente)) == 14 ? " / Empresa" : "";
                    $digito = empty($contaBancaria->agenciaDigito) ? "" : "-{$contaBancaria->agenciaDigito}";
                    $texto = "{$contaBancaria->banco->nome}    |    AG: {$contaBancaria->agencia}{$digito} / CC: {$contaBancaria->conta}-{$contaBancaria->contaDigito} {$tipoConta}";

                    //Icone
                    if (!empty($contaBancaria->banco->logo)) {
                        $icone = IMAGES . "bancos/" . $contaBancaria->banco->logo;
                    } else {
                        $icone = IMAGES . "currencies/BRL.png";
                    }

                    $object = (object)null;;
                    $object->id = $contaBancaria->id;
                    $object->op = \Utils\Criptografia::encriptyPostId(1); //Real
                    $object->text = $texto;
                    $object->icone = $icone;
                    $object->tag = "real";
                    $object->tipo = "b";

                    $jsonContas->children[] = $object;
                }
            }

            foreach ($categorias as $categoriaCarteira) {
                if ($categoriaCarteira->id == 1 && $configuracao->statusTransferenciaBrl > 0 && $mostrarAbaDepositoReais) {
                //    $object = (object)null;
                //    $object->id = \Utils\Criptografia::encriptyPostId(1); //Real
                //    $object->text = "Bancos";
                //    $object->icone = IMAGES . "currencies/" . "BRL.png";
                //    $object->tipo = "b";

                //    $jsonContas->children[] = $object;

                } else if ($categoriaCarteira->id > 1) { 
                    $moedas = $moedaRn->conexao->listar("id_categoria_moeda = {$categoriaCarteira->id} AND ativo = 1 AND visualizar_saque = 1", "principal DESC, nome", null, null);
                    foreach ($moedas as $moeda) {

                        $object = (object)null;;
                        $object->id = \Utils\Criptografia::encriptyPostId($moeda->id);
                        $object->text = $moeda->simbolo . " - " . $moeda->nome;    
                        $object->tipo = "c";
                        $object->icone = IMAGES . "currencies/" . $moeda->icone;
                        $object->rede = (!empty($moeda->redesSaque) && !empty($moeda->idMoedaSaque)) ? true : false;
                        $object->selected = $cliente->moedaFavorita == $moeda->id ? true : false;

                        if($categoriaCarteira->id == 2){
                            if($cliente->documentoVerificado == 1){
                                $jsonStablecoin->children[] = $object;
                            }
                        } else {
                            $jsonMoedas->children[] = $object;
                        }
                    }
                }
            }

            if ($cliente->documentoVerificado == 1) {
                $arrayMoedas[] = $jsonContas;
                $arrayMoedas[] = $jsonAtar;
                $arrayMoedas[] = $jsonCointrade;
                $arrayMoedas[] = $jsonStablecoin;
            }

            $arrayMoedas[] = $jsonMoedas;

            return json_encode($arrayMoedas);

        } catch (\Exception $ex) {

            return null;
        }
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
            
            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
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
            <td><?php echo $saque->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ?></td>
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

            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
            }

            \Utils\ValidarSeguranca::validar($cliente);

            $saque->idContaBancaria = \Utils\Post::get($params, "idContaBancaria", 0);
            $saque->valorSaque = \Utils\Post::getNumeric($params, "valorReais", 0);
            $processo = \Utils\Post::getEncrypted($params, "processo", 0);
            $token = \Utils\Post::get($params, "token", null);
            $pin = \Utils\Post::get($params, "pin", null);

            if($cliente->documentoVerificado != 1){
                throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            }

            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);

            \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::SAQUE, $saque->valorSaque, true);            

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

    public function getTaxaTokens($params) {
        try {
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();

            $id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $rede = \Utils\Post::get($params, "rede", null);

            $moeda = \Models\Modules\Cadastro\MoedaRn::get($id);
            $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);

            if ($rede == \Utils\Constantes::REDE_ERC20) {

                //Taxa cobrada em outra moeda
                $moedaTaxa = \Models\Modules\Cadastro\MoedaRn::get($taxaMoeda->idMoedaTaxa);
                $json["simboloTaxa"] = $moedaTaxa->simbolo;

                //Verifica se taxa para essa transferencia não está vazia, caso sim, busca a taxa da moeda de transferencia;
                if (!empty($taxaMoeda->taxaMoedaTransferencia) && $taxaMoeda->taxaMoedaTransferencia > 0) {
                    $json["taxa"] = $taxaMoeda->taxaMoedaTransferencia;
                } else {
                    $taxaMoedaTransf = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                    $json["taxa"] = $taxaMoedaTransf->taxaTransferencia;
                }

                $json["somar"] = false;

            } else if ($rede == \Utils\Constantes::REDE_BEP20){

                //Moeda Saque
                $moedaSaque = new \Models\Modules\Cadastro\Moeda(Array("id" => $moeda->idMoedaSaque));
                $moedaRn->conexao->carregar($moedaSaque);

                //Taxa da moeda saque
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moedaSaque->id);

                //Verifica se taxa para essa transferencia não está vazia, caso sim, busca a taxa da moeda de transferencia;
                if (!empty($taxaMoeda->taxaMoedaTransferencia) && $taxaMoeda->taxaMoedaTransferencia > 0) {
                    $json["taxa"] = $taxaMoeda->taxaMoedaTransferencia;

                } else {
                    $taxaMoedaTransf = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                    $json["taxa"] = $taxaMoedaTransf->taxaTransferencia;

                    //Moeda BNB
                    $moedaTaxa = \Models\Modules\Cadastro\MoedaRn::get($taxaMoedaTransf->idMoeda);

                    $json["simboloTaxa"] = $moedaTaxa->simbolo;
                }
                $json["somar"] = false;

                if($moeda->id == $taxaMoeda->idMoedaTaxa){
                    $json["somar"] = true;
                }

            } else {
                throw new \Exception("Rede não encontrada.");
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function showDados($params) {
        try {
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $cliente = \Utils\Geral::getCliente();

            $taxa = "";

            $id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $tipo = \Utils\Post::get($params, "tipo", null);

            if (!in_array(strtolower($tipo), Array("b", "c"))) {
                throw new \Exception("Código inválido");
            }

            $moeda = \Models\Modules\Cadastro\MoedaRn::get($id);

            if($tipo == "c"){

                $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);

                 //Verifica se a taxa é cobrada em outra moeda
                if(empty($taxaMoeda->idMoedaTaxa)){
                    //Taxa cobrada na mesma moeda
                    $json["taxa"] = number_format($taxaMoeda->taxaTransferencia, 8, ".", "");
                    $json["simboloTaxa"] = null;

                    $msg = str_replace("{var}", $moeda->nome, $this->idioma->getText("taxasMoeda"));
                    $json["taxasMoeda"] = str_replace("{var1}", $moeda->nome, $msg);
                } else {
                    //Taxa cobrada em outra moeda
                    $moedaTaxa = \Models\Modules\Cadastro\MoedaRn::get($taxaMoeda->idMoedaTaxa);
                    $json["simboloTaxa"] = $moedaTaxa->simbolo;
                    
                    //Verifica se taxa para essa transferencia não está vazia, caso sim, busca a taxa da moeda de transferencia;
                    if(!empty($taxaMoeda->taxaMoedaTransferencia) && $taxaMoeda->taxaMoedaTransferencia > 0){
                        $json["taxa"] = $taxaMoeda->taxaMoedaTransferencia;
                        
                    } else {
                        $taxaMoedaTransf = $taxaMoedaRn->getByMoeda($taxaMoeda->idMoedaTaxa);
                        $json["taxa"] = $taxaMoedaTransf->taxaTransferencia;
                    }   
                    
                    $msg = str_replace("{var}", $moeda->nome, $this->idioma->getText("taxasMoeda"));
                    $json["taxasMoeda"] = str_replace("{var1}", $moedaTaxa->nome, $msg);
                }
                
                 $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                 $clienteRn->conexao->carregar($cliente);
                
                if($cliente->considerarTaxaTransferenciaCurrency == 1) {                  
                    $json["taxa"] = $cliente->taxaComissaoTransfenciaCurrency;
                }
               
                $json["simbolo"] = $moeda->simbolo;
                $json["useCarteira"] =  str_replace("{var}", $moeda->nome, $this->idioma->getText("insiraSomente"));
                $json["nomeMoeda"] =  $moeda->nome;
                $json["icone"] = IMAGES . "currencies/{$moeda->icone}";
            }
            
            if($moeda->coinType == \Utils\Constantes::REDE_ERC20){
                if(!empty($moeda->redesSaque)){
                    
                    $redesMsg = "Essa moeda aceita saque da(s) rede(s) ";
                    
                    $redes = json_decode($moeda->redesSaque);
                    
                    if($redes->ERC20){
                        $json["erc20"] = true;
                        $redesMsg = $redesMsg . \Utils\Constantes::REDE_ERC20;
                    } else {
                        $json["erc20"] = false;
                    }
                    
                    if($redes->BEP20){
                        $json["bep20"] = true;
                        
                        if($redes->ERC20){
                            $redesMsg = $redesMsg . " e ";
                            $redesMsg = $redesMsg . \Utils\Constantes::REDE_BEP20;
                        } else {
                            $redesMsg = $redesMsg . \Utils\Constantes::REDE_BEP20;
                        }
                    } else {
                        $json["bep20"] = false;
                    }
                    
                    $json["aceitaRede"] = $redesMsg;
                } else {
                    $json["aceitaRede"] = "";
                }
            } else {
                $json["aceitaRede"] = "";
            }
            
            $dados = \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::SAQUE, 0, false);

            $json["limiteInformacao"] = "";
            $json["limiteDisponivel"] = "";
            
            if(!empty($dados) && $dados["limiteMensal"] > 0){
                $json["limiteDisponivel"] = "Limite Disponível: " . number_format($dados["limiteDisponivelMensal"], $moeda->casasDecimais, ",", ".") . " de " . number_format($dados["limiteMensal"], $moeda->casasDecimais, ",", ".") . " {$moeda->simbolo}";
            
                if ($dados["limiteDisponivelMensal"] <= 0 ) {
                    $json["limiteInformacao"] = "Para solicitar o aumento do limite de saque, por favor, envie um e-mail para support@Exchangecx.com";
                } else {
                    $json["limiteInformacao"] = "";
                }
            } else {
                $json["limiteDisponivel"] = "";
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
            
            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
            }
            
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

            if(empty($cliente->id)){
                throw new \Exception("Cliente inválido.");
            }

            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $cripto = false;

            if($id > 1){ // Criptomoeda

                $contaCorrenteRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $dados = $contaCorrenteRn->calcularSaldoConta($cliente, $id, true, false);

                $cripto = true;
                $moeda->id = $id;
                $moedaRn->carregar($moeda);

                $json["saldoTotal"] = number_format($dados["saldo"] + $dados["bloqueado"], 8, ".", "")  . " " . $moeda->simbolo;
                $json["saldo"] = number_format($dados["saldo"], 8, ".", "") . " " . $moeda->simbolo;
                $json["saldoBloqueado"] = number_format($dados["bloqueado"], 8, ".", "") . " " . $moeda->simbolo;;
                $json["saldoDisp"] = number_format($dados["saldo"], 8, ".", "");
            } else { // Reais

                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldoReais = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true, false);
                $cripto = false;

                $json["saldoTotal"] = "R$ " . number_format($saldoReais["saldo"] + $saldoReais["bloqueado"], 2, ",", ".") ;
                $json["saldo"] = "R$ " . number_format($saldoReais["saldo"], 2, ",", ".") ;
                $json["saldoBloqueado"] = "R$ " . number_format($saldoReais["bloqueado"], 2, ",", ".")  ;
                $json["saldoDisp"] = number_format($saldoReais["saldo"], 2, ",", ".");
            }

            $json["cripto"] = $cripto;
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
