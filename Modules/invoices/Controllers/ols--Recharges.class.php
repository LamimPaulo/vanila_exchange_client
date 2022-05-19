<?php

namespace Modules\invoices\Controllers;

class Recharges {
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    public function index($params) {
        
        try {
            $post = $params["_POST"];
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            
            $where = new \Zend\Db\Sql\Where();
            if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $where->equalTo("id_cliente", $cliente->id);
            }
            
            $where->equalTo("ativo", 1);
            $where->equalTo("cancelado", 0);
            
            $cartoes = $pedidoCartaoRn->conexao->listar($where, "numero_cartao");
            $params["cartoes"] = Array();
            foreach ($cartoes as $cartao) {
                $params["cartoes"][] = $cartao;
            }
            
            if (\Utils\Geral::isCliente()) {
                $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
                $c = $recargaCartaoRn->getCartoesRecargaParaTerceiros();
                foreach ($c as $pedidoCartao) {
                    $params["cartoes"][] = $pedidoCartao;
                }
            }
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_recharges", $params);
    }
    
    
    public function filtrar($params) {
        try {
            $post = $params["_POST"];
            
            $dataInicial = (isset($post["dataInicial"]) && strlen(trim($post["dataInicial"])) == 10) ? new \Utils\Data(trim($post["dataInicial"]) . " 00:00:00") : null;
            $dataFinal = (isset($post["dataFinal"]) && strlen(trim($post["dataFinal"])) == 10) ? new \Utils\Data(trim($post["dataFinal"]) . " 23:59:59") : null;
            $status = (isset($post["status"]) ? $post["status"] : "T");
            $idPedidoCartao = (isset($post["idPedidoCartao"]) ? $post["idPedidoCartao"] : 0);
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
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            
            $recargas = $recargaCartaoRn->filtrar($dataInicial, $dataFinal, $status, $idPedidoCartao, $filtro);
            
            $retorno = $this->htmlListaRecargas($recargas);
            
            $json["dados"] = $retorno["dados"];
            $json["html"] = $retorno["html"];
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    function htmlListaRecargas($lista) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $isCliente = \Utils\Geral::isCliente();
        $isAdm = (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR);
        
        
        ob_start();
        ?>
        <li class="list-group-item"> 
            <div class="row" style="font-size: 10px; font-weight: bold;">
                <div class="col col-md-1 text-center">
                    Pedido
                </div>
                <div class="col col-md-2 text-center">
                    Cartão
                </div>
                <div class="col col-md-2 text-center">
                    Data Pedido
                </div>
                <div class="col col-md-1 text-center">
                    Valor R$
                </div>
                <div class="col col-md-2 text-center">
                    Valor BTC
                </div>
                <div class="col col-md-1 text-center">
                    Comprovante
                </div>
                <?php if ($isCliente) { ?>
                <div class="col col-md-1 text-center">
                    Pagar
                </div>
                <?php }  ?>

                <?php if ($isAdm) { ?>
                <div class="col col-md-1 text-center">
                    Alterar
                </div>
                <?php }  ?>

                <div class="col col-md-2 text-center">
                    Status
                </div>
            </div>
        </li>
        <?php
        
        $aguardando = 0;
        $pagos = 0;
        $finalizados = 0;
        $cancelados = 0;
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $recargaCartao) {
                
                switch ($recargaCartao->status) {
                    case \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO:
                        $aguardando++;
                        break;
                    case \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO:
                        $cancelados++;
                        break;
                    case \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO:
                        $finalizados++;
                        break;
                    case \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO:
                        $pagos++;
                        break;
                }
                
                $this->htmlRecargaCartao($recargaCartao);
            }
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-xs-12 text-center" style="background-color: white;">
                        Nenhuma recarga encontrada para os dados informados
                    </div>
                </div>
            </li>
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
    
    
    function htmlRecargaCartao(\Models\Modules\Cadastro\RecargaCartao $recargaCartao) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $isCliente = \Utils\Geral::isCliente();
        $isAdm = (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR);
        
        $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao(Array("id" => $recargaCartao->idPedidoCartao));
        $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
        $pedidoCartaoRn->conexao->carregar($pedidoCartao);
        
        ?>
        <li class="list-group-item" id="recarga-<?php echo $recargaCartao->id ?>" style="font-size: 10px; font-weight: bold;">
            <div class="row" >
                <div class="col col-md-1 text-center">
                    <?php echo $recargaCartao->id ?>
                </div>
                <div class="col col-md-2 text-center">
                    <?php echo ucfirst($pedidoCartao->bandeira) . " - " . $pedidoCartao->numeroCartao ?>
                </div>
                <div class="col col-md-2 text-center">
                    <?php echo $recargaCartao->dataPedido->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                </div>
                <div class="col col-md-1 text-center">
                    <?php echo number_format($recargaCartao->valorReal, 2, ",", ".") ?>
                </div>
                <div class="col col-md-2 text-center">
                    <?php echo number_format($recargaCartao->valorBtc, 2, ",", ".") ?>
                </div>
                <div class="col col-md-1 text-center">
                    <a class="btn btn-link btn-circle" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_RECARGAS_INVOICE_COMPROVANTE ?>/<?php echo $recargaCartao->id ?>" target="_BLANK_COMP">
                        <i class="fa fa-print"></i>
                    </a>
                </div>
                <?php if ($isCliente) { ?>
                <div class="col col-md-1 text-center">
                    <?php if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO) { ?>
                    <button class="btn btn-success btn-circle btn-sm" onclick="pagar(<?php echo $recargaCartao->id ?>);">
                        <i class="fa fa-dollar"></i>
                    </button>
                    <?php } ?>
                </div>
                <?php }  ?>
                
