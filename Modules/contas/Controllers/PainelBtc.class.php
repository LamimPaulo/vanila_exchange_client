<?php

namespace Modules\contas\Controllers;


class PainelBtc {
    
    private  $codigoModulo = "monitoramento";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0", "principal DESC, simbolo ASC");
            $params["moedas"] = $moedas;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("painel_controle_btc", $params);
    }
    
    
    
    public function filtrar($params) {
        try {
            
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $filtro = \Utils\Post::get($params, "filtro");
            $status = \Utils\Post::get($params, "status", "0");
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", 0);
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            $lista = $transacaoPendenteBtcRn->filtrar($dataInicial, $dataFinal, $filtro, $status, $idMoeda);
            
            ob_start();
            if (sizeof($lista) > 0) {
                
                foreach ($lista as $transacaoPendenteBtc) {
                   
                    $transacaoPendenteBtcRn->carregar($transacaoPendenteBtc, false, false, false, true);
                    
                    if ($transacaoPendenteBtc->executada) {
                        $color = "green";
                    } else {
                        if (empty($transacaoPendenteBtc->erro)) {
                            $color = "blue";
                        } else {
                            $color = "red";
                        }
                    } 
                    
                    
                    ?>
                    <li class="list-group-item" style="color: <?php echo $color ?>">
                        
                        <div class="row">
                            <div class="col col-lg-2">
                                <strong><?php echo $transacaoPendenteBtc->moeda->simbolo ?></strong>
                            </div>
                            <div class="col col-lg-3">
                                <strong>Data: </strong><?php echo $transacaoPendenteBtc->data->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                            </div>
                            <div class="col col-lg-7">
                                <strong>Cliente: </strong> <?php echo $transacaoPendenteBtc->cliente->nome ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col col-lg-9">
                                <strong>Descrição:  </strong><?php echo $transacaoPendenteBtc->descricao ?>
                            </div>
                            <div class="col col-lg-3">
                                <strong>Status:  </strong><?php echo ($transacaoPendenteBtc->executada > 0 ? "Executada" : "Pendente") ?>
                            </div>
                        </div>
                        
                        <?php if ($transacaoPendenteBtc->executada > 0) { ?>
                        <div class="row">
                            <div class="col col-lg-9">
                                <strong>Confirmada por:  </strong><?php echo ($transacaoPendenteBtc->usuario != null ? $transacaoPendenteBtc->usuario->nome : ""); ?>
                            </div>
                            <div class="col col-lg-3">
                                <strong>Data da confirmação:  </strong><?php echo ($transacaoPendenteBtc->dataConfirmacao != null ? $transacaoPendenteBtc->dataConfirmacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO): "")?>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <div class="row">
                            <div class="col col-lg-6">
                                <strong>Carteira:  </strong><?php echo $transacaoPendenteBtc->enderecoBitcoin ?>
                            </div>
                            <div class="col col-lg-6">
                                <strong>Hash:  </strong><?php echo $transacaoPendenteBtc->hash ?>
                            </div>
                        </div>
                        
                        
                        <div class="row">
                            <div class="col col-lg-12" style="color: red;">
                                <strong>Erro:  </strong><?php echo $transacaoPendenteBtc->erro ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            
                            <div class="col col-lg-6 text-center">
                                
                                <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PAINELBTC, \Utils\Constantes::EDITAR)) { ?>
                                
                                <?php if ($transacaoPendenteBtc->executada < 1) { ?>
                                <button class="btn btn-danger" onclick="modalExcluir('<?php echo $transacaoPendenteBtc->hash ?>');">
                                    <i class="fa fa-remove"></i> Excluir
                                </button>
                                <?php } ?>
                                
                                <?php } ?>
                                
                            </div>
                            
                            <div class="col col-lg-6 text-center">
                                
                                <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PAINELBTC, \Utils\Constantes::EDITAR)) { ?>
                                
                                <?php if ($transacaoPendenteBtc->executada < 1) { ?>
                                <a class="btn btn-success" href="<?php echo URLBASE_CLIENT  . \Utils\Rotas::R_BTC_PAINEL_CONFIRMACAO ?>/<?php echo $transacaoPendenteBtc->hash ?>">
                                    <i class="fa fa-check"></i> Analisar
                                </a>
                                <?php } ?>
                                
                                <?php } ?>
                                
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
                            Nenhum registro encontrado
                        </div>
                    </div>
                </li>
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
    
    
    public function filtrarTransacoesNaoAutorizadas($params) {
        try {
            
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", 0);
            
            $where = Array();
            if ($idMoeda > 0) {
                $where[] = " id_moeda = {$idMoeda} ";
            }
            
            $where[] = " autorizada = 0 ";
            $where[] = " tipo = '".\Utils\Constantes::SAIDA."' ";
            
            $where = implode(" AND ", $where);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $lista = $contaCorrenteBtcRn->lista($where, "data_cadastro", null, null, true, true);
            
            ob_start();
            if (sizeof($lista) > 0) {
                
                foreach ($lista as $contaCorrenteBtc) {
                    //$contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                    //exit(print_r($contaCorrenteBtc));
                    ?>

            <tr style="">
                <td class="text-center"><?php echo $contaCorrenteBtc->id ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtc->moeda->simbolo ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtc->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtc->cliente->nome ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtc->enderecoBitcoin; ?></td>
                <td class="text-center"><?php echo number_format($contaCorrenteBtc->valorTaxa, $contaCorrenteBtc->moeda->casasDecimais, ".", "") ?></td>
                <td class="text-center"><?php echo number_format($contaCorrenteBtc->valor - $contaCorrenteBtc->valorTaxa, $contaCorrenteBtc->moeda->casasDecimais, ".", "") ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtc->direcao ?></td>
                <td class="text-center"></td>
                <td class="text-center"><?php echo ($contaCorrenteBtc->executada > 0 ? "Executada" : "Pendente") ?></td>
                <td class="text-center">
                    <button class="btn btn-danger btn-xs" onclick="modalNegarTransacao('<?php echo \Utils\Criptografia::encriptyPostId($contaCorrenteBtc->id) ?>');" style="font-size: 10px">
                     Negar
                    </button>
                    <button class="btn btn-success btn-xs" onclick="modalAutorizarTransacao('<?php echo \Utils\Criptografia::encriptyPostId($contaCorrenteBtc->id) ?>');" style="font-size: 10px">
                    Autorizar
                    </button>

                </td>
            </tr>
                    <?php
                }
                
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="11">Nenhum registro encontrado</td>
                </tr>
            </tr>
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
    
    public function listarSaquesIco($params) {
        try {
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $status = \Utils\Post::get($params, "status", 100);
            $filtro = \Utils\Post::get($params, "filtro", "");
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $lista = $saqueIcoRn->filtrar($dataInicial, $dataFinal, $status, $filtro);
            
            ob_start();
            if (sizeof($lista) > 0) {
                
                foreach ($lista as $saqueIco) {
                    //$saqueIco = new \Models\Modules\ICO\SaqueIco();
                    
                    ?>
                    <tr style="">
                        <td class="text-center" style="vertical-align: middle;"><?php echo $saqueIco->id ?></td>
                        <td class="text-center" style="vertical-align: middle;"><?php echo $saqueIco->cliente->nome ?></td>
                        <td class="text-center" style="vertical-align: middle;">
                            <?php echo number_format($saqueIco->volumeMoedaConversao, $saqueIco->moedaConversao->casasDecimais, ",", ".") . " - " . $saqueIco->moedaConversao->simbolo ?>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <?php echo number_format($saqueIco->cotacao, 4, ",", ".") ?>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <?php echo number_format($saqueIco->volumeMoedaSaque, $saqueIco->moedaSaque->casasDecimais, ",", ".") . " - " . $saqueIco->moedaSaque->simbolo ?>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <?php echo $saqueIco->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        </td>
                        <td class="text-center" style="vertical-align: middle;"><?php echo $saqueIco->getStatus() ?></td>
                        
                        <?php if ($saqueIco->status < 1) { ?>
                        <td class="text-center" style="vertical-align: middle;">
                            <button class="btn btn-primary btn-sm" type="button" onclick="modalAutorizarSaqueIco('<?php echo \Utils\Criptografia::encriptyPostId($saqueIco->id)?>');">
                                Aprovar
                            </button>
                        </td>
                        <td class="text-center" style="vertical-align: middle;">
                            <button class="btn btn-danger btn-sm" type="button" onclick="modalNegarSaqueIco('<?php echo \Utils\Criptografia::encriptyPostId($saqueIco->id)?>');">
                                Negar
                            </button>
                        </td>
                        <?php } else if ($saqueIco->status == 1) { ?>
                        <td class="text-center" colspan="2" style="vertical-align: middle;">
                            Processando Pagamento
                        </td>
                        <?php }  else if ($saqueIco->status == 2) { ?>
                        <td class="text-center" colspan="2" style="vertical-align: middle;">
                            <a href="https://blockchain.info/tx/<?php echo $saqueIco->txid ?>" target="_BLANK">Recibo</a>
                        </td>
                        <?php } else { ?>
                        <td class="text-center" colspan="2" style="vertical-align: middle;"></td>
                        <?php }  ?>
                    </tr>
                    <?php
                }
                
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="10">Nenhum registro encontrado</td>
                </tr>
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
    
    public function confirmacao($params) {
        try {
            
            $transacaoPendenteBtc = new \Models\Modules\Cadastro\TransacaoPendenteBtc();
            $transacaoPendenteBtc->hash = \Utils\Get::get($params, 0, 0);
            
            try {
                $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
                $transacaoPendenteBtcRn->carregar($transacaoPendenteBtc, TRUE, true);
            } catch (\Exception $ex) {
                throw new \Exception("Transação não localizada");
            }
            
            $params["transacao"] = $transacaoPendenteBtc;
            
            $params["sucesso"] = true;
        } catch (Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("painel_confirmacao_btc", $params);
    }
    
    
    public function confirmar($params) {
        try {
            $transacaoPendenteBtc = new \Models\Modules\Cadastro\TransacaoPendenteBtc();
            
            $transacaoPendenteBtc->data = \Utils\Post::getData($params, "data", NULL, "00:00:00");
            $transacaoPendenteBtc->descricao = \Utils\Post::get($params, "descricao", NULL);
            $transacaoPendenteBtc->enderecoBitcoin = \Utils\Post::get($params, "enderecoBitcoin", NULL);
            $transacaoPendenteBtc->hash = \Utils\Post::get($params, "hash", NULL);
            $transacaoPendenteBtc->valor = \Utils\Post::getNumeric($params, "valor", NULL);
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            $transacaoPendenteBtcRn->confirmar($transacaoPendenteBtc);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function excluir($params) {
        try {
            
            $transacaoPendenteBtc = new \Models\Modules\Cadastro\TransacaoPendenteBtc();
            $transacaoPendenteBtc->hash = \Utils\Post::getEncrypted($params, "hash", 0);
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            $transacaoPendenteBtcRn->excluir($transacaoPendenteBtc);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function autorizarTranzacao($params) {
        try {
            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrenteBtc->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteBtcRn->autorizarTransacao($contaCorrenteBtc);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function negarTranzacao($params) {
        try {
            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrenteBtc->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteBtcRn->negarTransacao($contaCorrenteBtc);
            //exit(print_r($contaCorrenteBtc));
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function carregarSaquesIco($params) {
        try {
            
            $saqueIco = new \Models\Modules\ICO\SaqueIco();
            $saqueIco->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $saqueIcoRn->carregar($saqueIco, true, true, true, true);
            
            $taxaTransacao = \Models\Modules\Cadastro\ConfiguracaoRn::getTaxaTransferenciaCurrency($saqueIco->cliente);
            
            $json["taxa"] = number_format($taxaTransacao, 8, ".", "");
            $json["saqueIco"] = $saqueIco;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function autorizarSaquesIco($params) {
        try {
            $saqueIco = new \Models\Modules\ICO\SaqueIco();
            $saqueIco->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $cotacao = \Utils\Post::getNumeric($params, "cotacao", 0);
            $exchange = \Utils\Post::get($params, "exchange", "");
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $saqueIcoRn->autorizarSaque($saqueIco, $cotacao, $exchange);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Saque autorizado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function negarSaquesIco($params) {
        try {
            $saqueIco = new \Models\Modules\ICO\SaqueIco();
            $saqueIco->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $motivo = \Utils\Post::get($params, "motivo", null);
            
            $saqueIcoRn = new \Models\Modules\ICO\SaqueIcoRn();
            $saqueIcoRn->negarSaque($saqueIco, $motivo);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Saque cancelado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}