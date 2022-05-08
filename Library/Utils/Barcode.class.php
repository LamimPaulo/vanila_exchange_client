<?php

namespace Utils;

class Barcode {
    
    public static function gerarCodigoBarras($numero) {
        $barcode = "";
        $fino = 1;
        $largo = 3;
        $altura = 50;

        $barcodes[0] = '00110';
        $barcodes[1] = '10001';
        $barcodes[2] = '01001';
        $barcodes[3] = '11000';
        $barcodes[4] = '00101';
        $barcodes[5] = '10100';
        $barcodes[6] = '01100';
        $barcodes[7] = '00011';
        $barcodes[8] = '10010';
        $barcodes[9] = '01010';

        for ($f1 = 9; $f1 >= 0; $f1--) {
            for ($f2 = 9; $f2 >= 0; $f2--) {
                $f = ($f1 * 10) + $f2;
                $texto = '';
                for ($i = 1; $i < 6; $i++) {
                    $texto .= substr($barcodes[$f1], ($i - 1), 1) . substr($barcodes[$f2], ($i - 1), 1);
                }
                $barcodes[$f] = $texto;
            }
        }

        $barcode .= '<img src="'.IMAGES.'barras/p.gif" width="' . $fino . '" height="' . $altura . '" border="0" />';
        $barcode .= '<img src="'.IMAGES.'barras/b.gif" width="' . $fino . '" height="' . $altura . '" border="0" />';
        $barcode .= '<img src="'.IMAGES.'barras/p.gif" width="' . $fino . '" height="' . $altura . '" border="0" />';
        $barcode .= '<img src="'.IMAGES.'barras/b.gif" width="' . $fino . '" height="' . $altura . '" border="0" />';

        $barcode .= '<img ';

        $texto = $numero;

        if ((strlen($texto) % 2) <> 0) {
            $texto = '0' . $texto;
        }

        while (strlen($texto) > 0) {
            $i = round(substr($texto, 0, 2));
            $texto = substr($texto, strlen($texto) - (strlen($texto) - 2), (strlen($texto) - 2));

            if (isset($barcodes[$i])) {
                $f = $barcodes[$i];
            }

            for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == '0') {
                    $f1 = $fino;
                } else {
                    $f1 = $largo;
                }

                $barcode .=  'src="'.IMAGES.'barras/p.gif" width="' . $f1 . '" height="' . $altura . '" border="0">';
                $barcode .=  '<img ';

                if (substr($f, $i, 1) == '0') {
                    $f2 = $fino;
                } else {
                    $f2 = $largo;
                }

                $barcode .=  'src="'.IMAGES.'barras/b.gif" width="' . $f2 . '" height="' . $altura . '" border="0">';
                $barcode .=  '<img ';
            }
        }
        $barcode .=  'src="'.IMAGES.'barras/p.gif" width="' . $largo . '" height="' . $altura . '" border="0" />';
        $barcode .=  '<img src="'.IMAGES.'barras/b.gif" width="' . $fino . '" height="' . $altura . '" border="0" />';
        $barcode .=  '<img src="'.IMAGES.'barras/p.gif" width="1" height="' . $altura . '" border="0" />';
        
        
        return $barcode;
    }

}