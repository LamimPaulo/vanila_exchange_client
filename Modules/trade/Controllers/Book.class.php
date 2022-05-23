<?php

namespace Modules\trade\Controllers;

require_once getcwd() . '/Library/Models/Modules/Cadastro/ClienteHasParidadeFavorita.class.php';
require_once getcwd() . '/Library/Models/Modules/Cadastro/ClienteHasParidadeFavoritaRn.class.php';

class Book {
    
    private  $codigoModulo = "trade";
    private $idioma = null;
    private $casasDecimaisMoedaTrade = 4;
    
    function __construct($params) {
        
        \Utils\Validacao::acesso($this->codigoModulo);
        
        $this->idioma = new \Utils\PropertiesUtils("book", IDIOMA);

        $paridade = \Modules\principal\Controllers\Principal::getParity();

        if (empty($paridade->casasDecimaisMoedaTrade)) {
            $this->casasDecimaisMoedaTrade = $paridade->moedaTrade->casasDecimais;
        } else {
            $this->casasDecimaisMoedaTrade = $paridade->casasDecimaisMoedaTrade;
        }
    }
    
    public function index($params) {
        try {

            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();

            $params["casasDecimais"] = $this->casasDecimaisMoedaTrade;

            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaTodasParidades(false);

            $params["paridades"] = $paridades;

            $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, false);

            $params["compra"] = number_format($taxas["compra"], 4, ".", "");
            $params["venda"] = number_format($taxas["venda"], 4, ".", "");

            \Utils\Layout::view("book", $params);
            
        } catch (\Exception $ex) {
            
        }
    }

    public function getParidadesByMoeda($params) {
        
        try {
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::get($params, "moeda", 0);
            
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaParidadesByMoeda($moeda, false, true);
            
            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $clienteHasParidadeFavoritaRn = new \Models\Modules\Cadastro\ClienteHasParidadeFavoritaRn();
            $favoritas = $clienteHasParidadeFavoritaRn->getParidadesFavoritas($cliente);
            
            ob_start();
            foreach ($paridades as $paridade) {
                
                $favorita = isset($favoritas[$paridade->id]);
                
                $precoAbertura = $paridade->primeiroPreco;
                               
                $precoVolume = $paridade->precoCompra;
                
                if (($paridade->idMoedaTrade > 1) && ($paridade->ativo == 1)) {
                    $par = $paridadeRn->getBySymbol("{$paridade->moedaTrade->simbolo}:BRL");
                    if ($paridade != null) {
                        $precoVolume = $par->precoCompra;
                    } else {
                        $precoVolume = 0;
                    }
                }
                
                if ($precoAbertura > 0) {
                    $variacao = (($paridade->precoCompra > $precoAbertura) ? (($paridade->precoCompra - $precoAbertura) / $precoAbertura) :  ($precoAbertura - $paridade->precoCompra) / $precoAbertura) * 100;
                } else {
                    $variacao = 0;
                }
                
                $icon = "";
                $color = "text-blue";
                if ($paridade->precoCompra < $precoAbertura) {
                    $icon = "<i class='fa fa-level-up' style='color: #1ab394;'></i>";
                    $color = "text-navy";
                } else if ($paridade->precoCompra > $precoAbertura) {
                    $icon = "<i class='fa fa-level-down' style='color: #ed5565;'></i>";
                    $color = "text-danger";
                }
                if($precoAbertura == 0){
                    $icon = "-";
                }
                
                if (empty($paridade->casasDecimaisMoedaTrade)) {
                    $casasDecimaisMoedaTrade = $paridade->moedaTrade->casasDecimais;
                } else {
                    $casasDecimaisMoedaTrade = $paridade->casasDecimaisMoedaTrade;
                }
                
                ?>

                <tr class="<?php echo ($favorita ? "favorite-parity" : "") ?> tr-h" data-paridade="<?php echo \Utils\Criptografia::encriptyPostId($paridade->id)?>" >                    
                    <td class="text-left change-parity column-paridade" style="vertical-align: middle; padding-top: 1px !important; padding-bottom: 1px !important; padding-left: 18px; width: 25% !important" data-name="<?php echo $paridade->symbol ?>">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $paridade->moedaBook->icone?>" style="width: 12px; height: 12px;" />&nbsp;
                        <?php echo $paridade->moedaBook->simbolo; ?>
                    </td>
                    <td class="text-right change-parity column-paridade" style="vertical-align: middle; padding-top: 1px !important; padding-bottom: 1px !important; width: 35%;" data-name="<?php echo $paridade->symbol ?>">
                        <?php echo number_format($paridade->precoCompra, $casasDecimaisMoedaTrade, ",", ".") ?> <?php echo $paridade->moedaTrade->simbolo; ?>
                    </td>
                    <td class="text-right change-parity<?php echo $color ?> column-paridade" style="vertical-align: middle; padding-top: 1px !important; padding-bottom: 1px !important; width: 30% !important;" data-name="<?php echo $paridade->symbol ?>">
                         <?php echo number_format($variacao, 1, ",", ".") ?>%
                    </td>           
                    <td><?php echo $icon ?></td>
                    <?php if($cliente->modoOperacao == "basic"){ ?>
                    <td class="text-right change-parity column-paridade" style="vertical-align: middle; padding-top: 1px !important; padding-bottom: 1px !important; width: 35% !important" data-name="<?php echo $paridade->symbol ?>">
                        <?php echo number_format($paridade->quoteVolume, $casasDecimaisMoedaTrade, ",", ".") ?> <?php echo $paridade->moedaTrade->simbolo ?>
                    </td>
                    <?php } ?>
                    <td class="text-center column-paridade" style="vertical-align: middle; padding-top: 1px !important; padding-bottom: 1px !important; width: 15% !important"  id="btn-favorito-<?php echo $paridade->id ?>" data-name="<?php echo $paridade->symbol ?>">
                        <?php if ($favorita) {?>
                        <button class="btn btn-link text-warning" type="button" onclick="removerFavorito('<?php echo \Utils\Criptografia::encriptyPostId($paridade->id)?>');">
                            <i class="fa  fa-star" style="font-size: 9px; color: gray"></i>
                        </button>
                        <?php } else { ?>
                        <button class="btn btn-link text-warning" type="button" onclick="addFavorito('<?php echo \Utils\Criptografia::encriptyPostId($paridade->id)?>');">
                            <i class="fa  fa-star-o" style="font-size: 9px; color: gray"></i>
                        </button>
                        <?php }  ?>
                    </td>
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
    
    
    public function getTableBalances($params) {
        
        try {
            
            $balanceMode = \Utils\Post::get($params, "balanceMode", 1);
            
            $cliente = \Utils\Geral::getCliente();
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar( " id > 1 AND (ativo = 1 OR status_saque = 1) ");
            
            $lista = Array();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldos = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get(1);
                $lista[] = Array("moeda" => $moeda, "saldos" => $saldos);
            }
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            foreach ($moedas as $moeda) {

                $saldos = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true, false);

                if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                    $lista[] = Array("moeda" => $moeda, "saldos" => $saldos);
                }
            }

            ob_start();
            foreach ($lista as $dados) {
                $moeda = $dados["moeda"];
                $saldos = $dados["saldos"];
                
                ?>
                <tr class="tr-h">
                    <td style="padding-left: 18px; padding-top: 3px !important; padding-bottom: 3px !important;" class="column-balance" data-name="<?php echo $moeda->simbolo?>" width="55%">
                        
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone?>" style="width: 12px; height: 12px;" />&nbsp;&nbsp;<?php echo $moeda->simbolo; ?>
                    </td>
                    <td class="text-right column-balance" style="padding-top: 3px !important; padding-bottom: 3px !important; width: 150px !important;">
                        <p style="padding: 0px; margin: 0px; <?php echo ($balanceMode > 1 ? "display: none;" : "") ?>" class="">
                            <span class="text-muted"><?php echo number_format($saldos["bloqueado"], $moeda->casasDecimais, ",", ".") ?> </span>
                        </p>
                    </td>
                    <td class="text-right column-balance" style="padding-top: 3px !important; padding-bottom: 3px !important; width: 150px !important; padding-right: 18px;">
                        <p style="padding: 0px; margin: 0px;" >
                            <span class="text-success"><?php echo number_format($saldos["saldo"], $moeda->casasDecimais, ",", ".") ?> </span>
                        </p>
                    </td>
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
    
    
    public function salvarOrdemCompra($params) {
        try {
            
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $amount = \Utils\Post::getNumeric($params, "amount", 0);
            $price = \Utils\Post::getNumeric($params, "price", 0);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $orderBookRn->registrarOrdemCompra($amount, $price, $paridade, false);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("ordemRegistradaSucessoC");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvarOrdemVenda($params) {
        try {            
            
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $amount = \Utils\Post::getNumeric($params, "amount", 0);
            $price = \Utils\Post::getNumeric($params, "price", 0);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $orderBookRn->registrarOrdemVenda($amount, $price, $paridade, false);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("ordemRegistradaSucessoV");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getBook($params) {
        
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            $cliente = \Utils\Geral::getCliente(); 
            
            $bd = new \Io\BancoDados(BDBOOK);
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn($bd);
            
            $listaCompra = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", 17, 0, true);
            $listaVenda = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", 16, 0, true);
            
            $venda = current($listaVenda);
            $compra = current($listaCompra);   
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            if($compra->valorCotacao != $paridade->precoCompra && !empty($compra->valorCotacao)){              
                $paridadeRn->conexao->update(Array("preco_compra" => $compra->valorCotacao), Array("id" => $paridade->id));                
            }
            
            if($venda->valorCotacao != $paridade->precoVenda && !empty($venda->valorCotacao)){               
                $paridadeRn->conexao->update(Array("preco_venda" => $venda->valorCotacao), Array("id" => $paridade->id));                
            }
            
            $diferencaPorcentagem = 0;
            
            if(!empty($compra->valorCotacao) && !empty($venda->valorCotacao)){
               $diferencaPorcentagem = number_format(100 * (1 - ($compra->valorCotacao) / (($venda->valorCotacao) * 1 )), 4, ",", "."); 
               
               if($diferencaPorcentagem >= 0){
                   $diferencaPorcentagemShow = $diferencaPorcentagem;
               } else {
                   $diferencaPorcentagemShow = 0;
               }
            }
            
            $diferenca = ($venda->valorCotacao - $compra->valorCotacao) < 0 ? "" : $venda->valorCotacao - $compra->valorCotacao;
            
            $json["diferenca"] = $paridade->moedaTrade->simbolo . " " . number_format($diferenca, $this->casasDecimaisMoedaTrade, ".", "");
            $json["diferencaPorcentagem"] = $diferencaPorcentagemShow;
            $json["adjusttbl"] = sizeof($listaVenda) < 16 ? (16 - sizeof($listaVenda)) * 4.77 : 0 ;
            $json["htmlCompra"] = $this->htmlOrdens($listaCompra, \Utils\Constantes::ORDEM_COMPRA, $cliente->id);
            $json["htmlVenda"] = $this->htmlOrdens($listaVenda, \Utils\Constantes::ORDEM_VENDA, $cliente->id);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }    
    
    private function htmlOrdens($lista, $tipo, $idCliente) {

        $listaAux = null;
        $volumeGeral = 0;
        
        foreach ($lista as $dados) {
            $listaAux[] = $dados;
            $volumeGeral = $volumeGeral + $dados->volumeCurrency;
        }
        
        if($tipo == \Utils\Constantes::ORDEM_VENDA){
            $listaAux = array_reverse($listaAux);
        }
        
        $volumeAcumulado = 0;
        ob_start();
        if (sizeof($listaAux) > 0) {
            foreach ($listaAux as $ordem) {  
                $volumeAcumulado += $ordem->volumeCurrency;
                
                $ac = ($tipo == \Utils\Constantes::ORDEM_COMPRA ? $volumeAcumulado : ($volumeGeral - $volumeAcumulado + $ordem->volumeCurrency));
                
                $this->htmlItemOrdem($ordem, $volumeGeral, $idCliente, $ac);
            }
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemOrdem(\Models\Modules\Cadastro\OrderBook $ordem, $volumeGeral, $idCliente, $volumeAcumulado) {

        $ordem->porcentagem =  ($ordem->volumeCurrency / $volumeGeral ) * 100;
        $ordemRemover = \Utils\Criptografia::encriptyPostId($ordem->id);
        $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "26, 179, 148" : "255, 30, 30"); 
        $colorHex = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "#1ab394" : "#ed5565"); 
        
        ?>
        <tr style="background-image: linear-gradient(to left, rgba(<?php echo $color ?>, 0.2) <?php echo number_format($ordem->porcentagem, 2, ".", "") ?>%, rgba(<?php echo $color ?>, 0) <?php echo number_format($ordem->porcentagem, 2, ".", "") ?>%) !important; font-weight: <?php echo $ordem->idCliente == $idCliente ? "bold" : "normal" ?>"    
            class="order-item" 
            onclick="configOrder('<?php echo ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ORDEM_VENDA : \Utils\Constantes::ORDEM_COMPRA) ?>', '<?php echo number_format($ordem->valorCotacao, $this->casasDecimaisMoedaTrade, ".", "")?>', '<?php echo number_format($volumeAcumulado, $ordem->paridade->moedaBook->casasDecimais, ".", "")?>');" >
            
            <td class="text-left td-price" style="padding-top: 1px !important; padding-bottom: 1px !important; color: <?php echo $colorHex ?>; ">
                <span><?php echo number_format($ordem->valorCotacao, $this->casasDecimaisMoedaTrade, ",", ".") ?>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <?php if ($ordem->idCliente == $idCliente) { ?>
                    <a onclick="cancelar('<?php echo $ordemRemover ?>');"><i class='fa fa-times' style='color: #676a6c !important'></i></a>
                <?php } ?>
            </td>
            <td class="text-right " style="padding-top: 1px !important; padding-bottom: 1px !important;"><?php echo number_format($ordem->volumeCurrency, $ordem->paridade->moedaBook->casasDecimais, ",", ".") ?></td>
            <td class="text-right " style="padding-top: 1px !important; padding-bottom: 1px !important;"><?php echo number_format(($ordem->valorCotacao * $ordem->volumeCurrency), $this->casasDecimaisMoedaTrade, ",", ".") ?></td> 
            
        </tr>
        <?php
        
    }
    
    public function listarMinhasOrdens($params) {
        try {
                        
            $cliente = \Utils\Geral::getCliente();
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            $todas = \Utils\Post::getBoolean($params, "todas", false);

            $whereParidade = "";
            if (!$todas) {
                $whereParidade = " AND id_paridade = {$paridade->id}";
            }
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->listar("id_cliente = {$cliente->id} AND cancelada = 0 AND executada = 0 {$whereParidade} ", "data_cadastro desc", null, null);
            

            $json["html"] = $this->htmlMinhasOrdens($lista);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlMinhasOrdens($lista) {
        
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {
                $this->htmlItemMinhasOrdens($ordem);
            }
        } else {
            ?>
            <tr class="my-order-item">
                <td class="text-center" colspan="12"><?php echo $this->idioma->getText("nenhumaOrdem") ?></td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemMinhasOrdens(\Models\Modules\Cadastro\OrderBook $ordem) {
        
        if ($ordem->cancelada > 0) {
            $color = "color: #666666;";
        } else {
            $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;");
        }
        
        $tradeSymbol = ($ordem->paridade->moedaTrade->simbolo);
        ?>
            <tr class="my-order-item tr-h" style="<?php echo $color?>;">
                <td class="text-center" style=""><?php echo $ordem->paridade->moedaBook->nome ?></td> 
                <td class="text-center"><?php echo ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? $this->idioma->getText("compraC") : $this->idioma->getText("vendaC")) ?></td>
                <td class="text-center"><?php echo $ordem->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR)?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format($ordem->valorCotacao, $this->casasDecimaisMoedaTrade, ",", ".")?></td>
                <td class="text-center"><?php echo number_format($ordem->volumeCurrency, $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format(($ordem->valorCotacao * $ordem->volumeCurrency), $this->casasDecimaisMoedaTrade, ",", ".")?></td>
                <td class="text-center"><?php echo number_format(($ordem->volumeExecutado), $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo number_format(($ordem->volumeCurrency - $ordem->volumeExecutado), $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo ($ordem->cancelada > 0 ? $this->idioma->getText("canceladaC") : ($ordem->executada > 0 ? $this->idioma->getText("executadaC") : $this->idioma->getText("pendenteC"))) ?></td>
                <td class="text-center">
                    <?php if (!($ordem->cancelada > 0) && !($ordem->executada > 0)) { ?>
                    <a class="" onclick="cancelar('<?php echo \Utils\Criptografia::encriptyPostId($ordem->id); ?>');">
                       <i class="fa fa-times" style="color: #252525"></i>
                    </a
                    <?php } ?>
                </td>

            </tr>
        <?php
        
    }
    
    
    public function cancelar($params) {
        try {
            
            $orderBook = new \Models\Modules\Cadastro\OrderBook();
            $orderBook->id = \Utils\Post::getEncrypted($params, "ordem", 0);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $orderBookRn->cancelar($orderBook);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function adicionarFavorito($params) {
        try {
            
            $idParidade = \Utils\Post::getEncrypted($params, "paridade", 0);
            
            $cliente = \Utils\Geral::getLogado();
            $clienteHasParidadeFavoritaRn = new \Models\Modules\Cadastro\ClienteHasParidadeFavoritaRn();
            $clienteHasParidadeFavoritaRn->salvar($cliente->id, $idParidade);
            
            ob_start();
            ?>
            <button class="btn btn-link text-warning" type="button" onclick="removerFavorito('<?php echo \Utils\Criptografia::encriptyPostId($idParidade)?>');">
                <i class="fa  fa-star"></i>
            </button>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["codigo"] = $idParidade;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function removerFavorito($params) {
        try {
            
            $idParidade = \Utils\Post::getEncrypted($params, "paridade", 0);
            
            $cliente = \Utils\Geral::getLogado();
            $clienteHasParidadeFavoritaRn = new \Models\Modules\Cadastro\ClienteHasParidadeFavoritaRn();
            $clienteHasParidadeFavoritaRn->remover($cliente->id, $idParidade);
            
            ob_start();
            ?>
            <button class="btn btn-link text-warning" type="button" onclick="addFavorito('<?php echo \Utils\Criptografia::encriptyPostId($idParidade)?>');">
                <i class="fa  fa-star-o"></i>
            </button>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["codigo"] = $idParidade;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function getListaTrade($params) {
        
        try {
            $bd = new \Io\BancoDados(BDBOOK);
            
            $minhasOrdens = \Utils\Post::getBoolean($params, "minhasOrdens", false);
            
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            
            
            $idCliente = 0;
            if ($minhasOrdens && \Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $idCliente = $cliente->id;
                
                $dataInicial = null;
                $dataFinal = null;
            } else {
                $dataInicial = new \Utils\Data(date("d/m/Y"));
                $dataFinal = new \Utils\Data(date("d/m/Y"));
                $dataInicial->subtrair(0, 0, 1);
            }
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn($bd);
            $lista = $ordemExecutadaRn->filtrar($paridade, $dataInicial, $dataFinal, "T", "T", $idCliente, 30, false);

            $json["html"] = $this->htmlTradeList($lista);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function htmlTradeList($lista) {
        
        $volumeAcumulado = 0;
        
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {
                $volumeAcumulado += $ordem->volumeCurrency;
                $this->htmlItemTradeList($ordem);
            }
        } else {
            ?>
            <tr class="trade-item" >
                <td class="text-center" colspan="3">Nenhuma ordem executada.</td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemTradeList(\Models\Modules\Cadastro\OrdemExecutada $ordem) {
        
        $tradeSymbol = "";
        $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;"); 
        ?>
        <tr class="trade-item" style="<?php echo $color?>; cursor: pointer; font-size: 10px;"  >
            <td class="text-center">
                <?php echo $ordem->dataExecucao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ?>
            </td>
            <td class="text-center">
                <?php echo $tradeSymbol ?> <?php echo number_format($ordem->valorCotacao, 8, ",", ".")?>
            </td>
            <td class="text-center">
                <?php echo number_format($ordem->volumeExecutado, 8, ".", "")?>
            </td>
        </tr>
        <?php
        
    }
}