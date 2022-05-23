<?php

namespace Modules\servicos\Controllers;


class Boletos {
    
    private  $codigoModulo = "servicos";
     private $idioma = null;
    function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("boleto", 'IDIOMA');
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
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categorias = $categoriaServicoRn->conexao->listar("id_cliente = {$cliente->id} AND ativo > 0", "descricao");
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $bancos = $bancoRn->conexao->listar("ativo = 1", "nome");
            
            $params["categorias"] = $categorias;
            $params["bancos"] = $bancos;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("boletos", $params);
    }
    
    
    public function getDadosBarras($params) {
        try {
            $barras = str_replace(Array(" ", ".", "-"), "", \Utils\Post::get($params, "barras", ""));
            
            $bar = new \boletosPHP();
            
            
            $bar->setBarras($barras);
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $codBanco = $bar->getCodBanco();
            
            $result = $bancoRn->conexao->select("codigo = '{$codBanco}'");
            if (sizeof($result) > 0) {
                $banco = $result->current();
            } else {
                $banco = new \Models\Modules\Cadastro\Banco(Array("id" => 0));
            }
            
            $json["codBanco"] = $codBanco;
            $json["banco"] = \Utils\Criptografia::encriptyPostId($banco->id);
            $json["valor"] = $bar->getValorDocumento();
            $json["validade"] = $bar->getDtVencimento();
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    function dadosPagamento($param) {
        try{
            $idBoleto = \Utils\Post::getEncrypted($param, "idBoleto", null);
            $boleto = new \Models\Modules\Cadastro\BoletoCliente();            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $boleto->id = $idBoleto;
            $boletoClienteRn->conexao->carregar($boleto);
            $cliente->id = $boleto->idCliente;
            $clienteRn->conexao->carregar($cliente);
            
            ob_start();
            ?>
                <ul class="list-group">
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("nomeClienteC") ?></strong> <span><?php echo $cliente->nome ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("cpfCnpjC") ?> </strong> <span><?php echo $cliente->documento ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("controleC") ?></strong> <span><?php echo $boleto->id ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("dataVencimentoC") ?></strong> <strong><span><?php echo $boleto->dataVencimento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></span></strong></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("dataCadastroC") ?></strong> <span><?php echo $boleto->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("codigoBarrasC") ?></strong> <span><?php echo $boleto->barras ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("valorC") ?></strong> R$ <span><?php echo number_format(($boleto->valor - $boleto->valorTaxa), 2, ",",".") ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("valorTaxaC") ?></strong> R$ <span><?php echo number_format($boleto->valorTaxa, 2,",",".") ?></span></li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("comentariosC") ?></strong> <div><p><?php echo $boleto->comentario ?></p></div> </li>
                    <li class="list-group-item"><strong><?php echo $this->idioma->getText("notaFiscalC") ?> </strong> <?php echo ($boleto->notaFiscal > 0 ? $this->idioma->getText("simC") : $this->idioma->getText("naoC")) ?></li>
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
    
    public function filtrar($params) {
        try {
                      
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $status = \Utils\Post::get($params, "status", "T");
            $tipoData = \Utils\Post::get($params, "tipoData", "C");
            $email = \Utils\Post::get($params, "email", null);
            $barras = \Utils\Post::get($params, "barras", null);
            $idBanco = \Utils\Post::get($params, "idBanco", 0);
            $nresultado = \Utils\Post::get($params, "nresultado", "T");
           
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("dtInicialInvalida"));
            }
            
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("dtFinalInvalida"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dtInicialMenorDtFinal"));
            }
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            
            $idCliente = null;
            if (!\Utils\Geral::isUsuario()) {
                $cliente = \Utils\Geral::getCliente();
                $idCliente = $cliente->id;
                if (empty($email)) {
                    $email = $cliente->email;
                }
            }
            
            $boletos = $boletoClienteRn->filtrarBoletosClientes($dataInicial, $dataFinal, $status, $idBanco, $tipoData, $email, $idCliente, $nresultado, $barras);
            
            $retorno = $this->htmlListaBoletos($boletos);
            
            $json["dados"] = $retorno["dados"];
            $json["html"] = $retorno["html"];
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    function htmlListaBoletos($lista) {
        
        ob_start();
        $aguardando = 0;
        $pagos = 0;
        $finalizados = 0;
        $cancelados = 0;
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $boletoCliente) {
                
                switch ($boletoCliente->status) {
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO:
                        $aguardando++;
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO:
                        $cancelados++;
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO:
                        $finalizados++;
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO:
                        $pagos++;
                        break;
                }
                
                $this->htmlBoletoCliente($boletoCliente);
            }
        } else {
            ?>
            
            <tr>
                <td class="text-center" colspan="13">
                <?php echo $this->idioma->getText("nenhumaBoletoEncontrado") ?>
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
        
    
    function htmlBoletoCliente(\Models\Modules\Cadastro\BoletoCliente $boletoCliente) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $isCliente = \Utils\Geral::isCliente();
        $isAdm = (\Utils\Geral::isUsuario());
        
        $bancoRn = new \Models\Modules\Cadastro\BancoRn();
        $banco = new \Models\Modules\Cadastro\Banco();
        $banco->id = $boletoCliente->idBanco;
        $bancoRn->conexao->carregar($banco);
        
        if ($isAdm) {
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $boletoCliente->idCliente));
            if ($cliente->id > 0) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            }
        }
        
        ?>

        <tr>    
            <?php if ($isAdm) { ?>
                <td class='text-center'><?php echo $cliente->nome ?></td>
            <?php } ?>
            
                <td class='text-center'><?php echo $boletoCliente->id ?></td>
                
                <td class='text-center'>
                    <a tabindex="0" class="btn btn-xs btn-info boleto-comentarios" role="button" data-controle='<?php echo $this->idioma->getText("codigoBarras") ?>' data-motivo='
                       <div style="width: 250px; height: 40px;"><p> <?php echo $boletoCliente->barras ?> </p><div>
                       ' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                            <?php echo $this->idioma->getText("codBarrasC") ?>
                    </a>
                </td>
                <td class="text-center">
                    <a href="<?php echo URLBASE_CLIENT . UPLOADS . $boletoCliente->arquivoBoleto ?>" target="_BOLETO<?php echo $boletoCliente->id ?>">
                        <i class="fa fa-file"></i>
                    </a>
                </td>
                <td class='text-center'><?php echo $boletoCliente->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                <td class='text-center'><?php echo $boletoCliente->dataVencimento->formatar(\Utils\Data::FORMATO_PT_BR)?></td>
                <td class='text-center'><?php echo ($boletoCliente->dataPagamento != null ? $boletoCliente->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "" ) ?></td>
                <td class='text-center'>R$ <?php echo number_format(($boletoCliente->valor - $boletoCliente->valorTaxa), 2, ",", ".") ?></td>
                <td class='text-center' style="width: 80px;">R$ <?php echo number_format($boletoCliente->valorTaxa, 2, ",", ".") ?></td>
                
                <td class='text-center'>
                    <a tabindex="0" class="btn btn-xs btn-info boleto-comentarios" role="button" data-controle='Comentário:' data-motivo='
                       <div style="width: 250px; height: 150px;"><p> <?php echo $boletoCliente->comentario ?> </p><div>
                       ' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                            <?php echo $this->idioma->getText("comentarioC") ?>
                    </a>
                    <?php if($boletoCliente->motivoCancelamento != null) { ?>
                    <a tabindex="0" class="btn btn-xs btn-danger boleto-cancelamento" role="button" data-controle='Motivo:' data-motivo='
                       <div style="width: 250px; height: 150px;"><p> <?php echo $boletoCliente->motivoCancelamento ?> </p><div>
                       ' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                            <?php echo $this->idioma->getText("boletoCanceladoC") ?>
                    </a>
                    <?php } ?>
                </td>
        
                <td class='text-center'><?php echo $boletoCliente->getStatus() ?></td>
                
                <td class='text-center'>
                    <?php if ($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO && $boletoCliente->arquivoComprovante != null) { ?>
                    <a href="<?php echo URLBASE_CLIENT . UPLOADS . $boletoCliente->arquivoComprovante ?>" target="_COMPROVANTE<?php echo $boletoCliente->id ?>">
                        <i class="fa fa-file"></i>
                    </a>
                    <?php } ?>
                </td>
                <td class='text-center'>
                    <?php if ($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO && $boletoCliente->notaFiscal > 0) { ?>
                    <a href="<?php echo $boletoCliente->arquivoBoleto ?>" target="_NF<?php echo $boletoCliente->id ?>">
                        <i class="fa fa-file"></i>
                    </a>
                    <?php } ?>
                </td>
                <?php if($isCliente) { ?>
                <td class="text-center">
                    <?php if($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO) { ?>
                    <button type="button" class="btn btn-danger btn-xs" style="font-size: 9px; "onclick="modalCancelarBoleto('<?php echo \Utils\Criptografia::encriptyPostId($boletoCliente->id); ?>')" >
                       <?php echo $this->idioma->getText("cancelarClienteC") ?>
                    </button>
                   <?php } else { ?>
                     -
                   <?php } ?>
                </td>
                <?php } ?> 
                <?php if ($isAdm) { ?>
                <td class="text-center">
                    <?php if (!in_array($boletoCliente->status, Array(\Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO, \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO))) { ?>
                    <button type="button" class="btn btn-primary btn-xs" style="font-size: 9px; "onclick="modalFinalizarBoleto('<?php echo \Utils\Criptografia::encriptyPostId($boletoCliente->id); ?>')" >
                        <?php echo $this->idioma->getText("informarPagamentoBtn") ?>
                    </button>
                    <button type="button" class="btn btn-danger btn-xs" style="font-size: 9px; "onclick="modalCancelarBoleto('<?php echo \Utils\Criptografia::encriptyPostId($boletoCliente->id); ?>')" >
                        <?php echo $this->idioma->getText("cancelarAdmC") ?>
                    </button>
                    <?php } ?>
                </td>
                <?php } ?>
            </tr>
        <?php
    }
    
    
    
    public function salvar($params) {
        try {
   
            $file = $params["_FILE"];
            
            $boletoCliente = new \Models\Modules\Cadastro\BoletoCliente();
            $boletoCliente->id = \Utils\Post::get($params, "id", 0);
            
            if ($boletoCliente->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_BOLETOS, \Utils\Constantes::EDITAR)) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoAlterar"));
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_BOLETOS, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoCadastrar"));
                }
            }
            
            $cliente = \Utils\Geral::getLogado();
            
            $boletoCliente->barras = \Utils\Post::get($params, "barras", null);
            $boletoCliente->dataVencimento = \Utils\Post::getData($params, "vencimento", null, "23:59:59");
            $boletoCliente->email = \Utils\Post::get($params, "email", null);
            $boletoCliente->idBanco = \Utils\Post::getEncrypted($params, "cadastroContaIdBanco", 0);
            $boletoCliente->valor = \Utils\Post::getNumeric($params, "valor", 0);
            $boletoCliente->idReferencia = \Utils\Post::getEncrypted($params, "referencia", null);
            $boletoCliente->arquivoBoleto = \Utils\File::get($params, "arquivo", null,  Array(), $cliente, "boletos");
            $boletoCliente->comentario = \Utils\Post::get($params, "comentario", null);
            $boletoCliente->notaFiscal = \Utils\Post::getBoolean($params, "aceitaNota", 0);
            $boletoCliente->idCategoriaServico = \Utils\Post::getEncrypted($params, "idCategoriaServico", null);
            
            if (!$boletoCliente->idCategoriaServico > 0) {
                $boletoCliente->idCategoriaServico = null;
            }
            /*if($boletoCliente->barras == null || $boletoCliente->valor == null || $boletoCliente->dataVencimento == null){
                throw new \Exception("Todos os campos do formulário devem ser preenchidos.");
            }*/
            
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $boletoClienteRn->salvar($boletoCliente);
            
            $json["idBoleto"] = $boletoCliente->id;
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("boletoRegistradoSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] =\Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function pagar($params) {
        try {
            $boleto = new \Models\Modules\Cadastro\BoletoCliente();
            $boleto->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $boletoRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $boletoRn->conexao->carregar($boleto);
            
            $conta = $boletoRn->debitarDoSaldo($boleto, true);
            
            if ($conta == null) {
                throw new \Exception($this->idioma->getText($this->idioma->getText("naoPossivelEfetuarBoleto")));
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function finalizar($params) {
        try {
            
            $boletoCliente = new \Models\Modules\Cadastro\BoletoCliente();
            $boletoCliente->id = \Utils\Post::getEncrypted($params, "idBoleto", 0);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
          
            $boletoClienteRn->conexao->carregar($boletoCliente);
            $cliente->id = $boletoCliente->idCliente;
            $clienteRn->conexao->carregar($cliente);        
            
            $boletoCliente->arquivoComprovante = \Utils\File::get($params, "comprovante", null, Array(), new \Models\Modules\Cadastro\Cliente(Array("id" => $boletoCliente->idCliente)), "comprovantes_boletos");
            
            if($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO){
                
            $boletoClienteRn->marcarComoPago($boletoCliente, $boletoCliente->arquivoComprovante);
           
            try{
                
                \Email\BoletoFinalizado::send($boletoCliente, $cliente);
                

            } catch (Exception $ex) {
                throw new \Exception($this->idioma->getText("erroEnviarEmail"));
            }
            
            } else {
                throw new \Exception($this->idioma->getText("boletoNaoPodeSerPago"));
            }
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("boletoFinalizadoSucesso");
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
            $boletoCliente = new \Models\Modules\Cadastro\BoletoCliente();
            $boletoCliente->id = \Utils\Post::getEncrypted($params, "idBoleto", 0);
            $motivoCancelamento = \Utils\Post::get($params, "motivoCancelamento", null);
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $banco = new \Models\Modules\Cadastro\Banco();
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            
            $boletoClienteRn->conexao->carregar($boletoCliente);
            $cliente->id = $boletoCliente->idCliente;
            $clienteRn->conexao->carregar($cliente);
            $banco->id = $boletoCliente->idBanco;
            $banco = $bancoRn->conexao->carregar($banco);
            $boletoCliente->nomeBanco = $banco->nome;
                
                
            if($boletoCliente->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO){
                  
                if($isAdm){
                    $boletoClienteRn->marcarComoCancelado($boletoCliente, $motivoCancelamento, $usuarioLogado->id);
                    \Email\CancelaBoleto::send($boletoCliente, $cliente);
                } else {
                    $boletoClienteRn->marcarComoCancelado($boletoCliente, $motivoCancelamento, null);
                }                  
                } else {
                   throw new \Exception($this->idioma->getText("boletoNaoCancelado"));
                }

            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("boletoCanceladoSucesso");
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
        \Utils\Layout::view("pagar_boleto",$params);
    }
    
    
    
    public function comprovante($params) {
        try {
            
            
            $invoiceBoleto = new \Models\Modules\Cadastro\InvoiceBoleto();
            $invoiceBoleto->id = \Utils\Get::get($params, 0, 0);
            try {
                $invoiceBoletoRn = new \Models\Modules\Cadastro\InvoiceBoletoRn();
                $invoiceBoletoRn->conexao->carregar($invoiceBoleto);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("invoiceNaoLocalizada"));
            }
            
            $boleto = new \Models\Modules\Cadastro\BoletoCliente();
            $boleto->id = $invoiceBoleto->idBoletoCliente;
            
            try {
                $boletoRn = new \Models\Modules\Cadastro\BoletoClienteRn();
                $boletoRn->conexao->carregar($boleto);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("boletoNaoLocalizada"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($boleto->email);
            
            if ($cliente == null) {
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $cliente->email = $boleto->email;
            }
            
            $PDFComprovanteInvoice = new \Modules\pdfs\Controllers\PDFComprovanteInvoice();
            $PDFComprovanteInvoice->gerar($cliente, $invoiceBoleto->idInvoice, $invoiceBoleto->id, $boleto->dataCadastro);
            
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
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $result = $boletoClienteRn->getConsumoPorCategoria($dataInicial, $dataFinal, $tipoData);
            
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
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $result = $boletoClienteRn->getConsumoPorMes($dataInicial, $dataFinal, $tipoData);
            
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
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $result = $boletoClienteRn->getConsumoPorMesPorCategoria($dataInicial, $dataFinal, $tipoData);
            
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
            
            $boletoRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $result = $boletoRn->conexao->listar("id_cliente = {$cliente->id} ");
            
            $eventos = Array();
            foreach ($result as $boleto) {
                //$boleto = new \Models\Modules\Cadastro\BoletoCliente();
                
                $eventos[] = Array(
                    "title" => $this->idioma->getText("cadastroBoleto") . "R$ {$boleto->valor}",
                    "start" =>  "{$boleto->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                    "color" => "#23c6c8",
                    "allDay" => false
                );
                    
                $vencimento = true;
                $pagamento = false;
                if ($boleto->dataPagamento != null) {
                    $pagamento = true;
                    if ($boleto->dataVencimento->formatar(\Utils\Data::FORMATO_ISO) == $boleto->dataPagamento->formatar(\Utils\Data::FORMATO_ISO)) {
                        $vencimento = false;
                    }
                }
                
                
                if ($vencimento) {
                    $eventos[] = Array(
                        "title" => $this->idioma->getText("boletoVencendo") . " R$ {$boleto->valor}",
                        "start" =>  "{$boleto->dataVencimento->formatar(\Utils\Data::FORMATO_ISO)} 00:00:00",
                        "color" => "#f8ac59",
                        "allDay" => false
                    );
                }
                
                if ($pagamento) {
                    $eventos[] = Array(
                        "title" => $this->idioma->getText("boletoPago") . "R$ {$boleto->valor}",
                        "start" =>  "{$boleto->dataPagamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                        "color" => "#00cf7a",
                        "allDay" => false
                    );
                }
                
                
                if ($boleto->dataCancelamento != null) {
                    $eventos[] = Array(
                        "title" =>  $this->idioma->getText("boletoCancelado") . "R$ {$boleto->valor}",
                        "start" =>  "{$boleto->dataCancelamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}",
                        "color" => "#ff2c58",
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