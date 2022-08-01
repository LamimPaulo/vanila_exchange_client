<?php
namespace Modules\acesso\Controllers;
class Sms {
    private $idioma = null;
    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("login", IDIOMA);

    if (!\Utils\Geral::isLogado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
        }

        if (\Utils\Geral::isAutenticado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }

    }

    public function auth($params) {
        
        try {
            $tipo = "";
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $tipo = $usuario->tipoAutenticacao;
            } else {
                $cliente = \Utils\Geral::getCliente();
                $tipo = $cliente->tipoAutenticacao;
            }
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("two_factor_auth", $params);
    }

    public function reenviar($params) {
        try {
            
            
            $tipo = "";
            
            $auth = new \Models\Modules\Cadastro\Auth();
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $auth->idUsuario = $usuario->id;
                $tipo = ($usuario->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL ? "E-mail" : $this->idioma->getText("celular"));
            } else {
                $cliente = \Utils\Geral::getCliente();
                $auth->idCliente = $cliente->id;
                $tipo = ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL ? "E-mail" : $this->idioma->getText("celular"));
            }
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth);
            
            $json["sucesso"] = true;
            $json["mensagem"] = str_replace("{var}", $tipo, $this->idioma->getText("tokenEnviadoTipo"));
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function validate($params) {
        try {
            
            $codigo = \Utils\Post::get($params, "codigo");
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($codigo);
            
            //\Models\Modules\Cadastro\NavegadorRn::registrarLog();
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}