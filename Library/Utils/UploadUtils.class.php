<?php

namespace Utils;

class UploadUtils {
    
    public static function defineDayPath() {
        /*
        $path = "";
        $today = date("d/m/Y");
        
        for ($i = 0; $i < strlen($today); $i++) {
            $val = (intval(substr($today, $i, 1)));
            
            $val = $val + ($val ^ 2) * ($val + 2);
            
            $path .= "{$val}";
        }
        
        $dir = "uploads/";
        if (!file_exists($dir . $path)) {
            foreach (glob($dir."*", GLOB_ONLYDIR) as $pasta) {
                if($pasta != "uploads/public") {
                    if (is_dir($pasta) && $pasta != "uploads/".$path) {
                        \rename($pasta, "uploads/".$path);
                        break;
                    }
                }
            }
        }
        */
        return "48444042410130";
        //return $path;
    }
    
}