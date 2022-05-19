<?php

namespace Firebase;

class FirebaseSend {
    
    public static function send($titulo, $message, \Models\Modules\Cadastro\Usuario $usuario = null) {
        
        $firebase = new Firebase();
        $push = new FirebasePush();
        
        $usuarios = Array();
        
        if ($usuario == null) {
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usrs = $usuarioRn->conexao->listar("cod_app_advisor IS NOT NULL");
            foreach ($usrs as $usr) {
                $usuarios[] = $usr->codAppAdvisor;
            }
        } else {
            $usuarios[] = $usuario->codAppAdvisor;
        }
        
        if(sizeof($usuarios) > 0) {
        // optional payload
        $payload = array();
        $payload['team'] = 'India';
        $payload['score'] = '5.6';
 
        // push type - single user / topic
        $push_type = "admin";
         
        // whether to include to image or not
        $include_image = FALSE;
 
 
        $push->setTitle($titulo);
        $push->setMessage($message);
        
        if ($include_image) {
            $push->setImage('https://api.androidhive.info/images/minion.jpg');
        } else {
            $push->setImage('');
        }
        $push->setIsBackground(false);
        $push->setPayload($payload);
 
 
        $json = '';
        $response = '';
 
        if ($push_type == 'topic') {
            $json = $push->getPush();
            $response = $firebase->sendToTopic('global', $json);
        } else if ($push_type == 'individual') {
            $json = $push->getPush();
            $regId = "d0mmrL5rkc0:APA91bGZ6B9Xx5jqAJePGAifHnnNGNtU4W_v6gMg563NdPpm9fgmBMqkP-FH5gYldoNTnqiigveim4azBDBNL5gQZ4es1N9uATDk4eRfL0Pi5j9i1dv0crnTarVXTC4m_RRzqS2eUlYd";
            $response = $firebase->send($regId, $json);
        } else if ($push_type == "admin") {
            $json = $push->getPush();
            foreach ($usuarios as $admin){
                $response = $firebase->send($admin, $json); 
            }
        }
        
        }
    }
    
}