<?php

namespace Modules\invoices\Controllers;

class Cards {
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            
            $paisRn = new \Models\Modules\Cadastro\PaisRn();
            $paises = $paisRn->conexao->listar(null, "nome");
            
            $params["paises"] = $paises;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("index_cartoes_adm", $params);
    }
    
    
    public function listar($params) {
        try {
            $post = $params["_POST"];
            
            $pais = \Utils\Post::get($params, "pais", "000");
            $ativo = \Utils\Post::get($params, "ativo", "T");
            $cancelado = \Utils\Post::get($params, "cancelado", "T");
            $bandeira = \Utils\Post::get($params, "bandeira", "T");
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $listaCartoes = $pedidoCartaoRn->filtrarCartoesCadastrados($pais, $ativo, $cancelado, $bandeira, $filtro);
            
            $dados = $this->htmlList($listaCartoes);
            $json["html"] = $dados["html"];
            
            $json["pedidosPendentes"] = $dados["pedidosPendentes"];
            $json["pedidosPagos"] = $dados["pedidosPagos"];
            $json["pedidosCancelados"] = $dados["pedidosCancelados"];
            $json["cartoesAtivos"] = $dados["cartoesAtivos"];
            $json["cartoesInativos"] = $dados["cartoesInativos"];
            $json["cartoesCancelados"] = $dados["cartoesCancelados"];
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlList($listaCartoes) {
        ob_start();
        ?>
        <li class="list-group-item" style="font-size: 10px; font-weight: bold;">
            <div class="row">
                <div class="col col-sm-3 text-center">
                    Nome
                </div>
                <div class="col col-sm-3 text-center">
                    Número
                </div>
                <div class="col col-sm-2 text-center">
                    Documento
                </div>
                <div class="col col-sm-1 text-center">
                    Bandeira
                </div>
                <div class="col col-sm-1 text-center">
                    Mensalidades
                </div>
                <div class="col col-sm-1 text-center">
                    Ativo?
                </div>
                <div class="col col-sm-1 text-center">
                    Cancel
                </div>
            </div>
        </li>
        <?php
        
        $pedidosPendentes = 0;
        $pedidosPagos = 0;
        $pedidosCancelados = 0;
        $cartoesAtivos = 0;
        $cartoesInativos = 0;
        $cartoesCancelados = 0;
        
        if (sizeof($listaCartoes) > 0) {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            foreach ($listaCartoes as $pedidoCartao) {
                
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
                $this->htmlCartao($pedidoCartao, $cliente);
            }
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        Nenhum cartão gerado no sistema
                    </div>
                </div>
            </li>
            <?php
        }
        
        $dados["pedidosPendentes"] = $pedidosPendentes;
        $dados["pedidosPagos"] = $pedidosPagos;
        $dados["pedidosCancelados"] = $pedidosCancelados;
        $dados["cartoesAtivos"] = $cartoesAtivos;
        $dados["cartoesInativos"] = $cartoesInativos;
        $dados["cartoesCancelados"] = $cartoesCancelados;
        $dados["html"] = ob_get_contents();
        ob_end_clean();
        return $dados;
    }
    
    private function htmlCartao(\Models\Modules\Cadastro\PedidoCartao $cartao, \Models\Modules\Cadastro\Cliente $cliente) {
        $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
        $atrasadas = $mensalidadeCartaoRn->hasMensalidadesAtrasadas($cartao);
        
        ?>
            <li class="list-group-item" id="html-cartao-id-<?php echo $cartao->id ?>">
                <div class="row">
                    <div class="col col-sm-3">
                        <?php echo $cartao->nomeCartao ?>
                    </div>
                    <div class="col col-sm-3 text-center">
                        <?php echo $cartao->numeroCartao ?>
                    </div>
                    <div class="col col-sm-2 text-center">
                        <?php echo $cliente->documento ?>
                    </div>
                    <div class="col col-sm-1 text-center">
                        <?php echo ucfirst($cartao->bandeira) ?>
                    </div>
                    <div class="col col-sm-1 text-center">
                        <?php echo ($atrasadas ? "Atrasadas" : "OK") ?>
                    </div>
                    <div class="col col-sm-1 text-center">
                        <?php if ($cartao->ativo > 0) { ?>
                        <button class="btn btn-success btn-circle" title="Clique para desativar o cartão." onclick="alterarStatusCartao(<?php echo $cartao->id ?>);">
                            <i class="fa fa-check"></i>
                        </button>
                        <?php } else { ?>
                        <button class="btn btn-danger btn-circle" title="Clique para ativar o cartão." onclick="alterarStatusCartao(<?php echo $cartao->id ?>);">
                            <i class="fa fa-square"></i>
                        </button>
                        <?php } ?>
                    </div>
                    <div class="col col-sm-1 text-center">
                        <?php if ($cartao->cancelado < 1) { ?>
                        <button class="btn btn-danger btn-circle" title="Clique para cancelar o cartão." onclick="dialogCancelarCartao(<?php echo $cartao->id ?>);">
                            <i class="fa fa-times"></i>
                        </button>
                        <?php } else { ?>
                        <i class="fa fa-check"></i>
                        <?php } ?>
                    </div>
                </div>
            </li>
        <?php
    }
    
    
    
    public function alterarStatusCartao($params) {
        try {
            
            
            $idPedidoCartao= \Utils\Post::get($params, "idPedidoCartao", 0);
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = $pedidoCartaoRn->alterarStatusCartao($idPedidoCartao);
            
            $status = ($pedidoCartao->ativo > 0 ? "ativado" : "desativado");
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            ob_start();
            
            $this->htmlCartao($pedidoCartao, $cliente);
            
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
          
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
            $pedidoCartao->id = \Utils\Post::get($params, "idPedidoCartao", 0);
            $pedidoCartaoRn->cancelar($pedidoCartao);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pedidoCartao->idCliente));
            $clienteRn->conexao->carregar($cliente);
            ob_start();
            
            $this->htmlCartao($pedidoCartao, $cliente);
            
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
    
    public function saldos($params) {
        
        try {
            
            
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
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_consulta_saldo", $params);
    }
    
    
    public function extratos($params) {
        
        try {
            
            
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
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_consulta_extrato", $params);
    }
    
    public function consultaSaldo($params) {
        try {
          
            
            $numeroCartao = isset($post["numeroCartao"]) ? $post["numeroCartao"] : null;
            if (empty($numeroCartao)) {
                throw new \Exception("O número do cartão deve ser informado");
            }
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
         
            $pedidoCartao = $pedidoCartaoRn->getByNumero($numeroCartao);
            if ($pedidoCartao == null) {
                throw new \Exception("Cartão não encontrado no sistema. Verifique o número informado e tente novamente.");
            }
           
            $consulta = new \APICartao\Consulta();
            $dados = $consulta->saldo($numeroCartao);
            
            $json["dados"] = $dados;
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        print json_encode($json);
        
    }
    
    
    public function consultaExtrato($params) {
        try {
           
            
            $numeroCartao = \Utils\Post::get($params, "numeroCartao", null);
            
            if (empty($numeroCartao)) {
                throw new \Exception("O número do cartão deve ser informado");
            }
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            
                $pedidoCartao = $pedidoCartaoRn->getByNumero($numeroCartao);
                if ($pedidoCartao == null) {
                    throw new \Exception("Cartão não encontrado no sistema. Verifique o número informado e tente novamente.");
                }
            try {     
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $cliente->id = $pedidoCartao->idCliente;
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não encontrado");
            }
            
            $consulta = new \APICartao\Consulta();
            $dados = $consulta->extrato($numeroCartao, $cliente->documento);
            
            ob_start();
            ?>
            
            <h1><?php echo $dados->title ?></h1>
            <div class="alert alert-success">
                <strong><?php echo $dados->message ?></strong>
            </div>
            
            <br><br>
            
            <?php if (sizeof($dados->extract) > 0) { ?>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th><strong>Data</strong></th>
                        <th><strong>Descrição</strong></th>
                        <th><strong>Valor</strong></th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php foreach ($dados->extract as $extrato) { ?>
                    <tr>
                        <td><?php echo $extrato->date ?></td>
                        <td><?php echo $extrato->description ?></td>
                        <td><?php echo $extrato->amount ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                
                <tfoot>
                    <tr>
                        <th>
                            <h1>Saldo: <?php echo $dados->balance ?></h1>
                        </th>
                    </tr>
                </tfoot>
            </table>
            <?php } ?>
            
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        print json_encode($json);
        
    }
    
    
    public function testApiRecarga($params) {
        $recargaCartao = new \Models\Modules\Cadastro\RecargaCartao(Array("id" => 1486060791));
        
        Cards::executaRecargaVisa($recargaCartao);
        
    }
    
    public static function executaRecargaVisa(\Models\Modules\Cadastro\RecargaCartao $recargaCartao) {
        try {
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            try{
                $recargaCartaoRn->conexao->carregar($recargaCartao);
            } catch (Exception $ex) {
                throw new \Exception("Pedido de recarga não encontrado no sistema");
            }
            
            if ($recargaCartao->status == \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO) {
                $recarga = new \Visa\Recarga(true);


                $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao(Array("id" => $recargaCartao->idPedidoCartao));
                $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
                $pedidoCartaoRn->conexao->carregar($pedidoCartao);
                $xml = $recarga->recarga($pedidoCartao->numeroCartao, $recargaCartao->valorReal);

                $recargaCartaoRn->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO,
                        "data_recarga_finalizada" => date("Y-m-d H:i:s")
                    ),
                    Array(
                        "id" => $recargaCartao->id
                    )
                );
                
            }
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
}