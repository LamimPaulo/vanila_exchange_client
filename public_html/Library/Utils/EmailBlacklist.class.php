<?php

namespace Utils;

class EmailBlacklist {

    public static function isBlacklist($email) {
        
        $validar = false;
        
        $blackList = array("qq.com", "nwytg", "ymail.com", "tuta.io", "yopmail", "EMAILRAPIDO.NET", "awdrt", "uacro", "lywenw", "uorak", "tuamaeaquelaursa",
            "tashjw", "qortu.com", "psk3n", "tuamaeaquelaursa", "dffwer", "v3n0m.work", "danwin1210", "ymail.com", "provlst.com", "tutanota.com", "gilfun",
            "roverbedf.ml", "whowlft.com", "hi2.in", "mailinator.com", "riseup.net", "xtsserv.com", "inbox.ru", "disroot.org", "box4mls.com", "mail2tor", "awdrt.net",
            "yandex.com", "@mail.com", "emailtown.club", "mailinator.com", "nwytg.net", "2emailock.com", "reqaxv.com", "burpcollaborator.net", "nwytg.net", "mailfence.com",
            "ukr.net", "mail.ru", "hubopss.com", "coalamails.com", "robo-trader.cc");

        
        foreach ($blackList as $blackDomain) {
            if (strpos(strtoupper($email), strtoupper($blackDomain))) {
                $validar = true;
                return $validar;
            }
        }
        
        return $validar;
    }
}
