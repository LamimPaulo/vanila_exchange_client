<?php

namespace Modules\notificacoes\Controllers;

class Notificacoes {
    
    private $idioma = null;
    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("notificacoes", 'IDIOMA');
    }
    
    public function cadastro($params) {
        try {
            
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarios = $usuarioRn->conexao->listar("ativo > 0");
            
            ob_start();
            foreach ($usuarios as $usuario) {
            ?>
            <option value="<?php echo \Utils\Criptografia::encriptyPostId($usuario->id)?>">
                <?php echo $usuario->nome?>
            </option>
            <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["usuarios"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = true;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function buscarCliente($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", "");
            
            if (empty($filtro)) {
                throw new \Exception($this->idioma->getText("informeEmailCpfCliente"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $lista = $clienteRn->conexao->listar("LOWER(documento) LIKE LOWER('%{$filtro}%') OR LOWER(email) LIKE LOWER('%{$filtro}%')");
            
            if (sizeof($lista) > 0) {
                $cliente = $lista->current();
                $json["cliente"] = Array("id" => \Utils\Criptografia::encriptyPostId($cliente->id), "nome" => $cliente->nome, "email" => $cliente->email, "codigo" => sha1($cliente->id));
                
                ob_start();
                ?>
                <li class="list-group-item" id="cadastroGlobalNotificacoesCliente<?php echo sha1($cliente->id) ?>">
                    <div class="row">
                        <div class="col col-lg-6">
                            <input type="hidden" class="cadastroGlobalNotificacoesIdsClientes" value="<?php echo \Utils\Criptografia::encriptyPostId($cliente->id) ?>" />
                            <?php echo $cliente->nome ?>
                        </div>
                        <div class="col col-lg-5">
                            <?php echo $cliente->email ?>
                        </div>
                        <div class="col col-lg-1 text-center">
                            <button class="btn btn-danger" type="button" onclick="notificationRemoverCliente('<?php echo sha1($cliente->id)?>')"> 
                                <i class="fa fa-remove"></i>
                            </button>
                        </div>
                    </div>
                </li>
                <?php
                $html = ob_get_contents();
                ob_end_clean();
                $json["html"] = $html;
            } else {
                throw new \Exception($this->idioma->getText("clienteNaoLocalizado"));
            }
            
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            $idsUsuarios = \Utils\Post::getArray($params, "idsUsuarios");
            $idsClientes = \Utils\Post::getArray($params, "idsClientes");
            $clientes = \Utils\Post::get($params, "clientes", 0);
            $usuarios = \Utils\Post::get($params, "usuarios", 0);
            $tipo = \Utils\Post::get($params, "tipo", "warning");
            $mensagem = \Utils\Post::get($params, "mensagem", "");
            
            $notificacao = new \Models\Modules\Cadastro\Notificacao();
            $notificacao->html = $mensagem;
            $notificacao->tipo = $tipo;
           
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
            $notificacaoRn->salvarNotificacao($notificacao, $idsClientes, $idsUsuarios, ($clientes > 0), ($usuarios > 0));
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Notificação enviada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getNotificacoesNaoExibidas($params) {
        try {
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
            $lista = $notificacaoRn->getNotificacoesNaoExibidas();
            
            $l = Array();
            foreach ($lista as $notificacao) {
                if (\Utils\Geral::isCliente()) {
                    $notificacao->usuario->nome = "Atendimento {$notificacao->usuario->id}";
                }
                $l[] = $notificacao;
            }
            
            $json["notificacoes"] = $l;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function shown($params) {
        try {
            $notificacaoHasDestinatario = new \Models\Modules\Cadastro\NotificacaoHasDestinatario();
            
            $notificacaoHasDestinatario->idNotificacao = \Utils\Post::get($params, "codigo", 0);
            
            $notificacaoHasDestinatarioRn = new \Models\Modules\Cadastro\NotificacaoHasDestinatarioRn();
            $notificacaoHasDestinatarioRn->marcarComoExibida($notificacaoHasDestinatario);
            
            
            $json = true;
        } catch (\Exception $ex) {
            //print_r($ex);
            $json = false;
        }
        print json_encode($json);
    }
    
    public function alertas($params) {
        
        try {
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
            $alertas = $notificacaoRn->getAlertas();
            
            ob_start();
            
            if (sizeof($alertas) > 0) {
                foreach ($alertas as $notification) {
                    //$notification = new \Models\Modules\Cadastro\Notificacao();
                    ?>
                    <li class="notifications-alert-dropdown">
                        <div class="dropdown-messages-box">
                        <a href="#">
                            <div class="media-body">
                                <i class="fa fa-envelope fa-fw"></i><?php echo (strlen($notification->html) > 40 ? substr($notification->html, 0, 40) . "..." : $notification->html) ?>
                                <br>
                                <small class="text-muted"><?php echo (\Utils\Geral::isCliente() ? $this->idioma->getText("atendimento") . $notification->usuario->id : $notification->usuario->nome) ?></small>
                            </div>
                        </a>
                        </div>
                    </li>
                    <li class="divider notifications-alert-dropdown"></li>
                    <?php
                    
                }
            } else {
                ?>
                <li class="notifications-alert-dropdown">
                    <a href="#">
                        <div style="text-align: center;">
                            <?php echo $this->idioma->getText("nenhumaMensagem") ?>
                        </div>
                    </a>
                </li>
                <li class="divider notifications-alert-dropdown"></li>
                <?php
            }
            
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["qtd"] = sizeof($alertas);
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function all($params) {
        
        \Utils\Layout::view("notificacoes", $params);
    }
    
    
    public function filtrar($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", "");
            $page = \Utils\Post::get($params, "pagina", "1");
            
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
            $notificacoes = $notificacaoRn->filtrarNotificacoes($filtro, $page);
            
            ob_start();
            if (sizeof($notificacoes) > 0) {
                foreach ($notificacoes as $dados) {
                    $notificacao = $dados["notificacao"];
                    $lida = $dados["lida"];
                    
                    $cor = "";
                    switch ($notificacao->tipo) {
                        case "s":
                            $cor = "#00cf7a";
                            break;
                        case "w":
                            $cor = "#f8ac59";
                            break;
                        case "e":
                            $cor = "#ff2c58";
                            break;
                    }
                    
                    ?>
                
                <div class="panel panel-default <?php echo $notificacao->getTipo() ?> " >
                    <div class="panel-heading <?php echo ($lida ? "read" : "unread") ?> tr-page-<?php echo $page ?> tr-<?php echo sha1($notificacao->id) ?>" style="color: <?php echo $cor ?>"  >
                        <h5 class="panel-title">
                            
                            <input type="checkbox"  class="notification-check i-checks-page-<?php echo $page ?>" value="<?php echo \Utils\Criptografia::encriptyPostId($notificacao->id) ?>">
                            
                            <?php if (!$lida) { ?>
                            <i class="fa fa-envelope fa-2x icon-unread" style="margin: 0px 5px 0px 5px !important" ></i>
                            <?php } ?>
                            
                            
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $notificacao->id ?>" aria-expanded="false" class="collapsed">
                                <?php echo (\Utils\Geral::isCliente() ? "Atendimento {$notificacao->usuario->id}" : $notificacao->usuario->nome) ?>
                                
                                <span class="pull-right">
                                    <?php echo $notificacao->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                                </span>
                            </a>
                            
                            
                        </h5>
                    </div>
                    <div id="collapse<?php echo $notificacao->id ?>" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                        <div class="panel-body">
                            <?php echo $notificacao->html ?>
                        </div>
                    </div>
                </div>
                        
                
                    <?php
                }
                
                
                ?>
                <div class="panel panel-info no-more-messages">
                    <div class="panel-body text-center">
                        <button class="btn btn-info" type="button" onclick="nextPage();">
                            <i class="fa fa-refresh"></i> <?php echo $this->idioma->getText("carregarMensagens") ?>
                        </button>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="panel panel-danger no-more-messages">
                    <div class="panel-body text-center">
                       <?php echo $this->idioma->getText("naoHaMensagens") ?>
                    </div>
                </div>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function marcarComoLida($params) {
        
        try {
            $ids = \Utils\Post::getArray($params, "notificacoes", Array());
            
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
            $notificacaoRn->marcarComoLida($ids);
            
            $notificacoes = Array();
            foreach ($ids as $id) {
                $notificacoes[] = sha1(\Utils\Criptografia::decriptyPostId($id));
            }
            
            $json["notificacoes"] = $notificacoes;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}