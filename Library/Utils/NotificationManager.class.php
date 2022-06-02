<?php
/**
 * Métodos para gerenciamento de E-mails.
 */
namespace Utils;


class NotificationManager{


    /**
     * Define o cliente
     * @var string 
     */
    public $cliente = null;

    /**
     * @var ID da operacao
     */
    public $operacao = null;
    
    /**
     * @var Mensagem para o cliente
     */
    public $mensagem = null;

    /**
     * @var Mensagem para o cliente
     */
    public $dados = null;

    /**
     * Construtor da classe
     * @param String $ddi - DDI do cliente
     * @param String $numero - Telefone do cliente
     * @param String $mensagem -  Conteúdo do SMS - Limite de cacteres de: 100
     */
    public function __construct($cliente, $operacao, $mensagem, $dados) {
        $this->cliente = $cliente;
        $this->operacao = $operacao;
        $this->mensagem = $mensagem;
        $this->dados = $dados;
    }

    public function enviar(){
        
        $notificaClienteRn = new \Models\Modules\Cadastro\NotificacaoClienteOperacaoRn();
        $notificacoes = $notificaClienteRn->getNotificacaoCliente($this->cliente, $this->operacao);
      
        if($notificacoes != null){
            foreach ($notificacoes as $notificacao) {
                
                switch ($notificacao["id_notificacao_comunicacao"]){
                    case 2:                 //($cliente, $idEmail = null, $mensagem = null, $variavel1 = null, $variavel2 = null, $variavel3 = null, $variavel4 = null, $variavel5 = null, $variavel6 = null, $variavel7 = null)
                        \Email\EmailMain::send($this->cliente, $notificacao["id_email_manager"], $this->mensagem, $this->dados);
                        break;
                    case 3:                        
                        $sms = new Sms($this->cliente->ddi, $this->cliente->celular, $this->mensagem);
                        $sms->enviar();
                        break;
                    case 4:
                        break;
                }
            }
        }
    }
}
?>
