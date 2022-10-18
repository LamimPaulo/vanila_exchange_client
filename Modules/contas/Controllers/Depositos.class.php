<?php

namespace Modules\contas\Controllers;

require_once getcwd() . '/Library/Utils/File.class.php';
require_once getcwd() . '/Library/Utils/Canvas.class.php';
require_once getcwd() . '/Library/Utils/Texto.class.php';

class Depositos {
    private $idioma = null;

    public function __construct(&$params) {
        $this->idioma = new \Utils\PropertiesUtils("index_carteiras", IDIOMA);

    }

    public function index($params) {
        
        try {
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            $cliente = \Utils\Geral::getCliente();
            
            if(\Utils\Geral::isCliente()){
                $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
                $carteiras = $carteiraRn->conexao->listar("id_cliente = {$cliente->id} AND id_moeda = {$moeda->id} AND inutilizada < 1", "principal DESC, data");
            } else {
                $carteiras = array();
            }
            if (sizeof($carteiras) > 0) {
                $params["carteira"] = $carteiras->current();
            } else {
                $params["carteira"] = null;
            }
            
            $di = new \Utils\Data(date("d/m/Y H:i:s"));
            $di->subtrair(0, 0, 0, 24);
            $df = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $processosDeposito = $depositoRn->calcularQuantiadeHorasMediasValidacaoDeposito($di, $df);
            
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancarias = $contaBancariaEmpresaRn->listar("ativo > 0", "id", NULL, null, true);
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $comissao = 0;
            $comissaoBoleto = $configuracao->taxaDepositoBoleto;
            $tarifaBoleto = $configuracao->tarifaDepositoBoleto;
            if ($cliente->considerarTaxaDepositoCliente) {
                $comissao = $cliente->taxaComissaoDeposito;
            } else {
                $comissao = $configuracao->taxaDeposito;
            }
            
            

            $params["configuracao"] = $configuracao;
            $params["comissaoBoleto"] = $comissaoBoleto;
            $params["tarifaBoleto"] = $tarifaBoleto;
            $params["contas"] = $contasBancarias;
            $params["comissao"] = $comissao;
            $params["sucesso"] = true;
            $params["processoDeposito"] = $processosDeposito;
        } catch (\Exception $ex) {
            
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_depositos", $params);
    }

    public function listar($params) {
        try {
            $idCliente = \Utils\Geral::getCliente()->id;       
            $dataInicial = \Utils\Post::getData($params, "dataInicialReal", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinalReal", null, "23:59:59");
            $idContaBancariaEmpresa = \Utils\Post::get($params, "idContaBancariaEmpresa", 0);
            $data = \Utils\Post::get($params, "data", "todos");
            $status = \Utils\Post::get($params, "status", "T");
            $tipoDeposito = \Utils\Post::get($params, "tipoDeposito", "Q");
            $filtro = \Utils\Post::get($params, "filtro", null);
            $nresultado = \Utils\Post::get($params, "nresultadoReal", "T");
            $boleto = \Utils\Post::getBoolean($params, "boleto", false);

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
                    $dataInicial = null;
                    $dataFinal = null;
                    break;
            }
            
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $lista = $depositoRn->filtrar($idCliente, $dataInicial, $dataFinal, $idContaBancariaEmpresa, $tipoDeposito, $status, $filtro, $nresultado, $boleto);
            
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
            foreach ($lista as $deposito) {
                $this->itemHtmlLista($deposito);
            }
        } else {
            ?>
            <tr> 
                <td colspan="7" style='text-align: center'>
                       <?php echo $this->idioma->getText("nenhumDepositoC") ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "popover" => $popover);
    }

    public function itemHtmlLista(\Models\Modules\Cadastro\Deposito $deposito) {
       
        ?>
            
            <tr style="text-decoration: <?php echo $deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO ? "line-through;" : "none;" ?>" class="tr-depositos-real"  >

                <td class='text-center'>
                    <?php echo $deposito->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ?>
                </td>
                
                <td class='text-center'>
                    <?php if ($deposito->tipoDeposito === \Utils\Constantes::GERENCIA_NET) {
                        if($deposito->statusGateway == \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                            echo "Gerando Boleto";
                        } else if($deposito->statusGateway == \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO) {?>  
                            &nbsp;<a class="boleto-codigo" tabindex="0" role="button" data-motivo="<?php echo $deposito->barcodeGateway ?>"><i class="fa fa-eye"></i></a> | 
                            <a id="barCode" data-clipboard-target="#codigoCopiar<?php echo $deposito->id ?>"><i class="fa fa-copy"></i></a> | <a href="<?php echo $deposito->linkGateway ?>" target="_BLANK"><i class="fa fa-download"></i></a>
                            <input style="position: absolute; height: 0; opacity: 0.0; overflow: hidden;" id="codigoCopiar<?php echo $deposito->id ?>" value="<?php echo $deposito->barcodeGateway ?>"/>
                    <?php } 
                        } else  { ?>
                    <a tabindex="0" class="deposito-motivo" role="button" data-controle='<?php echo $deposito->id?>' data-motivo='Cod.: <?php echo $deposito->contaBancariaEmpresa->banco->codigo ?> 
                       <br> <?php echo $this->idioma->getText("bancoC") ?> <?php echo $deposito->contaBancariaEmpresa->banco->nome?>
                       <br> <?php echo $this->idioma->getText("agenciaC") ?> <?php echo $deposito->contaBancariaEmpresa->agencia?> <br> <?php echo $this->idioma->getText("contaC") ?> <?php echo $deposito->contaBancariaEmpresa->conta?> 
                       <br> <?php echo $this->idioma->getText("tipoC") ?> <?php echo $deposito->getTipoDeposito()?>' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                            <i style="font-size: 15px;" class="fa fa-info-circle"></i>
                    </a>
                    <?php } ?>
                </td>
                
                <td class='text-center'>
                    R$ <?php echo number_format($deposito->valorDepositado, 2, ",", ".") ?>
                </td>
                
                <?php if($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET) { ?>
                <td class='text-center'>
                    R$ <?php echo number_format($deposito->valorTarifa, 2, ",", ".") ?>
                </td>
                <?php } ?>
                
                <td class='text-center'>
                    <?php echo number_format($deposito->taxaComissao, 2, ",", ".") ?>%
                </td>
                
                <td class='text-center'>
                    R$ <?php echo number_format($deposito->valorCreditado, 2, ",", ".")  ?>
                </td>
                
                <?php if($deposito->tipoDeposito != \Utils\Constantes::GERENCIA_NET) { ?>
                <td class='text-center'>
                    <?php if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) { ?>
                            <i style="color: #333 !important;" class="fa fa-file-pdf-o"></i>
                        <?php } else {
                    if (!empty($deposito->comprovante)) { ?>
                            <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/" . \Utils\Criptografia::encriptyPostId($deposito->comprovante) ?>" target="_BLANK_<?php echo $deposito->id?>">
                            <i style="color: #d00000 !important;" class="fa fa-file-pdf-o"></i>
                        </a>
                    <?php }
                        }?>
                </td>
                <?php } ?>
                
                <td class='text-center'>
                    
                    <?php
                    if ($deposito->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO && !empty($deposito->motivoCancelamento)) {
                        ?>
                        <?php echo $deposito->getStatus(); ?>
                        <a tabindex="0" class="deposito-motivo" role="button" data-controle='<?php echo $deposito->id?>' data-motivo='<?php echo $deposito->motivoCancelamento ?>' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                            <i style="font-size: 15px;"class="fa fa-info-circle"></i>
                        </a>
                        <?php
                    } else {
                        echo $deposito->getStatus();
                    }
                    ?>
                </td>

            </tr>
        <?php
    }

