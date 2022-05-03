function formatSlug(s, keyCode) {
    var s2 = "";
    if (keyCode === 8 && s.length > 0) {
        s = s.substring(0, (s.length-1));
    } else {
        s += String.fromCharCode(keyCode);
    }
    s = s.replace("(", "");
    s = s.replace(")", "");
    s = s.replace("*", "");
    s = s.replace("-", "");
    s = s.replace("#", "");
    s = s.replace(">", "");
    s = s.replace("<", "");
    s = s.replace("", "");
    s = removeAcento(s);
    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (c === " ") {
            c = "_";
        }
        s2 += c;
    }
    return s2.toLowerCase();
}

function removeAcento(strToReplace) {
    str_acento = "áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÖÔÚÙÛÜÇ";
    str_sem_acento = "aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC";
    var nova = "";
    for (var i = 0; i < strToReplace.length; i++) {
        if (str_acento.indexOf(strToReplace.charAt(i)) != -1) {
            nova += str_sem_acento.substr(str_acento.search(strToReplace.substr(i, 1)), 1);
        } else {
            nova += strToReplace.substr(i, 1);
        }
    }
    return nova;
}
