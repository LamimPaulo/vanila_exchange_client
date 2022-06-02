<?php

namespace Modules\contasempresa\Controllers;

class P2p {
    
    private  $codigoModulo = "servicos";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        
        try {
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarios = $usuarioRn->conexao->listar(null, "nome");
            $params["usuarios"] = $usuarios;
            
            $params["sucesso"] = true;
        } catch(\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_p2p", $params);
    }
    
    public function cadastro($params) {
        try {
            
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            
            $p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
            $p2pVenda->id = \Utils\Get::get($params, 0, 0);
            
            if ($p2pVenda->id > 0) {
                $p2pVendaRn->conexao->carregar($p2pVenda);
            }
            
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarios = $usuarioRn->conexao->listar(null, "nome");
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $bancos = $bancoRn->conexao->listar(null, "nome");
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $saldo = $contaCorrenteBtcEmpresaRn->calcularSaldoConta(2);
            
            $params["saldo"] = number_format($saldo, 8, ".", ",");
            $params["bancos"] = $bancos;
            $params["usuarios"] = $usuarios;
            $params["p2p"] = $p2pVenda;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            //exit(print_r($ex));
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("cadastro_p2p", $params);
    }
    
    public function listar($params) {
        try {
                        
            $dataInicial = \Utils\Post::getData($params, dataInicial, NULL, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $tipoData = \Utils\Post::get($params, "tipoData", null);
            $texto = \Utils\Post::get($params, "texto", null);
            $tipoDeposito = \Utils\Post::get($params, "tipoDeposito", null);
            $status = \Utils\Post::get($params, "status", null);
            $idUsuario = \Utils\Post::getArray($params, "idUsuario", null);
            
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            
            $lista = $p2pVendaRn->filtrar($dataInicial, $dataFinal, $tipoData, $texto, $tipoDeposito, $status, $idUsuario);
            
            $totalCompra = 0;
            $totalVenda = 0;
            $totalBtc = 0;
            
            ob_start();
            if (sizeof($lista) > 0) {
                foreach ($lista as $p2pVenda) {
                    //$p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
                    
                    if ($p2pVenda->tipo == \Utils\Constantes::ORDEM_COMPRA) {
                        $totalCompra += ($p2pVenda->valorCotacao * $p2pVenda->volumeBtc);
                    } else {
                        $totalVenda += ($p2pVenda->valorCotacao * $p2pVenda->volumeBtc);
                    }
                    $totalBtc += $p2pVenda->volumeBtc;
                    $this->getHtmlItemList($p2pVenda);
                }
            } else {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-12 text-center">
                            Nenhum dado encontrado para os filtros informados
                        </div>
                    </div>
                </li>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["totalVendas"] = sizeof($lista);
            $json["totalVendido"] = number_format($totalVenda, 2, ",", ".");
            $json["totalComprado"] = number_format($totalCompra, 2, ",", ".");
            $json["totalBtc"] = number_format($totalBtc, 8, ".", "");
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function getHtmlItemList(\Models\Modules\Cadastro\P2pVenda $p2pVenda) {
        
        $bg = "";
        switch ($p2pVenda->status) {
            case \Utils\Constantes::P2P_STATUS_CONCLUIDO:
                $bg = "";
                break;
            case \Utils\Constantes::P2P_STATUS_AGUARDANDO_DEPOSITO:
                $bg = "";
                break;
            case \Utils\Constantes::P2P_STATUS_PENDENTE:
                $bg = "bg-yellow";
                break;
            case \Utils\Constantes::P2P_STATUS_PROCESSANDO:
                $bg = "bg-blue";
                break;

            default:
                break;
        }
        
        ?>
        <li class="list-group-item " id="p2p-<?php echo $p2pVenda->id ?>" style="border: none !important;">
            <div class="row">
                <div class="col col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body <?php echo $bg ?>">
                            <div class="row">
                                <div class="col-lg-4">
                                    <strong>Data de lançamento:</strong> <?php echo ($p2pVenda->dataLancamento != null ? $p2pVenda->dataLancamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "")?>
                                </div>
                                <div class="col-lg-4">
                                    <strong>Data de atualização:</strong> <?php echo ($p2pVenda->dataAlteracao != null ? $p2pVenda->dataAlteracao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "") ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-6">
                                    <strong>Data da Operação:</strong> <?php echo ($p2pVenda->dataOperacao != null ? $p2pVenda->dataOperacao->formatar(\Utils\Data::FORMATO_PT_BR) : "")  ?>
                                </div>
                                <div class="col col-lg-6">
                                    <strong>Nome do Usuário:</strong> <?php echo ($p2pVenda->usuario != null ? $p2pVenda->usuario->nome : "") ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <strong>Nome do cliente:</strong> <?php echo $p2pVenda->nomeCliente ?>
                                </div>
                                <div class="col col-lg-5">
                                    <strong>Email do cliente:</strong> <?php echo $p2pVenda->emailCliente ?>
                                </div>
                                <div class="col col-lg-3">
                                    <strong>Telefone:</strong> <?php echo $p2pVenda->telefone ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-6">
                                    <strong>Tipo de Depósito:</strong> <?php echo $p2pVenda->getTipoDeposito() ?>
                                </div>
                                <div class="col col-lg-6">
                                    <strong>Valor do Depósito:</strong> <?php echo number_format($p2pVenda->valor, 2, ",", ".") ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col col-lg-4">
                                    <strong>Cotação:</strong> <?php echo number_format($p2pVenda->valorCotacao, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Volume BTC:</strong> <?php echo number_format($p2pVenda->volumeBtc, 8, ".", "") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Carteira:</strong> 
                                        <?php if (!empty($p2pVenda->carteiraDestino)) { ?>
                                            <a href="https://blockchain.info/address/<?php echo $p2pVenda->carteiraDestino ?>" style="color: white;" target="_BLANK">
                                                <?php echo $p2pVenda->carteiraDestino ?>
                                            </a>
                                        <?php } ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-3">
                                    <strong>Status:</strong> <?php echo $p2pVenda->getStatus() ?>
                                </div>
                                <div class="col col-lg-12">
                                    <strong>Hash: <a href="<?php echo $p2pVenda->hash ?>"  style="color: white;" target="_BLANK"><?php echo $p2pVenda->hash ?></a></strong>
                                </div>
                            </div>

                            <br>
                            
                            <div class="row">
                                <div class="col col-lg-12 text-center">
                                    <button class="btn btn-danger" onclick="modalExcluir(<?php echo $p2pVenda->id ?>);">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }
    
    public function salvar($params) {
        try {
            $p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
            $p2pVenda->id = \Utils\Post::getEncrypted($params, "id", 0);
            $p2pVenda->carteira = \Utils\Post::get($params, "carteira", null);
            $p2pVenda->dataOperacao = \Utils\Post::getData($params, "dataOperacao", null);
            $p2pVenda->emailCliente = \Utils\Post::get($params, "emailDepositante", null);
            $p2pVenda->hash = \Utils\Post::get($params, "hash", null);
            $p2pVenda->idUsuario = \Utils\Post::getEncrypted($params, "idUsuario", 0);
            $p2pVenda->idBanco = \Utils\Post::get($params, "idBanco", null);
            $p2pVenda->nomeCliente = \Utils\Post::get($params, "nomeCliente", null);
            $p2pVenda->status = \Utils\Post::get($params, "status", null);
            $p2pVenda->telefone = \Utils\Post::get($params, "telefone", null);
            $p2pVenda->tipoDeposito = \Utils\Post::get($params, "tipoDeposito", null);
            $p2pVenda->tipoOperacao = \Utils\Post::get($params,"tipoOperacao", null);
            $p2pVenda->valorCotacao = \Utils\Post::getNumeric($params, "valorCotacao", 0);
            $p2pVenda->volumeBtc = \Utils\Post::getNumeric($params, "volumeBtc", null);
                    
            if (!$p2pVenda->idBanco > 0) {
                $p2pVenda->idBanco = null;
            }
            
            if (!$p2pVenda->tipoDeposito == null) {
                $p2pVenda->tipoDeposito = \Utils\Constantes::TIPO_DEPOSITO_DOC;
            }
            
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            $p2pVendaRn->salvar($p2pVenda);
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $saldo = $contaCorrenteBtcEmpresaRn->calcularSaldoConta(2);
            
            $json["saldo"] = number_format($saldo, 8, ".", ",");
            $json["sucesso"] = true;
            $json["mensagem"] = "Registro Salvo com Sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $post = $params["_POST"];
            
            $p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
            $p2pVenda->id = isset($post["id"]) ? $post["id"] : 0;
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            $p2pVendaRn->excluir($p2pVenda);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Registro excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function atualizarStatus($params) {
        try {
            $post = $params["_POST"];
            
            $p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            
            $p2pVenda->id = isset($post["id"]) ? $post["id"] : 0;
            
            try {
                $p2pVendaRn->conexao->carregar($p2pVenda);
            } catch (\Exception $ex) {
                throw new \Exception("Registro não localizado no sistema");
            }
            
            $p2pVenda->status = isset($post["status"]) ? $post["status"] : null;
            $p2pVendaRn->conexao->update(Array("status" => $p2pVenda->status), Array("id" => $p2pVenda->id));
            
            ob_start();
            $this->getHtmlItemList($p2pVenda);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}