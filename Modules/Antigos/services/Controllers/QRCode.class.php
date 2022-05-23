<?php

namespace Modules\services\Controllers;

class QRCode {
    
    public function getQRCodeImg($content, $size = 3) {
        
        include_once './Library/phpqrcode/qrlib.php'; 
        
        \QRcode::png($content, false, QR_ECLEVEL_H, $size);
    }
    
}