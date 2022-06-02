<?php
/**
 * Classe para manipulação de datas
 */
namespace Utils;

use DateTime;
use DateInterval;

/**
 * Contém os métodos e funções que manipulam datas no sistema 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Data {

    const FORMATO_PT_BR = 'd/m/Y';
    const FORMATO_PT_BR_TIMESTAMP = 'd/m/Y H:i';
    const FORMATO_PT_BR_TIMESTAMP_LONGO = 'd/m/Y H:i:s';
    const FORMATO_ISO = 'Y-m-d';
    const FORMATO_ISO_TIMESTAMP = 'Y-m-d H:i';
    const FORMATO_ISO_TIMESTAMP_LONGO = 'Y-m-d H:i:s';
    const FORMATO_HORA = 'H:i';
    const FORMATO_HORA_TIMESTAMP = 'H:i:s';
    const FORMATO_EXTENSO = '%A, %d de %B de %Y';
    const CALCULO_SOMA = 0;
    const CALCULO_SUBTRACAO = 1;

    const DIAS_SEMANA_LONG = Array("Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado");
    const DIAS_SEMANA_SHORT = Array("Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb");

    public $data;

    /**
     * Construtor da classe 
     *  
     * @param string $data Valor da data
     * @param string $formato Formato da data para conversão e correta criação
     */
    function __construct($data = null, $formato = null) {
        //Chamo a função de criação de datas 
        $this->criarData($data, $formato);
    }

    /**
     * criarData() Cria a data 
     *
     * @param string $data Valor em string da data.
     * @param string $formato Formato da data para conversão e correta criação. 
     */
    public function criarData($data, $formato = null) {
        //Defino o formato de criação padrão como o formato iso caso não seja informado nenhum formato
        $formato = (is_null($formato)) ? ($this::FORMATO_ISO) : ($formato);
        //Tento criar a data, se não der certo, testo os formatos até conseguir
        $dataTemp = DateTime::createFromFormat($formato, $data);
        if ($dataTemp == '') {
            $dataTemp = DateTime::createFromFormat($this::FORMATO_ISO_TIMESTAMP, $data);
            if ($dataTemp == '') {
                $dataTemp = DateTime::createFromFormat($this::FORMATO_ISO_TIMESTAMP_LONGO, $data);
                if ($dataTemp == '') {
                    $dataTemp = DateTime::createFromFormat($this::FORMATO_PT_BR, $data);
                    if ($dataTemp == '') {
                        $dataTemp = DateTime::createFromFormat($this::FORMATO_PT_BR_TIMESTAMP, $data);
                        if ($dataTemp == '') {
                            $dataTemp = DateTime::createFromFormat($this::FORMATO_PT_BR_TIMESTAMP_LONGO, $data);
                            if ($dataTemp == '' && is_numeric($data)) {
                                $dataTemp = new DateTime ();
                                $dataTemp->setTimestamp($data);
                            }
                        }
                    }
                }
            }
        }
        $this->data = $dataTemp;
    }

    /**
     * formatar() Formata a data
     *
     * @param string $formato Formato desejado de retorno
     * @return string Data formatada com o formato passado
     */
    public function formatar($formato = null) {
        //Verifico se foi repassado algum formato, se não, o formato padrão será o PT_BR simples, exemplo: 24/10/2012
        $formato = (is_null($formato)) ? ($this::FORMATO_PT_BR) : ($formato);
        //Comparação feita pelo fato do datetime não retornar a data por extenso em pt-br, para isso é ncessário usar o
        //strftime
        if ($formato == $this::FORMATO_EXTENSO) {
            return strftime($this::FORMATO_EXTENSO, $this->timestamp());
        } else {
            return $this->data->format($formato);
        }
    }

    #created André format american default 25/07/2019
    public function getDateService($date) {

            if($date!=""){
                $date = str_replace("/", "-", $date);
                $date=date('Y-m-d H:i:s',strtotime($date) );
                
            }else{
                $date="";
            }

            return $date;


        }   


    /**
     * timestamp() Cria a data 
     *
     * @return string Retorna o timestamp da data
     */
    public function timestamp() {
        return $this->data->getTimestamp();
    }

    
    public function setTimestamp($timestamp) {
        $this->data->setTimestamp($timestamp);
    }
    
    
    /**
     * calcularPorString() Calcula a data por String
     *
     * @param const $tipo Define se vai ser uma SOMA ou uma SUBTRAÇÃO 
     * @param string $intervalo Valor em string do intervalo 
     * @link http://php.net/manual/en/class.dateinterval.php description
     * @see CALCULO_SOMA
     * @see CALCULO_SUBTRACAO
     */
    public function calcularPorString($tipo, $intervalo) {
        //Atribuo o intervalo
        $oDateInterval = new DateInterval($intervalo);
        //Inverto ou não de acordo com o parâmetro
        $oDateInterval->invert = $tipo;
        //Somo o intervalo
        $this->data->add($oDateInterval);
    }

    /**
     * calcular() Soma ou subtrair o valor dos parametros ao valor da data
     *
     * @param const $tipo Define se vai ser uma SOMA ou uma SUBTRAÇÃO
     * @param int $anos Quantidade em anos a ser calculada
     * @param int $meses Quantidade em meses a ser calculada
     * @param int $dias Quantidade em dias a ser calculada
     * @param int $horas Quantidade em horas a ser calculada
     * @param int $minutos Quantidade em minutos a ser calculada
     * @param int $segundos Quantidade em segundos a ser calculada
     * @see calcularPorString()
     * @see somar()
     * @see subtrair()
     * @see CALCULO_SOMA
     * @see CALCULO_SUBTRACAO
     */
    public function calcular($tipo, $anos = 0, $meses = 0, $dias = 0, $horas = 0, $minutos = 0, $segundos = 0) {
        //Gero as strings de cálculo
        $intervalDiasMesesAnos = "P{$anos}Y{$meses}M{$dias}D";
        $intervalHorasMinutosSegundos = "PT{$horas}H{$minutos}M{$segundos}S";
        //Executo o calculo 
        $this->calcularPorString($tipo, $intervalDiasMesesAnos);
        $this->calcularPorString($tipo, $intervalHorasMinutosSegundos);
    }

    /**
     * calcular() Soma o valor dos parametros ao valor da data
     *
     * @param int $anos Quantidade em anos a ser calculada
     * @param int $meses Quantidade em meses a ser calculada
     * @param int $dias Quantidade em dias a ser calculada
     * @param int $horas Quantidade em horas a ser calculada
     * @param int $minutos Quantidade em minutos a ser calculada
     * @param int $segundos Quantidade em segundos a ser calculada
     * @see calcular()
     * @see subtrair()
     */
    public function somar($anos = 0, $meses = 0, $dias = 0, $horas = 0, $minutos = 0, $segundos = 0) {
        $this->calcular($this::CALCULO_SOMA, $anos, $meses, $dias, $horas, $minutos, $segundos);
    }

    /**
     * subtrair() Subtrai o valor dos parametros ao valor da data
     *
     * @param int $anos Quantidade em anos a ser calculada
     * @param int $meses Quantidade em meses a ser calculada
     * @param int $dias Quantidade em dias a ser calculada
     * @param int $horas Quantidade em horas a ser calculada
     * @param int $minutos Quantidade em minutos a ser calculada
     * @param int $segundos Quantidade em segundos a ser calculada
     * @see calcular()
     * @see somar()
     */
    public function subtrair($anos = 0, $meses = 0, $dias = 0, $horas = 0, $minutos = 0, $segundos = 0) {
        $this->calcular($this::CALCULO_SUBTRACAO, $anos, $meses, $dias, $horas, $minutos, $segundos);
    }

    /**
     * diferenca() Calcula a diferença entre duas datas
     *
     * @param Data $dataComparacao Data que vai ser comparada 
     * @return DateInterval Diferença entre as datas
     */
    public function diferenca($dataComparacao) {
        $diferenca = $this->data->diff($dataComparacao->data);
        return $diferenca;
    }

    /**
     * maior() Define se a data passada como PARÃ‚METRO Ã© MAIOR do que o objeto data
     *
     * @param Data $dataComparacao Data que vai ser comparada
     * @return boolean
     */
    public function maior($dataComparacao) {
        $timestampLocal = $this->timestamp();
        $timestampDataComparacao = $dataComparacao->timestamp();
        //Calcula a diferenÃ§a, e define se a data repassada Ã© MAIOR do que o objeto data comparado
        return (($timestampLocal > $timestampDataComparacao) ? (true) : (false));
    }

    /**
     * menor() Define se a data passada como PARÃ‚METRO Ã© MENOR do que o objeto data
     *
     * @param Data $dataComparacao Data que vai ser comparada
     * @return boolean
     */
    public function menor($dataComparacao) {
        $timestampLocal = $this->timestamp();
        $timestampDataComparacao = $dataComparacao->timestamp();
//Calcula a diferenÃ§a, e define se a data repassada Ã© menor do que o objeto data comparado
        return (($timestampLocal < $timestampDataComparacao) ? (true) : (false));
    }

    /**
     * igual() Define se a data passada como PARÂMETRO é IGUAL ao objeto data
     *
     * @param Data $dataComparacao Data que vai ser comparada 
     * @return boolean
     */
    public function igual($dataComparacao) {
        if ($this->menor($dataComparacao) == false && $this->maior($dataComparacao) == false) {
            return true;
        } else {
            return false;
        }
    }
    
    public function maiorIgual($dataComparacao) {
        $timestampLocal = $this->timestamp();
        $timestampDataComparacao = $dataComparacao->timestamp();
        return (($timestampLocal >= $timestampDataComparacao) ? (true) : (false));
    }

    public function menorIgual($dataComparacao) {
        $timestampLocal = $this->timestamp();
        $timestampDataComparacao = $dataComparacao->timestamp();
        return (($timestampLocal <= $timestampDataComparacao) ? (true) : (false));
    }

    public function getNomeMes($short = true) {
        $mes = "";
        switch ($this->formatar("m")) {
            case "01": ($short ? $mes="Jan" : $mes="Janeiro"); break;
            case "02": ($short ? $mes="Fev" : $mes="Fevereiro"); break;
            case "03": ($short ? $mes="Mar" : $mes="Março"); break;
            case "04": ($short ? $mes="Abr" : $mes="Abril"); break;
            case "05": ($short ? $mes="Mai" : $mes="Maio"); break;
            case "06": ($short ? $mes="Jun" : $mes="Junho"); break;
            case "07": ($short ? $mes="Jul" : $mes="Julho"); break;
            case "08": ($short ? $mes="Ago" : $mes="Agosto"); break;
            case "09": ($short ? $mes="Set" : $mes="Setembro"); break;
            case "10": ($short ? $mes="Out" : $mes="Outubro"); break;
            case "11": ($short ? $mes="Nov" : $mes="Novembro"); break;
            case "12": ($short ? $mes="Dez" : $mes="Dezembro"); break;
        }
        
        return $mes;
    }
    
    public function getDiaSemana($short = true) {
        return ($short ? self::DIAS_SEMANA_SHORT[$this->formatar("w")] : self::DIAS_SEMANA_LONG[$this->formatar("w")]);
    }
}

?>
