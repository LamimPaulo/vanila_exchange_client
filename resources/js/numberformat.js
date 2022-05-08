/**
 * Este arquivo contém as funções para formatar de campos numéricos
 * @requires JQuery
 */


/**
 * 
 * @param {String} val valor para ser formatado
 * @param {int} casasDecimais número de casas decimais
 * @param {int} digitos número de dígitos
 * @returns {String} número formatado
 */
function currency(val, casasDecimais, digitos, keycode) {
    var number = "";
    //alert(keycode);
    // Elimino os caracters não numéricos
    for (i = 0; i < val.length; i++) {
        var c = val.charAt(i);
        if ($.isNumeric(c)) {
            number += c;
        }
    }
    if (keycode !== null && keycode === 8) {
        // Se a tecla pressionada foi a de backspace eu excluo o ultimo caractere
        number = number.substring(0, (number.length-1));
    } else if (keycode !== null) {
        if (number.length < (casasDecimais + digitos)) {
            // Se o número não atingiu o valor máximo eu converto o caractere em número e adiciono 
            if (keycode >= 48 && keycode<= 57) {
                number += String.fromCharCode(keycode);
            } else if (keycode >= 96 && keycode<= 105){
                number += String.fromCharCode(keycode - 48);
            }
        }
    }
    // Se a quantidade de dídigitos for insuficiente eu completo com zeros a esquerda
    while(number.length < (casasDecimais + 1)) {
        number = "0" + number;
    }
    var continuar = true;
    // Elimino zeros a esquerda que são desnecessários
    while(parseInt(number.charAt(0)) === 0 && continuar) {
        if (number.length > (casasDecimais + 1)) {
            number = number.substring(1, number.length);
        } else{
            continuar = false;
        }
    }
    
    
    
    // Adiciono a vírgula
    number = number.substring(0, (number.length - casasDecimais)) + "," + number.substring((number.length - casasDecimais), number.length);
    
    
    /*var a = number.split(",");
    
    var l = a[0].length;
    
    var b = "";
    if (l > 3) { 
        var i = 0;
        var count = 0;
        
        for (i=l; i > 0; i--) {
            var c = a[0].substring(i, i-1);
            b = c + b;
            
            count++;
            if ((count % 3 === 0) && (i > 1)) {
                b = "." + b;
                count = 0;
            }
        }
        number = b + "," + a[1];
    }*/
    
    return number;
}

function toFloat(number) {
    var n = "";
    var i = 0;
    for(i = 0; i < number.length; i++) {
        var a = number.charAt(i);
        if (a !== ".") {
            
            if (a === ",") {
                a= ".";
            }
            
            n += ""+a;
        }
    }
    return n;
}


function real(number) {
    
    var stringNumber = ""+number;
    var  a = stringNumber.split(".");
    var numeroFormatado = "";
    var cont = 0;
    for (realCount = (a[0].length - 1); realCount >= 0; realCount--) {
        var n = a[0].charAt(realCount);
        if (cont === 3) {
            numeroFormatado = "." + numeroFormatado;
            cont = 0;
        }
        numeroFormatado = n + numeroFormatado;
        cont++;
    }
    if (a[1] !==  'undefined' && a[1] !== null) {
        try {
            while (a[1].length < 2) {
                a[1] += "0";
            }
        } catch (e) {
            a[1] = "00";
        }
    } else {
        a[1] = "00";
    }
    numeroFormatado += "," + a[1];
    return numeroFormatado;
}
