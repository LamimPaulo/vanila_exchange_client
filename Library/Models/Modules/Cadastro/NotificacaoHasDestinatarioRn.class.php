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
class NotificacaoHasDestinatarioRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoHasDestinatario());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoHasDestinatario());
        }
    }
    
    public function salvar(NotificacaoHasDestinatario &$notificacaoHasDestinatario) {
        
        if (!$notificacaoHasDestinatario->idNotificacao > 0) {
            throw new \Exception($this->idioma->getText("necessarioIndentNoti"));
        }
        
        if (!$notificacaoHasDestinatario->idUsuario && !$notificacaoHasDestinatario->idCliente) {
            throw new \Exception($this->idioma->getText("necessarioInformarIdentDest"));
        }
        
        if ($notificacaoHasDestinatario->idCliente > 0) {
            $result = $this->conexao->select(Array("id_notificacao" => $notificacaoHasDestinatario->idNotificacao, "id_cliente" => $notificacaoHasDestinatario->idCliente));
            if (sizeof($result) > 0) {
                throw new \Exception($this->idioma->getText("notfiJaExiste"));
            }
        }
        
        if ($notificacaoHasDestinatario->idUsuario > 0) {
            $result = $this->conexao->select(Array("id_notificacao" => $notificacaoHasDestinatario->idNotificacao, "id_usuario" => $notificacaoHasDestinatario->idUsuario));
            if (sizeof($result) > 0) {
                throw new \Exception($this->idioma->getText("notfiJaExisteUser"));
            }
        }
        
        $this->conexao->insert(Array(
            "id_usuario" => $notificacaoHasDestinatario->idUsuario,
            "id_cliente" => $notificacaoHasDestinatario->idCliente,
            "id_notificacao" => $notificacaoHasDestinatario->idNotificacao,
            "exibida" =>"0"
        ));
    }
   
    
    
    public function marcarComoExibida(NotificacaoHasDestinatario $notificacaoHasDestinatario) {
        $usuario = \Utils\Geral::getLogado();
        $cliente = \Utils\Geral::getCliente();
        
        if (!($notificacaoHasDestinatario->idNotificacao) > 0) {
            throw new \Exception($this->idioma->getText("notfiInvalidNaoEncont"));
        }
        
        
        
        $where = Array();
        if (\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
            $where[] = " (id_usuario = {$usuario->id} OR id_cliente = {$cliente->id}) ";
        } else if (\Utils\Geral::isUsuario()) {
            $where[] = " id_usuario = {$usuario->id} ";
        } else if (\Utils\Geral::isCliente()) {
            $where[] = " id_cliente = {$cliente->id} ";
        }
        
        $where[] = " id_notificacao = {$notificacaoHasDestinatario->idNotificacao} ";
        $where = implode(" AND ", $where);
        
        $result = $this->conexao->listar($where);
        
        
        if (!sizeof($result) > 0) {
            $nHD = new NotificacaoHasDestinatario();
            $nHD->exibida = 1;
            $nHD->idCliente = ($cliente != null ? $cliente->id : null);
            $nHD->idUsuario = ($usuario instanceof Usuario && $usuario != null ? $usuario->id : null);
            $nHD->idNotificacao = $notificacaoHasDestinatario->idNotificacao;
            
            $this->conexao->salvar($nHD);
        } else {
            $this->conexao->update(Array("exibida" => 1), $where);
        }
        
        
        
    }
    
}

?>