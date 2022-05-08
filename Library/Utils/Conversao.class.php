<?php
/**
 * Classe para conversões
 */
namespace Utils;

/**
 * Contém os métodos e funções que realizam conversões no sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Conversao {

    /**
     * booleanToString() Recebe um boolean e retorna a string correspondente
     * @access public static
     * @param string $boolean Variável com o valor booleano (TRUE OR FALSE)
     * @return string String formatada com o valor booleano
     */
    public static function booleanToString($boolean) {
        return (($boolean) ? ('true') : ('false'));
    }

    /**
     * stringToBoolean() Recebe um boolean e retorna a string correspondente
     * @access public static
     * @param string $string Variável com o valor booleano em string (true or false)
     * @return boolean Valor boolean
     */
    public static function stringToBoolean($string) {
        return ((strtolower($string) == 'true' || strtolower($string) == 'on') ? (true) : (false));
    }

    public static function strToHex($string){
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }
    public static function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
}

?>
