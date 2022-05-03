<?php

namespace Models\Modules\Model;

class CommomRegex {
    
    private static $_ALPHABET = Array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
    
    public static function getRandonAlias() {
        
        $i = random_int(0, 675);
        if ($i < 26) {
            return self::$_ALPHABET[$i];
        } else {
            $first = intval($i / 26);
            $second = intval($i%26);
            return self::$_ALPHABET[$first].self::$_ALPHABET[$second];
        }
    }
    
    public static function extract($docs) {
        $matches = Array();
        preg_match_all("/@(\w+?)(.*?)/U", $docs, $matches);
        return $matches;
    }
    
}