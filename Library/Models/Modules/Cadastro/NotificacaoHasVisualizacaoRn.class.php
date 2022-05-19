<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade NotificacaoUsuario
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class NotificacaoHasVisualizacaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoHasVisualizacao());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoHasVisualizacao());
        }
    }
    
    public function salvar(NotificacaoHasVisualizacao &$notificacaoHasVisualizacao) {
        
        if (!$notificacaoHasVisualizacao->idNotificacao > 0) {
            throw new \Exception("É necessário informar a identificação da notificação");
        }
        
        if (!$notificacaoHasVisualizacao->idUsuario && !$notificacaoHasVisualizacao->idCliente) {
            throw new \Exception("É necessário informar a identificação do destinatário");
        }
        
        if ($notificacaoHasVisualizacao->idCliente > 0) {
            $result = $this->conexao->select(Array("id_notificacao" => $notificacaoHasVisualizacao->idNotificacao, "id_cliente" => $notificacaoHasVisualizacao->idCliente));
            if (sizeof($result) > 0) {
                throw new \Exception("A notificação já existe para o cliente informado");
            }
        }
        
        if ($notificacaoHasVisualizacao->idUsuario > 0) {
            $result = $this->conexao->select(Array("id_notificacao" => $notificacaoHasVisualizacao->idNotificacao, "id_usuario" => $notificacaoHasVisualizacao->idUsuario));
            if (sizeof($result) > 0) {
                throw new \Exception("A notificação já existe para o usuário informado");
            }
        }
        
        $notificacaoHasVisualizacao->dataVisualizacao = new \Utils\Data(date("d/m/Y H:i:s"));
        
        $this->conexao->insert(Array(
            "id_usuario" => $notificacaoHasVisualizacao->idUsuario,
            "id_cliente" => $notificacaoHasVisualizacao->idCliente,
            "id_notificacao" => $notificacaoHasVisualizacao->idNotificacao,
            "data_visualizacao" => $notificacaoHasVisualizacao->dataVisualizacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
        ));
    }
   
    
}

?>