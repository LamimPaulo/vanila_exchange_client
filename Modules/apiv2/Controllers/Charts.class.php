<?php

namespace Modules\apiv2\Controllers;

class Charts {
    private $method = null;
    
    public function __construct() {
        
        header('Access-Control-Allow-Origin: *');
    }
    
    public function ohlc($params) {
        $httpResponse = new HttpResult();
        
        error_reporting(E_ALL);
        try {
            
            $p = \Utils\Get::get($params, 0, 0);
            
            $off = \Utils\Get::get($params, 1, 0);
            $escala = \Utils\Get::get($params, 2, "5");
            $unidade = \Utils\Get::get($params, 3, "min");
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol($p);
               
            if ($paridade == null) {
                throw new \Exception("Paridade inválida", 400);
            }
            
            $currentOffset = $off;
            
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $result = $ordemExecutadaRn->getDadosGrafico($paridade->id, $off, $unidade);
                 
            $lista = Array();
            $currentIndex = "";
            $i = -1;
            if (sizeof($result) > 0) {
                foreach ($result as $dados) {
                    $data = new \Utils\Data(substr($dados["data_execucao"], 0, 19));

                    
                    $dataIndex = BookUtils::ajusteIndiceUnidade($unidade, $escala, $data);
                   
                    if ($currentIndex != $dataIndex) {
                        
                        if (!empty($currentIndex)) {
                            
                            $lastCandle = $lista[$i];
                            
                            $dtUm = BookUtils::getDataByIndex($currentIndex, $unidade);
                            $dtDois = BookUtils::getDataByIndex($dataIndex, $unidade);

                            $difArray = BookUtils::calcularPosicoesEntreDatas($dtUm, $dtDois, $unidade, $escala);

                            if (sizeof($difArray) > 0) {

                                foreach ($difArray as $candle) {
                                    $i++;
                                    $candle["o"] = $lastCandle["c"];
                                    $candle["h"] = $lastCandle["c"];
                                    $candle["l"] = $lastCandle["c"];
                                    $candle["c"] = $lastCandle["c"];
                                    $lista[$i] = $candle;
                                }

                            }
                        }
                        $i++;
                        $currentIndex = $dataIndex;
                    }
            
                    if (!isset($lista[$i]["time"])) {
                        $dataRef = BookUtils::getDataByIndex($dataIndex, $unidade);
                        $lista[$i]["time"] = strtotime($dataRef->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP) . ":00") * 1000;
                        $lista[$i]["data"] = $dataRef->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                        $lista[$i]["UTC"] = Array(
                            "y" => $dataRef->formatar("Y"),
                            "m" => $dataRef->formatar("m"),
                            "d" => $dataRef->formatar("d"),
                            "h" => $dataRef->formatar("h"),
                            "i" => $dataRef->formatar("i"),
                            "s" => $dataRef->formatar("s"),
                        );
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
                        $lista[$i]["volume"] += number_format(($lista[$i]["volume"] + $dados["volume"]), $paridade->moedaBook->casasDecimais, ".", "");
                    } else {
                        $lista[$i]["volume"] = number_format($dados["volume"], $paridade->moedaBook->casasDecimais, ".", "");
                    }

                    if (!isset($lista[$i]["volumecompra"])) {
                        $lista[$i]["volumecompra"] = 0;
                    }

                    if (!isset($lista[$i]["volumevenda"])) {
                        $lista[$i]["volumevenda"] = 0;
                    }

                    if ($dados["tipo"] == \Utils\Constantes::ORDEM_COMPRA) {
                        $lista[$i]["volumecompra"] += number_format(($lista[$i]["volumecompra"] + $dados["volume"]), $paridade->moedaBook->casasDecimais, ".", "");
                    } else {
                        $lista[$i]["volumevenda"] += number_format(($lista[$i]["volumevenda"] + $dados["volume"]), $paridade->moedaBook->casasDecimais, ".", "");
                    }

                    //$lista[$i][] = $dados;

                    $offset = $dados["id"];
                }
            } else {
                $offset = $currentOffset;
            }
            
            $httpResponse->addBody("grafico", $lista);
            $httpResponse->addBody("nome", $paridade->moedaBook->nome);
            $httpResponse->addBody("mercado", $paridade->moedaBook->simbolo);
            $httpResponse->addBody("offset", $offset);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
}