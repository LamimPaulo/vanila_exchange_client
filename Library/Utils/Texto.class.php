<?php
/**
 * Classe com métodos para manipulação de textos.
 */
namespace Utils;

/**
 * Classe de Texto
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Texto {

    /**
     * removerAcentos Recebe uma string, remove os acentos e retorna a string modificada
     *
     * @param string $valor Valor a ser aplicada a máscara
     * @return string String formatada com o valor da máscara
     */
    public static function removerAcentos($valor) {
        //Dia
        $from = str_split(utf8_decode('ÀÁÃÂÉÊÍÓÕÔÚÜÇàáãâéêíóõôúüç'));
        $to = str_split(utf8_decode('AAAAEEIOOOUUCaaaaeeiooouuc'));
        for ($i = 0, $sf = count($from), $st = count($to); $i < $sf && $i < $st; $i++) {
            $valor = str_replace(utf8_encode($from[$i]), $to[$i], $valor);
        }
        return $valor;
    }
    

}

?>