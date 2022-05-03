<?php
/**
 * Classe para máscaras
 */
namespace Utils;

/**
 * Contém os métodos de aplicação de máscara a strings
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Mascara {

    /**
     * mascara Recebe uma string e aplica a mascara repassada
     *
     * @param string $valor Valor a ser aplicada a máscara
     * @param string $mascara Formato da máscara
     * @return string String formatada com o valor da máscara
     */
    public function mascara($valor, $mascara) {
        $valorMascara = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mascara) - 1; $i++) {
            if ($mascara[$i] == '#') {
                if (isset($valor[$k]))
                    $valorMascara .= $valor[$k++];
            }
            else {
                if (isset($mascara[$i]))
                    $valorMascara .= $mascara[$i];
            }
        }
        return $valorMascara;
    }

    /**
     * cnpj Recebe um CNPJ e aplica a máscara pré-determinada
     *
     * @param string $cnpj Valor do CNPJ a ser aplicada a máscara
     * @return string CNPJ formatado
     */
    public function cnpj($cnpj) {
        return Mascara::mascara($cnpj, '##.###.###/####-##');
    }

    /**
     * cpf Recebe um CPF e aplica a máscara pré-determinada
     *
     * @param string $cpf Valor do CPF a ser aplicada a máscara
     * @return string CPF formatado
     */
    public function cpf($cpf) {
        return Mascara::mascara($cpf, '###.###.###-##');
    }

        public function cartao($cpf) {
        return Mascara::mascara($cartao, 'XXXX-XXXX-XXXX-####');
    }

    /**
     * cep Recebe um CEP e aplica a máscara pré-determinada
     *
     * @param string $cep Valor do CEP a ser aplicada a máscara
     * @return string CEP formatado
     */
    public function cep($cep) {
        return Mascara::mascara($cep, '##.###-###');
    }

}

?>