                <?php if ($isAdm) { ?>
                <div class="col col-md-1 text-center">
                    <button class="btn btn-success btn-circle btn-sm" onclick="dialogAlterarStatusRecarga(<?php echo $recargaCartao->id ?>, '<?php echo $recargaCartao->status ?>');">
                        <i class="fa fa-credit-card"></i>
                    </button>
                </div>
                <?php }  ?>
                
                <div class="col col-md-2 text-center">
                    <?php echo $recargaCartao->getStatus() ?>
                </div>
            </div>
        </li>
        <?php
    }
    
    
    public function finalizar($params) {
        try {
            
            $post = $params["_POST"];
            
            $recargaCartao = new \Models\Modules\Cadastro\RecargaCartao();
            $recargaCartao->id = isset($post["idRecargaCartao"]) ? $post["idRecargaCartao"] : 0;
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $recargaCartaoRn->finalizar($recargaCartao);
            
            ob_start();
            $this->htmlRecargaCartao($recargaCartao);
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Recarga finalizada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    
    public function getInvoicePaymentData($params) {
        try {
            $post = $params["_POST"];
            
            $recargaCartao = new \Models\Modules\Cadastro\RecargaCartao();
            $recargaCartao->id = isset($post["idRecargaCartao"]) ? $post["idRecargaCartao"] : 0;
            
            try {
                $recargaCartaoRN = new \Models\Modules\Cadastro\RecargaCartaoRn();
                $recargaCartaoRN->conexao->carregar($recargaCartao);
            } catch (\Exception $ex) {
                throw new \Exception("Pedido de recarga não lozalizado no sistema");
            }
            
            if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO) {
                throw new \Exception("Pedido de recarga já consta como pago");
            }
            if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO) {
                throw new \Exception("Pedido de recarga cancelado");
            }
            if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO) {
                throw new \Exception("Pedido de recarga consta como finalizado no sistema");
            }
            
            if ($recargaCartao->dataExpiracaoInvoice->menor(new \Utils\Data(date("d/m/Y H:i:s")))) {
                throw new \Exception("Invoice expirada");
            }
            
            $json["id"] = $recargaCartao->id;
            $json["valor"] = number_format($recargaCartao->valorBtc, 8, ".", "");
            $json["address"] = $recargaCartao->address;
            $json["timeToExpire"] = ($recargaCartao->dataExpiracaoInvoice->timestamp() - time());
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}