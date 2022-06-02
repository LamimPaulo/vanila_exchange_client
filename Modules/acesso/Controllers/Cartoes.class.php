<?php

namespace Modules\acesso\Controllers;
  
class Cartoes {
    
    public function __construct($params) {
        
    }
    
    public function registrar($params) {
        try {
              
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("cadastro_cliente_simplificado", $params);
    }
    
    public function registrarex($params) {
        try {
            
            $paisRn = new \Models\Modules\Cadastro\PaisRn();
            $paises = $paisRn->conexao->listar(null, "nome");
            $params["paises"]  = $paises;
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("cadastro_cliente_ex", $params);
    }
    
    public function index($params) {
        try {
            
            $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            $params["estados"]  = $estadoRn->conexao->listar(NULL, "sigla", null, null);
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $params["cidades"]  = $cidadeRn->conexao->listar("id_estado = 1", "nome");
              
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("cadastro_cliente", $params);
    }
    
    
    public function getCidades($params) {
        try {
           
            $sigla = \Utils\Post::get($params, "sigla", null);
            
            if (strlen($sigla) != 2) {
                throw new \Exception("Estado inválido");
            }
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidades  = $cidadeRn->getCidadesBySiglaEstado($sigla, false);
              
            ob_start();
            foreach ($cidades as $cidade) {
                ?>
                <option value="<?php echo utf8_encode($cidade->nome)?>"><?php echo utf8_encode($cidade->nome)?></option>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"]  = $html;
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function aderirSimplificado($params) {
        try {
            
            $cliente = $this->cadastro($params);
            
            $json["url"]  = "";
            //$json["band"]  = base64_encode($bandeira);
            $json["cliente"]  = $cliente;
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function cadastro($params) {
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        if (!$configuracao->statusNovosCadastros > 0) {
            throw new \Exception("No momento o sistema não está aberto para novos cadastros. Obrigado!");
        }

        $cliente = new \Models\Modules\Cadastro\Cliente();

        $cliente->email = \Utils\Post::get($params, "email", null);
        $cliente->nome = \Utils\Post::get($params, "nome", null);

        if (empty($cliente->email)) {
            throw new \Exception("O email deve ser informado");
        }
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->listar("email = '{$cliente->email}'");
        if (sizeof($result) > 0) {
            throw new \Exception("O email já está cadastrado no sistema");
        } else {
            $cliente->id = 0;
        }

        $cliente->documentoTipo = 0;
        $cliente->nome = \Utils\Post::get($params, "nome", null);
        $cliente->status = \Utils\Constantes::CLIENTE_ATIVO;
        $cliente->idReferencia = \Utils\Post::getEncrypted($params, "referencia", 15093064546903); //Se vazaio -> Referencia Financeiro
        if (!is_numeric($cliente->idReferencia)) {
            $cliente->idReferencia = null;
        }

         
        $cliente->comissaoConvitePago = 0;

        if (empty($cliente->nome)) {
            throw new \Exception("O nome deve ser informado");
        }

        /*
        $consultaCpfRn = new \Models\Modules\Cadastro\ConsultaCpfRn();
        $consultaCpf = $consultaCpfRn->getByCpf($cliente->documento);
        
        if ($consultaCpf != null) {
            
            $cliente->endereco = $consultaCpf->logradouro;
            $cliente->bairro = $consultaCpf->bairro;
            $cliente->cep = $consultaCpf->cep;
            $cliente->complemento = $consultaCpf->complemento;
            $cliente->nomeMae = $consultaCpf->nomeMae;
            $cliente->nome = $consultaCpf->titular;
            $cliente->numero = $consultaCpf->numero;
            $cliente->sexo = ($consultaCpf->genero == "FEMININO" ? "F" : "M");
            
            try {
                $correios = \Modules\services\Controllers\Consulta::cep($consultaCpf->cep);
                
                if ($correios != null) {
                    $cliente->endereco = (empty($correios["endereco"]) ? $cliente->endereco : $correios["endereco"]);
                    $cliente->bairro = (empty($correios["bairro"]) ? $cliente->bairro : $correios["bairro"]);
                    $cliente->numero = (empty($correios["numero"]) ? $cliente->numero : $correios["numero"]);
                    $cidade = new \Models\Modules\Cadastro\Cidade(Array("codigo" => $correios["ibge"]));
                    $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
                    
                    $cliente->cidade = $cidade->codigo;
                    try {
                        $cidadeRn->carregar($cidade);
                    } catch (\Exception $ex) {

                    }
                }
                
            } catch (\Exception $e) {
                //exit(print_r($e));
            }
            
        }
        */

        $where = "tipo IN ('C', 'UC') ";
        $rotinaHasAcaoRn = new \Models\Modules\Acesso\RotinaHasAcaoRn();
        $rotinasHasAcoes = $rotinaHasAcaoRn->conexao->listar($where);
        $permissoesRotinas = Array();
        foreach ($rotinasHasAcoes as $rha) {
            $permissoesRotinas[] = $rha->id;
        }

        $moduloHasAcaoRn = new \Models\Modules\Acesso\ModuloHasAcaoRn();
        $modulosHasAcoes = $moduloHasAcaoRn->listar($where, null, null, null, true);
        $permissoesModulos = Array();
        foreach ($modulosHasAcoes as $mha) {
            //$mha = new \Models\Modules\Acesso\ModuloHasAcao();
            if ($mha->idModulo != 12) {  // modulo 12 = Recebimentos PDV
                if ($mha->acao->codigo != "TPE") {
                    $permissoesModulos[] = $mha->id;
                }
            }
        }
        //$ip = $_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']);

        if(strpos($ip,',') !== false) {
            $ip = substr($ip,0,strpos($ip,','));
        }
        
        $_SESSION["cadastrado"] = true;

        $cliente->statusDepositoBrl = 1;
        $cliente->statusDepositoCurrency = 1;
        $cliente->statusResgatePdv = 1;
        $cliente->statusSaqueBrl = 1;
        $cliente->statusSaqueCurrency = 1;
        $cliente->idPaisNaturalidade = 30;
        $cliente->utilizaSaqueDepositoBrl = 1;
        $cliente->modoOperacao = "basic";
        
        $cliente->origemCadastro = \Utils\Post::get($params, "origem", \Utils\Constantes::ORIGEM_SITE);
        $cliente->ipCadastro = $ip;
        $clienteRn->salvar($cliente, null, $permissoesRotinas, $permissoesModulos);
        
        return $cliente;
    }
    
    public function invoice($params) {
        try {
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Get::get($params, 0) ? \Utils\Get::get($params, 0) : 0;
            $bandeira = \Utils\Get::get($params, 1) ? base64_decode(\Utils\Get::get($params, 0)) : \Utils\Constantes::CARTAO_VISA;
            
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                //throw new \Exception("Não foi possível localizar o cadastro do cliente");
            }
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            
            $valor = ($cliente->codigoPais == "076" ? $configuracao->valorCartao : $configuracao->valorCartaoEx);
            
            
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo("id_cliente", $cliente->id);
            $where->equalTo("status", \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO);
            $where->equalTo("valor_total", $valor);
            $where->greaterThanOrEqualTo("data_expiracao_invoice", date("Y-m-d H:i:s"));
            
            $pedidos = $pedidoCartaoRn->conexao->listar($where, "data_expiracao_invoice DESC", null, 1);
            if (sizeof($pedidos) > 0) {
                $pedidoCartao = $pedidos->current();
            } else {
                $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            
                $orders = new \BitcoinToYou\Orders();
                $order = $orders->create($valor, URLBASE_CLIENT . \BitcoinToYou\Access::DEFAULT_REDIRECT_CALLBACK . $pedidoCartao->idInvoice);

                $pedidoCartao->id = 0;
                $pedidoCartao->address = $order->DigitalCurrencyAddress;
                $pedidoCartao->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));
                $pedidoCartao->valorTotal = $valor;
                $pedidoCartao->idInvoice = $order->InvoiceId;
                $pedidoCartao->idCliente = $cliente->id;
                $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO;

                $pedidoCartao->transferToAccountEstimateTimestamp = $order->TransferToAccountEstimateTimestamp;
                $pedidoCartao->transferToAccountTimestamp = $order->TransferToAccountTimestamp;
                $pedidoCartao->digitalCurrencyAmount = $order->DigitalCurrencyAmount;
                $pedidoCartao->digitalCurrency = $order->DigitalCurrency;
                $pedidoCartao->redirectUrl = $order->RedirectUrl;
                $pedidoCartao->expirationTimestamp = $order->ExpirationTimestamp;
                $pedidoCartao->tc0015Id = $order->tc0015_id;
                $pedidoCartao->currencyTotal = $order->CurrencyTotal;
                $pedidoCartao->digitalCurrencyAmountPaid = $order->DigitalCurrencyAmountPaid;
                $pedidoCartao->currency = $order->Currency;
                $pedidoCartao->customId = $order->CustomID;
                $pedidoCartao->digitalCurrencyQuotation = $order->DigitalCurrencyQuotation;
                $pedidoCartao->notificationEmail = $order->NotificationEmail;
                $pedidoCartao->transferToAccountEstimateDate = ($order->TransferToAccountEstimateDate != null ? 
                        new \Utils\Data(str_replace("T", " ", $order->TransferToAccountEstimateDate)) : null);
                $pedidoCartao->transferToAccountDate = ($order->TransferToAccountDate != null ? 
                        new \Utils\Data(str_replace("T", " ", $order->TransferToAccountDate)) : null);
                $pedidoCartao->redirectUrlReturn = $order->RedirectUrlReturn;
                $pedidoCartao->bandeira = $bandeira;

                $pedidoCartaoRn->salvar($pedidoCartao);
            }
            
            
            $params["pedidoCartao"] = $pedidoCartao;
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("invoice", $params);
    }
    
    public function expired($params) {
        try {
            
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("invoice_expirada", $params);
    }
   
    public function paid($params) {
        try {
            $idPedidoCartao = \Utils\Get::get($params, 0, 0);
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = $idPedidoCartao;
            
            try {
                $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não encontrado nos sistema");
            }
            
            $params["pedidoCartao"]  = $pedidoCartao;
            $params["sucesso"]  = true;
        } catch (\Exception $ex) {
            $params["sucesso"]  = false;
            $params["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("invoice_paga", $params);
    }
    
    public function getInvoice($params) {
        try {
            
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = \Utils\Get::get($params, "idPedidoCartao", 0);
            
            try {
                $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não encontrado nos sistema");
            }
            
            $json["status"]  = $pedidoCartao->status;
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function gerarQRCodeInvoice($params) {
        $address = \Utils\Get::get($params, 0);
        
        $amount = \Utils\Get::get($params, 1);
        $message = str_replace("-", "%20", \Utils\Get::get($params, 2));
        
        $arrayParams = Array();
        if (!empty($amount)) {
            $arrayParams[] = "amount={$amount}";
        }
        if (!empty($message)) {
            $arrayParams[] = "message={$message}";
        }
        
        $dados = (sizeof($arrayParams) > 0 ? implode("&", $arrayParams) : "");
        //$content = "{$address}";
        $content = "{$address}";
        
        $QRCode = new \Modules\services\Controllers\QRCode();
        $QRCode->getQRCodeImg($content, 5);
    }
    
    
    public function comprovante($params) {
        try {
            $get = $params["_parameters"];
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = \Utils\Get::get($params, 0);
            
            try {
                $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não localizado no sistema");
            }
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = $pedidoCartao->idCliente;
            
            try {
                $clienteRN = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRN->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido não localizado no sistema");
            }
            
            $PDFComprovanteInvoice = new \Modules\pdfs\Controllers\PDFComprovanteInvoice();
            $PDFComprovanteInvoice->gerar($cliente, $pedidoCartao->idInvoice, $pedidoCartao->id, $pedidoCartao->dataPedido);
            
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
}