    public function solicitar($params) {
        
        if (!\Utils\Geral::isCliente()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DEPOSITOS);
        }
        
        try {
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("necessarioLogadoC"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_DEPOSITOS, \Utils\Constantes::CADASTRAR)) {
                throw new \Exception($this->idioma->getText("permissaoDepositoC"));
            }
            
            $comissao = 0;
            if ($cliente->considerarTaxaDepositoCliente) {
                $comissao = $cliente->taxaComissaoDeposito;
            } else {
                $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
                $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
                $configuracaoRn->conexao->carregar($configuracao);
                $comissao = $configuracao->taxaDeposito;
            }
            
            $id = \Utils\SQLInjection::clean(str_replace("ID_SOLICITACAO-", "", base64_decode(\Utils\Get::get($params, 0, 0)))); 
            
            $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $id));
            if ($deposito->id > 0) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->conexao->carregar($deposito);
            }
            
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancarias = $contaBancariaEmpresaRn->listar("ativo > 0", "id", NULL, null, true);
            $params["contas"] = $contasBancarias;
            $params["deposito"] = $deposito;
            $params["comissao"] = $comissao;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("solicitar_deposito", $params);
    }

    public function getDadosContaBancaria($params) {
        try {
            $contaBancariaEmpresa = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
            $contaBancariaEmpresa->id = \Utils\Post::get($params, "idContaBancariaEmpresa", 0);
            
            try {
                $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
                $contaBancariaEmpresaRn->carregar($contaBancariaEmpresa, true, true);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("contaNaoEncontradaC"));
            }
            $contaBancariaEmpresa->tipoConta = ($contaBancariaEmpresa->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? $this->idioma->getText("contaCorrenteC") : $this->idioma->getText("contaPoupancaC"));
            
            
            $json["conta"] = $contaBancariaEmpresa;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function salvar($params) {
        try {
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            // if($cliente->documentoVerificado != 1){
            //     throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            // }

            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);

            \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::ENTRADA, $valor, true);

            $deposito->comprovante = \Utils\File::get($params, "comprovante", "", Array(), $cliente, "depositos", false);
            $deposito->id = \Utils\Post::get($params, "id", 0);

            $deposito->tipoDeposito = \Utils\Post::get($params, "tipoDeposito", null);
            $deposito->idContaBancariaEmpresa = \Utils\Post::getEncrypted($params, "idContaBancariaEmpresa", 0);
            $deposito->valorDepositado = $valor;

            if ($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET) {
                $deposito->idContaBancariaEmpresa = 15052774463553;
                $deposito->comprovante = null;

                if($valor > $configuracao->valorMaxBoleto){
                    throw new \Exception("Valor máximo permitido de R$ " . number_format($configuracao->valorMaxBoleto, 2, ",", ".") . ".");
                }

                if(empty($cliente->documento)){
                    throw new \Exception("Documento CPF inválido. Atualize o CPF no menu Meu Perfil, aba Meus Dados.");
                } else {
                    if(!\Utils\Validacao::cpf($cliente->documento)){
                        throw new \Exception("Documento CPF inválido. Atualize o CPF no menu Meu Perfil, aba Meus Dados.");
                    }
                }

                $nomeCliente = \Utils\Validacao::limparString($cliente->nome, false);

                if(strlen($nomeCliente) < 8){
                    throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
                }

                if(!\Utils\Validacao::verificarNomeCompleto($nomeCliente)){
                    throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
                }

            } else {
                if (empty($deposito->comprovante)) {
                    throw new \Exception($this->idioma->getText("arqInvalidoC"));
                }
            }

            if($deposito->valorDepositado < $configuracao->valorMinimoDepositoReais){
                $valorMinimo = "R$ " . number_format($configuracao->valorMinimoDepositoReais, 2, ",", ".");
                throw new \Exception(str_replace("{var1}", $valorMinimo, $this->idioma->getText("valorMinimoDeposito")));
            }

            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositoRn->solicitarDeposito($deposito);

            if ($deposito->tipoDeposito == \Utils\Constantes::GERENCIA_NET) {
                $json["boleto"] = true;
            } else {
                $json["boleto"] = false;
            }

            $json["deposito"] = \Utils\Criptografia::encriptyPostId($deposito->id);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function aprovar($params) {
        if (!\Utils\Geral::isUsuario() || \Utils\Geral::getLogado()->tipo != \Utils\Constantes::ADMINISTRADOR) {
            \Utils\Geral::redirect(URLBASE_CLIENT . "contas/depositos");
        }

        try {

            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_DEPOSITOS, \Utils\Constantes::EDITAR)) {
                throw new \Exception($this->idioma->getText("permissaoAlteraDepC"));
            }

            $id = \Utils\SQLInjection::clean(str_replace("ID_SOLICITACAO-", "", base64_decode(\Utils\Get::get($params, 0, 0))));

            $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $id));
            if ($deposito->id > 0) {
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->carregar($deposito, true, true, false, false);
            } else {
                throw new \Exception($this->idioma->getText("identicacaoInvalidaC"));
            }
            
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $deposito->idCliente));
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contasBancarias = $contaBancariaRn->listar("id_cliente = {$cliente->id}", "id", NULL, null, true);
            
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancariasEmpresa = $contaBancariaEmpresaRn->listar("ativo > 0", "id", NULL, null, true);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estorno = $estornoRn->getByDeposito($deposito);
            if ($estorno != null) {
                $estornoRn->carregar($estorno, false, true, false, false, false, false);
            }
            $params["estorno"] = $estorno;
            $params["contasEmpresa"] = $contasBancariasEmpresa;
            $params["contas"] = $contasBancarias;
            $params["deposito"] = $deposito;
            $params["configuracao"] = $configuracao;
            $params["cliente"] = $cliente;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("aprovar_deposito", $params);
    }

    public function aprovarDeposito($params) {
        try {
            if (!\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_DEPOSITOS, \Utils\Constantes::EDITAR)) {
                throw new \Exception($this->idioma->getText("permissaoAlteraDepC"));
            }
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $deposito->id = \Utils\Post::get($params, "id", 0);
            $deposito->idContaBancariaEmpresa = \Utils\Post::get($params, "idContaBancariaEmpresa", 0);
            $deposito->valorDepositado = \Utils\Post::getNumeric($params, "valor", 0);
            $deposito->tipoDeposito = \Utils\Post::get($params, "tipoDeposito", NULL);

            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositoRn->aprovar($deposito);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function cancelar($params) {
        try {
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $deposito->id = \Utils\Post::get($params, "id", 0);
            $deposito->motivoCancelamento =  \Utils\Post::get($params, "motivoCancelamento", 0);
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositoRn->cancelar($deposito);

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function imprimir($params) {
        try {
            $d = \Utils\Get::get($params, 0, "");
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));

            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ? new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ? new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $idContaBancariaEmpresa =  isset($a[2]) ? $a[2] : 0;
            $status =  isset($a[3]) ? $a[3] : "T";
            $tipoDeposito =  isset($a[4]) ? $a[4] : "Q";
            $filtro =  isset($a[5]) ? $a[5] : null;
            $idCliente = isset($a[6]) ? $a[6] : 0;

            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositos = $depositoRn->filtrar($idCliente, $dataInicial, $dataFinal, $idContaBancariaEmpresa, $tipoDeposito, $status, $filtro);

            $params["depositos"] = $depositos;

            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_depositos", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_depositos.pdf", "D", false, false, false);
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
}