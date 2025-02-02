<?php
/**
 * Métodos para gerenciamento de E-mails.
 */
namespace Utils;

use PHPMailer\PHPMailer\PHPMailer;
use Zend\Config\Reader\Xml;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part;
use Zend\Mime\Mime;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Utils\Arquivo;

/**
 * Contém os métodos de envio de email
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Mail {

    /**
     * Array com os dados da conexão
     * Ex: arquivo contido em configs/geral.xml
     * @var array 
     */
    public $conexao = null;
    
    /**
     * Define o quem é o remetente
     * @var string 
     */
    public $fromName = null;

    /**
     * Define o conteúdo do email
     * @var string 
     */
    public $conteudo = null;

    /**
     * Define o assunto do email
     * @var string 
     */
    public $assunto = null;

    /**
     * Define se a lista de envio de emails
     * $array[] = array('email' => 'joao@gmail.com', 'nome' => 'João da Silva');
     * @var array 
     */
    public $listaEnvio = null;

    /**
     * Lista de arquivos de anexo com o caminho do arquivo
     * @var array
     */
    public $listaAnexos = null;

    /**
     * Construtor da classe
     *  
     * @param String $assunto Assunto do email
     * @param String $conteudo Conteúdo do email
     * @param String $listaEnvio Lista de Emails Ex: $listaEmail[] = array('email' => 'joao@gmail.com', 'nome' => 'João da Silva');
     * @param String $tipo Tipo de Email (text/html)
     * @param String $conexao Arquivo de conexão ao email
     * @param array $listaAnexos Lista de arquivos de anexo com o caminho do arquivo
     */
    public function __construct($fromName, $assunto, $conteudo, $listaEnvio, $listaAnexos = array()) {
        $this->fromName = $fromName;
        $this->assunto = $assunto;
        $this->conteudo = $conteudo;
        $this->listaEnvio = $listaEnvio;
        $this->listaAnexos = $listaAnexos;
    }

    /**
     * enviar() Envia o email
     */
    public function enviar() {
        //Conexão ao email
        $options = new SmtpOptions($this->conexao['conexao']);
        $transport = new Smtp();
        $transport->setOptions($options);

        //Geração do conteúdo do email
        $part = new Part($this->conteudo);
        $part->type = 'text/html';
        $part->disposition = Mime::DISPOSITION_INLINE;
        $part->encoding = Mime::ENCODING_BASE64;

        $parts = array();
        //Anexo do conteúdo principal
        $parts[] = $part;
        //Incluo a lista de anexos
//        foreach ($this->listaAnexos as $aux) {
//            $anexo = new Part(fopen($aux, 'r'));
//            $arquivo = new Arquivo($aux);
//            $anexo->type = $arquivo->tipo;
//            $anexo->filename = $arquivo->nome;
//            $anexo->disposition = Mime::DISPOSITION_ATTACHMENT;
//            $anexo->encoding = Mime::ENCODING_BASE64;
//            $parts[] = $anexo;
//        }

        //Adição do conteúdo 
        $mimeMessage = new MimeMessage();
        $mimeMessage->setParts($parts);

        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->setBody($mimeMessage);
        $message->setSubject($this->assunto);

        //Adição dos endereços de envio, resposta, etc.
        $message->setFrom($this->conexao['enderecoEnvio']['email'], $this->conexao['enderecoEnvio']['nome']);
        $message->addTo($this->conexao['enderecoEnvio']['email'], $this->conexao['enderecoEnvio']['nome']);
        foreach ($this->listaEnvio as $aux) {
            $message->addBcc($aux['email'], $aux['nome']);
        }

        $transport->send($message);
    }
    
    
    public function send () {
        try {
            // Inicia a classe PHPMailer
            $mail = new PHPMailer(true);

            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);

            // Define os dados do servidor e tipo de conexÃ£o
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

            $mail->IsSMTP(); // Define que a mensagem serÃ¡ SMTP
            $mail->SMTPDebug = 0; // Mostra as mensagens de erro
            $mail->Host = $configuracao->emailSmtp; // EndereÃ§o do servidor SMTP
            $mail->SMTPAuth = ($configuracao->emailSmtpAuth > 0); // Usa autenticaÃ§Ã£o SMTP? (opcional)
            $mail->Port = $configuracao->emailPorta;
            $mail->Username = $configuracao->emailUsuario; // UsuÃ¡rio do servidor SMTP
            $mail->SMTPSecure = "tls";
            $mail->Password = $_ENV["EnvPassEmail"]; // Senha do servidor SMTP

            // Define o remetente
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            //exit(print_r($mail));

            //$mail->From = $configuracao->emailUsuario; // Seu e-mail
             $mail->SetFrom($configuracao->emailUsuario, $this->fromName);
            //$mail->FromName = $configuracao->emailNome; // Seu nome
            // Define os destinatÃ¡rio(s)$
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
             
            foreach ($this->listaEnvio as $destinatario) {
                $mail->AddAddress($destinatario["email"], $destinatario["nome"]);
            }
            
            //$mail->AddCC('ciclano@site.net', 'Ciclano'); // Copia
            //$mail->AddBCC('fulano@dominio.com.br', 'Fulano da Silva'); // CÃ³pia Oculta
            // Define os dados tÃ©cnicos da Mensagem
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

            $mail->IsHTML(true); // Define que o e-mail serÃ¡ enviado como HTML

            $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
            // Define a mensagem (Texto e Assunto)
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

            $mail->Subject = $this->assunto; // Assunto da mensagem
            $mail->Body = $this->conteudo;
            $mail->AltBody = $this->conteudo;

            //$mail->AltBody = "Este Ã© o corpo da mensagem de teste, em Texto Plano! \r\n <img src="http://i2.wp.com/blog.thiagobelem.net/wp-includes/images/smilies/icon_smile.gif?w=625" alt=":)" class="wp-smiley" width="15" height="15"> ";
            // Define os anexos (opcional)
            // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
            //$mail->AddAttachment("c:/temp/documento.pdf", "novo_nome.pdf");  // Insere um anexo
            // Envia o e-mail
            //exit(print_r($mail));
            $enviado = $mail->Send();
            if (!$enviado) {
                if (AMBIENTE == 'producao') {
                    throw new \Exception("Não foi possível enviar o email.");
                } else {
                    throw new \Exception("Não foi possível enviar o email." . htmlentities($mail->ErrorInfo));
                }
            }

            // Limpa os destinatÃ¡rios e os anexos
            $mail->ClearAllRecipients();
            $mail->ClearAttachments();
        } catch (\Exception $erro) {
            if (AMBIENTE == 'producao') {
                throw new \Exception("Não foi possível enviar o email.");
            } else {
                throw new \Exception($erro->getMessage());
            }
        }
    }

    /**
     * conectar() Conecta ao servidor de envio de emails
     * @param String/array $dadosConexao Conexão com o banco de dados em array ou do caminho do arquivo
     */
    private function conectar($dadosConexao = 'configs/geral.xml') {
        //Verifico se os dados da conexão é um array
        if (is_array($dadosConexao)) {
            $this->conexao = $dadosConexao;
        } else {
            //Senão, carrego os dados a partir da conexão contida no arquivo XML, senão for passado algum, será enviado o xml padrão
            $xml = new Xml();
            $this->conexao = $xml->fromFile($dadosConexao);
        }
    }

    /**
     * template() Retorna o conteúdo HTML padrão do email
     * @param String $conteudo Conteúdo do email
     * @param String $cabecalhoConteudo Cabeçalho do email, em branco não é impressa 
     * @param String $titulo Título do email, em branco não é impressa a div do título 
     * @param String $rodape Rodapé do email
     * 
     */
    public static function template($conteudo, $cabecalhoConteudo, $titulo = '', $nome = '') {
        ob_start();
        ?>
        <!--doctype html -->
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- NAME: 1 COLUMN -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidex</title>
</head>

<body>
<div class="">
    <div style="margin:0">
        <div style="background-color:#eeeeee;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:0;width:100%!important">
            <center>
                <table style="background-color:#eeeeee;border-collapse:collapse;border-spacing:0;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:0;width:100%!important"
                        width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                        <td style="background-color:#eeeeee;color:#555555;font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:14px;height:100%!important;line-height:1.4em;margin:0;padding:1em;text-align:auto;width:100%!important"
                            valign="top" align="center">
                            <table style="border-collapse:collapse;border-spacing:0" width="600" cellspacing="0"
                                    cellpadding="0" border="0">
                                <tbody>
                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left"
                                                    width="120" height="60">

                                                    <!-- <img src="https://navi.inf.br/storage/api/clients/3/liquidex.png" -->
                                                    <img src="https://navi.inf.br/storage/api/clients/52/b84f2934-a4c2-42b4-b717-3516f3f52cb4.png"
                                                         width="120"/>

                                                </td>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding-left:.8em;text-align:right">
                                                    <?php echo $titulo ?>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <div style="background-color:#ffffff;border:1px solid #e5e5e5">
                                            <table style="border-collapse:collapse;border-spacing:0"
                                                    width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;max-width:264px;min-width:264px;padding:24px 24px 24px 12px;text-align:left;vertical-align:top;color:#999">

                                                        Olá,
                                                        <strong style="color:#000000">
                                                        <?php echo $nome ?? 'Cliente'?>
                                                        </strong><br>


                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <div style="background-color:#ffffff;border:1px solid #e5e5e5">
                                            <table style="border-collapse:collapse;border-spacing:0"
                                                   width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>

                                                <tr>
                                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:24px;text-align:left"
                                                        valign="top" align="left">

                                                        <?php
                                                            if(is_array($conteudo)){
                                                                foreach ($conteudo as $key => $value) { ?>
                                                                    <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 2px;">
                                                                        <?php echo $key . ": "; ?>
                                                                        <span style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                                                            <?php echo $value . "<br>"; ?>
                                                                        </span>
                                                                    </p>
                                                                    <?php
                                                                }
                                                            } else { ?>
                                                                <span style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                                                    <?php echo($conteudo); ?>
                                                                </span>
                                                            <?php }
                                                        ?>

                                                        <!-- <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 2px;">
                                                            #IP:
                                                            <span style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                                                {{$info['ip']}}
                                                            </span>
                                                        </p> -->

                                                        <!-- <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 2px;">
                                                            {{ trans('mail.notify_login.access') }}:
                                                            <span style="font-size: 12px; color: #111; font-weight: bold; margin-bottom: 10px;">
                                                                {{$info['created']}}
                                                            </span>
                                                        </p> -->

                                                        <!-- <p style="text-transform: uppercase; font-size: 11px; letter-spacing: 1px; font-weight: bold; color: #B8B8B8; margin-bottom: 2px;">
                                                            {{ trans('mail.notify_login.source') }}:
                                                            <span style="font-size: 12px; color: #111; font-weight: bold;">
                                                                {{$info['agent']}}
                                                            </span>
                                                        </p> -->

                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;font-size:.4em;height:.4em;line-height:1em;text-align:left">
                                        &nbsp;
                                    </td>
                                </tr>

                                <!-- <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:left">
                                        <div style="background-color:#ffffff;border:1px solid #e5e5e5">
                                            <table style="border-collapse:collapse;border-spacing:0"
                                                   width="100%" cellspacing="0" cellpadding="0" border="0">
                                                <tbody>
                                                <tr>
                                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:12px;text-align:center"
                                                        valign="top" align="center">
                                                        <center>
                                                            <h4 style="margin-bottom: 10px; color: #000000">
                                                                {{ trans('mail.notify_login.info_2') }}
                                                            </h4>

                                                        </center>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr> -->

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:center"
                                        valign="top" align="center">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:24px;text-align:center"
                                                    valign="top" align="center">
                                                    <center>
                                                        <p>
                                                        Está é uma mensagem automática, por favor não responda. Se você não fez esta solicitação ou não é nosso cliente, entre em contato imediatamente com a Coinage.
                                                        </p>

                                                    </center>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;text-align:center"
                                        valign="top" align="center">
                                        <table style="border-collapse:collapse;border-spacing:0" width="100%"
                                               cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:'Helvetica Neue','Helvetica','Roboto','Calibri','Arial',sans-serif;padding:24px;text-align:center"
                                                    valign="top" align="center">
                                                    <center>

                                                        <br>

                                                        <strong>

                                                        </strong>
                                                        <br>
                                                        <div style="color: #A5A5A5; font-size: 10px; margin-bottom: 5px;">
                                                            Copyright <?php echo date('Y') ?> Coinage. Direitos
                                                            Reservados.
                                                        </div>
                                                    </center>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </center>

        </div>

    </div>
</div>
</body>
</html>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public static function template2($conteudo, $cabecalhoConteudo, $titulo = '', $rodape = '') {
        ob_start();
        ?>
        <!--doctype html -->
        <html lang="en">
            <head>
                <title><?php echo($titulo); ?></title>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            </head>

            <body bgcolor="#F4F4F4" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
                <?php
                if ($titulo != '') {
                    ?>
                    <!-- Email Title -->
                    <table width="650" cellspacing="0" cellpadding="0" border="0" align="center">
                        <tbody>
                            <tr>
                                <td height="75">
                                    <a style="font-family: Helvetica, arial, sans-serif; font-size: 18px; font-weight: bold; 
                                        text-decoration: none; color: #484848" href="#"><?php echo($titulo); ?></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php
                }
                ?>

                <!-- Top Border -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center">
                    <tbody>
                        <tr>
                            <td height="3">
                                <img width="650" height="3" style="display: block;" src="<?php echo(IMAGES); ?>mail/top.jpg">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Headline -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center" 
                       style="border-left: 1px solid #C5C5C5; border-right: 1px solid #C5C5C5;">
                    <tbody>
                        <tr>
                            <td bgcolor="#FFFFFF" align="center" height="100" style="font-family: Helvetica, arial, 
                                sans-serif; font-size: 22px; color: #191919; font-weight: bold; line-height: 29px;">
                                <?php echo($cabecalhoConteudo); ?><br>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Divider -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center" 
                       style="border-left: 1px solid #C5C5C5; border-right: 1px solid #C5C5C5;">
                    <tbody><tr>
                            <td bgcolor="#FFFFFF" height="20" valign="top">
                                <img width="648" height="1" style="display: block;" 
                                     src="<?php echo(IMAGES); ?>mail/divider.jpg">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Content -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center" 
                       style="border-left: 1px solid #C5C5C5; border-right: 1px solid #C5C5C5;">
                    <tbody>
                        <tr>
                            <td bgcolor="#FFFFFF" width="30">&nbsp;</td>
                            <td bgcolor="#FFFFFF" align="left" style="font-family: Helvetica, arial, sans-serif; 
                                font-size: 14px; color: #4F4F4F; line-height: 15px;">
                                <?php
                                    if(is_array($conteudo)){
                                        foreach ($conteudo as $key => $value){
                                            echo $key . ": " . $value . "<br>";
                                        }
                                    } else {
                                        echo($conteudo);
                                    }
                                 ?>
                            </td>
                            <td bgcolor="#FFFFFF" width="30">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty Placeholder -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center" 
                       style="border-left: 1px solid #C5C5C5; border-right: 1px solid #C5C5C5;">
                    <tbody>
                        <tr>
                            <td bgcolor="#FFFFFF" height="20" valign="top">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Bottom Border -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center">
                    <tbody>
                        <tr>
                            <td height="22">
                                <img width="650" height="22" style="display: block;" 
                                     src="<?php echo(IMAGES); ?>mail/bottom.jpg">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Padding -->
                <table width="650" cellspacing="0" cellpadding="0" border="0" align="center">
                    <tbody>
                        <tr>
                            <td width="650" bgcolor="#F4F4F4" height="10">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php
                if ($rodape != '') {
                    ?>
                    <!-- Footer -->
                    <table width="650" cellspacing="0" cellpadding="0" border="0" align="center">
                        <tbody>
                            <tr>
                                <td width="650" bgcolor="#F4F4F4" align="center">
                                    <span style="font-family: Helvetica, arial, sans-serif; font-size: 11px; color: #636363; 
                                          font-style: normal; line-height: 16px;">
                                        <?php echo($rodape); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php
                }
                ?>
            </body>
        </html>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

}
?>
