<?php

namespace PhpMain;


use PHPMailer\PHPMailer\PHPMailer;

class Smtp
{


    protected $config;

    public function __construct()
    {
        $this->config = [
            'host' => 'smtp.mandrillapp.com',
            'port' => 587,
            'user' => 'any username will work - try "navi" for example',
            'pass' => 'fjHTcoqqNAe45zJgqWkCQQ',
            'from' => [
                'mail' => 'no-reply@nav.inf.br',
                'name' => 'Nav inf'
            ]
        ];
    }

    public function sendMail($to = [])
    {
        try {


            if(empty($to)){
                throw new \Exception('Necessário informar destinatário');
            }

            $mail = new PHPMailer();
            //Server settings
            $mail->SMTPDebug = true;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->config['host'] ?? null;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->config['user'] ?? null;                     //SMTP username
            $mail->Password   = $this->config['pass'] ?? null;                               //SMTP password
            $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
            $mail->Port       = $this->config['port'] ?? 0;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($this->config['from']['mail'] ?? null, $this->config['from']['name'] ?? null);
            $mail->addAddress($to['mail'], $to['name']);     //Add a recipient
            $mail->addAddress('ellen@example.com');               //Name is optional
          /*  $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');*/

            //Attachments
            /*$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name*/

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();

        } catch (\Exception $e) {
            throw new \Exception(sprintf('Error %s', $e->getMessage()));
        }
    }


}
