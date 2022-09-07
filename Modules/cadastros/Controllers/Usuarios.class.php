<?php

namespace Modules\cadastros\Controllers;

use Utils\Layout;
class Usuarios {
    
    private  $codigoModulo = "cadastros";

    /**
     * Faço a validação das permissões de acesso no construtor
     * @param type $params
     */
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    /**
     * Função responsável pela exibição da view
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    function index($params) {
        try {
            $usuario = \Utils\Geral::getLogado();
            
            if (!$usuario->tipo == \Utils\Constantes::ADMINISTRADOR) {
                throw new \Exception("Você não tem permissão para acessar este módulo");
            }
            
            $rotinaHasAcaoRn = new \Models\Modules\Acesso\RotinaHasAcaoRn();
            $arvore = $rotinaHasAcaoRn->montarArvorePermissoes(new \Models\Modules\Cadastro\Usuario());
            
            $params["arvore"] = $arvore;
        } catch (\Exception  $ex) {
            $params["resultado"] = Array("tipo"=>"danger", "mensagem"=>  \Utils\Excecao::mensagem($ex));
        }
        Layout::view("index_usuarios", $params);
    }

    public function listar($params) {
        try {
            $usuarioLogado = \Utils\Geral::getLogado();
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            
           
            $filtro = \Utils\Post::get($params, "f", null);
            
            $where = Array();
            if ($usuarioLogado->tipo != \Utils\Constantes::ADMINISTRADOR) {
                $where[] = " id = {$usuarioLogado->id} ";
            }
            
            if (!empty($filtro)) {
                $where[] =  " (LOWER(email) LIKE LOWER('%{$filtro}%') OR LOWER(nome) LIKE LOWER('%{$filtro}%')) ";
            }
            $where = (sizeof($where) > 0 ? implode(" AND ", $where) : null);
            $usuarios = $usuarioRn->conexao->listar($where, "nome");
            
            $dados = $this->htmlListaUsuarios($usuarios);
            
            $json["ativos"] = $dados["ativos"];
            $json["inativos"] = $dados["inativos"];
            $json["total"] = $dados["ativos"]+$dados["inativos"];
            
            $json["html"] = $dados["html"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaUsuarios($listaUsuarios) {
        $usuarioLogado = \Utils\Geral::getLogado();
        
        $ativos = 0;
        $inativos = 0;
        ob_start();
        ?>
        <li class="list-group-item">
            <div class="row">
                <div class="col col-lg-4">
                    <strong>Nome</strong>
                </div>
                <div class="col col-lg-5">
                    <strong>Email</strong>
                </div>

                <div class="col col-lg-1 text-center">
                    <strong>Ativo?</strong>
                </div>


                <div class="col col-lg-1 text-center">
                    <strong>Alt. Status</strong>
                </div>

                <div class="col col-lg-1 text-center">
                    <strong>Editar</strong>
                </div>

                <!--
                <div class="col col-lg-1 text-center">
                    <strong>Excluir</strong>
                </div>
                -->
            </div>
        </li>
        <?php
        if (sizeof($listaUsuarios)) {
            foreach ($listaUsuarios as $usuario) {
                if ($usuario->ativo > 0) {
                    $ativos++;
                } else {
                    $inativos++;
                }
                $this->htmlUsuario($usuario);
            }
        } else {
            ?>
            <li class="list-group-item no-data-usuarios">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Nenhum registro cadastrado
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "ativos" => $ativos, "inativos" => $inativos);
    }
    
    
    private function htmlUsuario(\Models\Modules\Cadastro\Usuario $usuario) {
        $usuarioLogado = \Utils\Geral::getLogado();
        ?>
            
            <li class="list-group-item" id="usuario-<?php echo $usuario->id?>">
                <div class="row">
                    <div class="col col-lg-4">
                        <?php echo $usuario->nome ?>
                    </div>
                    <div class="col col-lg-5">
                        <?php echo $usuario->email ?>
                    </div>

                    <div class="col col-lg-1 text-center">
                        <?php echo ($usuario->ativo > 0 ? "Sim" : "Não") ?>
                    </div>

                    <div class="col col-lg-1 text-center">
                        
                        <?php if ($usuario->permiteAlteracao > 0) { ?>
                            <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::ALTERAR_STATUS)) { ?>
                        <button class="btn btn-circle btn-<?php echo ($usuario->ativo > 0 ? "danger" : "success")?>" onclick="alterarStatusUsuario('<?php echo \Utils\Criptografia::encriptyPostId($usuario->id) ?>')">
                                <i class="fa fa-<?php echo ($usuario->ativo > 0 ? "times" : "check")?>"></i>
                            </button>
                            <?php } ?>
                        <?php } ?>
                        
                    </div>

                    <div class="col col-lg-1 text-center">
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::EDITAR)) { ?>
                        <button class="btn btn-circle btn-primary" onclick="cadastroUsuario('<?php echo \Utils\Criptografia::encriptyPostId($usuario->id) ?>')">
                            <i class="fa fa-edit"></i>
                        </button>
                        <?php } ?>
                    </div>

                </div>
            </li>
            
        <?php
    }
    
    function cadastro($params) {
        try {

            $usuario = new \Models\Modules\Cadastro\Usuario();
            $usuario->id = \Utils\Post::getEncrypted($params, "idUsuario", 0);

            $json["permissoesRotinas"] = Array();
            $json["permissoesModulos"] = Array();

            if ($usuario->id > 0) {
                $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
                $usuarioRn->conexao->carregar($usuario);

                $permissaoUsuarioRn = new \Models\Modules\Acesso\PermissaoUsuarioRn();
                $resultRotinas = $permissaoUsuarioRn->conexao->listar("id_usuario = {$usuario->id}");
                foreach ($resultRotinas as $permissaoUsuario) {
                    $json["permissoesRotinas"][] = $permissaoUsuario->idRotinaHasAcao;
                }

                $permissaoModuloUsuarioRn = new \Models\Modules\Acesso\PermissaoModuloUsuarioRn();
                $resultModulos = $permissaoModuloUsuarioRn->conexao->listar("id_usuario = {$usuario->id}");
                foreach ($resultModulos as $permissaoModuloUsuario) {
                    $json["permissoesModulos"][] = $permissaoModuloUsuario->idModuloHasAcao;
                }

                $json["salvar"] = ($usuario->permiteAlteracao && \Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::EDITAR));
            } else {
                $usuario->tipo = \Utils\Constantes::VENDEDOR;
                $json["salvar"] = ($usuario->permiteAlteracao && \Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::CADASTRAR));
            }

            $usuario->dataExpiracao = ($usuario->dataExpiracao != null ? $usuario->dataExpiracao->formatar(\Utils\Data::FORMATO_PT_BR) : "");
            unset($usuario->observacoes);

            $json["usuario"] = $usuario;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    function salvar($params) {
        try {
            
            
            $usuario = new \Models\Modules\Cadastro\Usuario();
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            
            $usuario->id = \Utils\Post::get($params, "idUsuario", 0);
            
            if ($usuario->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para alterar dados do usuário");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para alterar dados do usuário");
                }
            }
            
            $usuario->nome = \Utils\Post::get($params, "nome", null);
            $usuario->matricula = \Utils\Post::get($params, "matricula", null);
            $usuario->email = \Utils\Post::get($params, "email", null);
            $usuario->tipo =  \Utils\Constantes::ADMINISTRADOR;
            $usuario->senha = \Utils\Post::get($params, "senha", null);
            $usuario->anotacoes = \Utils\Post::get($params, "anotacoes", null);
            $usuario->celular = \Utils\Post::get($params, "celular", null);
            $usuario->cidade = \Utils\Post::get($params, "cidade", null);
            $usuario->cpf = \Utils\Post::get($params, "cpf", null);
            $usuario->dataExpiracao = \Utils\Post::getData($params, "dataExpiracao", NULL, "23:59:59");
            $usuario->twoFactorAuth = 1;
            $usuario->tipoAutenticacao = \Utils\Post::get($params, "tipoAutenticacao", null);
            
            $confirmacaoSenha = \Utils\Post::get($params, "confirmacao", null);
            
            $permissoesRotinas = \Utils\Post::getArray($params, "permissoesRotina", Array());
            $permissoesModulos = \Utils\Post::getArray($params, "permissoesModulo", Array());
            $usuario->statusEnviarNotificacao = \Utils\Post::getBooleanAsInt($params, "statusEnviarNotificacao", NULL);
            
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::PERMISSOES)) {
                if ($usuario->id > 0) {
                    $permissaoModuloUsuarioRn = new \Models\Modules\Acesso\PermissaoModuloUsuarioRn();
                    $permissaoUsuarioRn  = new \Models\Modules\Acesso\PermissaoUsuarioRn();

                    $permissoesM = $permissaoModuloUsuarioRn->conexao->listar("id_usuario = {$usuario->id}");
                    $permissoesR = $permissaoUsuarioRn->conexao->listar("id_usuario = {$usuario->id}");

                    $permissoesRotinas = Array();
                    $permissoesModulos = Array();
                    foreach ($permissoesM as $permissaoModuloUsuario) {
                        $permissoesModulos[] = $permissaoModuloUsuario->idModuloHasAcao;
                    }

                    foreach ($permissoesR as $permissaoRotina) {
                        $permissoesRotinas[] = $permissaoRotina->idRotinaHasAcao;
                    }
                }
            }
            
            
            $usuarioRn->salvar($usuario, $confirmacaoSenha, $permissoesRotinas, $permissoesModulos);
            
            // atualizo os dados na sessão
            $usuarioLogado = \Utils\Geral::getLogado();
            if ($usuarioLogado != null && $usuario->id === $usuarioLogado->id) {
                $cliente = \Utils\Geral::getCliente();
                \Utils\Geral::setLogado($usuario, $cliente);
            }
    
            $json["usuario"] = $usuario;
            $json["sucesso"] = true;
            $json["mensagem"] = "Usuário salvo com sucesso!";
        } catch (\Exception  $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    function alterarStatusAtivo($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::ALTERAR_STATUS)) {
                throw new \Exception("Você não tem permissão para alterar o status do usuário");
            }
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuario = new \Models\Modules\Cadastro\Usuario();

            $usuario->id = \Utils\Post::get($params, "idUsuario", 0);

            $usuarioRn->alterarStatusAtivo($usuario);

            ob_start();
            $this->htmlUsuario($usuario);
            $html = ob_get_contents();
            ob_end_clean();
            $json["html"] = $html;

            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception  $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    function excluir($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTAUSUARIOS, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir o usuário");
            }
            
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuario = new \Models\Modules\Cadastro\Usuario();
           
            $usuario->id = \Utils\Post::get($params, "idUsuario", 0);
            $usuarioRn->excluir($usuario);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Usuário removido com sucesso!";
        } catch (\Exception  $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}