<?php

namespace Modules\ws\Controllers;

class BookSocketIo {
    
    public function index() {        
       
         $options = array(
            'cluster' => 'mt1',
            'useTLS' => true
        );
         
        $pusher = new \Pusher\Pusher(
            '6d6796b91c225c158e45',
            '487a914f3d9f79797046',
            '1062353',
            $options
          );
        
        
        if(sizeof($pusher->get_channels()->channels) > 0) {
            foreach ($pusher->get_channels()->channels as $channel => $v) {
              
                //Recebe paridade pelo canal
                $paridade = $channel . "";
                
                //Descrypt
                $paridade = str_replace("_", ":", \Utils\SQLInjection::clean($paridade));
                
                //Coleta dados                
                $dados = $this->getBook($paridade);
                                
                $batch = array();
                $batch[] = array('channel' => $channel, 'name' => 'book', 'data' => $dados);
                $pusher->triggerBatch($batch);
            }
        }        
    }
    
    public function bookSocket($params) {

        $paridade = \Utils\Post::get($params, "paridade", null);
        $paridade = str_replace("_", ":", \Utils\SQLInjection::clean($paridade));

        $dados = $this->getBook($paridade);

        print json_encode($dados);
    }
    
    
    public function getBook($simboloParidade) {
       try {
           
           //exit("Simbolo " . $simboloParidade);
           
            $bd = new \Io\BancoDados(BDBOOK);
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);
            $paridade = $paridadeRn->getBySymbol($simboloParidade);

            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn($bd);
            
            $listaCompra = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_COMPRA, "N", "N", 30, 0, true);
            $listaVenda = $orderBookRn->getOrders($paridade, \Utils\Constantes::ORDEM_VENDA, "N", "N", 30, 0, true);
            
            $venda = current($listaVenda);
            $compra = current($listaCompra);   
            
            
            if($compra->valorCotacao != $paridade->precoCompra && !empty($compra->valorCotacao)){              
                $paridadeRn->conexao->update(Array("preco_compra" => $compra->valorCotacao), Array("id" => $paridade->id));                
            }
            
            if($venda->valorCotacao != $paridade->precoVenda && !empty($venda->valorCotacao)){               
                $paridadeRn->conexao->update(Array("preco_venda" => $venda->valorCotacao), Array("id" => $paridade->id));                
            }
            
            $novaCompra = Array();
            $volumeGeralCompra = 0;
            foreach ($listaCompra as $compra) {
                $compraNova = Array();
                
                $compraNova["timestamp"] = strtotime(date("Y-m-d H:i:s"));
                $compraNova["price"] = number_format($compra->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", "");
                $compraNova["quantity"] = number_format($compra->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "");
                $compraNova["total"] = number_format(($compra->valorCotacao * $compra->volumeCurrency), $paridade->moedaTrade->casasDecimais, ".", "");
                //$volumeGeralCompra = $volumeGeralCompra + $compra->volumeCurrency;
                $novaCompra[] = $compraNova;
            }
            
            $novaVenda = Array();
            $volumeGeralVenda = 0;
            foreach ($listaVenda as $venda) {
                $vendaNova = Array();
                
                $vendaNova["timestamp"] = strtotime(date("Y-m-d H:i:s"));
                $vendaNova["price"] = number_format($venda->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", "");
                $vendaNova["quantity"] = number_format($venda->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "");
                $vendaNova["total"] = number_format(($venda->valorCotacao * $venda->volumeCurrency), $paridade->moedaTrade->casasDecimais, ".", "");
                //$volumeGeralVenda = $volumeGeralVenda + $venda->volumeCurrency;
                $novaVenda[] = $vendaNova;
            }

            $dados["success"] = true;
            $dados["result"]["buy"] = $novaCompra;
            $dados["result"]["sell"] = $novaVenda;
        } catch (\Exception $ex) {
            $dados["success"] = false;            
            $dados["message"] = \Utils\Excecao::mensagem($ex);
        }
        
        return $dados;
        
    }    
    
}