<?php

namespace Modules\emails\Controllers;

class Temmplate {
    
    public function template($object) {
        
        require 'sengrid/vendor/autoload.php';
        //Necessário inserir a chave
        $apiKey = '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';
        $sg = new \SendGrid($apiKey);

        $Formulario = \Utils\Post::get($params, 'formualario');
        $NomeCliente = \Utils\Post::get($params, 'nomecliente');
        $Email = \Utils\Post::get($params, 'email');
        $Departamento = \Utils\Post::get($params, 'departamento');
        $Mensagem = \Utils\Post::get($params, 'mensagem');
        $Assunto = \Utils\Post::get($params, 'assunto');


        switch ($Formulario) {
    case 0:
        $from = new SendGrid\Email(null, "hostmaster@newc.com.br");
        $subject = $assunto;
        $to = new SendGrid\Email(null, $NomeCliente);
        $content = new SendGrid\Content("text/html", "teste1");
        $mail = new SendGrid\Mail($from, $subject, $to, $content);
        break;
    case 1:
        $from = new SendGrid\Email(null, "hostmaster@newc.com.br");
        $subject = $assunto;
        $to = new SendGrid\Email(null, $NomeCliente);
        $content = new SendGrid\Content("text/html", "teste2");
        $mail = new SendGrid\Mail($from, $subject, $to, $content);
        break;
    case 2:
        $from = new SendGrid\Email(null, "hostmaster@newc.com.br");
        $subject = $assunto;
        $to = new SendGrid\Email(null, $NomeCliente);
        $content = new SendGrid\Content("text/html", "teste3");
        $mail = new SendGrid\Mail($from, $subject, $to, $content);
        break;
}


        $response = $sg->client->mail()->send()->post($mail);
        echo $response->statusCode();
       // echo $response->headers();
        //echo $response->body();
    }
    
}