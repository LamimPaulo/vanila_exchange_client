/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function currencymask(input, positions, decimals, decimalCharacter) {
    
    if (typeof positions === "undefined" || positions < 0) {
        decimals = 1;
    }
    
    if (typeof decimals === "undefined" || decimals < 0) {
        decimals = 0;
    }
    
    if (typeof decimalCharacter === "undefined" || decimalCharacter.length <= 0) {
        decimalCharacter = ",";
    }
    var accepted = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
    
    $(input).on("keydown", function (event) {
        var value = $(this).val() + event.key;
        var onlyNumbers = "";
        var i = 0;
        for (i = 0; i < value.length; i++) {
            var c = value.charAt(i);
            var cont = 0;
            for(cont = 0; cont < accepted.length; cont++) {
                if (c === accepted[cont]) {
                    onlyNumbers += c;
                    cont = accepted.length;
                }
            }
        }
        if (event.which === 8 || event.key === "Backspace") {
            onlyNumbers = onlyNumbers.substr(0, onlyNumbers.length - 1);
        }
        if (onlyNumbers.length <= decimals) {
            while(onlyNumbers.length < decimals) {
                onlyNumbers = "0"+onlyNumbers;
            }
            onlyNumbers = "0" + decimalCharacter + onlyNumbers;
        } else if (onlyNumbers.length <= (decimals + positions)) {
            onlyNumbers = (onlyNumbers.substring(0, (onlyNumbers.length - decimals)) + decimalCharacter + onlyNumbers.substring((onlyNumbers.length - decimals), onlyNumbers.length));
        } else {
            onlyNumbers = onlyNumbers.substring((onlyNumbers.length - decimals - positions), onlyNumbers.length);
            onlyNumbers = (onlyNumbers.substring(0, (onlyNumbers.length - decimals)) + decimalCharacter + onlyNumbers.substring((onlyNumbers.length - decimals), onlyNumbers.length));
        }
        var continuar = true;
        // Elimino zeros a esquerda que são desnecessários
        while(parseInt(onlyNumbers.charAt(0)) === 0 && continuar) {
            if (onlyNumbers.length > (decimals + 2)) {
                onlyNumbers = onlyNumbers.substring(1, onlyNumbers.length);
            } else{
                continuar = false;
            }
        }
        $(this).val(onlyNumbers);
        event.preventDefault();
        return false;
    });
    
}