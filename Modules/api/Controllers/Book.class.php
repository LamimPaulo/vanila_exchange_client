<?php

namespace Modules\api\Controllers;

class Book {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function candles($params) {
        try {
            
            $idMoeda = \Utils\Get::get($params, 0, 0);
            $off = \Utils\Get::get($params, 1, 0);
            $escala = \Utils\Get::get($params, 2, "5");
            $unidade = \Utils\Get::get($params, 2, "min");
            
            $moeda = \Models\Modules\Cadastro\MoedaRn::get($idMoeda);
            
            $whereData = BookUtils::dateInterval($unidade);
            
            $currentOffset = $off;
            if ($off > 0) {
                $off = " AND oe.id > {$off} ";
            } else {
                $off = "";
            }
            
            $sql = "SELECT 
                    oe.id,
                    oe.data_execucao,
                    oe.valor_cotacao AS valor,
                    oe.volume_executado AS volume,
                    oe.tipo
                    FROM ordens_executadas oe 
                    INNER JOIN order_book ob ON ((oe.tipo = 'C' AND ob.id = oe.id_order_book_compra) OR (oe.tipo = 'V' AND ob.id = oe.id_order_book_venda)) 
                    WHERE ob.id_moeda = {$idMoeda} {$off}
                    ORDER BY oe.data_execucao ;
                    ";
             //exit($sql);       
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $result = $ordemExecutadaRn->conexao->adapter->query($sql)->execute();
            
            
            
            $lista = Array();
            $currentIndex = "";
            $i = -1;
            if (sizeof($result) > 0) {
                foreach ($result as $dados) {
                    $data = new \Utils\Data(substr($dados["data_execucao"], 0, 19));

                    $dataIndex = BookUtils::ajusteIndiceUnidade($unidade, $escala, $data);
                    
                    
                    if ($currentIndex != $dataIndex) {
                        $i++;
                        $currentIndex = $dataIndex;
                    }

                    if (!isset($lista[$i]["time"])) {
                        $dataRef = BookUtils::getDataByIndex($dataIndex, $unidade);
                        $lista[$i]["time"] = strtotime($dataRef->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP) . ":00") * 1000;
                        $lista[$i]["data"] = $dataRef->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                    }

                    if (!isset($lista[$i]["o"])) {
                        $lista[$i]["o"] = $dados["valor"];
                    } 

                    $lista[$i]["c"] = $dados["valor"];

                    if (!isset($lista[$i]["h"])) {
                        $lista[$i]["h"] = $dados["valor"];
                    } else {
                        if ($lista[$i]["h"] < $dados["valor"]) {
                            $lista[$i]["h"] = $dados["valor"];
                        }
                    }

                    if (!isset($lista[$i]["l"])) {
                        $lista[$i]["l"] = $dados["valor"];
                    } else {
                        if ($lista[$i]["l"] > $dados["valor"]) {
                            $lista[$i]["l"] = $dados["valor"];
                        }
                    }

                    if (isset($lista[$i]["volume"])) {
                        $lista[$i]["volume"] += number_format(($lista[$i]["volume"] + $dados["volume"]), $moeda->casasDecimais, ".", "");
                    } else {
                        $lista[$i]["volume"] = number_format($dados["volume"], $moeda->casasDecimais, ".", "");
                    }

                    if (!isset($lista[$i]["volumecompra"])) {
                        $lista[$i]["volumecompra"] = 0;
                    }

                    if (!isset($lista[$i]["volumevenda"])) {
                        $lista[$i]["volumevenda"] = 0;
                    }

                    if ($dados["tipo"] == \Utils\Constantes::ORDEM_COMPRA) {
                        $lista[$i]["volumecompra"] += number_format(($lista[$i]["volumecompra"] + $dados["volume"]), $moeda->casasDecimais, ".", "");
                    } else {
                        $lista[$i]["volumevenda"] += number_format(($lista[$i]["volumevenda"] + $dados["volume"]), $moeda->casasDecimais, ".", "");
                    }

                    //$lista[$i][] = $dados;

                    $offset = $dados["id"];
                }
            } else {
                $offset = $currentOffset;
            }
            /*
            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataRef = new \Utils\Data($currentIndex . ":00");
            while ($dataRef->menor($dataFinal)) {
                
                $open = $lista[sizeof($lista) - 1]["c"];
                
                $num = rand(0, 10);
                
                $close = ($num > 5 ? $open+$num : $open-$num);
                $high = ($num > 5 ? $open+2+$num : $open+2-$num);
                $low = ($num > 5 ? $open-2-$num : $open-2-$num);
                
                $vc = rand(0, 10);
                $vv = rand(0, 10);
                $v = $vc + $vv;
                
                $lista[] = Array(
                    "time" => strtotime($data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP) . ":00") * 1000,
                    "data" => $data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP),
                    "o" => number_format($open, 4, ".", ""),
                    "c" => number_format($close, 4, ".", ""),
                    "h" => number_format($high, 4, ".", ""),
                    "l" => number_format($low, 4, ".", ""),
                    "volume" => number_format($v, 8, ".", ""),
                    "volumecompra" => number_format($vc, 8, ".", ""),
                    "volumevenda" => number_format($vv, 8, ".", ""),
                );
                $dataRef->somar(0, 0, 0, 0, 1, 0);
            }
            */
            $json["grafico"] = $lista;
            $json["offset"] = $offset;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}
