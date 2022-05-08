<?php

namespace IPorto;

class IPorto {
    
    //private static $key_old = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImNkYTVlNjVhMzA4NzIzMTNjZjNlZjJhNjI2MTAwMmY2ZWQ0N2Q4MjhhMTZkMjJmZDAzOWIyMTY5YjliZGZkNzliOWIwNjdhMDQ5ZjI2YWQ2In0.eyJhdWQiOiIxIiwianRpIjoiY2RhNWU2NWEzMDg3MjMxM2NmM2VmMmE2MjYxMDAyZjZlZDQ3ZDgyOGExNmQyMmZkMDM5YjIxNjliOWJkZmQ3OWI5YjA2N2EwNDlmMjZhZDYiLCJpYXQiOjE1NzAxOTg2NTQsIm5iZiI6MTU3MDE5ODY1NCwiZXhwIjoxNjAxODIxMDU0LCJzdWIiOiIxMDgyIiwic2NvcGVzIjpbXX0.d6RMDrqSgdvmh1hFvqywj316rFevjSgG_xEeu6o5V08JBV-Z7r4d93T73mBCr86TP47UePoZ1lYB6jj14kTVgR6HVcNaLIZMpm8dY3gQvxRyuf39LZ_Q7p1FcguzRTt3wQyHJY-TCbQ89fUCfox-ntWIR908aKwZ_KGbJAJi880clm79QIRqd1WFq9RspzSv2dRGvPbVbxrDI-I0sw-XRNh358WB6vJARXCQxLm137iK3M3wX8teKb3-TnyHKIjRjU6Wfz1EVS1drag9-SHbzSRKHMVpv5N1HRAYnMC_0l5EvDwDOe2Rq6tTVUA1Z8ZOxfYMqlq93pCnrPZ7iW-rvkqk9K0pzXAR2mf7GJDbdScGG7tgKH9qLxEIYAqygVMY79VEwh5YWHvhHxALzz9ObrnrX0MM8VWkGhISMWXof3a00M552nolwjcW8DooqhOQmk0APFg9LkVxhUoIrZCwX9z-cTSQ86TSofYcQWRYjslTKsIaMbsGaxZrqys8A0HyN_RiRM-H_gffSowu2dhk6106VGK2w4nHAvoDb7ldGezgkStaGJX5PBa1T6d4UGmG6cnxwOcOgEZx0scwtI2FHvKxSICZlE5mJDTloAfjaK4YCG44p7s_NpF-VRnT0jggyGjqO0ob2NJ_AbhwrYV9hMBjYmk_05JUIx8K_egfYfc";
    private static $key =   "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImE0NjUyZTRiNWM1MTNjMWUwNDc1YTM0N2UwNzBkYzczM2NlNmE4ODI1ZTI2YzMzYzUxY2E3ODgwOWJhMTg5YzRhOGRhYzE4ZWUxMDExOTU3In0.eyJhdWQiOiIxIiwianRpIjoiYTQ2NTJlNGI1YzUxM2MxZTA0NzVhMzQ3ZTA3MGRjNzMzY2U2YTg4MjVlMjZjMzNjNTFjYTc4ODA5YmExODljNGE4ZGFjMThlZTEwMTE5NTciLCJpYXQiOjE2MDI2MjQ0OTYsIm5iZiI6MTYwMjYyNDQ5NiwiZXhwIjoxNjM0MTYwNDk2LCJzdWIiOiIxMDgyIiwic2NvcGVzIjpbXX0.4j91xCeJvkZ2bhH7UK3fhghL6Mb0M4H1c4pA6hmFOFuWUL1I8QIKsGK5rEwsBC6fAQFmL1KykZsclKBdWDXAK-t8WgzNvOBKzzbrr5iac5KYMp3G-Ht4JyfVxloNtCMMlXLni0pjIVubaPHsnHnJyEmURt9U6JkwW1y9dXnfWrXawoTnXrFMvIGZc9xlwuP8MhT0TVhAX9hD0y7jzhYSoc0ALKmnPBAvqhZt5XqNMUpI0TACnWEImyBaOxPVMAEObD4Mwh5yK2_H6dRxBPGM2Y5sORwI-Nxp6Qusr9gDYBxxROy7TcrDXGwXKsjVrKnEuZiFs2qRcjqcChoi2NqVqhk5BF9KXv2lJQ84vSYQyambkYDLNbwoW3MSd_WlxBL4NmicS8gC0G8nuZ8JYwPy27liE2EQAlb5tFEBuJCbgaiDgnMFz7cRQ8EUsViNMx4an8Wn9N5pde2H9J-ioPJzLXs0XVZkB8TgAcHEjIF-ggJVKmwRc7wyZiVtMecEih-zEzb4Beqonpqqn_aWFMJy9CoOKc4TKYfacHFos8xFuygccWfv2KzX2hMWNGNrU1EGoIboBDvqEHcSCuV5XWGSajESEvm1JY6gsu_NA8gI_ixzPLA_OsFxJNMTAT055acQcWNeAxZ9LRCBFY0DhelKT3zkBrvF45r5uGj7z-qykeo";

    
    public static function isValidEmail($email) {
        $key = self::$key;    
        
        $blackList = array("qq.com", "nwytg", "ymail.com", "tuta.io", "yopmail", "EMAILRAPIDO.NET", "awdrt", "uacro", "lywenw", "uorak", "tuamaeaquelaursa",
            "tashjw", "qortu.com", "psk3n", "tuamaeaquelaursa", "dffwer", "v3n0m.work", "danwin1210", "ymail.com", "provlst.com", "tutanota.com", "gilfun",
            "roverbedf.ml", "whowlft.com", "hi2.in", "mailinator.com", "riseup.net", "xtsserv.com", "inbox.ru", "disroot.org", "box4mls.com", "mail2tor", "awdrt.net",
            "yandex.com", "@mail.com", "emailtown.club", "mailinator.com", "nwytg.net", "2emailock.com", "reqaxv.com", "burpcollaborator.net", "nwytg.net", "mailfence.com",
            "ukr.net", "mail.ru", "hubopss.com", "coalamails.com", "robo-trader.cc");

        
        foreach ($blackList as $blackDomain) {
            if (strpos(strtoupper($email), strtoupper($blackDomain))) {
                return 5;
            }
        }
        
        $whiteList = array("outlook.com", "hotmail.com", "msn.com", "icloud.com", "live.com");
        $allowed = false;
        $domain = explode('@', $email);
     
        if (in_array($domain[1], $whiteList)) {
            $allowed = true;
        } else {
            $allowed = false;
        }

        if (!$allowed) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.iporto.com.br/api/token/generate",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json',
                  'Accept: application/json',
                  "Authorization: Bearer {$key}",
                ),
              ));


            $response = curl_exec($curl);
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                \Utils\Notificacao::notificar("Erro - Verificar API IPorto: {$response} ", true, true, null, true);
                return null;
            } else {
                $jsonEmail = "";
                $json = json_decode($response);
                
                if ($http_status == 200) {
                    $token = $json->success->token;
                    $aux = \IPorto\IPorto::validaEmail($email, $token);
                    $jsonEmail = json_decode($aux);
                    
                    if ($jsonEmail->data->is_valid == 1) {
                        return 1;
                    } else if ($jsonEmail->data->is_valid == 0) {
                        return 0;
                    } else {
                        return 2;
                    }
                } else {
                    \Utils\Notificacao::notificar("Verificar API IPorto: {$response}", true, true, null, true);
                    return 1; //Erro de API
                }
            }
        } else {            
            return 1;
        }
    }

    public static function validaEmail($email, $token) {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.iporto.com.br/api/panel/pkg/email-validation/http/check?email={$email}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$token}"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            return $response;
        }
    }

}
