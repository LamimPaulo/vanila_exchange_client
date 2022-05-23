<?php

namespace Modules\apiv2\Controllers;

class BookUtils {
    
    public static function dateInterval($unidade, $offset = 0) {
        $dataIni = new \Utils\Data(date("d/m/Y") . " 00:00:00");
        $utilizadoOffset = false;
        if ($offset > 0) {
            try {
                $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
                $ordemExecutada = new \Models\Modules\Cadastro\OrdemExecutada();
                $ordemExecutada->id = $offset;
                $ordemExecutadaRn->conexao->carregar($ordemExecutada);
                
                $dataIni = new \Utils\Data($ordemExecutada->dataExecucao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
                $utilizadoOffset = true;
            } catch (\Exception $ex) {

            }
        }
         
        if (!$utilizadoOffset) {
            switch (strtolower($unidade)) {
                case ChartInterval::$MIN:
                    $dataIni->subtrair(0, 0, 7);
                    break;
                case ChartInterval::$HOR:
                    $dataIni->subtrair(0, 1);
                    break;
                case ChartInterval::$DIA:
                    $dataIni->subtrair(0,0,5);
                    break;
                case ChartInterval::$SEM:
                    $dataIni->subtrair(7);
                    break;
                case ChartInterval::$MES:
                    $dataIni->subtrair(10);
                    break;
                case ChartInterval::$ANO:
                    $dataIni->subtrair(30);
                    break;

                default:
                    break;
            }
        }
        
        $dataFin = new \Utils\Data(date("d/m/Y") . " 23:59:59");
        $whereData = " AND oe.data_execucao BETWEEN '{$dataIni->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFin->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";    
            
        return $whereData;
        
    }
    
    public static function ajusteIndiceUnidade($unidade, $escala, \Utils\Data $data) {
        $dataIndex = "";
        switch (strtolower($unidade)) {
            case ChartInterval::$MIN:
                if ($escala == 1) {
                    $dataIndex = $data->formatar("d/m/Y H:i");
                } else {
                    $dataIndex = $data->formatar("d/m/Y H:");
                    $min = $data->formatar("i");
                    $m = (($min % intval($escala)) > 0 ? ($min - ($min%intval($escala))) : $min);
                    
                    $dataIndex .= (strlen($m) < 2 ? "0{$m}" : $m);
                } 
                break;
            case ChartInterval::$HOR:
                if ($escala == 1) {
                    $dataIndex = $data->formatar("d/m/Y H");
                } else {
                    $dataIndex = $data->formatar("d/m/Y ");
                    $hou = $data->formatar("H");
                    $m = (($hou % intval($escala)) > 0 ? ($hou - ($hou%intval($escala))) : $hou);
                    $dataIndex .= (strlen($m) < 2? "0{$m}" : $m);
                }
                break;
            case ChartInterval::$DIA:
                if ($escala == 1) {
                    $dataIndex = $data->formatar("d/m/Y");
                } else {
                    $dataIndex = $data->formatar("/m/Y");
                    $day = $data->formatar("d");
                    $m = (($day % intval($escala)) > 0 ? ($day - ($day%intval($escala))) : $day);
                    $dataIndex = (strlen($m) < 2 ? "0{$m}" : $m) . $dataIndex;
                }
                break;
            case ChartInterval::$SEM:

                break;
            case ChartInterval::$MES:
                    if ($escala == 1) {
                        $dataIndex = $data->formatar("m/Y");
                    } else {
                        $dataIndex = $data->formatar("/Y");
                        $mes = $data->formatar("m");
                        $m = (($mes % intval($escala)) > 0 ? ($mes - ($mes%intval($escala))) : $mes);
                        $dataIndex = (strlen($m) < 2 ? "0{$m}" : $m) . $dataIndex;
                    }
                break;
            case ChartInterval::$ANO:
                    if ($escala == 1) {
                        $dataIndex = $data->formatar("Y");
                    } else {
                        $ano = $data->formatar("Y");
                        $m = (($ano % intval($escala)) > 0 ? ($ano - ($ano%intval($escala))) : $ano);
                        $dataIndex = $ano;
                    }
                break;
            default:
                break;
        }

        return $dataIndex;
        
    }
    
    public static function getDataByIndex($dataIndex, $unidade) {
        $dataRef = null;
        switch (strtolower($unidade)) {
            case ChartInterval::$MIN:
                $dataRef = new \Utils\Data($dataIndex . ":00");
                break;
            case ChartInterval::$HOR:
                $dataRef = new \Utils\Data($dataIndex . "00:00");
                break;
            case ChartInterval::$DIA:
                $dataRef = new \Utils\Data($dataIndex . " 00:00:00");
                break;
            case ChartInterval::$SEM:

                break;
            case ChartInterval::$MES:
                    $dataRef = new \Utils\Data("01/".$dataIndex . ":00");
                break;
            case ChartInterval::$ANO:
                    $dataRef = new \Utils\Data("01/01/".$dataIndex . ":00");
                break;
            default:
                break;
        }

        return $dataRef;
        
    }
    
    
    public static function calcularPosicoesEntreDatas(\Utils\Data $dataUm, \Utils\Data $dataDois, $unidade, $escala) {
        
        $array = Array();
        
        switch (strtolower($unidade)) {
            case ChartInterval::$MIN:
                $dataUm->somar(0, 0, 0, 0, $escala);
                if (!$dataUm->igual($dataDois)) {
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar(0, 0, 0, 0, $escala);
                    }
                }
                break;
            case ChartInterval::$HOR:
                $dataUm->somar(0, 0, 0, $escala);
                if (!$dataUm->igual($dataDois)) {
                    
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar(0, 0, 0, $escala);
                    }
                }
                break;
            case ChartInterval::$DIA:
                $dataUm->somar(0, 0,  $escala);
                if (!$dataUm->igual($dataDois)) {
                    
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar(0, 0, $escala);
                    }
                }
                break;
            case ChartInterval::$SEM:
                $dataUm->somar(0, 0,  ($escala * 7));
                if (!$dataUm->igual($dataDois)) {
                    
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar(0, 0, ($escala * 7));
                    }
                }
                break;
            case ChartInterval::$MES:
                $dataUm->somar(0,  $escala);
                if (!$dataUm->igual($dataDois)) {
                    
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar(0, $escala);
                    }
                }
                break;
            case ChartInterval::$ANO:
                    $dataUm->somar($escala);
                if (!$dataUm->igual($dataDois)) {
                    
                    while (!$dataUm->igual($dataDois)) {
                        $array[] = self::getEmptyArrayOHLC($dataUm);
                        $dataUm->somar($escala);
                    }
                }
                break;
            default:
                break;
        }

        return $array;
        
    }
    
    private static function getEmptyArrayOHLC($data) {
        return Array(
            "time" => strtotime($data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP) . ":00") * 1000,
            "data" => $data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP),
            "UTC" => Array(
                "y" => $data->formatar("Y"),
                "m" => $data->formatar("m"),
                "d" => $data->formatar("d"),
                "h" => $data->formatar("h"),
                "i" => $data->formatar("i"),
                "s" => $data->formatar("s"),
            ),
            "o" => 0,
            "c" => 0,
            "h" => 0,
            "l" => 0,
            "volume" => 0,
            "volumeCompra" => 0,
            "volumeVenda" => 0
        );
    }
    
}