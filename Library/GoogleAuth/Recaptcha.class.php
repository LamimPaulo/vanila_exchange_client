<?php
namespace GoogleAuth;
/**
 * PHP Class for handling Google Authenticator 2-factor authentication.
 *
 * @author Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @link http://www.phpgangsta.de/
 */
class Recaptcha
{
    
public static  function validarRecaptcha($code) {

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data1 = array('secret' => GOOGLESECRET, 'response' => $code);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    $status = json_decode($response, true);
    
    if ($status['success']) {
        return true;
    } else {
        return false;
    }
}

    
    
}