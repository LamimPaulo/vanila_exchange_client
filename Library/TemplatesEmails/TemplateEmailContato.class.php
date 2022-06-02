<?php 
namespace TemplatesEmails;

/**
 * ContÃ©m os dados do arquivo.
 */

class TemplateEmailContato {
    
    private static $hostImagens = "";
    
    public function __construct() {
        if (AMBIENTE == "desenvolvimento") {
            $hostImagens = "http://fairsales.com.br/resources/images/";
        } else {
            $hostImagens = IMAGES;
        }
    }
    
    public static function solicitacaoContato ($nome, $email, $telefone, $mensagem) {
        ob_start();
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Contato Fair Sales</title>
                <!-- If you delete this tag, the sky will fall on your head -->
                <meta name="viewport" content="width=device-width" />

                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <link rel="stylesheet" type="text/css" href="<?php echo CSS ?>solicitacao_contato_email.css" />

            </head>

            <body bgcolor="#FFFFFF">

                <!-- HEADER -->
                <table class="head-wrap" bgcolor="#999999">
                    <tr>
                        <td></td>
                        <td class="header container">

                            <div class="content">
                                <table bgcolor="#999999">
                                    <tr>
                                        <td><img src="<?php echo IMAGES ?>logo-emails-fairsales.png" /></td>
                                        <td align="right"><h6 class="collapse">Fair Sales</h6></td>
                                    </tr>
                                </table>
                            </div>

                        </td>
                        <td></td>
                    </tr>
                </table><!-- /HEADER -->


                <!-- BODY -->
                <table class="body-wrap">
                    <tr>
                        <td></td>
                        <td class="container" bgcolor="#FFFFFF">

                            <div class="content">
                                <table>
                                    <tr>
                                        <td>

                                            <h3>Nova Solicitação de  Contato do Sistema Fair Sales</h3>
                                            <p class="lead">
                                                
                                                <strong>Nome do cliente:</strong> <?php echo $nome ?> <br>
                                                <strong>Email do cliente:</strong> <?php echo $email ?> <br>
                                                    <strong>Telefone do cliente:</strong> <?php echo $telefone ?> <br><br>
                                                <strong>Mensagem:</strong> <?php echo $mensagem ?> <br>
                                                
                                            </p>

                                            <!-- 
                                            <p><img src="http://placehold.it/600x300" /></p><!-- /hero -->

                                            <!-- 
                                            <p class="callout">
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt. <a href="#">Do it Now! &raquo;</a>
                                            </p>

                                            <h3>Title Ipsum <small>This is a note.</small></h3>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                            <a class="btn">Click Me!</a>
                                             -->
                                            <br/>
                                            <br/>							

                                            <!-- social & contact -->
                                            <table class="social" width="100%">
                                                <tr>
                                                    <td>

                                                        <!--- column 1 -->
                                                        <table align="left" class="column">
                                                            <tr>
                                                                <td>				
<!-- 
                                                                    <h5 class="">Curta nossa página:</h5>
                                                                    <p class="">
                                                                        <a href="https://www.facebook.com/fairsalesbrasil/?fref=ts" class="soc-btn fb">Facebook</a> 
                                                                        
                                                                        <a href="https://www.facebook.com/fairsalesbrasil/?fref=ts" class="soc-btn tw">Twitter</a> 
                                                                        <a href="#" class="soc-btn gp">Google+</a></p>
                                                                        -->

                                                                </td>
                                                            </tr>
                                                        </table><!-- /column 1 -->	

                                                        <!--- column 2 -->
                                                        <table align="left" class="column">
                                                            <tr>
                                                                <td>				
                                                                    <!--
                                                                    <h5 class="">Contact Info:</h5>												
                                                                    <p>Phone: <strong>(62) 9821 4275</strong><br/>
                                                                        Email: <strong><a href="emailto:rafael@ontechsistemas.com.br">rafael@ontechsistemas.com.br</a></strong><br/>
                                                                        Email: <strong><a href="emailto:vagnercarvalho.vfc@gmail.com">vagnercarvalho.vfc@gmail.com</a></strong>
                                                                    </p>
                                                                    -->
                                                                </td>
                                                            </tr>
                                                        </table><!-- /column 2 -->

                                                        <span class="clear"></span>	

                                                    </td>
                                                </tr>
                                            </table><!-- /social & contact -->


                                        </td>
                                    </tr>
                                </table>
                            </div>

                        </td>
                        <td></td>
                    </tr>
                </table><!-- /BODY -->

                <!-- FOOTER -->
                <table class="footer-wrap">
                    <tr>
                        <td></td>
                        <td class="container">

                            <!-- content -->
                            <div class="content">
                                <table>
                                    <tr>
                                        <td align="center">
                                            <p><!--
                                                <a href="#">Terms</a> |
                                                <a href="#">Privacy</a> |
                                                <a href="#"><unsubscribe>Unsubscribe</unsubscribe></a> -->
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div><!-- /content -->

                        </td>
                        <td></td>
                    </tr>
                </table><!-- /FOOTER -->

            </body>
        </html>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public static function respostaSolicitacaoContato() {
        
        
        
    }
    
    
}


?>
