<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * Description of NotificacaoClienteOperacaoRn
 *
 * @author willianchiquetto
 */
class NotificacaoClienteOperacaoRn {
    
            /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoClienteOperacao());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoClienteOperacao());
        }
    }
    
    public function salvar(NotificacaoClienteOperacao &$notificacaoClienteOperacao) {
        
        if ($notificacaoClienteOperacao->id > 0) {
            $aux = new NotificacaoMoeda(Array("id" => $notificacaoClienteOperacao->id));
            $this->conexao->carregar($aux);
            $notificacaoClienteOperacao->id = $aux->id;
        }        
        
        if (empty($notificacaoClienteOperacao->idCliente)) {
            throw new \Exception("Cliente não identificado");
        }
        
        if (empty($notificacaoClienteOperacao->idNotificacaoComunicacao)) {
            throw new \Exception("Notificação de comunicação não identificada.");
        }
        
        if (empty($notificacaoClienteOperacao->idNotificacaoOperacao)) {
            throw new \Exception("Notificação de operação não identificada.");
        }
        
        if (empty($notificacaoClienteOperacao->ativo)) {
            throw new \Exception("Definir função para a operação.");
        }
        

        $this->conexao->salvar($notificacaoClienteOperacao);
    }
    
    public function alterarStatus(NotificacaoClienteOperacao &$notificacaoClienteOperacao) {
        try {
            $this->conexao->carregar($notificacaoClienteOperacao);
        } catch (\Exception $ex) {
            throw new \Exception("Notificação não localizada.");
        }        
        $notificacaoClienteOperacao->ativo = $notificacaoClienteOperacao->ativo == 1 ? 0 : 1;
        $this->conexao->update(Array("ativo" => $notificacaoClienteOperacao->ativo), Array("id" => $notificacaoClienteOperacao->id));
    } 
    
    public function getNotificacaoCliente($cliente, $idOperacao) {
        try {
            
            $query = "SELECT 
                    c.id,
                    c.id_cliente,
                    c.id_notificacao_comunicacao,
                    c.id_notificacao_operacao,
                    c.ativo,
                    nco.id_email_manager
                    FROM notificacao_cliente_operacao AS c
                    INNER JOIN notificacao_comunicacao AS nc ON c.id_notificacao_comunicacao = nc.id AND nc.ativo = 1
                    INNER JOIN notificacao_operacao AS nco ON c.id_notificacao_operacao = nco.id AND nco.ativo = 1
                    WHERE c.ativo = 1  AND c.id_cliente = {$cliente->id} AND c.id_notificacao_operacao = {$idOperacao};";
                   
            $result = $this->conexao->adapter->query($query)->execute();

            if(sizeof($result) > 0){
                foreach ($result as $dados){                    
                    $noticacao[] = $dados;
                }     
              
                return $noticacao;
            } else {
                return null;
            }
             exit(print_r($noticacao));
        } catch (\Exception $ex) {
            throw new \Exception("Notificação não localizada.");
        }    
    }

}
