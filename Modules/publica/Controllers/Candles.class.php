<?php

namespace Modules\publica\Controllers;

class Candles {private $method = null;
    
    const TIMEZONE = "America/Argentina/Buenos_Aires";
    
    public function __construct($params) {
        date_default_timezone_set(self::TIMEZONE);
        
        if (AMBIENTE == "desenvolvimento") {
            /*$from = \Utils\Get::get($params, "from", null);

            $to = \Utils\Get::get($params, "to", null);
            
            $symbol = \Utils\Get::get($params, "symbol", null);
            $resolution = \Utils\Get::get($params, "resolution", null);

            echo $symbol . "<br>";
            echo $resolution . "<br>";
            echo date("d/m/Y H:i:s", $from) . "<br>";
            echo date("d/m/Y H:i:s", $to) . "<br><br><br>";
            exit("ok");*/
        }
        header('Access-Control-Allow-Origin: *');
    }
    
    public function history($params) {
                
        $httpResponse = new HttpResult();
        
        try {
           
            date_default_timezone_set("UTC");
            
            $symbol = \Utils\Get::get($params, "symbol", "BTC:BRL");
            $resolution = \Utils\Get::get($params, "resolution", 60);
            $from = \Utils\Get::get($params, "from", null);
            $to = \Utils\Get::get($params, "to", null);
            
            if($from != null){
                $dtFrom = new \Utils\Data(date("d/m/Y H:i:s", $from));
            } else {
                $dtFrom = new \Utils\Data(date("d/m/Y H:i:s"));
                $dtFrom->subtrair(0, 0, 7);
            }
            
            if($to != null){
                $dtTo = new \Utils\Data(date("d/m/Y H:i:s", $to));
            } else {
                $dtTo = new \Utils\Data(date("d/m/Y H:i:s"));
            }
            
            if(strpos($symbol, "_") !== false){
                $symbol = str_replace("_", ":", $symbol);
            }

            $symbols = explode(":", $symbol);
            if (sizeof($symbols) < 2) {
                $symbols[1] = "BRL";
            }
            
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol(implode(":", $symbols));
            
            if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
                $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
            }

            if (!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0) {
                $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
            }

            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            
            $dates = Array();
            $t = Array();
            $s = "ok";
            $o = Array();
            $h = Array();
            $l = Array();
            $c = Array();
            $v = Array();
            
            $precoAberturaSerie = $ordemExecutadaRn->getLasPriceUDFChartOHLC($dtFrom, $paridade);
            
            $result = $ordemExecutadaRn->getUDFChartOHLC($dtFrom, $dtTo, $paridade);
            
            $i = 0;
            
            $httpResponse->addBody("from", $dtFrom->formatar("d/m/Y H:i:s"));
            $httpResponse->addBody("to", $dtTo->formatar("d/m/Y H:i:s"));
            
            $debug = Array();
            
            $debug[] = "{$dtFrom->timestamp()} <= {$dtFrom->timestamp()}";
            while($dtFrom->menorIgual($dtTo)) {
                
                $dtFromFinal = new \Utils\Data($dtFrom->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
                $this->calcularIntervalo($dtFromFinal, $resolution, "+");
                $dtFromFinal->subtrair(0, 0, 0, 0, 0, 1);
                
                $high = null;
                $low = null;
                $close = null;
                $open = (sizeof($c) > 0 ? $c[sizeof($c) -1] : $precoAberturaSerie);
                $volume = 0;
                
                $encontrado = false;
                foreach ($result as $d) {
                    
                    $dataExecucao = strtotime($d["data_execucao"]);
                    
                    if ($dataExecucao >= $dtFrom->timestamp() && $dataExecucao <= $dtFromFinal->timestamp()) {
                        $encontrado = true;
                        if ($open == null) {
                            $open = $d["o"];
                        }
                        
                        if ($low == null || $low > $d["l"]) {
                            $low = $d["l"];
                        }
                        
                        if ($high == null || $high < $d["h"]) {
                            $high = $d["h"];
                        }
                        
                        $close = $d["c"];
                        
                        $volume += $d["v"];
                    }
                }
                
                $barTime = $this->adjustCandleTime($dtFrom, $resolution);
                
                $dates[] = $barTime->formatar("Y-m-d") . "T".$barTime->formatar("H:i:s") . ".000Z";
                $t[] = $barTime->timestamp();
                
                
                if (!( $high + $low+ $close) > 0) {
                    $high = $open;
                    $low = $open;
                    $close = $open;
                }
                
                if ($open > $high) {
                    $high = $open;
                }
                
                if ($open < $low) {
                    $low = $open;
                }
                
                if($paridade->id == 28){
                    
                    $o[] = number_format($open, 13, ".", "");
                    $h[] = number_format($high, 13, ".", "");
                    $l[] = number_format($low, 13, ".", "");
                    $c[] = number_format($close, 13, ".", "");
                    $v[] = number_format($volume, 13, ".", "");
                    
                } else {
                    
                    $o[] = (double) number_format($open, $paridade->moedaTrade->casasDecimais, ".", "");
                    $h[] = (double) number_format($high, $paridade->moedaTrade->casasDecimais, ".", "");
                    $l[] = (double) number_format($low, $paridade->moedaTrade->casasDecimais, ".", "");
                    $c[] = (double) number_format($close, $paridade->moedaBook->casasDecimais, ".", "");
                    $v[] = (double) number_format($volume, $paridade->moedaBook->casasDecimais, ".", "");
                
                }
                
                
                
                $precoAberturaSerie = $close;
                
                //echo " {$dtFrom->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)} <br>";
                $this->calcularIntervalo($dtFrom, $resolution, "+");
                
                
                if ($i == sizeof($result)) {
                    break;
                }
                $i++;
                
                //$debug[] = "{$dtFrom->timestamp()} <= {$dtFrom->timestamp()}";
            }
            if (AMBIENTE == "desenvolvimento") {
                /*echo date("d/m/Y H:i:s", $t[0]) . "<br>";
                echo date("d/m/Y H:i:s", $t[sizeof($t)-1]) . "<br>";
                exit("ok");*/
            }
             
            $httpResponse->addBody("dates", $dates);
            $httpResponse->addBody("s", $s);
            $httpResponse->addBody("t", $t);
            $httpResponse->addBody("o", $o);
            $httpResponse->addBody("h", $h);
            $httpResponse->addBody("l", $l);
            $httpResponse->addBody("c", $c);
            $httpResponse->addBody("v", $v);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            //$httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    private function findPeriod($resolution) {
        $periodos = Array("S", "D", "W", "M", "Y");
        $p = "";
        foreach ($periodos as $periodo) {
            
            if (is_numeric(strpos(strtoupper($resolution), $periodo))) {
               
                $p = $periodo;
                
                break;
            }
        }
        
        return $p;
    }
    
    private function adjustCandleTime(\Utils\Data &$date, $resolution) {
        $p = $this->findPeriod($resolution);
        $time = str_replace($p, "", $resolution);
        
        if (empty($time)) {
            $time  = 1;
        }
        
        switch ($p) {
            case "S":
                return new \Utils\Data($date->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
            case "D":
                return new \Utils\Data($date->formatar("d/m/Y H") . ":00:00");
            case "W":
                return new \Utils\Data($date->formatar("d/m/Y") . " 00:00:00");
            case "M":
                return new \Utils\Data($date->formatar("d/m/Y") . " 00:00:00");
            case "Y":
                return new \Utils\Data($date->formatar("d/m/Y") . " 00:00:00");
            default:
                if ($time % 60 > 0) {
                    return new \Utils\Data($date->formatar("d/m/Y H:i") . ":00"); // gráfico de minutos
                } else {
                    return new \Utils\Data($date->formatar("d/m/Y H") . ":00:00"); // gráfico de horas
                }
        }
    }
    
    private function calcularIntervalo(\Utils\Data &$from, $resolution, $operacao = "+") {
        
        $p = $this->findPeriod($resolution);
        $resolution = str_replace($p, "", $resolution);
        
        if (empty($resolution)) {
            $resolution = 1;
        }
        
        $time = 0;
        
        $anos= $meses = $dia = $horas = $minutos = $segundos = 0;
        switch ($p) {
            case "S":
                $time = $resolution;
                break;
            case "D":
                $time = ($resolution * 24 * 60 * 60);
                break;
            case "W":
                $time = ($resolution * 7 * 24 * 60 * 60);
                break;
            case "M":
                $time = ($resolution * 30 * 24 * 60 * 60);
                break;
            case "Y":
                $time = ($resolution * 365 * 24 * 60 * 60);
                break;
            default:
                $time = ($resolution * 60);
        }
        //exit("{$anos}-{$meses}-{$dia} {$horas}:{$minutos}:{$segundos} <br>");
        if ($operacao == "-") { 
            //$from->subtrair($anos, $meses, $dia, $horas, $minutos, $segundos);
            
            $from->setTimestamp($from->timestamp() - $time);
        } else {
            
            $from->setTimestamp($from->timestamp() + $time);
            
            /*$horaAntes = $from->timestamp();
            $from->somar($anos, $meses, $dia, $horas, $minutos, $segundos);
            if ($horaAntes >= $from->timestamp()) {
                echo "{$horaAntes} >= {$from->timestamp()} <br>";
                // correção do horário de verão
                $horas++;
                
                $from->somar($anos, $meses, $dia, $horas, $minutos, $segundos);
                echo ("Somando: {$horas} h e {$minutos} m <br>");
            }*/
            
            
         }
    }
    
    
    public function symbols($params) {
        
        $httpResponse = new HttpResult();
        try {
            
            $symbol = \Utils\Get::get($params, "symbol", null);
            $symbols = explode(":", $symbol);
            
            $firstIndex = 0;
            if (strlen($symbols[0]) > 5) {
                if (!sizeof($symbols)  > 1 ) {
                    throw new \Exception("Dados inválidos", 401);
                }
                
                $firstIndex++;
            }
            $paridadeTrade = (isset($symbols[$firstIndex+1]) ? $symbols[$firstIndex+1] : "BRL");
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridade = $paridadeRn->getBySymbol("{$symbols[$firstIndex]}:{$paridadeTrade}");
            
            if (!empty($paridade->casasDecimaisMoedaTrade) && $paridade->casasDecimaisMoedaTrade > 0) {
                $paridade->moedaTrade->casasDecimais = $paridade->casasDecimaisMoedaTrade;
            }

            if (!empty($paridade->casasDecimaisMoedaBook) && $paridade->casasDecimaisMoedaBook > 0) {
                $paridade->moedaBook->casasDecimais = $paridade->casasDecimaisMoedaBook;
            }

            $firstIndex++;
            
            if ($paridade == null) {
                throw new \Exception("Moeda inválida", 401);
            }
            
            
            $name = $paridade->moedaBook->simbolo;
            $exchangeTrated = TITULO;
            $exchangeListed = TITULO;
            $timezone = self::TIMEZONE;
            $minmov = 100;
            $minmov2 = 0;
            $pointvalue = 1;
            $session = "24x7";
            $hasIntraday = true;
            $hasNoVolume = false;
            
            $hasDaily = true;
            $hasWeeklyAndMonthly = true;
            
            $description = $paridade->moedaBook->nome;
            $type = "FX";
            /*$suportedResolutions = Array(
                //"5", "15", "30", "60", "360", "720", "D", "7D", "M", "3M", "Y"  
                "24x7"
            );*/
            $intradayMultipliers =  ["5", "15", "30", "60", "360", "720"];
            
            $priceScale = pow(10, $paridade->moedaTrade->casasDecimais);
            
            $ticker = $paridade->symbol;
            
            $volumePrecision = $paridade->moedaTrade->casasDecimais - 3;
            
            $httpResponse->addBody("name", $name);
            $httpResponse->addBody("exchange-traded", $exchangeTrated);
            $httpResponse->addBody("exchange-listed", $exchangeListed);
            $httpResponse->addBody("timezone", $timezone);
            $httpResponse->addBody("minmov", $minmov);
            $httpResponse->addBody("minmov2", $minmov2);
            $httpResponse->addBody("pointvalue", $pointvalue);
            $httpResponse->addBody("session", $session);
            $httpResponse->addBody("has_intraday", $hasIntraday);
            $httpResponse->addBody("has_no_volume", $hasNoVolume);
            $httpResponse->addBody("has_daily", $hasDaily);
            $httpResponse->addBody("has_weekly_and_monthly", $hasWeeklyAndMonthly);
            $httpResponse->addBody("description", $description);
            $httpResponse->addBody("type", $type);
            //$httpResponse->addBody("supported_resolutions", $suportedResolutions);
            $httpResponse->addBody("pricescale", $priceScale);
            $httpResponse->addBody("ticker", $ticker);
            $httpResponse->addBody("intraday_multipliers", $intradayMultipliers);
            $httpResponse->addBody("volume_precision", $volumePrecision);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function config($params) {
        $httpResponse = new HttpResult();
        
        try {
            
            $exchanges = Array(
                Array(
                    "desc" => "",
                    "name" => TITULO,
                    "value" => "cointrade"
                )
            );
            $suportedResolutions = Array(
                "5", "15", "30", "60", "360", "720", "D", "7D", "M", "3M", "Y" 
            );
            
            $symbolsTypes = Array(
                Array(
                    "name" => "All Types",
                    "value" => "alltypes"
                ),
                Array(
                    "name" => "Index",
                    "value" => "index"
                ),
                Array(
                    "name" => "Stock",
                    "value" => "stock"
                )
            );
            
            
            $httpResponse->addBody("exchanges", $exchanges);
            $httpResponse->addBody("supported_resolutions", $suportedResolutions);
            
            $httpResponse->addBody("supports_group_requestd", false);
            $httpResponse->addBody("supports_marks", false);
            $httpResponse->addBody("supports_timescale_marks", false);
            $httpResponse->addBody("supports_search", true);
            $httpResponse->addBody("supports_time", true);
            $httpResponse->addBody("symbols_types", $symbolsTypes);
            
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function time() {
        $time = time();
        print ("{$time}");
    }
    
}
