<?php

namespace Modules\invoices\Controllers;

class Mensalidades {
    
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            
            
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_mensalidades", $params);
    }
    
    
    public function listar($params) {
        try {
            $post = $params["_POST"];
            
            $idCliente = isset($post["idCliente"]) ? $post["idCliente"] : 0;
            $idPedidoCartao = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            $status = isset($post["status"]) ? $post["status"] : "";
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            $lista = $mensalidadeCartaoRn->filtrar($idCliente, $idPedidoCartao, $status);
            
            $atrasadas = 0;
            $aberto = 0;
            $pagas = 0;
            
            ob_start();
            
            ?>
            <li class="list-group-item bg-head2" style="font-size: 10px;">
                <div class="row">
                    <div class="col col-lg-1 text-center">
                        <strong>Selecionar</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Referência</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Vencimento</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Cartão</strong>
                    </div>
                    <div class="col col-lg-3 text-center">
                        <strong>Cliente</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Valor</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Comprovante</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Status</strong>
                    </div>
                </div>
            </li>
            <?php
            
            if (sizeof($lista) > 0) {
                foreach ($lista as $mensalidadeCartao) {
                    //$mensalidadeCartao = new \Models\Modules\Cadastro\MensalidadeCartao();
                    
                    $bg = "";
                    switch ($mensalidadeCartao->status) {
                        case \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_AGUARDANDO:
                            $aberto += $mensalidadeCartao->valor;
                            $bg = "bg-blue";
                            break;
                        case \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO:
                            $pagas += $mensalidadeCartao->valor;
                            $bg = "bg-green";
                            break;
                        case \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA:
                            $atrasadas += $mensalidadeCartao->valor;
                            $bg = "bg-red";
                            break;
                    }
                    
                    ?>
                    <li class="list-group-item <?php echo $bg ?>" style="font-size: 10px;">
                        <div class="row">
                            <div class="col col-lg-1  text-center">
                                <input type="checkbox" class="check-mensalidade mensalidade-<?php echo $mensalidadeCartao->id ?>" 
                                       value="<?php echo $mensalidadeCartao->id ?>"
                                       <?php echo ($mensalidadeCartao->status == \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA ? "checked" : "") ?>
                                       <?php echo (in_array($mensalidadeCartao->status, Array(\Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA, \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO))? "disabled" : "") ?>
                                       />
                            </div>
                            <div class="col col-lg-2 text-center">
                                <?php echo $mensalidadeCartao->mesRef ?>
                            </div>
                            <div class="col col-lg-1 text-center">
                                <?php echo $mensalidadeCartao->dataValidade->formatar(\Utils\Data::FORMATO_PT_BR)?>
                            </div>
                            <div class="col col-lg-2 text-center">
                                <?php echo $mensalidadeCartao->pedidoCartao->numeroCartao ?>
                            </div>
                            <div class="col col-lg-3">
                                <?php echo $mensalidadeCartao->pedidoCartao->cliente->nome ?>
                            </div>
                            <div class="col col-lg-1 text-center">
                                R$ <?php echo number_format($mensalidadeCartao->valor, 2, ",",".") ?>
                            </div>
                            <div class="col col-lg-1 text-center">
                                <?php if ($mensalidadeCartao->status == \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO) { ?>
                                <a class="btn btn-info btn-circle" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_MENSALIDADES_INVOICE_COMPROVANTE ?>/<?php echo $mensalidadeCartao->id ?>" target="_BLANK_COMP">
                                    <i class="fa fa-print"></i>
                                </a>
                                <?php } ?>
                            </div>
                            <div class="col col-lg-1 text-center">
                                <?php echo $mensalidadeCartao->status ?>
                            </div>
                        </div>
                    </li>
                    <?php
                    
                }
            } else {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-12 text-center">
                            <strong>Nenhum registro para os filtros informados</strong>
                        </div>
                    </div>
                </li>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["aberto"] = number_format($aberto, 2, ",", ".");
            $json["pagas"] = number_format($pagas, 2, ",", ".");
            $json["atrasadas"] = number_format($atrasadas, 2, ",", ".");
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getCartoesByCliente($params) {
        try {
            $post = $params["_POST"];
            
            $idCliente = isset($post["idCliente"]) ? $post["idCliente"] : 0;
            
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo("ativo", "1");
            $where->equalTo("cancelado", "0");
            if ($idCliente > 0) {
                $where->equalTo("id_cliente", $idCliente);
            }
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $result = $pedidoCartaoRn->conexao->listar($where, "numero_cartao");
            
            ob_start();
            ?>
                <option value="0">Todos os cartões</option>
            <?php
            foreach($result as $pedidoCartao) {
            ?>
                <option value="<?php echo $pedidoCartao->id ?>"><?php echo $pedidoCartao->numeroCartao ?></option>
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
    
    private function getMensalidadesPagaveis(array $ids, $idCliente, $idPedidoCartao) {
        $mensalidades = Array();
        if (is_array($ids)) {
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            foreach ($ids as $id) {
                $mensalidadeCartao = new \Models\Modules\Cadastro\MensalidadeCartao(Array("id" => $id));
                try {
                    $mensalidadeCartaoRn->carregar($mensalidadeCartao, true, true);
                    
                    if ($mensalidadeCartao->status != \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO) {
                        $mensalidades[$mensalidadeCartao->id] = $mensalidadeCartao;
                    }
                    
                } catch (\Exception $ex) {
                    
                }
            }
            
            // obrigo o pagamento das mensalidades vencidas não selecionadas
            $resultVencidas = $mensalidadeCartaoRn->filtrar($idCliente, $idPedidoCartao, \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_VENCIDA);
            if (sizeof($resultVencidas) > 0) {
                foreach ($resultVencidas as $mensalidadeCartao) {
                    if (!isset($mensalidades[$mensalidadeCartao->id])) {
                        $mensalidades[$mensalidadeCartao->id] = $mensalidadeCartao;
                    }
                }
            }
        }
        return $mensalidades;
    }
    
    public function pagar($params) {
        try {
            $post = $params["_POST"];
            
            $ids = isset($post["ids"]) ? $post["ids"] : Array();
            $idCliente = isset($post["idCliente"]) ? $post["idCliente"] : 0;
            $idPedidoCartao = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            
            $mensalidades = $this->getMensalidadesPagaveis($ids, $idCliente, $idPedidoCartao);
            if (!sizeof($mensalidades) > 0) {
                throw new \Exception("Você deve selecionar ao menos uma mensalidade.");
            }
            
            $valorTotal = 0;
            $ids = Array();
            ob_start();
            ?>
            <li class="list-group-item bg-head2" style="font-size: 10px;">
                <div class="row">
                    <div class="col col-lg-2 text-center">
                        <strong>Referência</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Vencimento</strong>
                    </div>
                    <div class="col col-lg-3 text-center">
                        <strong>Cartão</strong>
                    </div>
                    <div class="col col-lg-4 text-center">
                        <strong>Cliente</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Valor</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Status</strong>
                    </div>
                </div>
            </li>
            <?php
            foreach ($mensalidades as $mensalidadeCartao) {
                $valorTotal += $mensalidadeCartao->valor;
                $ids[] = $mensalidadeCartao->id;
                ?>
                <li class="list-group-item" style="font-size: 10px;">
                    <div class="row">
                        <div class="col col-lg-2 text-center">
                            <?php echo $mensalidadeCartao->mesRef ?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            <?php echo $mensalidadeCartao->dataValidade->formatar(\Utils\Data::FORMATO_PT_BR)?>
                        </div>
                        <div class="col col-lg-3 text-center">
                            <?php echo $mensalidadeCartao->pedidoCartao->numeroCartao ?>
                        </div>
                        <div class="col col-lg-4">
                            <?php echo $mensalidadeCartao->pedidoCartao->cliente->nome ?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            R$ <?php echo number_format($mensalidadeCartao->valor, 2, ",",".") ?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            <?php echo $mensalidadeCartao->status ?>
                        </div>
                    </div>
                </li>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["html"] = $html;
            $json["valorTotal"] = number_format($valorTotal, 2, ",", ".");
            $json["ids"] = implode("|", $ids);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function gerarInvoice($params) {
        try {
            
            $post = $params["_POST"];
            
            $ids = isset($post["ids"]) ? explode("|", $post["ids"]) : Array();
            $idCliente = isset($post["idCliente"]) ? $post["idCliente"] : 0;
            $idPedidoCartao = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            
            $mensalidades = $this->getMensalidadesPagaveis($ids, $idCliente, $idPedidoCartao);
            if (!sizeof($mensalidades) > 0) {
                throw new \Exception("Nenhum mensalidade selecionada para pagamento");
            }
            
            $valorTotal = 0;
            $ids = Array();
            foreach ($mensalidades as $mensalidadeCartao) {
                $valorTotal += $mensalidadeCartao->valor;
                $ids[] = $mensalidadeCartao->id;
            }
            
            $mensalidadeRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            if ($mensalidadeRn->debitarDoSaldo(new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente)), $ids, $valorTotal)) { 
                $json["saldo"] = true;
            } else {
                $json["saldo"] = false;
                $orders = new \BitcoinToYou\Orders();
                $order = $orders->create($valorTotal, URLBASE_CLIENT . \BitcoinToYou\Access::DEFAULT_REDIRECT_CALLBACK);



                $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
                $mensalidadeCartaoRn->conexao->adapter->iniciar();
                $mensalidadeCartaoRn->zerarDadosInvoices($ids);

                $dt = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));

                foreach ($mensalidades as $mensalidadeCartao) {

                    $mensalidadeCartao->address = $order->DigitalCurrencyAddress;
                    $mensalidadeCartao->dataExpiracaoInvoice = $dt;
                    $mensalidadeCartao->idInvoice = $order->InvoiceId;
                    $mensalidadeCartao->valorBtc = $order->DigitalCurrencyAmount;

                    $mensalidadeCartaoRn->atualizarDadosInvoice($mensalidadeCartao);
                }

                $mensalidadeCartaoRn->conexao->adapter->finalizar();
                $json["address"] = $order->DigitalCurrencyAddress;
                $json["numero"] = $order->InvoiceId;
                $json["time"] = ($dt->timestamp() - time());
                $json["btc"] = number_format($order->DigitalCurrencyAmount, 8, ".", "");

                $json["qr"] = URLBASE_CLIENT . \Utils\Rotas::R_QRCODEINVOICE . "/{$order->DigitalCurrencyAddress}";
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getStatusInvoice($params) {
        try {
            
            $post = $params["_POST"];
            
            $invoiceId = isset($post["invoiceId"]) ? $post["invoiceId"] : 0;
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            
            $json = $mensalidadeCartaoRn->getInvoiceStatus($invoiceId);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function comprovante($params) {
        try {
            $get = $params["_parameters"];
            
            $mensalidadeCartao = new \Models\Modules\Cadastro\MensalidadeCartao();
            $mensalidadeCartao->id = isset($get[0]) ? $get[0] : 0;
            
            try {
                $mensalidadeRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
                $mensalidadeRn->conexao->carregar($mensalidadeCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Mensalidade não localizado no sistema");
            }
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = $mensalidadeCartao->idPedidoCartao;
            
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
            $PDFComprovanteInvoice->gerar($cliente, $mensalidadeCartao->idInvoice, $mensalidadeCartao->id, $mensalidadeCartao->dataValidade);
            
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