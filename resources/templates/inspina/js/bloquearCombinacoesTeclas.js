
function bloquearCtrlJ(){   // Verificação das Teclas  
    var tecla=window.event.keyCode;   //Para controle da tecla pressionada  
    var ctrl=window.event.ctrlKey;    //Para controle da Tecla CTRL  

    if (ctrl && tecla===74){    //Evita teclar ctrl + j  
        event.keyCode=0;  
        event.returnValue=false;  
        return true;
    }  
    return false;
}  
    