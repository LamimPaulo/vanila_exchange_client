<?php

namespace Modules\servicos\Controllers;


class Remessas {
    
    private  $codigoModulo = "servicos";
    private $idioma = null;
    
    function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("remessa", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
        $cliente = \Utils\Geral::getCliente();
        
        if (\Utils\Geral::isCliente() && $cliente->utilizaSaqueDepositoBrl < 1) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        \Modules\principal\Controllers\Principal::validarAcessoCliente($params, false);
    }
    
    
    public function index($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            
            if (\Utils\Geral::isCliente()) {
                $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
                $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
                $categorias = $categoriaServicoRn->conexao->listar("id_cliente = {$cliente->id} AND ativo > 0", "descricao");
                $contas = $remessaRn->getContasBancariasCliente($cliente);
                
            } else {
                $categorias = Array();
                $contas = Array();
            }
            
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $bancos = $bancoRn->conexao->listar("ativo = 1", "nome");
            
            $params["contas"] = $contas;
            $params["categorias"] = $categorias;
            $params["bancos"] = $bancos;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("remessas", $params);
    }

    public function contasCadastradas($params) {
        try{
            
        $idRemessa = \Utils\Post::get($params, "idRemessa", null);
        $remessa = new \Models\Modules\Cadastro\RemessaDinheiro();
        $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
       
        $remessa->id = $idRemessa;
        $remessaRn->carregar($remessa);

        
            $json["remessa"] = $remessa;        
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function filtrar($params) {
        try {
           
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $status = \Utils\Post::get($params, "status", "T");
            $tipoData = \Utils\Post::get($params, "tipoData", "C");
            $idBanco = \Utils\Post::get($params, "idBanco", 0);
            $titular = \Utils\Post::get($params, "titular", null);
            $agencia = \Utils\Post::get($params, "agencia", null);
            $conta = \Utils\Post::get($params, "conta", null);
            $nresultado = \Utils\Post::get($params, "nresultado", "T");
            $email = null;
            
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("dtInicialInvalida"));
            }
            
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("dtFinalInvalida"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dtInicialMenorDtFinal"));
            }
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            
            $cliente = \Utils\Geral::getCliente();
            
            $remessas = $remessaDinheiroRn->filtrarRemessasClientes($dataInicial, $dataFinal, $status, $idBanco, $tipoData, $email, $titular, $agencia, $conta, ($cliente != null ? $cliente->id : 0), $nresultado);
            
            $retorno = $this->htmlListaRemessas($remessas);
            
            $json["dados"] = $retorno["dados"];
            $json["html"] = $retorno["html"];
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    function htmlListaRemessas($lista) {
        
        ob_start();
        $aguardando = 0;
        $pagos = 0;
        $finalizados = 0;
        $cancelados = 0;
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $remessaDinheiro) {
                
                switch ($remessaDinheiro->status) {
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO:
                        $aguardando++;
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO:
                        $cancelados++;
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO:
                        $finalizados++;
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO:
                        $pagos++;
                        break;
                }
                
                $this->htmlRemessaDinheiro($remessaDinheiro);
            }
        } else {
            ?>
            <tr>
                    <td class="text-center" colspan="11">
                        <?php echo $this->idioma->getText("nenhumaRemessaEncontrada") ?>
                    </td>
                
            </tr>
            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        
        $dados = Array(
            "aguardando" => $aguardando,
            "pago" => $pagos,
            "finalizado" => $finalizados,
            "cancelado" => $cancelados
        );
        
        return Array("dados" => $dados, "html" => $html);
    }
    
    
    function htmlRemessaDinheiro(\Models\Modules\Cadastro\RemessaDinheiro $remessaDinheiro) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $isCliente = \Utils\Geral::isCliente();
        $isAdm = (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR);
        
        $bancoRn = new \Models\Modules\Cadastro\BancoRn();
        $banco = new \Models\Modules\Cadastro\Banco();
        $banco->id = $remessaDinheiro->idBanco;
        $bancoRn->conexao->carregar($banco);
        
        $remessaDinheiro->valor = $remessaDinheiro->valor - $remessaDinheiro->valorTaxa;
        
        $invoiceRemessaRn = new \Models\Modules\Cadastro\InvoiceRemessaDinheiroRn();
        $invoice = $invoiceRemessaRn->getCurrentInvoice($remessaDinheiro);
        
        ?>
            <tr>
                <?php if (\Utils\Geral::isUsuario()) { ?>
                <td><?php echo $remessaDinheiro->cliente->nome ?></td>
                <?php } ?>
                <td class='text-center'><?php echo $remessaDinheiro->id ?></td>
                <td class='text-center'><?php echo $remessaDinheiro->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                <td class='text-center'><?php echo $remessaDinheiro->titular ?></td>
                <td class='text-center'><?php echo $banco->codigo . "-" .$banco->nome ?> <strong>|</strong> <?php echo $remessaDinheiro->agencia . ($remessaDinheiro->agenciaDigito != null ? "-".$remessaDinheiro->agenciaDigito:"")?> <strong>|</strong>
                <?php echo $remessaDinheiro->conta . ($remessaDinheiro->contaDigito != null ? "-".$remessaDinheiro->contaDigito:"")?>
                </td>
                <td class='text-center'>R$ <?php echo number_format(($remessaDinheiro->valor - $remessaDinheiro->tarifaTed), 2, ",", ".") ?></td>
                <td class='text-center'>R$ <?php echo number_format(($remessaDinheiro->valorTaxa + $remessaDinheiro->tarifaTed), 2, ",", ".") ?></td>
                <td class='text-center'>x</td>
                <td class='text-center'>
                    <?php if ($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO && $remessaDinheiro->arquivoComprovante != null) { ?>
                    <a href="<?php echo URLBASE_CLIENT . UPLOADS . $remessaDinheiro->arquivoComprovante ?>" target="_COMPROVANTE<?php echo $remessaDinheiro->id ?>">
                        <i class="fa fa-file"></i>
                    </a>
                    <?php } ?>
                </td>
                <td class='text-center'>
                    <?php if ($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO && $remessaDinheiro->notaFiscal > 0) { ?>
                    <a href="<?php echo $remessaDinheiro->arquivoComprovante ?>" target="_NF<?php echo $remessaDinheiro->id ?>">
                        <i class="fa fa-file"></i>
                    </a>
                    <?php } ?>
                </td>
                <td class='text-center'><?php echo $remessaDinheiro->getStatus() ?></td>
                <?php if($isCliente) { ?>
                <td class="text-center">
                    <?php if($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO) { ?>
                    <button type="button" class="btn btn-danger btn-xs" style="font-size: 9px; "onclick="modalCancelarRemessa('<?php echo \Utils\Criptografia::encriptyPostId($remessaDinheiro->id); ?>')" >
                        <?php echo $this->idioma->getText("cancelarClienteC") ?>
                    </button>
                   <?php } else { ?>
                     -
                   <?php } ?>
                </td>
                <?php } ?> 
                <?php if ($isAdm) { ?>
                <td class="text-center">
                    <?php if($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO) { ?>
                    <button type="button" class="btn btn-primary btn-xs" style="font-size: 9px; "onclick="modalFinalizarRemessa('<?php echo \Utils\Criptografia::encriptyPostId($remessaDinheiro->id); ?>')" >
                        <?php echo $this->idioma->getText("informarPagamentoBtn") ?>
                    </button>
                    <button type="button" class="btn btn-danger btn-xs" style="font-size: 9px; "onclick="modalCancelarRemessa('<?php echo \Utils\Criptografia::encriptyPostId($remessaDinheiro->id); ?>')" >
                        <?php echo $this->idioma->getText("cancelarAdmC") ?>
                    </button>
                    <?php } else { ?>
                      -
                   <?php } ?>
                </td>
                <?php } ?>
            </tr>
        
        <?php
    }
    
