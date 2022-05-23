<?php

namespace Modules\servicos\Controllers;

class P2p {
    
    private  $codigoModulo = "servicos";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        
        try {
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarios = $usuarioRn->conexao->listar("tipo = 'A'", "nome");
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
            $usuarios = $usuarioRn->conexao->listar("tipo = 'A'", "nome");
            
            $params["indicesCliente"] = $p2pVendaRn->getAllClientes();
            $params["indicesDepositante"] = $p2pVendaRn->getAllDepositantes();
            
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
            $totalDepositado = 0;
            $totalBtc = 0;
            $totalResultado = 0;
            
            ob_start();
            if (sizeof($lista) > 0) {
                foreach ($lista as $p2pVenda) {
                    //$p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
                    
                    $totalCompra += $p2pVenda->valorCompra;
                    $totalDepositado += $p2pVenda->valorDeposito;
                    $totalBtc += $p2pVenda->volumeBtc;
                    $totalResultado += $p2pVenda->valorResultado;
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
            $json["totalCompra"] = number_format($totalCompra, 2, ",", ".");
            $json["totalDeposito"] = number_format($totalDepositado, 2, ",", ".");
            $json["totalResultado"] = number_format($totalResultado, 2, ",", ".");
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
        if ($p2pVenda->prioridade == \Utils\Constantes::P2P_ALTA) { 
            $bg = "bg-red";
        } else {
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
                                <div class="col-lg-4">
                                    <strong>Prioridade:</strong> <?php echo $p2pVenda->getPrioridade() ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-6">
                                    <strong>Data da compra:</strong> <?php echo ($p2pVenda->dataCompra != null ? $p2pVenda->dataCompra->formatar(\Utils\Data::FORMATO_PT_BR) : "")  ?>
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
                                <div class="col col-lg-4">
                                    <strong>Nome do depositante:</strong> <?php echo $p2pVenda->nomeDepositante ?>
                                </div>
                                <div class="col col-lg-5">
                                    <strong>Email do depositante:</strong> <?php echo $p2pVenda->emailDepositante ?>
                                </div>
                                <div class="col col-lg-3">
                                    <strong>Telefone:</strong> <?php echo $p2pVenda->telefoneDepositante ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <strong>Tipo de Depósito:</strong> <?php echo $p2pVenda->getTipoDeposito() ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Valor do Depósito:</strong> <?php echo number_format($p2pVenda->valorDeposito, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Taxa:</strong> <?php echo number_format($p2pVenda->taxa, 3, ",", ".") ?>%
                                </div>
                            </div>
                            <div class="row">
                                <div class="col col-lg-4">
                                    <strong>Compra:</strong> <?php echo number_format($p2pVenda->valorCompra, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Resultado:</strong> <?php echo number_format($p2pVenda->valorResultado, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Volume BTC:</strong> <?php echo number_format($p2pVenda->volumeBtc, 8, ".", "") ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-4">
                                    <strong>Cotação:</strong> <?php echo number_format($p2pVenda->valorCotacao, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Cot. Média:</strong> <?php echo number_format($p2pVenda->valorCotacaoMedia, 2, ",", ".") ?>
                                </div>
                                <div class="col col-lg-4">
                                    <strong>Carteira Dest.:</strong> 
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
                                <div class="col col-lg-4">
                                    <strong>Data de Finalização:</strong> <?php echo ($p2pVenda->dataFinalizacao != null ? $p2pVenda->dataFinalizacao->formatar(\Utils\Data::FORMATO_PT_BR) : "") ?>
                                </div>
                                <div class="col col-lg-5">
                                    <strong>Comprovante: </strong>
                                        <?php if (!empty($p2pVenda->comprovanteDeposito)) { ?>
                                            <a href="<?php echo URLBASE_CLIENT . UPLOADS . $p2pVenda->comprovanteDeposito?>"  target="_BLANK-<?php echo $p2pVenda->id?>">Clique aqui para ver o comprovante.</a>
                                        <?php } ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-lg-12">
                                    <strong>Informações bancárias:</strong> <?php echo $p2pVenda->informacoesBancarias ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col col-lg-12">
                                    <strong>Hash: <a href="<?php echo $p2pVenda->hash ?>"  style="color: white;" target="_BLANK"><?php echo $p2pVenda->hash ?></a></strong>
                                </div>
                            </div>

                            <br>
                            <div class="row">
                                <?php if ($p2pVenda->status != \Utils\Constantes::P2P_STATUS_CONCLUIDO) { ?>
                                <div class="col col-lg-2">
                                    <strong>Alterar Status: </strong>
                                </div>
                                <div class="col col-lg-3">
                                    <div class="form-group">
                                        <select class="form-control" id="status-<?php echo $p2pVenda->id ?>">
                                            <option value="<?php echo \Utils\Constantes::P2P_STATUS_PROCESSANDO ?>" 
                                                <?php echo (\Utils\Constantes::P2P_STATUS_PROCESSANDO == $p2pVenda->status ? "selected = 'true'" : "")?> >Processando</option>
                                            <option value="<?php echo \Utils\Constantes::P2P_STATUS_AGUARDANDO_DEPOSITO ?>" 
                                                    <?php echo (\Utils\Constantes::P2P_STATUS_AGUARDANDO_DEPOSITO == $p2pVenda->status ? "selected = 'true'" : "")?>>Aguardando Depósito</option>
                                            <option value="<?php echo \Utils\Constantes::P2P_STATUS_PENDENTE ?>" 
                                                    <?php echo (\Utils\Constantes::P2P_STATUS_PENDENTE == $p2pVenda->status ? "selected = 'true'" : "")?>>Pendente</option>
                                            <option value="<?php echo \Utils\Constantes::P2P_STATUS_CONCLUIDO ?>" 
                                                    <?php echo (\Utils\Constantes::P2P_STATUS_CONCLUIDO == $p2pVenda->status ? "selected = 'true'" : "")?>>Concluído</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col col-lg-3 ">
                                    <button class="btn btn-info" onclick="atualizarStatus(<?php echo $p2pVenda->id ?>);">
                                        <i class="fa fa-refresh"></i> Atualizar Status
                                    </button>
                                </div>
                                <?php } ?>
                                <div class="col col-lg-2 text-center">
                                    
                                    <?php if ($p2pVenda->status == \Utils\Constantes::P2P_STATUS_CONCLUIDO) { ?>
                                    <button class="btn btn-primary" onclick="modalConfirmarEditar(<?php echo $p2pVenda->id ?>)" >
                                        <i class="fa fa-edit"></i>Editar
                                    </button>
                                    <?php } else { ?>
                                    <a class="btn btn-primary" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_P2P_CADASTRO ?>/<?php echo $p2pVenda->id ?>">
                                        <i class="fa fa-edit"></i>Editar
                                    </a>
                                    <?php } ?>
                                </div>
                                <div class="col col-lg-2 text-center">
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
            $post = $params["_POST"];
            $file = $params["_FILE"];
            
            $p2pVenda = new \Models\Modules\Cadastro\P2pVenda();
            $p2pVenda->id = isset($post["id"]) ? $post["id"] : 0;
            $p2pVenda->carteiraDestino = isset($post["carteiraDestino"]) ? $post["carteiraDestino"] : null;
            $p2pVenda->dataCompra = (isset($post["dataCompra"]) && strlen(trim($post["dataCompra"])) == 10) ? new \Utils\Data(trim($post["dataCompra"]) . " 00:00:00") : null;
            $p2pVenda->dataFinalizacao = (isset($post["dataFinalizacao"]) && strlen(trim($post["dataFinalizacao"])) == 10) ? new \Utils\Data(trim($post["dataFinalizacao"]) . " 00:00:00") : null;
            $p2pVenda->emailCliente = isset($post["emailCliente"]) ? $post["emailCliente"] : null;
            $p2pVenda->emailDepositante = isset($post["emailDepositante"]) ? $post["emailDepositante"] : null;
            $p2pVenda->hash = isset($post["hash"]) ? $post["hash"] : null;
            $p2pVenda->idUsuario = isset($post["idUsuario"]) ? $post["idUsuario"] : null;
            $p2pVenda->informacoesBancarias = isset($post["informacoesBancarias"]) ? $post["informacoesBancarias"] : null;
            $p2pVenda->nomeCliente = isset($post["nomeCliente"]) ? $post["nomeCliente"] : null;
            $p2pVenda->nomeDepositante = isset($post["nomeDepositante"]) ? $post["nomeDepositante"] : null;
            $p2pVenda->status = isset($post["status"]) ? $post["status"] : null;
            $p2pVenda->taxa = isset($post["taxa"]) ? str_replace(",", ".", $post["taxa"]) : null;
            $p2pVenda->telefone = isset($post["telefone"]) ? $post["telefone"] : null;
            $p2pVenda->telefoneDepositante = isset($post["telefoneDepositante"]) ? $post["telefoneDepositante"] : null;
            $p2pVenda->tipoDeposito = isset($post["tipoDeposito"]) ? $post["tipoDeposito"] : null;
            $p2pVenda->valorCompra = isset($post["valorCompra"]) ? str_replace(",", ".", $post["valorCompra"]) : null;
            $p2pVenda->valorCotacao = isset($post["valorCotacao"]) ? str_replace(",", ".", $post["valorCotacao"]) : null;
            $p2pVenda->valorCotacaoMedia = isset($post["valorCotacaoMedia"]) ? str_replace(",", ".", $post["valorCotacaoMedia"]) : null;
            $p2pVenda->valorDeposito = isset($post["valorDeposito"]) ? str_replace(",", ".", $post["valorDeposito"]) : null;
            $p2pVenda->valorResultado = isset($post["valorResultado"]) ? str_replace(",", ".", $post["valorResultado"]) : null;
            $p2pVenda->volumeBtc = isset($post["volumeBtc"]) ? str_replace(",", ".", $post["volumeBtc"]) : null;
            
            
            $p2pVenda->cpfCliente = isset($post["cpfCliente"]) ? $post["cpfCliente"] : null;
            $p2pVenda->cpfDepositante = isset($post["cpfDepositante"]) ? $post["cpfDepositante"] : null;
            $p2pVenda->prioridade = isset($post["prioridade"]) ? $post["prioridade"] : null;
            $p2pVenda->anotacoes = isset($post["anotacoes"]) ? $post["anotacoes"] : null;
            
            if (isset($file["comprovanteDeposito"]) && $file["comprovanteDeposito"]["error"] <= 0) {
                $arquivo = new \Utils\Arquivo($file["comprovanteDeposito"]);
                $arquivo->uploadArquivo();
                
                $p2pVenda->comprovanteDeposito = $arquivo->nome_saida;
            } else {
                $p2pVenda->comprovanteDeposito = null;
            }
            
            
            $p2pVendaRn = new \Models\Modules\Cadastro\P2pVendaRn();
            $p2pVendaRn->salvar($p2pVenda);
            
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
            $p2pVendaRn->conexao->excluir($p2pVenda);
            
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