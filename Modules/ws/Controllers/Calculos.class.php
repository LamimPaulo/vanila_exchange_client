<?php

namespace Modules\ws\Controllers;

class Calculos {
    
    
    public function ticker($params) {
        error_reporting(E_ALL);
        /*$paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        $paridades = $paridadeRn->listar("ativo = 1 AND status_mercado = 1", "id", null, null, false, false);
        
        $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn($paridadeRn->conexao->adapter);
        $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn($paridadeRn->conexao->adapter);
        
        $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataInicial->subtrair(0, 0, 0,24, 0, 0);
        $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
        
        foreach ($paridades as $paridade) {
            //$paridade = new \Models\Modules\Cadastro\Paridade();
            
            if ($paridade->moedaBook->ativo == 1){
               $ordemExecutadaRn->calculosTicker($paridade);
           }
           
           
            
            /*$ordemExecutada = $ordemExecutadaRn->getUltimaOrdemExecutada($paridade);
            $transacaoPendente = $transacaoPendenteBtcRn->getUltimaTransacao($paridade->idMoedaBook);
          
            $calcular = true;( ($ordemExecutada != null && $ordemExecutada->dataExecucao->maior($paridade->ultAtualizacaoTicker)) ||
                    ($transacaoPendente != null && $transacaoPendente->dataCadastro->maior($paridade->ultAtualizacaoTicker)) );
            
            if ($calcular) {
            
                try {

                    $dadosVolume = $ordemExecutadaRn->calcularVolumeParidade($paridade->id, $dataInicial, $dataFinal);
                    $dadosPrecos = $orderBookRn->getPrecos($paridade->id);
                    $dadosPrecos2 = $orderBookRn->getPrecoMinMaxDia($dataFinal, $paridade);

                    $volume = $dadosVolume["currency"];
                    $ultimaCompra = $dadosPrecos["ultimo"];                
                    $ultimaVenda = $dadosPrecos["ultimo"];
                    $precoVenda = $dadosPrecos["venda"];
                    $precoCompra = $dadosPrecos["compra"];
                    $menorPreco = $dadosPrecos2["min"];
                    $maiorPreco = $dadosPrecos2["max"];

                    $paridadeRn->conexao->update(
                        Array(
                            "volume" => number_format($volume, 25, ".", ""),
                            "menor_preco" => number_format($menorPreco, 25, ".", ""),
                            "preco_venda" => number_format($precoVenda, 25, ".", ""),
                            "ultima_compra" => number_format($ultimaCompra, 25, ".", ""),
                            "maior_preco" => number_format($maiorPreco, 25, ".", ""),
                            "ultima_venda" => number_format($ultimaVenda, 25, ".", ""),
                            "preco_compra" => number_format($precoCompra, 25, ".", ""),
                            "ult_atualizacao_ticker" => date("Y-m-d H:i:s"),
                        ),
                        Array(
                            "id" => $paridade->id
                        )
                    );

                } catch (\Exception $ex) {

                }
                echo "Calculou {$paridade->symbol}";
            } */
        /*}
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn($paridadeRn->conexao->adapter);
        $moedas = $moedaRn->listar("id > 1 AND ativo > 0", "id", null, null);
        
        foreach ($moedas as $moeda) {
            $ordemExecutada = $ordemExecutadaRn->getUltimaOrdemExecutadaByMoeda($moeda);
            $transacaoPendente = $transacaoPendenteBtcRn->getUltimaTransacao($moeda->id);
           
            $calcular = true; /*( ($ordemExecutada != null && $ordemExecutada->dataExecucao->maior($moeda->ultAtualizacaoTicker)) ||
                    ($transacaoPendente != null && $transacaoPendente->dataCadastro->maior($moeda->ultAtualizacaoTicker)) );*/
            
            
            /*if ($calcular) {
                
                try {
                    $volume = $ordemExecutadaRn->calcularVolumeMoeda($moeda->id, $dataInicial, $dataFinal);

                    $moedaRn->conexao->update(
                            Array(
                                "volume" => number_format($volume, 25, ".", ""),
                                "ult_atualizacao_ticker" => date("Y-m-d H:i:s")
                            ), 
                            Array("id" => $moeda->id)
                        );
                    
                    
                } catch (\Exception $ex) {
                    //print_r($ex);
                }
                echo "Calculou Moeda {$moeda->simbolo}";
            }
        }*/
        
    }
    
}