    function dadosPagamento($param) {
        try{
            $idRemessa = \Utils\Post::getEncrypted($param, "idRemessa", null);
            $remessa = new \Models\Modules\Cadastro\RemessaDinheiro();            
            $RemessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $banco = new \Models\Modules\Cadastro\Banco();
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            
            $remessa->id = $idRemessa;
            $RemessaDinheiroRn->carregar($remessa);
            $cliente->id = $remessa->idCliente;
            $clienteRn->conexao->carregar($cliente);
            $banco->id = $remessa->idBanco;
            $bancoRn->conexao->carregar($banco);
            
            ob_start();
            ?>
                <ul class="list-group">
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("nomeClienteC") ?></strong> <span><?php echo $cliente->nome ?></span></li>
                    <hr>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("controleC") ?> </strong> <span><?php echo $remessa->id ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("nomeTitularC") ?> </strong> <span><?php echo $remessa->titular ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("cpfCnpjC") ?> </strong> <span><?php echo $remessa->documento ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("dataCadastroC") ?> </strong> <strong><span><?php echo $remessa->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></span></strong></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("bancoC") ?> </strong><span><?php echo $remessa->idBanco . " - " . $banco->nome ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("agenciaC") ?></strong> <span><?php echo $remessa->agencia . ($remessa->agenciaDigito != null ? "-".$remessa->agenciaDigito : "") ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("contaC") ?> </strong><span><?php echo $remessa->conta . ($remessa->contaDigito != null ? "-".$remessa->contaDigito : "") ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("tipoContaC") ?></strong><span><?php echo ($remessa->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? "Conta Corrente" : "Conta Poupança") ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("operacaoC") ?> </strong><?php echo $remessa->operacao ?></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("valorTaxaC") ?></strong> R$ <?php echo number_format($remessa->valorTaxa, 2, ",",".") ?></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("tarifaTedC") ?></strong> R$ <?php echo number_format($remessa->tarifaTed, 2, ",",".") ?></li>
                    <li class="list-group-item"><strong class="h5" style="color: #ff1e1e"><?php echo $this->idioma->getText("valorSerTransferidoC") ?>  R$ <?php echo number_format(($remessa->valor - $remessa->valorTaxa - $remessa->tarifaTed), 2, ",",".") ?></strong></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("notaFiscalC") ?> </strong> <?php echo ($remessa->aceitaNota > 0 ? $this->idioma->getText("simC") : $this->idioma->getText("naoC")) ?></li>
                </ul>

            <?php            
            $htmlDados = ob_get_contents();
            ob_end_clean();
            $json["dados"] = $htmlDados;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
        
    }
    
    
    
    public function salvar($params) {
        try {
                       
            $remessaDinheiro = new \Models\Modules\Cadastro\RemessaDinheiro();
            $remessaDinheiro->id = \Utils\Post::get($params, "id", 0);
            
            if ($remessaDinheiro->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_REMESSAS, \Utils\Constantes::EDITAR)) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoEditar"));
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_REMESSAS, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoCadastrar"));
                }
            }
                       
            
            $remessaDinheiro->agencia = \Utils\Post::get($params, "agencia", null);
            $remessaDinheiro->conta = \Utils\Post::get($params, "conta", null);
            $remessaDinheiro->agenciaDigito = \Utils\Post::get($params, "agenciaDigito", 0);
            $remessaDinheiro->contaDigito = \Utils\Post::get($params, "contaDigito", 0);
            $remessaDinheiro->idBanco = \Utils\Post::get($params, "idBanco", null);
            $remessaDinheiro->documento = \Utils\Post::getDoc($params, "documento", null);
            $remessaDinheiro->tipoConta = \Utils\Post::get($params, "tipoConta", null);
            $remessaDinheiro->titular = \Utils\Post::get($params, "titular", null);
            $remessaDinheiro->operacao = \Utils\Post::get($params, "operacao", null);
            $remessaDinheiro->valor = \Utils\Post::getNumeric($params, "valor", 0);
            $remessaDinheiro->idReferencia = \Utils\Post::get($params, "referencia", null);
            $remessaDinheiro->aceitaNota = \Utils\Post::getBooleanAsInt($params, "aceitaNota", 0); 
            $remessaDinheiro->idCategoriaServico = \Utils\Post::getEncrypted($params, "idCategoriaServico", null); 
            
