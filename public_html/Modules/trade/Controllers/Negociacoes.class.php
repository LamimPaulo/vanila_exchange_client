<?php

namespace Modules\trade\Controllers;

class Negociacoes {
    
    private $codigoModulo = "trade";
    private $idioma = null;
    
    public function __construct($params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("negociacoes", IDIOMA);
    }
    
    
    public function index($params) {
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $params["casasDecimais"] = $configuracao->qtdCasasDecimais;
        \Utils\Layout::view("index_negociacoes", $params);
    }
    
    
    
    public function listarOrdensCompra($params) {
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();            
            $moeda->id = $paridade->idMoedaTrade;
            $moedaRn->carregar($moeda);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", 0, 0, true);
            
            $json["html"] = $this->htmlOrdens($lista, \Utils\Constantes::ORDEM_COMPRA, $moeda);
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
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();            
            $moeda->id = $paridade->idMoedaTrade;
            $moedaRn->carregar($moeda);
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", 0, 0, true);
            
            $json["html"] = $this->htmlOrdens($lista, \Utils\Constantes::ORDEM_VENDA, $moeda);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function htmlOrdens($lista, $tipo, $moeda) {
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {
                $this->htmlItemOrdem($ordem, $configuracao->qtdCasasDecimais, $moeda);
            }
        } else {
            ?>
            <tr class=" order-item-<?php echo $tipo?>">

            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemOrdem(\Models\Modules\Cadastro\OrderBook $ordem, $decimais, $moeda) {
        
        $casasDecimais = ($ordem->paridade->idMoedaTrade == 1 ? $decimais : 8);
        $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;"); 
        ?>
        <tr class="order-item-<?php echo $ordem->tipo ?>" 
                style="<?php echo $color?>; cursor: pointer;" 
                 >
            
            <td class="text-center">
                 <?php echo $moeda->simbolo . " " . number_format($ordem->valorCotacao, $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-center">
                <?php echo number_format($ordem->volumeCurrency, $ordem->paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
            <td class="text-center">
                 <?php echo $moeda->simbolo . " " . number_format(($ordem->valorCotacao * $ordem->volumeCurrency), $ordem->paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
        </tr>
        <?php
    }
    
    public function listarTrade($params) {
        try {
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            $dataInicial = new \Utils\Data(date("d/m/Y"));
            $dataFinal = new \Utils\Data(date("d/m/Y"));
            $dataInicial->subtrair(0, 0, 1);
                        
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $lista = $ordemExecutadaRn->filtrar($paridade, $dataInicial, $dataFinal, "T", "T", 0, 200, false);

            $json["html"] = $this->htmlOrdensTrade($lista, $paridade);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    private function htmlOrdensTrade($lista, $paridade) {


        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {  
   
                $this->htmlItemOrdemTrade($ordem, $paridade);

            }
        } else {
            ?>
            <tr class="order-item-trade">
                <td class="text-center" colspan="5">
                        <?php echo $this->idioma->getText("nenhumaOrdem24Horas") ?>
                    </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemOrdemTrade(\Models\Modules\Cadastro\OrdemExecutada $ordemExecutada, \Models\Modules\Cadastro\Paridade $paridade) {
        
        $tipo = ($ordemExecutada->id > 0 ? ($ordemExecutada->idOrderBookCompra > 0 && $ordemExecutada->idOrderBookVenda > 0 ? "Book" : "OTC") : "API");        
        $color = ($ordemExecutada->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;");
        
        if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
            $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
        }
        
        ?>
            <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
                <td class="text-left">
                    <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
                </td>
                <td class="text-center">
                    <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
                </td>
                <td class="text-right">
                    <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
                </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>
        <tr class="order-item-trade" style="<?php echo $color?>; cursor: pointer;" >
            <td class="text-left">
                <?php echo $ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP)?>
            </td>
            <td class="text-center">
                <?php echo $paridade->moedaTrade->simbolo . " " . number_format($ordemExecutada->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?>
            </td>
            <td class="text-right">
                <?php echo number_format($ordemExecutada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ",")?>
            </td>
        </tr>


        <?php
        
    }
}


