<?php

namespace Modules\trade\Controllers;

class BookNew {
    
    private  $codigoModulo = "trade";
    private $idioma = null;
    
    function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("book", IDIOMA);
   
    }
    
    public function index($params) {
        try {
                $cliente = \Utils\Geral::getLogado();
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                $paridade = \Modules\principal\Controllers\Principal::getParity();
                
                $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();
                $params["casasDecimais"] = $paridade->moedaTrade->casasDecimais;
                
                $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
                $paridades = $paridadeRn->getListaTodasParidades(false);
                
                $params["paridades"]  = $paridades;
                
                if($cliente->modoOperacao == "basic"){
                    $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, true);
                   
                    $params["compra"] = number_format($taxas["compra"], 4, ".", "");
                    $params["venda"] = number_format($taxas["venda"], 4, ".", "");
                    
                     \Utils\Layout::view("compra_venda_direta", $params);
                } else {
                    
                    $taxas = $clienteHasTaxaRn->getTaxaCliente($cliente, $paridade->idMoedaBook, false);

                    $params["compra"] = number_format($taxas["compra"], 4, ".", "");
                    $params["venda"] = number_format($taxas["venda"], 4, ".", "");                    
                    
                     \Utils\Layout::view("book_new", $params);
                }

        } catch (\Exception $ex) {
            
        }
       
    }

    
    public function getParidadesByMoeda($params) {
        
        try {
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params, "moeda", 0);
            
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaParidadesByMoeda($moeda, true, true);
            
            $cliente = \Utils\Geral::getLogado();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $clienteHasParidadeFavoritaRn = new \Models\Modules\Cadastro\ClienteHasParidadeFavoritaRn();
            $favoritas = $clienteHasParidadeFavoritaRn->getParidadesFavoritas($cliente);
            
            ob_start();
            foreach ($paridades as $paridade) {
                
                $favorita = isset($favoritas[$paridade->id]);
                
                $precoAbertura = $paridadeRn->getPrecoAberturaUltimas24Horas($paridade);
                               
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
                    $icon = " ";
                }
                ?>

                <tr class="<?php echo ($favorita ? "favorite-parity" : "") ?> tr-h" data-paridade="<?php echo \Utils\Criptografia::encriptyPostId($paridade->id)?>" >                    
                    <td class="text-left change-parity column-paridade" style="vertical-align: middle; padding-left: 5px; width: 25% !important" data-name="<?php echo $paridade->symbol ?>">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $paridade->moedaBook->icone?>" style="width: 12px; height: 12px;" />&nbsp;
                        <?php echo $paridade->moedaBook->simbolo; ?>
                    </td>
                    <td class="text-center change-parity column-paridade" style="vertical-align: middle;  width: 35%;" data-name="<?php echo $paridade->symbol ?>">
                        <?php echo number_format($paridade->precoCompra, $paridade->moedaTrade->casasDecimais, ",", ".") ?> <?php echo $paridade->moedaTrade->simbolo; ?>
                    </td>
                    <td class="text-right change-parity<?php echo $color ?> column-paridade" style="vertical-align: middle; width: 30% !important;" data-name="<?php echo $paridade->symbol ?>">
                         <?php echo number_format($variacao, 1, ",", ".") ?>%
                    </td>           
                    <td><?php echo $icon ?></td>
                    <?php if($cliente->modoOperacao == "basic"){ ?>
                    <td class="text-right change-parity column-paridade" style="vertical-align: middle;  width: 35% !important" data-name="<?php echo $paridade->symbol ?>">
                        <?php echo number_format(($paridade->volume * $precoVolume), $paridade->moedaTrade->casasDecimais, ",", ".") ?> <?php echo $paridade->moedaTrade->simbolo ?>
                    </td>
                    <?php } ?>
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
            
            $moedas = Array();
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->listar(" ativo = 1 AND status_mercado = 1 ", null, null, null, true, false);
            
            $lista = Array();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldos = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get(1);
                $lista[] = Array("moeda" => $moeda, "saldos" => $saldos);
            }
           
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            foreach ($paridades as $paridade) {
                
            if (!in_array($paridade->idMoedaBook, $moedas)) {
                $moedas[] = $paridade->idMoedaBook;
                    
                $moeda = $paridade->moedaBook;
               
                $saldos = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true, false);             
                
                if ($saldos["saldo"] > 0 || $saldos["bloqueado"] > 0) {
                    $lista[] = Array("moeda" => $moeda, "saldos" => $saldos);
                    }
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

    
    
    public function listarOrdensCompra($params) {
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", 12, 0, true);
            
            $json["html"] = $this->htmlOrdens($lista, \Utils\Constantes::ORDEM_COMPRA);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listarOrdensVenda($params) {
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", 12, 0, true);
            
            $json["html"] = $this->htmlOrdens($lista, \Utils\Constantes::ORDEM_VENDA);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
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
                <td class="text-center"><?php echo $ordem->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP)?></td>
                <td class="text-center" style=""><?php echo str_replace(":", "/", $ordem->paridade->symbol) ?></td> 
                <td class="text-center"><?php echo ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? $this->idioma->getText("compraC") : $this->idioma->getText("vendaC")) ?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format($ordem->valorCotacao, $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?></td>
                <td class="text-center"><?php echo number_format($ordem->volumeCurrency, $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format(($ordem->valorCotacao * $ordem->volumeCurrency), $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?></td>
                <td class="text-center"><?php echo number_format(($ordem->volumeExecutado), $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo ($ordem->cancelada > 0 ? $this->idioma->getText("canceladaC") : ($ordem->executada > 0 ? $this->idioma->getText("executadaC") : $this->idioma->getText("pendenteC"))) ?></td>
                <td class="text-center">
                    <?php if (!($ordem->cancelada > 0) && !($ordem->executada > 0)) { ?>
                    <a class="" onclick="cancelar('<?php echo \Utils\Criptografia::encriptyPostId($ordem->id); ?>');">
                       <i class="fa fa-times" style="color: #ff1e1e"></i>
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
    
    
    public function salvarLayout($params) {
        
        try {
           
            $cliente = \Utils\Geral::getCliente(); 
            $layourRn = new \Models\Modules\Cadastro\LayoutRn();
            
            $layouts = Array();
            $layouts["marketBook"] = \Utils\Post::get($params, "marketBook", null);
            $layouts["coinBook"] = \Utils\Post::get($params, "coinBook", null);
            $layouts["coinTrade"] = \Utils\Post::get($params, "coinTrade", null);
            $layouts["layoutMercado"] = \Utils\Post::get($params, "layoutMercado", null);
            $layouts["operacao"] = \Utils\Post::get($params, "operacao", null);
            $layouts["ordensCompra"] = \Utils\Post::get($params, "ordensCompra", null);
            $layouts["ordemVenda"] = \Utils\Post::get($params, "ordemVenda", null);
            $layouts["historicoOrdem"] = \Utils\Post::get($params, "historicoOrdem", null);
            $layouts["myOrder"] = \Utils\Post::get($params, "myOrder", null);
            $layouts["myOrderHistory"] = \Utils\Post::get($params, "myOrderHistory", null);
            
            $json = json_encode($layouts);
            
            $layoutCliente = \Models\Modules\Cadastro\LayoutRn::getLayout($cliente->id);
            
            if(empty($layoutCliente)){
                $layoutCliente = new \Models\Modules\Cadastro\Layout();
                $layoutCliente->idCliente = $cliente->id;
                $layoutCliente->orderBook = $json; 
                
            } else {
               $layoutCliente->orderBook = $json; 
            }
            
            $layourRn->salvarOrderBook($layoutCliente);
                       
            $json["mensagem"] = "Layout salvo.";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function dadosParidade() {
        
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();

            if($paridade->moedaTrade->id == 1){
                $json["maior"] = "R$ " . number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ",", ".");
                $json["menor"] = "R$ " . number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ",", ".");
                $json["ultimo"] = "R$ " . number_format($paridade->ultimoPreco, $paridade->moedaTrade->casasDecimais, ",", ".");
                $json["volume"] = number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", "");
            } else {
                $json["maior"] = number_format($paridade->maiorPreco, $paridade->moedaTrade->casasDecimais, ".", ""); 
                $json["menor"] = number_format($paridade->menorPreco, $paridade->moedaTrade->casasDecimais, ".", ""); 
                $json["ultimo"] = number_format($paridade->ultimoPreco, $paridade->moedaTrade->casasDecimais,".", "");
                $json["volume"] = number_format($paridade->volume, $paridade->moedaBook->casasDecimais, ".", ""); 
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function dadosSaldo() {
        
        try {
           
            $cliente = \Utils\Geral::getCliente();
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            
            //Saldo moeeda Trade
            if($paridade->moedaTrade->id == 1){ //REAL
               $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
               $saldoTrade = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true); 
               
               $json["tradeBloqueado"] = number_format($saldoTrade["bloqueado"], $paridade->moedaTrade->casasDecimais, ".", "");
               $json["tradeDisponivel"] = number_format($saldoTrade["saldo"], $paridade->moedaTrade->casasDecimais, ".", "");
               
            } else { // CRIPTO
                $saldoTrade = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $paridade->moedaTrade->id, true, false);  
                
                $json["tradeBloqueado"] = number_format($saldoTrade["bloqueado"], $paridade->moedaTrade->casasDecimais, ".", "");
                $json["tradeDisponivel"] = number_format($saldoTrade["saldo"], $paridade->moedaTrade->casasDecimais, ".", "");
            }
            
            //Saldo moeda Book
            $saldoBook = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $paridade->moedaBook->id, true, false);
            
            $json["bookBloqueado"] = number_format($saldoBook["bloqueado"], $paridade->moedaBook->casasDecimais, ".", "");
            $json["bookDisponivel"] = number_format($saldoBook["saldo"], $paridade->moedaBook->casasDecimais, ".", "");
           
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function listarOrdensExecutadas($params) {
        try {
            
            $data = \Utils\Post::get($params, "data", "todos");
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $limite = \Utils\Post::get($params, "limite", 10);
            
            $cliente = \Utils\Geral::getCliente();
            $paridade = \Modules\principal\Controllers\Principal::getParity();

            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $cancelada = "N";
            
            switch ($data) {                
                case "dia":                        
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;
                case "todos":
                    $dataInicial = null;
                    $dataFinal = null;
                    break;
            }
            
            switch ($tipo) {
                case "compra":                        
                    $tipo = \Utils\Constantes::ORDEM_COMPRA;
                    $cancelada = "T";
                    break;
                case "venda":                        
                    $tipo = \Utils\Constantes::ORDEM_VENDA;
                    $cancelada = "T";
                    break;                                
                case "todos":
                    $tipo = "T";
                    $cancelada = "T";
                    break;
            }
           
            if($limite == "todos"){
                $limite = 0;
            }
            
            if($tipo == "todos"){
                $tipo = "T";
            }
            
            $lista = $ordemExecutadaRn->filtrar($paridade, $dataInicial, $dataFinal, "T", "T", $cliente->id, "todos");
                        
            $json["html"] = $this->htmlOrdensExecutadas($lista, $cliente, $tipo, $limite);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlOrdensExecutadas($lista, $cliente, $tipo, $limite) {
        $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
        $orderBook = new \Models\Modules\Cadastro\OrderBook();
        
        $idOrdem = 0;
        $ordem = null;
        $qtdOperacao = 0;
        
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordemExecutada) {
                
                switch ($cliente->id) {
                    case $ordemExecutada->idClienteComprador:
                        if($ordemExecutada->tipo == \Utils\Constantes::ORDEM_COMPRA){
                            $idOrdem = $ordemExecutada->idOrderBookCompra;
                        } else {
                            $idOrdem =  $ordemExecutada->idOrderBookVenda;
                        }
                        break;
                    case $ordemExecutada->idClienteVendedor:
                        if($ordemExecutada->tipo == \Utils\Constantes::ORDEM_VENDA){
                            $idOrdem =  $ordemExecutada->idOrderBookCompra;
                        } else {
                            $idOrdem = $ordemExecutada->idOrderBookVenda;
                        }
                        break;
                }                
                
                if ($idOrdem != $orderBook->id) {
                    $orderBook->id = $idOrdem;
                    $orderBookRn->carregar($orderBook, true, true);
                    
                    if($orderBook->tipo == $tipo || $tipo == "T"){
                        if(is_numeric($limite)){
                            if($qtdOperacao <= $limite){
                                
                                $qtdOperacao++;
                                $this->htmlItemOrdensExecutadas($ordemExecutada, $orderBook);
                                
                            } else {
                                break;
                            }
                        } else {
                             $this->htmlItemOrdensExecutadas($ordemExecutada, $orderBook);
                        }
                       
                    }
                }
                
                
                
            }
            
            
        } else {
            ?>
            <tr class="my-extrato-order-item">
                <td class=" text-center" colspan="8">
                    <?php echo $this->idioma->getText("nenhumaOrdemExibida") ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemOrdensExecutadas(\Models\Modules\Cadastro\OrdemExecutada $ordemExecutada, \Models\Modules\Cadastro\OrderBook $ordem) {

        $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;");
        $tradeSymbol = $ordem->paridade->moedaTrade->simbolo;
        $bookSymbol = $ordem->paridade->moedaBook->simbolo;
        
        ?>
            
        <tr class="" style="<?php echo $color?>;">
                <td class="text-center"><?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP)?></td>
                <td class="text-center"><?php echo str_replace(":", "/", $ordem->paridade->symbol) ?></td> 
                <td class="text-center"><?php echo ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? $this->idioma->getText("compraC") : $this->idioma->getText("vendaC")) ?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format($ordem->valorCotacao, $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format($ordemExecutada->valorCotacao, $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?></td>
                <td class="text-center"><?php echo $bookSymbol ?> <?php echo number_format($ordemExecutada->volumeExecutado, $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo $bookSymbol ?> <?php echo number_format($ordem->valorTaxaExecutada, $ordem->paridade->moedaBook->casasDecimais, ".", "")?></td>
                <td class="text-center"><?php echo $tradeSymbol ?> <?php echo number_format($ordemExecutada->quoteVolume, $ordem->paridade->moedaTrade->casasDecimais, ".", "")?></td>
            </tr>
        <?php
        
    }
}