            if (!$remessaDinheiro->idCategoriaServico > 0) {
                $remessaDinheiro->idCategoriaServico = null;
            }
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $remessaDinheiroRn->salvar($remessaDinheiro);
            
            $json["idRemessa"] = $remessaDinheiro->id;
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("remessaRegistradaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function cancelar($params) {
        try {
            $usuarioLogado = \Utils\Geral::getLogado();
            $isAdm = (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $remessaDinheiro = new \Models\Modules\Cadastro\RemessaDinheiro();
            $remessaDinheiro->id = \Utils\Post::getEncrypted($params, "idRemessa", 0);
            $motivoCancelamento = \Utils\Post::get($params, "motivoCancelamento", null);
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $banco = new \Models\Modules\Cadastro\Banco();
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            
            $remessaDinheiroRn->carregar($remessaDinheiro);
            $cliente->id = $remessaDinheiro->idCliente;
            $clienteRn->conexao->carregar($cliente);
            $banco->id = $remessaDinheiro->idBanco;
            $bancoRn->conexao->carregar($banco);
            
                if($remessaDinheiro->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO){
                    
                    if($isAdm){
                        $remessaDinheiroRn->marcarComoCancelado($remessaDinheiro, $motivoCancelamento, $usuarioLogado->id);
                        \Email\CancelaRemessa::send($remessaDinheiro, $cliente, $banco);  
                    } else {
                        $remessaDinheiroRn->marcarComoCancelado($remessaDinheiro, $motivoCancelamento, null); 
                    }            
                } else {
                   throw new \Exception($this->idioma->getText("remessaNaoCancelada"));
                }

            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("remessaFoiCancelada");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function pagar($params) {
        try {
            $remessa = new \Models\Modules\Cadastro\RemessaDinheiro();
            $remessa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $remessaRn->conexao->carregar($remessa);
            
            $conta = $remessaRn->debitarDoSaldo($remessa, true);
            
            if ($conta == null) {
                throw new \Exception($this->idioma->getText("naoPossivelEfetuarRemessa"));
            }
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("remessaValoresSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function finalizar($params) {
        try {
                       
            $file = $params["_FILE"];
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();            
            $remessaDinheiro = new \Models\Modules\Cadastro\RemessaDinheiro();
            $remessaDinheiro->id = \Utils\Post::getEncrypted($params, "idRemessa", 0);
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $banco = new \Models\Modules\Cadastro\Banco();
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            
            $remessaDinheiroRn->carregar($remessaDinheiro);
            $cliente->id = $remessaDinheiro->idCliente;
            $clienteRn->conexao->carregar($cliente);
            $banco->id = $remessaDinheiro->idBanco;
            $bancoRn->conexao->carregar($banco);
            
            if (isset($file["comprovante"]) && $file["comprovante"]["error"] < 1) {
                $arquivo = new \Utils\Arquivo($file["comprovante"]);
                $arquivo->uploadArquivo();
                $remessaDinheiro->arquivoComprovante = $arquivo->nome_saida;
            } else {
                $remessaDinheiro->arquivoComprovante = "";
            }            
                        
            
            $remessaDinheiroRn->marcarComoPago($remessaDinheiro, $remessaDinheiro->arquivoComprovante);
            $remessaDinheiroRn->carregar($remessaDinheiro);
            try {
                \Email\RemessaFinalizado::send($remessaDinheiro, $cliente, $banco);
            } catch (Exception $ex) {
                throw new \Exception($this->idioma->getText("erroEnviarEmail"));
            }

            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("remessaFinalizadaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function cadastro($params) {
        try {
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $bancos = $bancoRn->conexao->listar("ativo = 1", "nome");
            
            $params["bancos"] = $bancos;
            
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("pagar_remessa",$params);
    }
    
    
    
    public function comprovante($params) {
        try {
           
            
            $invoiceRemessa = new \Models\Modules\Cadastro\InvoiceRemessaDinheiro();
            $invoiceRemessa->id = \Utils\Get::get($params, 0, 0);
            try {
                $invoiceRemessaRn = new \Models\Modules\Cadastro\InvoiceRemessaDinheiroRn();
                $invoiceRemessaRn->conexao->carregar($invoiceRemessa);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("invoiceNaoLocalizada"));
            }
            
            $remessa = new \Models\Modules\Cadastro\RemessaDinheiro();
            $remessa->id = $invoiceRemessa->idRemessaDinheiro;
            
            try {
                $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
                $remessaRn->conexao->carregar($remessa);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("remessaNaoLocalizada"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($remessa->email);
            
            if ($cliente == null) {
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $cliente->email = $remessa->email;
            }
            
            $PDFComprovanteInvoice = new \Modules\pdfs\Controllers\PDFComprovanteInvoice();
            $PDFComprovanteInvoice->gerar($cliente, $invoiceRemessa->idInvoice, $invoiceRemessa->id, $remessa->dataCadastro);
            
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1><?php echo \Utils\Excecao::mensagem($ex)?></h1>
                </body>
            </html>
            <?php
        }
    }
    
    
    
    public function getPiechartCartegorias($params) {
        try {
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, " 00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, " 23:59:59");
            $tipoData = \Utils\Post::get($params, "tipoData", "A");
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $result = $remessaDinheiroRn->getConsumoPorCategoria($dataInicial, $dataFinal, $tipoData);
            
            $json["dados"] = $result["dados"];
            $json["categorias"] = $result["categorias"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getConsumoPorMes($params) {
        try {
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, " 00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, " 23:59:59");
            $tipoData = \Utils\Post::get($params, "tipoData", "A");
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $result = $remessaDinheiroRn->getConsumoPorMes($dataInicial, $dataFinal, $tipoData);
            
            $json["meses"] = $result["meses"];
            $json["categorias"] = $result["categorias"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getConsumoPorMesPorCategoria($params) {
        try {
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, " 00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, " 23:59:59");
            $tipoData = \Utils\Post::get($params, "tipoData", "A");
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $result = $remessaDinheiroRn->getConsumoPorMesPorCategoria($dataInicial, $dataFinal, $tipoData);
            
            $json["grafico"] = $result["grafico"];
            $json["meses"] = $result["meses"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function carregarEventos($params) {
        try {
            $cliente = \Utils\Geral::getLogado();
            
            $remessaRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $result = $remessaRn->conexao->listar("id_cliente = {$cliente->id} ");
            
            $eventos = Array();
            foreach ($result as $remessa) {
                //$remessa = new \Models\Modules\Cadastro\RemessaDinheiro();
                
                $eventos[] = Array(
                    "title" => $this->idioma->getText("cadastroRemessa") . "R$ {$remessa->valor}",
                    "start" =>  "{$remessa->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                    "color" => "#23c6c8",
                    "allDay" => false
                );
                
                if ($remessa->dataPagamento != null) {
                    $eventos[] = Array(
                        "title" => $this->idioma->getText("remessaEnviada") . "R$ {$remessa->valor}",
                        "start" =>  "{$remessa->dataPagamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                        "color" => "#1ab394",
                        "allDay" => false
                    );
                }
                
                
                if ($remessa->dataCancelamento != null) {
                    $eventos[] = Array(
                        "title" =>  $this->idioma->getText("remessaCancelada")." R$ {$remessa->valor}",
                        "start" =>  "{$remessa->dataCancelamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                        "color" => "#ed5565",
                        "allDay" => false
                    );
                }
            }
            
            $json["eventos"] = $eventos;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}
