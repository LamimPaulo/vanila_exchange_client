<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Utils;

/**
 * Description of Senha
 *
 * @author desenv03
 */
class Senha {

    public static function forca($senha) {
        $forca = 0;
        $condicoesFracasSomar = array('/[[:lower:]]+/', '/[[:upper:]]+/', '/[[:digit:]]+/');
        $condicoesFortesSomar = array('/[!#_-]+/');

        foreach ($condicoesFracasSomar as $aux) {
            if (preg_match($aux, $senha)) {
                $forca++;
            }
        }

        foreach ($condicoesFortesSomar as $aux) {
            if (preg_match($aux, $senha)) {
                $forca+= 2;
            }
        }

        if (strlen($senha) >= 8) {
            $forca++;
        }
        
        return $forca;
    }

    /**
     * gerar() Gera a senha a ser gravada e usada para comparação de senha na autenticação do sistema
     *
     * @param string $usuario Nome de usuario do login
     * @param string $senha Senha de usuário do login
     * @return string String com o valor gerado e criptografado
     */
    public static function gerar($usuario, $senha) {
        if ((trim($usuario) != '' && trim($senha) != '')) {
            //String gerada exclusivamente para a geração do valor criptografado
            $salt = "0634dacbf99afc9aeda625ad30924797e76c39b027bd21c62e61301553e4fa168a14bc73def7ed25af40518c7699c0920d22be6
            c499571b3845db93c46a62d98";
        }
        else{
            throw new \Exception("Usuário/Senha em branco.");
        }
        
        return Criptografia::sha512($senha . $salt . $usuario);
    }

    
    public static function gerarMobile($senha) {
        $idioma = new \Utils\PropertiesUtils("utils", IDIOMA);
        if (trim($senha) != '') {
            //String gerada exclusivamente para a geração do valor criptografado
            $salt = "adlnadnslanwifwqp8or7w8oo172853r284573yrowqihfwqbpi387234209-ekwpodjqpuq9wwq84pq4pq2p4ip230432jwmd23mmmi4r9032842-302-";
        }
        else{
            throw new \Exception($idioma->getText("senhaBranco"));
        }
        
        return Criptografia::sha512($senha . $salt);
    }
}

?>
