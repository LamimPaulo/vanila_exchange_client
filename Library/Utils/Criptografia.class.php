<?php
/**
 * Classes com os métodos de criptografia
 */
namespace Utils;

/**
 * Contém os métodos e funções criptográficas no sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Criptografia {

    
    
    /**
     * hash() Recebe o algoritmo e aplica a criptografia no valor passado
     *
     * @param string $algoritmo Algoritmo a ser usado para a criptografia
     * @param string $valor Valor a ser criptografado
     * @return string String com o valor já criptografado
     */
    public static function hash($algoritmo, $valor) {
        return hash($algoritmo, $valor);
    }

    /**
     * md5() Aplica o algoritmo md5 ao valor passado
     *
     * @param string $valor Valor a ser criptografado
     * @return string String com o valor já criptografado com md5
     */
    public function md5($valor) {
        return Criptografia::hash('md5', $valor);
    }

    /**
     * sha1() Aplica o algoritmo sha1 ao valor passado
     *
     * @param string $valor Valor a ser criptografado
     * @return string String com o valor já criptografado com sha1
     */
    public function sha1($valor) {
        return Criptografia::hash('sha1', $valor);
    }

    /**
     * sha512() Aplica o algoritmo sha512 ao valor passado
     *
     * @param string $valor Valor a ser criptografado
     * @return string String com o valor já criptografado com sha512
     */
    public static function sha512($valor) {
        return Criptografia::hash('sha512', $valor);
    }
    
    
    public static function encriptyPostId($value) {
        $seed = "1!2@3#";
        $base64 = base64_encode($seed.$value);
        $num = strlen($base64);
        $ultima = substr($base64, $num-1);
        
        $qtd=0;
        while ($ultima == "=") {
            $qtd++;
            $base64 = substr($base64, 0, $num-1);
            $num = strlen($base64);
            $ultima = substr($base64, $num-1);
        }
        
        if ($qtd > 0) {
            $base64.="s{$qtd}";
        } else {
            $base64.="n0";
        }
        
        /*$lastTwo = substr($base64, strlen($base64) -2, 2);
        if ($lastTwo == "==") {
            $base64 = substr($base64, 0, strlen($base64) -2) . "s";
        } else {
            $base64 .= "n";
        }
         * */
        $sobra = (strlen($base64) % 2);
        $half = ((strlen($base64) - $sobra ) / 2) + $sobra;
        $base64Half1 = substr($base64, 0, $half);
        $base64Half2 = substr($base64, $half, strlen($base64) - $half);
        return $base64Half2.$base64Half1;
    }
    
    
    public static function decriptyPostId($value, $validSqlException = true) {
        $seed = "1!2@3#";
        $sobra = (strlen($value) % 2);
        $half = ((strlen($value) - $sobra) / 2) + $sobra;   
        $base64Half1 = substr($value, 0, strlen($value) - $half);     
        $base64Half2 = substr($value, strlen($value) - $half, $half);
        $base64 = $base64Half2.$base64Half1;
        $last = substr($base64, strlen($base64)-1);
        $base64 = substr($base64, 0, strlen($base64)-2);
        
        while($last > 0) {
            $base64.="=";
            $last--;
        }
        $id = str_replace($seed, "", base64_decode($base64));
        return ($validSqlException ? SQLInjection::clean($id) : $id);
    }
    
    
    
    public static function token($seed = "") {
        if ($seed == null) {
            $seed = "". time();
        }
        
        $hash = "@#$%&BoLhAsEt{$seed}@#$&*ChEcKoUtNuMbEr";
        $milis = time() + rand(0, getrandmax());
        $_string = "{$milis}";
        $a = hash( 'crc32' , $hash );
        $b = hash( 'crc32' , sprintf( '%s%s' , md5( $_string ) , md5( $a ) ) );
        $c = sscanf( sprintf( '%s%s' , $a , $b ) , '%4s%4s%4s%4s' );
        $d = 1;
        for ( $i = 0 ; $i < 4 ; $i++ )
        for ( $j = 0 ; $j < 4 ; $d += pow( ord( $c[ $i ]{ $j } ) , $i ) , $j++ );
        $c[ 4 ] = $d;
        $token =  vsprintf('%s-%s-%s-%s-%05x' , $c );
        return $token;
    }
    
    
    public static function userid($seed = "") {
        if ($seed == null) {
            $seed = "". time();
        }
        
        $hash = "@#$%&BoLhAsEt{$seed}@#$&*ChEcKoUtNuMbEr";
        $milis = time() + rand(0, getrandmax());
        $_string = "{$milis}";
        $a = hash( 'crc32' , $hash );
        $b = hash( 'crc32' , sprintf( '%s%s' , md5( $_string ) , md5( $a ) ) );
        
        $id =  $a . "@client-".$b.".com";
        
        return $id;
    }
    
}

?>
