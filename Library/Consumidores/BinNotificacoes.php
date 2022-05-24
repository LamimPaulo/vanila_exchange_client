<?php
/**
 * @author Leandro
 * 21/02/22
 *
 **/

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/autoload_consumers.php';

class BinNotificacoes extends Consumer
{
    private $rabbit;
    private $phpmailler;
    private $config_mandril;

    public $queue_name = 'notificacoes';


    public function __construct()
    {
        parent::__construct();
        $loadingConfig = include 'config/configs.php';
        $this->config_mandril = $loadingConfig['mandrill'];
        $this->rabbit = new Consumer();
        $this->phpmailler = new PHPMailer();
    }

    private function sendMail(array $to = [], string $template = '', array $params = [])
    {
        //Server settings
        try {

            if (empty($to)) {
                throw new \Exception('Necessário informar destinatário');
            }


            $this->phpmailler->SMTPDebug = false;                      //Enable verbose debug output
            $this->phpmailler->isSMTP();                                            //Send using SMTP
            $this->phpmailler->Host = $this->config_mandril['host'] ?? null;                     //Set the SMTP server to send through
            $this->phpmailler->SMTPAuth = true;                                   //Enable SMTP authentication
            $this->phpmailler->Username = $this->config_mandril['user'] ?? null;                     //SMTP username
            $this->phpmailler->Password = $this->config_mandril['pass'] ?? null;                               //SMTP password
            $this->phpmailler->SMTPSecure = 'tls';            //Enable implicit TLS encryption
            $this->phpmailler->Port = $this->config_mandril['port'] ?? 0;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $this->phpmailler->setFrom($this->config_mandril['from']['mail'] ?? null, $this->config_mandril['from']['name'] ?? null);
            $this->phpmailler->addAddress($to['email'], $to['nome']);     //Add a recipient

            //Content
            $htmlTemplate = sprintf('Titulo: %s<br>Params: %s', $template, json_encode($params));
            $this->phpmailler->isHTML(true);                                  //Set email format to HTML
            $this->phpmailler->Subject = $template;
            $this->phpmailler->Body = $htmlTemplate;
            $this->phpmailler->AltBody = $htmlTemplate;

            $this->phpmailler->send();
            // Time para respeitar os limites de envio do provedor
            sleep(2);

            return [
                'sucesso' => true,
                'message' => sprintf( 'Message has been sent %s  %s', $to['nome'], $to['email'])
            ];


        } catch (\Exception $e) {
            throw new \Exception(sprintf('Error %s', $e->getMessage()));
        }


    }

    public function exec(array $body = [])
    {

        try {
            echo sprintf('-> %s' . PHP_EOL, json_encode($body));

            if (empty($body)) {
                echo 'Não existe Body' . PHP_EOL;
                return true;
            }

            $send = $this->sendMail([
                'nome' => $body['nome'],
                'email' => $body['email']
            ],
            $body['template_name'],
            $body['params'] ?? []);
            echo sprintf('Result -> %s' . PHP_EOL, json_encode($send));
            return  true;

        } catch (\Exception $e) {
            echo sprintf('Error %s'.PHP_EOL, $e->getMessage());
            return true;
        }


    }


}

$consumidor = new BinNotificacoes();
return $consumidor->pull($consumidor->queue_name);
