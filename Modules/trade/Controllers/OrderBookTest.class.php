<?php

namespace Modules\trade\Controllers;

class OrderBookTest {
    
    private $printBook = true;
    private $printOrdens = true;
    private $tipoOrdem = \Utils\Constantes::ORDEM_COMPRA;
    
    private $volumeMinimoOrdens = 0.025;
    private $volumeMaximoOrdens = 1.5;
    private $cotacaoMin = 27000.00;
    private $cotacaoMax = 28000.00;
    private $qtdOrdens = 20;
    private $idMoeda = 2;
    
    private $ordens = Array(
        
        Array(
            "id" => 1,
            "volume" => 0.5,
            "cotacao" => 27101.00,
            "taxa" => 0.45
        ),
        
        Array(
            "id" => 2,
            "volume" => 1.5,
            "cotacao" => 27101.20,
            "taxa" => 0.45
        )
        
    );
    
    
    /**
     * Use se vc precisar startar o book com alguma ordem específica
     * @var Array() 
     */
 
    private $ordensForcadas = Array(
        
        /**
         * 
         * Abaixo segue um exemplo de ordem. Deve-se informar apenas o volume e a cotação
        Array(
            "volume" => 0.5,
            "cotacao" => 26799.99
        )
         * 
         */
    );
    
    
    public function testar($params) {
        
        try {
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $orderBookRn->setHomolog(true);
            
            $ordensAtivas = Array();
            
            foreach ($this->ordens as $d) {
                $order = new \Models\Modules\Cadastro\OrderBook();
                $order->cancelada = 0;
                $order->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $order->direta = 0;
                $order->executada = 0;
                $order->id = $d["id"];
                $order->idCliente = 1;
                $order->idMoeda = $this->idMoeda;
                $order->percentualTaxa = $d["taxa"];
                $order->tipo = $this->tipoOrdem;
                $order->valorCotacao = $d["cotacao"];
                $order->valorCotacaoReferencia = 0;
                $order->valorTaxa = number_format(($d["volume"] * ($d["taxa"]/100)), 8, ".", "");
                $order->volumeCurrency = number_format(($d["volume"] - $order->valorTaxa), 8, ".", "");
                $order->valorTaxaExecutada = 0;
                $order->volumeExecutado = 0;
                
                $ordensAtivas[] = $order;
            }
            
            $this->printOrders($ordensAtivas);
            
            $tipoOrdensPassivas = ($this->tipoOrdem == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ORDEM_VENDA : \Utils\Constantes::ORDEM_COMPRA);
            
            
            $orderBookRn->generateOrdersHomolog(\Utils\Constantes::ORDEM_VENDA, $this->volumeMinimoOrdens, $this->volumeMaximoOrdens, $this->cotacaoMin, $this->cotacaoMax, $this->qtdOrdens, $this->idMoeda, $this->ordensForcadas);
            
            $book = $orderBookRn->getBookHomolog();
            
            
            
            $this->printBook($book, $tipoOrdensPassivas);
            
            foreach ($ordensAtivas as $ordem) {
                $orderBookRn->executarOrdemPassiva($ordem);
                
                $this->printOrder($ordem);
                
                $book = $orderBookRn->getBookHomolog();
                $this->printBook($book, $tipoOrdensPassivas);
                
                $this->printOrders($ordensAtivas);
            }
            
            
            
            
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
        
    }
    
    
    
    
    private function printBook($book, $tipo) {
        $volumeTotalOrdens = 0;
        $volumeTotalExecutado = 0;
        $volumeTotalTaxas = 0;
        $volumeTotalTaxasExecutadas = 0;
        if ($this->printBook) {
            ?>
            <h3>Order Book de <?php echo ($tipo == \Utils\Constantes::ORDEM_COMPRA ? "Compra" : "Venda")?></h3>
            <br>
            <table style="width: 100%; border: 1px solid #cccccc;" >
                
                <thead>
                    <tr>
                        <th style="text-align: center;"><strong>Ordem</strong></th>
                        <th style="text-align: center;"><strong>Volume Ordem ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Executado ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Taxa ($)</strong></th>
                        <th style="text-align: center;"><strong>Vol. Taxa Ex. ($)</strong></th>
                        <th style="text-align: center;"><strong>Cotação (R$)</strong></th>
                        <th style="text-align: center;"><strong>Per. Taxa (%)</strong></th>
                        <th style="text-align: center;"><strong>Finalizada</strong></th>
                        
                    </tr>
                </thead>
                
                <tbody>
            <?php
            foreach ($book as $b) {
                $volumeTotalOrdens += $b["volume_currency"];
                $volumeTotalExecutado += $b["volume_executado"];
                $volumeTotalTaxas += $b["valor_taxa"];
                $volumeTotalTaxasExecutadas += $b["valor_taxa_executada"];
                ?>
                <tr style="color: <?php echo ($b["executada"] > 0 ? "red" : ($b["volume_executado"] > 0 ? "orange" : "blue")) ?>;">
                    <td style="text-align: center;"><?php echo $b["id"] ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["volume_currency"], 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["volume_executado"], 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["valor_taxa"], 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["valor_taxa_executada"], 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["valor_cotacao"], 2, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b["percentual_taxa"], 2, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo ($b["executada"] > 0 ? "Sim" : "Não") ?></td>
                </tr>
                <?php
            }
            ?>
                </tbody>
                
                <tfoot>
                    <tr style="border-top: 2px solid #666666;">
                        <th style="text-align: center;"><strong></strong></th>
                        <th style="text-align: center;"><strong><?php echo number_format($volumeTotalOrdens, 8, ".", "") ?></strong></th>
                        <th style="text-align: center;"><strong><?php echo number_format($volumeTotalExecutado, 8, ".", "") ?></strong></th>
                        <th style="text-align: center;"><strong><?php echo number_format($volumeTotalTaxas, 8, ".", "") ?></strong></th>
                        <th style="text-align: center;"><strong><?php echo number_format($volumeTotalTaxasExecutadas, 8, ".", "") ?></strong></th>
                        <th style="text-align: center;"><strong></strong></th>
                        <th style="text-align: center;"><strong></strong></th>
                        <th style="text-align: center;"><strong></strong></th>
                    </tr>
                </tfoot>
            </table>
            <?php
        }
    }
    
    
    private function printOrders($ordens) {
        
        if ($this->printOrdens) {
            ?>
            <h3>Suas Ordens de <?php echo ($this->tipoOrdem == \Utils\Constantes::ORDEM_COMPRA ? "Compra" : "Venda")?></h3>
            <br>
            <table style="width: 100%; border: 1px solid #cccccc;" >
                <thead>
                    <tr>
                        <th style="text-align: center;"><strong>Ordem</strong></th>
                        <th style="text-align: center;"><strong>Volume Ordem ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Executado ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Taxa ($)</strong></th>
                        <th style="text-align: center;"><strong>Vol. Taxa Ex. ($)</strong></th>
                        <th style="text-align: center;"><strong>Cotação (R$)</strong></th>
                        <th style="text-align: center;"><strong>Per. Taxa (%)</strong></th>
                        <th style="text-align: center;"><strong>Finalizada</strong></th>
                        
                    </tr>
                </thead>
                
                <tbody>
            <?php
            foreach ($ordens as $b) {
                
                ?>
                <tr style="color: <?php echo ($b->executada > 0 ? "red" : ($b->volumeExecutado > 0 ? "orange" : "blue")) ?>;">
                    <td style="text-align: center;"><?php echo $b->id ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->volumeCurrency, 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->volumeExecutado, 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->valorTaxa, 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->valorTaxaExecutada, 8, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->valorCotacao, 2, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo number_format($b->percentualTaxa, 2, ".", "") ?></td>
                    <td style="text-align: center;"><?php echo ($b->executada > 0 ? "Sim" : "Não") ?></td>
                </tr>
                <?php
            }
            ?>
                </tbody>
                
            </table>
            <?php
        }
    }
    
    
    private function printOrder(\Models\Modules\Cadastro\OrderBook $b) {
        
        if ($this->printOrdens) {
            ?>
            <br><br><br>
            <h3>Ordem Executada:  <?php echo ($this->tipoOrdem == \Utils\Constantes::ORDEM_COMPRA ? "Compra" : "Venda")?></h3>
            <br>
            <table style="width: 100%; border: 1px solid #cccccc;" >
                
                <thead>
                    <tr>
                        <th style="text-align: center;"><strong>Ordem</strong></th>
                        <th style="text-align: center;"><strong>Volume Ordem ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Executado ($)</strong></th>
                        <th style="text-align: center;"><strong>Volume Taxa ($)</strong></th>
                        <th style="text-align: center;"><strong>Vol. Taxa Ex. ($)</strong></th>
                        <th style="text-align: center;"><strong>Cotação (R$)</strong></th>
                        <th style="text-align: center;"><strong>Per. Taxa (%)</strong></th>
                        <th style="text-align: center;"><strong>Finalizada</strong></th>
                        
                    </tr>
                </thead>
                
                <tbody>
                    <tr style="color: <?php echo ($b->executada > 0 ? "red" : ($b->volumeExecutado > 0 ? "orange" : "blue")) ?>;">
                        <td style="text-align: center;"><?php echo $b->id ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->volumeCurrency, 8, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->volumeExecutado, 8, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->valorTaxa, 8, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->valorTaxaExecutada, 8, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->valorCotacao, 2, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo number_format($b->percentualTaxa, 2, ".", "") ?></td>
                        <td style="text-align: center;"><?php echo ($b->executada > 0 ? "Sim" : "Não") ?></td>
                    </tr>
                </tbody>
            </table>
            <?php
        }
    } 
}