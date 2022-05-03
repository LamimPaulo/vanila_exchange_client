<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class NotificacaoHasDestinatario {
   
    
    /**
     * 
     * @var Integer 
     */
    public $idNotificacao;
    
    
    /**
     * 
     * @var Integer 
     */
    public $idUsuario;
    
    
    /**
     * 
     * @var Integer 
     */
    public $idCliente;
    
    
    /**
     *
     * @var Integer 
     */
    public $exibida;
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idNotificacao = ((isset($dados ['id_notificacao'])) ? ($dados ['id_notificacao']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->exibida = ((isset($dados ['exibida'])) ? ($dados ['exibida']) : (null));
    }
    
    public function getTable() {
        return "notificacoes_has_destinatarios";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotificacaoHasDestinatario();
    }


}

?>