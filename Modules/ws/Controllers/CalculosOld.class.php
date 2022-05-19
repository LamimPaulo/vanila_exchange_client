<?php

namespace Modules\ws\Controllers;

class Calculos {
    
    public function ticker($params) {
        
        $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
        $paridades = $paridadeRn->listar(null , "id", null, null, false, false);
        
        $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn($paridadeRn->conexao->adapter);
        
        $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataInicial->subtrair(0, 0, 0,24, 0, 0);
        $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
        
        foreach ($paridades as $paridade) {
            
            try {
                $ordemExecutadaRn->calculosTicker($paridade);
    
                /*$dadosVolume = $ordemExecutadaRn->calcularVolumeParidade($paridade->id, $dataInicial, $dataFinal);
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
                );*/
                
                
            } catch (\Exception $ex) {

            }
            
        }
        
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn($paridadeRn->conexao->adapter);
        $moedas = $moedaRn->listar("id > 1", "id", null, null);
        
        foreach ($moedas as $moeda) {
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
        }
        
    }
    
}