<?php

namespace Modules\acesso\Controllers;

class FilesManager {
    
    private $privateDirs = Array(
        "selfies",
        "comprovantes_residencia",
        "documentos",
        "documentopj",
        "profile",
        "boletos",
        "comprovantes_boletos",
        "comprovantes_saques",
        "depositos"
    );

    public function file($params) {
        $file = \Utils\Get::getEncrypted($params, 0, "");
        if (!empty($file)) {
            $type = \Utils\DownloadManager::PUBLICO;
            $paths = explode("/", $file);

            if (sizeof($paths) > 1 && !in_array("img-public", $paths)) {
                if (in_array($paths[1], $this->privateDirs)) {
                    $type = \Utils\DownloadManager::PRIVADO;
                }
            }

            \Utils\DownloadManager::getFile($type, $file);
        } else {
            http_response_code(404);
        }
    }
}