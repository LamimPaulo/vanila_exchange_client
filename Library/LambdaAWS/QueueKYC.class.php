<?php
namespace LambdaAWS;



/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class QueueKYC {

    public static function validarEmail($nome, $email, $referencia, $senha) {

        //Essa fila verifica o email do cliente no iPORTO e na SendGrid
        //Após verificação, devolve a resposta no endpoint ws/lbdmain.
        
        
        $urlQueue = 'https://sqs.us-east-1.amazonaws.com/293963835247/kycstart_prod.fifo'; //PROD
       

        $object = [
            "comando" => "user.cadastrar",
            "parametros" => [
                "nome" => $nome,
                "email" => $email,
                "senha" => $senha,
                "referencia" => $referencia,
                ]
        ];

        $result = LambdaMain::enviar($urlQueue, json_encode($object));

        return $result;
    }

    public static function sendLog( $queue_name, $priority,$params) {
         
        $urlQueue = 'https://sqs.us-east-1.amazonaws.com/293963835247/'.$queue_name.'_prod.fifo'; //PROD

        $result = LambdaMain::enviar($urlQueue, json_encode($params));

        return $result;
    }

    public static function sendQueue($exName, $params)
    {
        $result = LambdaMain::enviar($exName, json_encode($params));
        if ($result) {
            //Processado com sucesso
            return [
                'processado' => true,
                'msg' => 'Solicitação processada com sucesso.',
            ];
        } else {
            //Falha no processo
            return [
                'processado' => false,
                'msg' => 'Falha ao enviar sua solicitação'
            ];
        }
    }

}
