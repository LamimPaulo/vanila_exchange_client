<?php

namespace Modules\ws\Controllers;

class BookSocketHistory {

    public function index() {

        $options = array(
            'cluster' => 'mt1',
            'useTLS' => true
        );

        $pusher = new \Pusher\Pusher(
            '56e0950f25b64bc084f5',
            '55b581fb1b662448c12c',
            '1062419',
            $options
          );

        if (sizeof($pusher->get_channels()->channels) > 0) {
            foreach ($pusher->get_channels()->channels as $channel => $v) {
                
                //Recebe paridade pelo canal
                $paridade = $channel . "";
                
                //Descrypt
                $paridade = str_replace("_", ":", \Utils\SQLInjection::clean($paridade));

                $dados = $this->ordemExecutadas($paridade);
                
                
                $batch = array();
                $batch[] = array('channel' => $channel, 'name' => 'history', 'data' => $dados);
                $pusher->triggerBatch($batch);
            }
        }
    }
    
    public function bookOrdemExecutadas($params) {

        $paridade = \Utils\Post::get($params, "paridade", null);
        $paridade = str_replace("_", ":", \Utils\SQLInjection::clean($paridade));

        $dados = $this->ordemExecutadas($paridade);

        print json_encode($dados);
    }

    private function ordemExecutadas($paridade) {

        try {
            $dados = Array();
            
            $bd = new \Io\BancoDados(BDBOOK);
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn($bd);
            $paridade = $paridadeRn->getBySymbol($paridade);
            
            $dataInicial = new \Utils\Data(date("d/m/Y"));
            $dataFinal = new \Utils\Data(date("d/m/Y"));
            $dataInicial->subtrair(0, 0, 1);

            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $lista = $ordemExecutadaRn->filtrar($paridade, $dataInicial, $dataFinal, "T", "T", 0, 0, false);

            foreach ($lista as $executada) {
    
                $executadas[] = Array("price" => number_format($executada->valorCotacao, $paridade->moedaTrade->casasDecimais, ".", ""),
                    "quantity" => number_format($executada->volumeExecutado, $paridade->moedaBook->casasDecimais, ".", ""),
                    "total" => number_format(($executada->valorCotacao * $executada->volumeExecutado),  $paridade->moedaTrade->casasDecimais, ".", ""),
                    "op" => $executada->tipo, "date" => $executada->dataExecucao->formatar(\Utils\Data::FORMATO_HORA_TIMESTAMP));
            }
                        
            $dados["low"] = number_format($paridade->menorPreco,  $paridade->moedaTrade->casasDecimais, ".", "");
            $dados["high"] = number_format($paridade->maiorPreco,  $paridade->moedaTrade->casasDecimais, ".", "");
            $dados["volume"] = number_format($paridade->volume,  $paridade->moedaBook->casasDecimais, ".", "");
            $dados["last_price"] = number_format($paridade->ultimaCompra,  $paridade->moedaTrade->casasDecimais, ".", "");
            $dados["list"] = $executadas;
            
            
            $dados["result"] = $dados;
            $dados["message"] = "";
            $dados["success"] = true;
        } catch (\Exception $ex) {
            $dados["success"] = false;
            $dados["message"] = \Utils\Excecao::mensagem($ex);
        }
        
        return $dados;
    }
}
