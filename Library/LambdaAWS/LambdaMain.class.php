<?php

namespace LambdaAWS;
/**
 * Description of Dinamize
 *
 * @author willianchiquetto
 */
class LambdaMain {

    public static function enviar($urlFila, $json) {
        try {
                        
            $clientSQS = new \Aws\Sqs\SqsClient([
                'credentials' => [
                    'key' => getenv("EnvLambdaKey"),
                    'secret' => getenv("EnvLambdaSecret"),
                ],
                'region' => "us-east-1",
                'version' => '2012-11-05'
            ]);
            
            $paramsSQS = [
                'MessageDeduplicationId' => strtotime(date("Y-m-d H:i:s")),
                'MessageGroupId' => 'group1',
                'MessageBody' => $json,
                'QueueUrl' => $urlFila
            ];

            $result = $clientSQS->sendMessage($paramsSQS);

            if(AMBIENTE == "desenvolvimento"){
                //var_dump($result);
            }
            
            return true;
            
        } catch (\Aws\Exception\AwsException $e) {
            if(AMBIENTE == "desenvolvimento"){
                //var_dump($e->getMessage());
            }
            
            return false;
        }
    }

}
