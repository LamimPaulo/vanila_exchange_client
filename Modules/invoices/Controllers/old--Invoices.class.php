<?php

namespace Modules\invoices\Controllers;

class Invoices {
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            $usuarioLogado = \Utils\Geral::getLogado();
            if (!\Utils\Geral::isUsuario() || $usuarioLogado->tipo != \Utils\Constantes::ADMINISTRADOR) {
                throw new \Exception("Você não tem permissão para acessar este módulo");
            }
            
            
            $paisRn = new \Models\Modules\Cadastro\PaisRn();
            $paises = $paisRn->conexao->listar(null, "nome");
            
            $params["paises"] = $paises;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_invoices", $params);
    }
    
    
    public function filtrar($params) {
        try {
            $post = $params["_POST"];
            
            $dataInicial = (isset($post["dataInicial"]) && strlen(trim($post["dataInicial"])) == 10) ? new \Utils\Data(trim($post["dataInicial"]) . " 00:00:00") : null;
            $dataFinal = (isset($post["dataFinal"]) && strlen(trim($post["dataFinal"])) == 10) ? new \Utils\Data(trim($post["dataFinal"]) . " 23:59:59") : null;
            $status = isset($post["status"]) ? $post["status"] : "T";
            $pais = isset($post["pais"]) ? $post["pais"] : "000";
            $ativo = isset($post["ativo"]) ? $post["ativo"] : "T";
            $cancelado = isset($post["cancelado"]) ? $post["cancelado"] : "T";
            $bandeira = isset($post["bandeira"]) ? $post["bandeira"] : "T";
            $filtro = isset($post["filtro"]) ? $post["filtro"] : null;
            
            if (!isset($dataInicial->data) || $dataInicial->data == null) {
                throw new \Exception("Data inicial inválida");
            }
            
            if (!isset($dataFinal->data) || $dataFinal->data == null) {
                throw new \Exception("Data final inválida");
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("A data inicial não pode ser maior que a data final");
            }
            
            if (!in_array($status, Array("T", \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO, \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO, \Utils\Constantes::STATUS_PEDIDO_CARTAO_CANCELADO))) {
                throw new \Exception("Status inválido");
            }
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidos = $pedidoCartaoRn->filtrarPedidosCartoes($dataInicial, $dataFinal, $status, $pais, $ativo, $cancelado, $bandeira, $filtro);
            
            
            $pedidosPendentes = 0;
            $pedidosPagos = 0;
            $pedidosCancelados = 0;
            $cartoesAtivos = 0;
            $cartoesInativos = 0;
            $cartoesCancelados = 0;
            
            ob_start();
            if (sizeof($pedidos) > 0) {
                
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $i = 0;
                foreach ($pedidos as $pedidoCartao) {
                    //$pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
                    
                    if ($pedidoCartao->status == \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO) {
                        $pedidosPendentes++;
                    } else if ($pedidoCartao->status == \Utils\Constantes::STATUS_PEDIDO_CARTAO_CANCELADO) {
                        $pedidosCancelados++;
                    } else if ($pedidoCartao->status == \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO) {
                        $pedidosPagos++;
                        if ($pedidoCartao->cancelado > 0) {
                            $cartoesCancelados++;
                        } else if ($pedidoCartao->ativo > 0) {
                            $cartoesAtivos++;
                        } else if ($pedidoCartao->ativo < 1) {
                            $cartoesInativos++;
                        }
                    }
                    
                    $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
                    try {
                        $clienteRn->conexao->carregar($cliente);
                    } catch (\Exception $ex) {
                        $cliente = new \Models\Modules\Cadastro\Cliente();
                    }
                    $this->htmlPedidoCartao($pedidoCartao, $cliente);
                    $i++;
                }
            } else {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-12 text-center">
                            Nenhum pedido para os filtros informados
                        </div>
                    </div>
                </li>
                <?php
            }
            
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["pedidosPendentes"] = $pedidosPendentes;
            $json["pedidosPagos"] = $pedidosPagos;
            $json["pedidosCancelados"] = $pedidosCancelados;
            $json["cartoesAtivos"] = $cartoesAtivos;
            $json["cartoesInativos"] = $cartoesInativos;
            $json["cartoesCancelados"] = $cartoesCancelados;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function htmlPedidoCartao(\Models\Modules\Cadastro\PedidoCartao $pedidoCartao, \Models\Modules\Cadastro\Cliente $cliente) {
        ?>
        <ul class="list-group" id="html-cartao-id-<?php echo $pedidoCartao->id ?>">
            <li class="list-group-item" style="border-bottom: none;">
                <div class="row" >
                    <div class="col-xs-6" >
                        <strong>Cliente: </strong> <?php echo $cliente->nome ?>
                    </div>
                    <div class="col-xs-6" >
                        <strong>Email: </strong> <?php echo $cliente->email ?>
                    </div>
                </div>
            </li>
            <li class="list-group-item"  style="border-top: none; border-bottom: none;">
                <div class="row" >
                    <div class="col-xs-3" >
                        <strong>Nº: </strong> <?php echo $pedidoCartao->id ?>
                    </div>
                    <div class="col-xs-9" >
                        <strong>Endereço de pagamento: </strong> <?php echo $pedidoCartao->address ?>
                    </div>
                </div>
            </li>
            <li class="list-group-item"  style="border-top: none; border-bottom: none;">
                <div class="row" >
                    <div class="col-xs-4" >
                        <strong>Data do pedido:</strong> <?php echo $pedidoCartao->dataPedido->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                    </div>
                    <div class="col-xs-4" >
                        <strong>Valor Total:</strong> <?php echo number_format($pedidoCartao->currencyTotal, 2, ",", ".") ?>
                    </div>
                    
                    <div class="col-xs-4" >
                        <strong>Bandeira:</strong> <?php echo ucfirst($pedidoCartao->bandeira) ?>
                    </div>
                </div>
            </li>
            <li class="list-group-item"  style="border-top: none; border-bottom: none;">
                <div class="row" >
                    <div class="col col-xs-4" >
                        <strong>Status:</strong> <?php echo $pedidoCartao->getStatus() ?>
                    </div>
                    <div class="col-xs-2 text-center" >
                        <button class="btn btn-primary btn-circle" onclick="detalhesPedidos(<?php echo $pedidoCartao->id ?>);">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>

                    <?php if ($pedidoCartao->cancelado < 1) { ?>
                    <div class="col-xs-2 text-center" >
                        <button class="btn btn-primary " onclick="inserirDadosCartao(<?php echo $pedidoCartao->id ?>);">
                            <i class="fa fa-credit-card"></i> Cartão
                        </button>
                    </div>
                    <?php } ?>


                    <?php if ($pedidoCartao->cancelado < 1 && !empty($pedidoCartao->numeroCartao) && !empty($pedidoCartao->nomeCartao) && !empty($pedidoCartao->idCartao) && !empty($pedidoCartao->senhaCartao)) { ?>
                    <div class="col-xs-2 text-center" >
                        <?php if ($pedidoCartao->ativo > 0) { ?>
                        <button class="btn btn-danger " onclick="alterarStatusCartao(<?php echo $pedidoCartao->id ?>);">
                            <i class="fa fa-square"></i> Desativar Cartão
                        </button>
                        <?php } else { ?>
                        <button class="btn btn-success " onclick="alterarStatusCartao(<?php echo $pedidoCartao->id ?>);">
                            <i class="fa fa-check"></i> Ativar Cartão
                        </button>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($pedidoCartao->cancelado < 1) { ?>
                    <div class="col-xs-2 text-center" >
                        <button class="btn btn-danger " onclick="dialogCancelarCartao(<?php echo $pedidoCartao->id ?>);">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                    </div>
                    <?php } else { ?>
                    <div class="col-xs-2 text-center" >
                        Cancelado em <?php echo $pedidoCartao->dataCancelamentoCartao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?>
                    </div>
                    <?php } ?>
                </div>
            </li>
            
            <?php if (!empty($pedidoCartao->numeroCartao) || !empty($pedidoCartao->nomeCartao) || !empty($pedidoCartao->idCartao)) { ?>
            <li class="list-group-item" style="border-top: none; border-bottom: none;">
                <div class="row" >
                    <div class="col-lg-3" >
                        <strong>Cartão:</strong> <?php echo ucfirst($pedidoCartao->bandeira) . " - " . $pedidoCartao->numeroCartao ?>
                    </div>
                    <div class="col-lg-4" >
                        <strong>Nome:</strong> <?php echo $pedidoCartao->nomeCartao ?>
                    </div>
                    <div class="col-lg-3" >
                        <strong>Id:</strong> <?php echo $pedidoCartao->idCartao ?>
                    </div>
                    <div class="col-lg-2" >
                        <strong>Status:</strong> <?php echo ($pedidoCartao->ativo > 0 ? "Ativo" : "Inativo") ?>
                    </div>
                </div>
            </li>
            <?php } ?>
            <li class="list-group-item" style="border-top: none;"></li>
        </ul>
        
        <?php
    }
    
    public function getDadosInvoice($params) {
        try {
            $usuarioLogado = \Utils\Geral::getLogado();
            $post = $params["_POST"];
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            try {
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Identificação do pedido inválida");
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            
            ob_start();
            ?>
            <ul class="list-group">
                <li class="list-group-item">
                    <strong>Cliente: </strong><?php echo $cliente->nome ?>
                </li>
                <li class="list-group-item">
                    <strong>Email: </strong><?php echo $cliente->email ?>
                </li>
                <li class="list-group-item">
                    <strong>Celular: </strong><?php echo $cliente->celular ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Número do Pedido: </strong><?php echo $pedidoCartao->id ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Bandeira: </strong><?php echo ucfirst($pedidoCartao->bandeira) ?>
                </li>
                <li class="list-group-item">
                    <strong>Número da Invoice: </strong><?php echo $pedidoCartao->idInvoice ?>
                </li>
                <li class="list-group-item" id="html-li-status-id-<?php echo $pedidoCartao->id ?>">
                    <strong>Status: </strong> <?php echo $pedidoCartao->getStatus() ?>
                    <?php if (\Utils\Geral::isUsuario() && $usuarioLogado->tipo === \Utils\Constantes::ADMINISTRADOR && $pedidoCartao->status == \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO) { ?>
                    <button class="btn btn-success pull-right btn-sm" onclick="dialogPagarPedido(<?php echo $pedidoCartao->id ?>);">
                        Marcar como Pago
                    </button>
                    <br><br>
                    <?php } ?>
                </li>
                <li class="list-group-item">
                    <strong>Endereço de Pagamento: </strong><?php echo $pedidoCartao->address ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Valor Cobrado: </strong><?php echo number_format($pedidoCartao->valorTotal, 2, ".", ",") ?>
                </li>
                <li class="list-group-item">
                    <strong>Moeda: </strong><?php echo $pedidoCartao->currency ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Valor Total: </strong><?php echo number_format($pedidoCartao->currencyTotal, 2, ".", ",") ?>
                </li>
                <li class="list-group-item">
                    <strong>Data do Pedido: </strong><?php echo ($pedidoCartao->dataPedido != null ? $pedidoCartao->dataPedido->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                </li>
                <li class="list-group-item">
                    <strong>Data de Expiração: </strong><?php echo ($pedidoCartao->dataExpiracaoInvoice != null ? $pedidoCartao->dataExpiracaoInvoice->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                </li>
                <li class="list-group-item">
                    <strong>Timestamp Expiração: </strong><?php echo $pedidoCartao->expirationTimestamp ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Moeda Digital: </strong><?php echo $pedidoCartao->digitalCurrency ?>
                </li>
                <li class="list-group-item">
                    <strong>Valor Moeda Digital: </strong><?php echo number_format($pedidoCartao->digitalCurrencyAmount, 2, ".", ",") ?>
                </li>
                <li class="list-group-item">
                    <strong>Cotação Moeda Digital: </strong><?php echo number_format($pedidoCartao->digitalCurrencyQuotation, 2, ".", ",") ?>
                </li>
                <li class="list-group-item">
                    <strong>Data de Pagamento: </strong><?php echo ($pedidoCartao->dataPagamento != null ? $pedidoCartao->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                </li>
                
                <li class="list-group-item">
                    <strong>Valor Pago Moeda Digital: </strong><?php echo number_format($pedidoCartao->digitalCurrencyAmountPaid, 2, ".", ",") ?>
                </li>
                <li class="list-group-item">
                    <strong>Custom Id: </strong><?php echo $pedidoCartao->customId ?>
                </li>
                <li class="list-group-item">
                    <strong>Email de Notificação: </strong><?php echo $pedidoCartao->notificationEmail ?>
                </li>
                <li class="list-group-item">
                    <strong>: </strong><?php echo $pedidoCartao->redirectUrl ?>
                </li>
                <li class="list-group-item">
                    <strong>Redirecionamento de retorno: </strong><?php echo $pedidoCartao->redirectUrlReturn ?>
                </li>
                <li class="list-group-item">
                    <strong>TC 0015Id: </strong><?php echo $pedidoCartao->tc0015Id ?>
                </li>
                
                <li class="list-group-item">
                    <strong>Data de Transferência para a Conta: </strong><?php echo ($pedidoCartao->transferToAccountDate != null ? $pedidoCartao->transferToAccountDate->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                </li>
                
                <li class="list-group-item">
                    <strong>Timestamp de Transferência para a Conta: </strong><?php echo $pedidoCartao->transferToAccountTimestamp ?>
                </li>
                <li class="list-group-item">
                    <strong>Data Estimada de Transferência para a Conta: </strong><?php echo ($pedidoCartao->transferToAccountEstimateDate != null ? $pedidoCartao->transferToAccountEstimateDate->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                </li>
                
                <li class="list-group-item">
                    <strong>Timestamp Estimada de Transferência para a Conta: </strong><?php echo $pedidoCartao->transferToAccountEstimateTimestamp ?>
                </li>
            </ul>
            <?php
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
    
    
    public function getDadosCartao($params) {
        try {
            $post = $params["_POST"];
            
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            try {
                $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Peiddo não localizado no sistema");
            }
            
            $json["pedido"] = Array(
                "id" => $pedidoCartao->id,
                "nomeCartao" => $pedidoCartao->nomeCartao,
                "numeroCartao" => $pedidoCartao->numeroCartao,
                "idCartao" => $pedidoCartao->idCartao,
                "senhaCartao" => $pedidoCartao->senhaCartao,
                "validade" => $pedidoCartao->validade,
                "ativo" => $pedidoCartao->ativo,
            );
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvarDadosCartao($params) {
        try {
            $post = $params["_POST"];
            
            $idPedidoCartao=isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            $nomeCartao = isset($post["nomeCartao"]) ? $post["nomeCartao"] : null;
            $numeroCartao =isset($post["numeroCartao"]) ? $post["numeroCartao"] : null;
            $idCartao = isset($post["idCartao"]) ? $post["idCartao"] : null;
            $senhaCartao= isset($post["senhaCartao"]) ? $post["senhaCartao"] : null;
            $validade= isset($post["validade"]) ? $post["validade"] : null;
            
            if (empty($validade)) {
                throw new \Exception("Validade do cartão inválida");
            }
            
            $arrayValidade = explode("/", $validade);
            if (sizeof($arrayValidade) != 2) {
                throw new \Exception("Validade do cartão inválida");
            }
            if ($arrayValidade[0] < 1 || $arrayValidade[0] > 12) {
                throw new \Exception("Mês de validade do cartão inválida");
            }
            
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = $pedidoCartaoRn->alterarDadosCartao($idPedidoCartao, $nomeCartao, $numeroCartao, $idCartao, $senhaCartao, $validade);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            ob_start();
            
            $this->htmlPedidoCartao($pedidoCartao, $cliente);
            
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Dados do cartão atualizados com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function cards($params) {
        \Utils\Layout::view("index_cartoes_cliente", $params);
    }
    
    public function alterarStatusCartao($params) {
        try {
            $post = $params["_POST"];
            
            $idPedidoCartao=isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = $pedidoCartaoRn->alterarStatusCartao($idPedidoCartao);
            
            $status = ($pedidoCartao->ativo > 0 ? "ativado" : "desativado");
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            ob_start();
            
            $this->htmlPedidoCartao($pedidoCartao, $cliente);
            
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Cartão {$status} com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function cancelar($params) {
        try {
            $post = $params["_POST"];
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            $pedidoCartaoRn->cancelar($pedidoCartao);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            ob_start();
            
            $this->htmlPedidoCartao($pedidoCartao, $cliente);
            
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Cartão cancelado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function confirmarPagamentoPedidoCartao($params) {
        try {
            $post = $params["_POST"];
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0;
            $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO;
            $pedidoCartaoRn->conexao->update(
                    Array(
                        "status" => $pedidoCartao->status,
                        "data_pagamento" => date("Y-m-d H:i:s")
                    ), 
                    Array("id" => $pedidoCartao->id));
            
            
            ob_start();
            
            ?>
              <li class="list-group-item" id="html-li-status-id-<?php echo $pedidoCartao->id ?>">
                    <strong>Status: </strong> <?php echo $pedidoCartao->getStatus() ?>
                    
                </li>  
            <?php
            
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "O Status do cartão foi alterado para Pago!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}