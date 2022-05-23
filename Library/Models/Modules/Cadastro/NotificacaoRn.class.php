<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Notificacao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class NotificacaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        $this->conexao = new GenericModel(\Dduo::conexao(), new Notificacao());
    }
    
    public function salvar(Notificacao &$notificacao) {
        
        if ($notificacao->id > 0) {
            $aux = new Notificacao(Array("id" => $notificacao->id));
            $this->conexao->carregar($aux);
            $notificacao->data = $aux->data;
            $notificacao->idUsuarioCriacao = $aux->idUsuarioCriacao;
        } else {
            $notificacao->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $usuarioLogado = \Utils\Geral::getLogado();
            if ($usuarioLogado != null) {
                $notificacao->idUsuarioCriacao = $usuarioLogado->id;
            } else {
                $notificacao->idUsuarioCriacao = 1;
            }
        }
        
        
        if (empty($notificacao->html)) {
            throw new \Exception($this->idioma->getText("necessarioConteudoMensagem"));
        }
        
        $tipos = Array("s", "e", "w");
        if (!in_array($notificacao->tipo, $tipos)) {
            throw new \Exception($this->idioma->getText("tipoNotfiInvalido"));
        }
        
        unset($notificacao->usuario);
        $this->conexao->salvar($notificacao);
    }
    
    public function salvarNotificacao(Notificacao &$notificacao, $idsClientes = Array(), $idsUsuarios = Array(), $paraTodosOsClientes = false, $paraTodosOsUsuarios = false) {
        try {
            $this->conexao->adapter->iniciar();
            
            if (!$paraTodosOsClientes) {
                if (!sizeof($idsClientes) > 0) {
                    //throw new \Exception("É necessário selecionar pelo menos um cliente");
                }
            }
           
            if (!$paraTodosOsUsuarios) {
                if (!sizeof($idsUsuarios) > 0) {
                    //throw new \Exception("É necessário selecionar pelo menos um usuário");
                }
            }
            
            $notificacao->clientes = ($paraTodosOsClientes ? 1 : 0);
            $notificacao->usuarios = ($paraTodosOsUsuarios ? 1 : 0);
            
            $this->salvar($notificacao);
            
            if (!$paraTodosOsClientes || !$paraTodosOsUsuarios) {
                $notificacaoHasDestinatarioRn = new NotificacaoHasDestinatarioRn($this->conexao->adapter);
                
                if (!$paraTodosOsClientes) {
                    foreach ($idsClientes as $idCliente) {
                        $notificacaoHasDestinatario = new NotificacaoHasDestinatario();
                        $notificacaoHasDestinatario->idCliente = (is_numeric($idCliente) ? $idCliente :  \Utils\Criptografia::decriptyPostId($idCliente));
                        $notificacaoHasDestinatario->idNotificacao = $notificacao->id;

                        $notificacaoHasDestinatarioRn->salvar($notificacaoHasDestinatario);
                    }
                }
                
                if (!$paraTodosOsUsuarios) {
                    foreach ($idsUsuarios as $idUsuario) {
                        $notificacaoHasDestinatario = new NotificacaoHasDestinatario();
                        $notificacaoHasDestinatario->idUsuario = (is_numeric($idUsuario) ? $idUsuario : \Utils\Criptografia::decriptyPostId($idUsuario));
                        $notificacaoHasDestinatario->idNotificacao = $notificacao->id;

                        $notificacaoHasDestinatarioRn->salvar($notificacaoHasDestinatario);
                    }
                }
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function carregar (Notificacao &$notificacao, $carregar = true, $carregarUsuario = true) {
        
        if ($carregar) {
            $this->conexao->carregar($notificacao);
        }
        
        if ($carregarUsuario && $notificacao->idUsuarioCriacao > 0) {
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $notificacao->usuario = new \Models\Modules\Cadastro\Usuario(Array("id" => $notificacao->idUsuarioCriacao));
            $usuarioRn->conexao->carregar($notificacao->usuario);
        }
        
    }
    
    
    public function listar($where, $order, $offset, $limit, $carregarUsuario = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $notificacao) {
            $this->carregar($notificacao, false, $carregarUsuario);
            $lista[] = $notificacao;
        }
        return $lista;
    }
    
    public function listarNotificacoesUsuarioAtual(\Utils\Data $dataInicial, \Utils\Data $dataFinal, array $idsUsuarios = Array()) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $cliente = \Utils\Geral::getCliente();
        
        $where = Array();
        if (sizeof($idsUsuarios) > 0) {
            $where[] = " n.id_usuario_criacao IN (". implode(",", $idsUsuarios).") ";
        }

        $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);

        $where[] = " n.data BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        
        if (\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
            $where[] = " (d.id_usuario = {$usuarioLogado->id} OR d.id_cliente = {$cliente->id}) ";
        } else if (\Utils\Geral::isUsuario()) {
            $where[] = " d.id_usuario = {$usuarioLogado->id} ";
        } else if (\Utils\Geral::isCliente()) {
            $where[] = " d.id_cliente = {$cliente->id} ";
        }
        
        $where = (sizeof($where) > 0 ? implode(" AND ", $where) : "");
        
        $query = " SELECT DISTINCT(n.*) "
                 ." FROM notificacoes n  "
                 ." INNER JOIN notificacoes_has_destinatarios d ON (n.id = d.id_notificacao)  "
                 ." WHERE "
                 ." {$where} "
                 ." ORDER BY n.data DESC;";
                 
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        
        foreach ($result as $dados) {
            $notificacao = new Notificacao($dados);
            $this->carregar($notificacao, false, true);
            $lista[] = $notificacao;
        }
        
        return $lista;
    }
    
    public function getAlertas() {
        $usuarioLogado = \Utils\Geral::getLogado();
        $cliente = \Utils\Geral::getCliente();
        
        
        if (\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
            $where[] = " (d.id_usuario = {$usuarioLogado->id} OR d.id_cliente = {$cliente->id}) ";
        } else if (\Utils\Geral::isUsuario()) {
            $where[] = " d.id_usuario = {$usuarioLogado->id} ";
        } else if (\Utils\Geral::isCliente()) {
            $where[] = " d.id_cliente = {$cliente->id} ";
        }
        
        $where[] = " v.data_visualizacao IS NULL ";
        
        $where = (sizeof($where) > 0 ? implode(" AND ", $where) : "");
        
        $query = " SELECT  "
                 . "DISTINCT(id)  AS distinction, "
                 . "n.* "
                 ." FROM notificacoes n  "
                 ." INNER JOIN notificacoes_has_destinatarios d ON (n.id = d.id_notificacao) "
                 ." LEFT JOIN notificacoes_has_visualizacoes v ON (d.id_notificacao  = v.id_notificacao AND (d.id_usuario = v.id_usuario OR d.id_cliente = v.id_cliente)) "
                 ." WHERE "
                 ." {$where} "
                 ." ORDER BY n.data DESC "
                 ." LIMIT 10;";
                 
                 
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        
        foreach ($result as $dados) {
            $notificacao = new Notificacao($dados);
            $this->carregar($notificacao, false, true);
            $lista[] = $notificacao;
        }
        
        return $lista;
    }
    
    public function getNotificacoesNaoExibidas() {
        $usuarioLogado = \Utils\Geral::getLogado();
        $cliente = \Utils\Geral::getCliente();
        
        $whereUsuario = "";
        
        $tipo = "";
        
        $dataCadastro = "";
        if (\Utils\Geral::isUsuario()) {
            $whereUsuario = " id_usuario = {$usuarioLogado->id} ";
            $tipo = "usuarios";
            
            $dataCadastro = $usuarioLogado->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        } else if (\Utils\Geral::isCliente()) {
            $whereUsuario = " id_cliente = {$cliente->id} ";
            $tipo = "clientes";
            $dataCadastro = $cliente->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        }
        
        //$where = (sizeof($where) > 0 ? implode(" AND ", $where) : "");
        
        $query = " SELECT "
                 . " DISTINCT(n.id) AS distinction, n.* "
                 . " FROM notificacoes n "
                 . " WHERE "
                 . " ( "
                 . " (n.{$tipo} < 1 AND (SELECT MIN(exibida) FROM notificacoes_has_destinatarios WHERE {$whereUsuario} AND id_notificacao = n.id) != 1) OR "
                 . " (n.{$tipo} > 0 AND (SELECT COUNT(*) FROM notificacoes_has_destinatarios WHERE {$whereUsuario} AND id_notificacao = n.id) <= 0) "
                 . " ) AND "
                 . " n.data >= '{$dataCadastro}' "
                 . " ORDER BY n.data DESC;";
        
        
                 
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        
        foreach ($result as $dados) {
            $notificacao = new Notificacao($dados);
            $this->carregar($notificacao, false, true);
            $lista[] = $notificacao;
        }
        
        return $lista;
    }
    
    public function excluir(Notificacao &$notificacao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $notificacaoHasDestinatarioRn = new NotificacaoHasDestinatarioRn();
            $notificacaoHasDestinatarioRn->conexao->delete("id_notificacao = {$notificacao->id}");
            
            $notificacaoHasVisualizacaoRn = new NotificacaoHasVisualizacaoRn();
            $notificacaoHasVisualizacaoRn->conexao->delete("id_notificacao = {$notificacao->id}");
            
            $this->conexao->excluir($notificacao);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    
    
    
    public function filtrarNotificacoes($filtro = null, $page = 1) {
        $usuarioLogado = \Utils\Geral::getLogado();
        $cliente = \Utils\Geral::getCliente();
        
        
        if (\Utils\Geral::isUsuario() && \Utils\Geral::isCliente()) {
            $where[] = " (d.id_usuario = {$usuarioLogado->id} OR d.id_cliente = {$cliente->id}) ";
        } else if (\Utils\Geral::isUsuario()) {
            $where[] = " d.id_usuario = {$usuarioLogado->id} ";
        } else if (\Utils\Geral::isCliente()) {
            $where[] = " d.id_cliente = {$cliente->id} ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(n.html) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        $where = (sizeof($where) > 0 ? implode(" AND ", $where) : "");
        
        $offset = (($page - 1) * 100);
        
        $query = " SELECT  "
                 . "DISTINCT(id)  AS distinction, "
                 . "n.*,"
                . " v.data_visualizacao "
                 ." FROM notificacoes n  "
                 ." INNER JOIN notificacoes_has_destinatarios d ON (n.id = d.id_notificacao) "
                 ." LEFT JOIN notificacoes_has_visualizacoes v ON (d.id_notificacao  = v.id_notificacao AND (d.id_usuario = v.id_usuario OR d.id_cliente = v.id_cliente)) "
                 ." WHERE "
                 ." {$where} "
                 ." ORDER BY n.data DESC "
                 ." LIMIT 100"
                 ." OFFSET {$offset}; ";
                 
                 
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        
        foreach ($result as $dados) {
            
            $notificacao = new Notificacao($dados);
            $this->carregar($notificacao, false, true);
            $lista[] = Array("notificacao"=> $notificacao, "lida" => ($dados["data_visualizacao"] != null));
        }
        
        
        
        return $lista;
    }
    
    
    
    public function marcarComoLida($notificacoes) {
        try {
            $this->conexao->adapter->iniciar();
            
            $usuarioLogado = \Utils\Geral::getLogado();
            $cliente = \Utils\Geral::getCliente();
            
            $notificacaoHasDestinatarioRn = new NotificacaoHasDestinatarioRn();
            $notificacaoHasVisualizacaoRn = new NotificacaoHasVisualizacaoRn();
            
            foreach ($notificacoes as $idNotificacao) {
                $notificacao = new Notificacao();
                $notificacao->id = \Utils\Criptografia::decriptyPostId($idNotificacao);
                
                if (\Utils\Geral::isUsuario()) {
                    
                    $result = $notificacaoHasDestinatarioRn->conexao->listar("id_notificacao = {$notificacao->id} AND id_usuario = {$usuarioLogado->id}", null, null, 1);
                    if (sizeof($result) > 0) {
                        $result = $notificacaoHasVisualizacaoRn->conexao->listar("id_notificacao = {$notificacao->id} AND id_usuario = {$usuarioLogado->id}", null, null, 1);
                        
                        if (!sizeof($result) > 0) {
                            $notificacaoHasVisualizacao = new NotificacaoHasVisualizacao();
                            $notificacaoHasVisualizacao->idCliente = null;
                            $notificacaoHasVisualizacao->idUsuario = $usuarioLogado->id;
                            $notificacaoHasVisualizacao->idNotificacao = $notificacao->id;
                            
                            $notificacaoHasVisualizacaoRn->salvar($notificacaoHasVisualizacao);
                        }
                    }
                }
                
                if (\Utils\Geral::isCliente()) {
                    
                    $result = $notificacaoHasDestinatarioRn->conexao->listar("id_notificacao = {$notificacao->id} AND id_cliente = {$cliente->id}", null, null, 1);
                    if (sizeof($result) > 0) {
                        $result = $notificacaoHasVisualizacaoRn->conexao->listar("id_notificacao = {$notificacao->id} AND id_cliente = {$cliente->id}", null, null, 1);
                        
                        if (!sizeof($result) > 0) {
                            $notificacaoHasVisualizacao = new NotificacaoHasVisualizacao();
                            $notificacaoHasVisualizacao->idCliente = $cliente->id;
                            $notificacaoHasVisualizacao->idUsuario = null;
                            $notificacaoHasVisualizacao->idNotificacao = $notificacao->id;
                            
                            $notificacaoHasVisualizacaoRn->salvar($notificacaoHasVisualizacao);
                        }
                    }
                }
                
                
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
}

?>