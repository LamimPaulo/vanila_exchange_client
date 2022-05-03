<?php
/**
 * Métodos para gerenciamento de E-mails.
 */
namespace Utils;

use Utils\Arquivo;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
/**
 * Contém os métodos de envio de email
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Sms{


    /**
     * Define o assunto do email
     * @var string 
     */
    public $ddi = null;

    /**
     * @var string
     */
    public $numero = null;
    
    /**
     * @var string
     */
    public $mensagem = null;


    /**
     * Construtor da classe
     * @param String $ddi - DDI do cliente
     * @param String $numero - Telefone do cliente
     * @param String $mensagem -  Conteúdo do SMS - Limite de cacteres de: 100
     */
    public function __construct($ddi, $numero, $mensagem) {
        $this->ddi = $ddi;
        $this->numero = $numero;
        $this->mensagem = $mensagem;
    }

    public function enviar(){
        
        $SnSclient = new SnsClient([
	'credentials' => ['key' => 'AKIAUI4M4E5XXGDBOUYX', 'secret' => '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'],
        'region' => 'us-east-1',
        'version' => 'latest'
        ]);
        
        $celular = $this->ddi . $this->numero;
        $celularValido = str_replace(Array("(", ")", " ", "-", "+"), "", $celular);
 
        $phone = "+" . $celularValido;
        
        try {
            $result = $SnSclient->publish([
                'Message' => $this->mensagem,
                'PhoneNumber' => $phone,
            ]);
            
        } catch (AwsException $e) {

        }
    }
    
    
    

}
?>
