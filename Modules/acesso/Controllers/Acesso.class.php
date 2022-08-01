<?php

namespace Modules\acesso\Controllers;

use Email\BoasVindas;
use Models\Modules\Cadastro\BrandRn;
use Models\Modules\Cadastro\UsuarioRn;
use Models\Modules\Cadastro\Usuario;
use Utils\Mail;
use Utils\Session;
use Utils\Layout;
use Utils\Geral;

class Acesso {

    public $idioma = null;

    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("login", IDIOMA);
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * 
     * Função responsável pela exibição da view
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    function index($params) {
        
        if (Geral::isLogado()) {
            Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD, 0);
        } else {
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            
            $token = \Utils\Post::get($params, "token", null);
            if (empty($token)) {
                $token = \Utils\Get::get($params, 0, null);
            }
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            if (!empty($token)) {
                try {
                    $tokenApiRN = new \Models\Modules\Cadastro\TokenApiRn();
                    $tokenApi = $tokenApiRN->validar($token);

                    $logado = $tokenApiRN->getUserByToken($token);
                    
                    
                    $auth = new \Models\Modules\Cadastro\Auth();
                    if ($logado instanceof \Models\Modules\Cadastro\Usuario) {

                        $auth->idUsuario = $logado->id;

                        Geral::setLogado($logado, null);
                    } else {
                        
                        $clienteRn->conexao->update(Array("email_confirmado" => 1), Array("id" => $logado->id));
                        
                        $auth->idCliente = $logado->id;
                        Geral::setLogado(null, $logado);
                        
                        $cliente->id = $auth->idCliente;
                        $clienteRn->conexao->carregar($cliente);
                        
                        $navegador = $this->verificarSessao($cliente);
                        
                         $idClienteEncripty = \Utils\Criptografia::encriptyPostId(base64_encode($cliente->id));
                         $idNavegadorEncripty = \Utils\Criptografia::encriptyPostId(base64_encode($navegador->id));
                            
                        $linkRevogar = URLBASE_CLIENT . \Utils\Rotas::R_REVOGAR . "?nnc={$idNavegadorEncripty}&cnc={$idClienteEncripty}";

                        $dataAtual = new \Utils\Data(date("d/m/Y H:i"));

                        $listaEnvio = Array(
                            Array("nome" => $cliente->nome, "email" => $cliente->email)
                        );

                        $conteudo = Array(
                            "Sistema" => $navegador->sistemaOperacional,
                            "Navegador" => $navegador->navegador,
                            "Data" => $dataAtual->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ,
                            "IP" => $navegador->ipUltimoAcesso,
                            //"id_session" => $navegador->idSession,
                            "Revogar" => $linkRevogar,
                            //"id_cliente" => $cliente->id,
                            //"notificar" => $cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL ? false : true,
                        );

                        $conteudo = Mail::template($conteudo, "Log Acesso", "Log Acesso");

                        $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Log Acesso", $conteudo, $listaEnvio);
                        $mail->send();
                    }
                    
                    Geral::setAutenticado(false);
                    $authRn = new \Models\Modules\Cadastro\AuthRn();
                    $authRn->salvar($auth);
                    
                    
                    Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_TWOFACTORAUTH);
                    

                    
                } catch (\Exception $e) {
                    Layout::view("login", $params);
                }
            } else {
                
                $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
                $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
                $configuracaoRn->conexao->carregar($configuracao);
                $params["configuracao"] = $configuracao;
                
                Layout::view("login", $params);
            }
            
        }
    }

    public function verificarSessao(\Models\Modules\Cadastro\Cliente $cliente) {

        $navegadorRn = new \Models\Modules\Cadastro\NavegadorRn();
        $navegador = $navegadorRn->conexao->listar(" id_cliente = {$cliente->id}", "id DESC", null, 1);
        $navegador = $navegador->current();

        $session_id_to_destroy = \Utils\Criptografia::decriptyPostId($navegador->idSession);

        if (session_id()) {
            session_commit();
        }

        session_start();
        session_regenerate_id(true);
        $current_session_id = session_id();
        session_commit();

        session_id($session_id_to_destroy);
        session_start();
        session_destroy();
        session_commit();

        session_id($current_session_id);
        session_start();
        session_commit();


        $navegador = \Models\Modules\Cadastro\NavegadorRn::registrarLog($cliente);
        
        return $navegador;

    }

    /**
     * Método responsável por logar o usuário no sistema
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    public function logar($params) {
        try {
            unset($_SESSION["login"]);
            
            $email = \Utils\Post::get($params, "email", NULL);
            $senha = \Utils\Post::get($params, "senha", NULL);
            
            $usuario = new Usuario(Array("email" => $email, "senha" => $senha));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->logar($usuario);
            Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        } catch (\Exception $e) {
            Layout::view('login', array('loginInvalido' => true, 'mensagem' => \Utils\Excecao::mensagem($e), '_POST' => $params['_POST']));
        }
    }

    /**
     * Método responsável por logar o usuário no sistema
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    public function logarapi($params) {
        try {
            unset($_SESSION["login"]);
            $email = \Utils\Post::get($params, "email", NULL);
            $senha = base64_decode(\Utils\Post::get($params, "senha", null));
            $googleCode = \Utils\Post::get($params, "code", null);
            if (!empty($googleCode)) {
                $validate = \GoogleAuth\Recaptcha::validarRecaptcha($googleCode);
                if (!$validate) {
                    throw new \Exception("Recaptcha inválido.");
                }
            } else {
                throw new \Exception("Recaptcha inválido.");
            }
            $usuario = new Usuario(Array("email" => $email, "senha" => $senha));

            $tokenApiRn = new \Models\Modules\Cadastro\TokenApiRn();
            $token = $tokenApiRn->login($usuario->email, $usuario->senha);

            $json["token"] = $token->token;
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        } 
        print json_encode($json);
    }

    /**
     * Método responsável pelo processo de recuperação de senha do cadastro de usuário
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    public function recuperar($params) {
        if (\Utils\Geral::isLogado() || \Utils\Geral::isAutenticado()) {
             Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        
        Layout::view("recuperar", $params);
    }

    /**
     * Método responsável pelo processo de recuperação de senha do cadastro de usuário
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    public function register($params) {
        if (\Utils\Geral::isLogado() || \Utils\Geral::isAutenticado()) {
             Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        Layout::view("cadastro", $params);
    }

    public function validarDadosRecuperacao($params) {
        try {
            
            $googleCode = \Utils\Post::get($params, "code", null);
            
            if (!empty($googleCode)) {
                $validate = \GoogleAuth\Recaptcha::validarRecaptcha($googleCode);
                if (!$validate) {
                    throw new \Exception("Recaptcha inválido.");
                }
            } else {
                throw new \Exception("Recaptcha inválido.");
            }
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            if ($configuracao->statusLoginSistema < 1) {
                throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"));
            }
            
            $email = \Utils\Post::getDoc($params, "email", null);

            if (!\Utils\Validacao::email($email)) {
                throw new \Exception($this->idioma->getText("emailInvalido"), 99);
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);

            if (!$cliente->id > 0) {
                throw new \Exception($this->idioma->getText("emailInvalido"));
            }
            
            if ($cliente->bloquearRecuperacaoSenha > 0) {
                
                if ($cliente->dataUltimaTentativaRecuperar != null ) {
                    $diff = $cliente->dataUltimaTentativaRecuperar->diferenca(new \Utils\Data(date("d/m/Y H:i:s")));
                    if (($diff->h + $diff->d + $diff->m + $diff->y) == 0 && $diff->i < 5) {
                        throw new \Exception($this->idioma->getText("login1"));
                    }
                }
            }
            
            if (intval($cliente->status) != 1) {
                throw new \Exception($this->idioma->getText("recuperacaoBloqueadaSenha"));
            }
            

            $auth = new \Models\Modules\Cadastro\Auth();
            $auth->idCliente = $cliente->id;
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth, null);
            
            
            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["placeholder"] = $this->idioma->getText("placeHolderInputTokEmail");
                $json["mensagem"] = $this->idioma->getText("placeHolderInputTokEmail");
            } 
            
            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS){
               $json["placeholder"] = $this->idioma->getText("placeHolderInputTokSMS");
               $json["mensagem"] = $this->idioma->getText("placeHolderInputTokSMS");
            }
            
            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                $json["placeholder"] = $this->idioma->getText("placeHolderInputTokGoogle");
                $json["mensagem"] = $this->idioma->getText("placeHolderInputTokGoogle");
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        print json_encode($json);
    }

    public function authRecover($params) {
        try {
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            if ($configuracao->statusLoginSistema < 1) {
                throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"));
            }
            
            $email = \Utils\Post::getDoc($params, "email", null);
            $token = \Utils\Post::get($params, "token", NULL);
            
            if (!\Utils\Validacao::email($email)) {
                throw new \Exception($this->idioma->getText("emailInvalido"), 99);
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente  = $clienteRn->getByEmail($email);
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token, $cliente);
            
            
            if ($cliente->emailConfirmado < 1) {
                //throw new \Exception("Você precisa confirmar o seu E-mail. Para isso acesse a página de login e cliquem em \"Confirmar e-mail\"");
            }
            if ($cliente->bloquearRecuperacaoSenha > 0) {
                
                if ($cliente->dataUltimaTentativaRecuperar != null ) {
                    $diff = $cliente->dataUltimaTentativaRecuperar->diferenca(new \Utils\Data(date("d/m/Y H:i:s")));
                    if (($diff->h + $diff->d + $diff->m + $diff->y) == 0 && $diff->i < 5) {
                        throw new \Exception($this->idioma->getText("login1"));
                    }
                }
            }
            
            $usuarioRn = new UsuarioRn();
            $usuarioRn->recuperar(new Usuario(Array("email" => $email)));
            

            $json["mensagem"] = $this->idioma->getText("chaveEnviadaJson");  
            $json["sucesso"] = true;  
        } catch (\Exception $e) {
            $json["sucesso"] = false; 
            $json["mensagem"] = $this->idioma->getText("falhaEnviaRecupeErr") . \Utils\Excecao::mensagem($e); 
        }
        
        print json_encode($json);
    }

    public function logout() {
        $logado = Geral::getLogado();
        Session::close();
        Geral::redirect(URLBASE_CLIENT);
    }

    public function newPassword($params) {
        try {
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            if ($configuracao->statusLoginSistema < 1) {
                throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"));
            }
            
            $msg = $this->idioma->getText("recuperacaoBloqueadaSenha");
            
            $hash = strtolower(\Utils\Post::get($params, "key", null));
            $email = \Utils\Post::getDoc($params, "email", null);
            
            if (empty($hash) || empty($email)) {
                throw new \Exception($msg);
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);
            
            
            if ($cliente->emailConfirmado < 1) {
                //throw new \Exception("Você precisa confirmar o seu E-mail. Para isso acesse a página de login e cliquem em \"Confirmar e-mail\"");
            }

            if ($cliente->bloquearRecuperacaoSenha > 0) {
                throw new \Exception($this->idioma->getText("recuperacaoBloqueadaSenha"));
            }

            if ($cliente == null) {
                throw new \Exception($msg);
            }

            if ($hash != $cliente->hashRecuperacaoSenha) {
                throw new \Exception($this->idioma->getText("chaveInvalidaErr"));
            }

            $dtAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            if ($cliente->validadeHashRecuperacaoSenha->menor($dtAtual)) {
                throw new \Exception($this->idioma->getText("dataExpiradaErr"));
            }

            $time = time();
            $seedNovaSenha = sha1("@Nova{$time}SenhaNewCash");

            $cliente->senha = substr($seedNovaSenha, 0, 10);
            $senha = sha1($cliente->senha.\Utils\Constantes::SEED_SENHA);

            $clienteRn->conexao->update(Array("senha"=> $senha, "bloquear_recuperacao_senha" => 0, "quantidade_tentativas_recuperacao" => 0, "hash_recuperacao_senha" => null, "data_update_senha" => date("Y-m-d H:i:s")), Array("id"=>$cliente->id));

            $listaEnvio = Array(
                Array("nome" => $cliente->nome, "email" => $cliente->email)
            );

            $conteudo = Array(
                "Senha" => $cliente->senha
            );

            $conteudo = Mail::template($conteudo, "Nova Senha", "Nova Senha");

            $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Nova Senha", $conteudo, $listaEnvio);
            $mail->send();
            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("senhaEnviadaMsg");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function confirmation($params) {
        try {
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        Layout::view("confirmacao_email", $params);
    }

    public function confirmarEmail($params) {
        try {
            $hash = \Utils\Post::get($params, "key", NULL);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $result = $clienteRn->conexao->listar("hash_validacao_email = '{$hash}'");
            
            if (sizeof($result) > 0) {
                $cliente = $result->current();
                
                if ($cliente->emailConfirmado > 0) {
                    throw new \Exception($this->idioma->getText("emailConfirmadoMsg"));
                }
                
                $dtAtual = new \Utils\Data(date("d/m/Y H:i:s"));
                if ($cliente->validadeHashValidacaoEmail->menor($dtAtual)) {
                    throw new \Exception("Data expirada");
                }
                
                $senhaTemp = substr(sha1($cliente->email . \Utils\Constantes::SEED_SENHA), 0, 10);
                $cliente->senha = sha1($senhaTemp.\Utils\Constantes::SEED_SENHA);
                
                $clienteRn->conexao->update(
                        Array(
                            "email_confirmado" => 1,
                            "senha" => $cliente->senha,
                            "hash_validacao_email" => null, 
                            "validade_hash_validacao_email" => null
                        ),
                        Array(
                            "id" => $cliente->id
                        )
                    );

                $cliente->senha = $senhaTemp;

                $listaEnvio = Array(
                    Array("nome" => $cliente->nome, "email" => $cliente->email)
                );

                $conteudo = Array(
                    "Mensagem" => "Conta ativada com sucesso."
                );

                $conteudo = Mail::template($conteudo, "Ativação Conta", "Ativação Conta");

                $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Ativação Conta", $conteudo, $listaEnvio);
                $mail->send();
                
                
            } else {
                throw new \Exception("Chave inválida!");
            } 
            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("emailConfirmadoSucessoMsg");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function revogarAcesso($params) {
        try {       
            $idCliente = \Utils\Get::getEncrypted($params, "cnc");
            //$idNavegador = \Utils\Get::getEncrypted($params, "nnc");
            
            //exit($idCliente . " --- " . $idNavegador);
            
            $idCliente = base64_decode($idCliente);
            //$idNavegador = base64_decode($idNavegador);
                        
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $navegador = new \Models\Modules\Cadastro\Navegador();
            $navegadorRn = new \Models\Modules\Cadastro\NavegadorRn();
            
            $cliente->id = $idCliente;             
            $clienteRn->conexao->carregar($cliente); 
            $cliente->status = \Utils\Constantes::CLIENTE_NEGADO;
            $clienteRn->alterarStatusCliente($cliente); 
            
            $ultimoAcesso = $navegadorRn->ultimoAcessoCliente($idCliente);
            $navegador->id = $ultimoAcesso["id"];
            $navegadorRn->conexao->carregar($navegador);
            $navegadorRn->revogarAcesso($navegador);      
            
            $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
            
            $observacoesCliente = new \Models\Modules\Cadastro\ObservacaoCliente();
            $observacoesCliente->idCliente = $cliente->id;
            $observacoesCliente->observacoes = "Conta revogada na data {$dataAtual->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)}"
            . " - IP: {$navegador->ipUltimoAcesso} - Navegador: {$navegador->navegador} - Localidade: {$navegador->localizacao}";
            $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();
            
            $observacaoClienteRn->salvar($observacoesCliente);

            $navegadorSessao = $navegadorRn->conexao->listar(" id_cliente = {$cliente->id}", "id DESC", null, 1);
            $navegadorSessao = $navegadorSessao->current();

            $session_id_to_destroy = \Utils\Criptografia::decriptyPostId($navegadorSessao->idSession);

            session_id($session_id_to_destroy);
            session_start();
            session_destroy();
            session_commit();

            $listaEnvio = Array(
                Array("nome" => $cliente->nome, "email" => $cliente->email)
            );

            $conteudo = Array(
                "Status" => "Conta bloqueada"
            );

            $conteudo = Mail::template($conteudo, "Revogar Conta", "Revogar Conta");

            $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Revogar Conta", $conteudo, $listaEnvio);
            $mail->send();
            
        } catch (\Exception $ex) {
           
        } 
        
       Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGOUT);
    }

    public function novoCadastro($params) {
        try { 
                        
            unset($_SESSION["login"]);
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (!in_array($method, Array(\Utils\Constantes::POST, \Utils\Constantes::GET))) {
                throw new \Exception("Método inválido.", 400);
            }
            
            switch ($method) {
                case \Utils\Constantes::POST:
                    $email = \Utils\Post::get($params, "email", null);
                    $token = \Utils\Post::getEncrypted($params, "cid", null);
                   
                    break;
                case \Utils\Constantes::GET:
                    $token = \Utils\Get::getEncrypted($params, "at", null);
                    
                    $dados = base64_decode($token);
                    $dados = explode("]", $dados);
                    
                    $token = \Utils\Criptografia::decriptyPostId($dados[0]);
                    $email = \Utils\SQLInjection::clean($dados[1]);
                    
                    break;

                default:
                    throw new \Exception($this->idioma->getText("emailInvalido"));
                    break;
            }
                        
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);
            
            $redirecionando = false;
            
            if($cliente->emailConfirmado == 1 || $cliente->status != 0 || $token == null || empty($token) || $email == null || empty($email)){
                $redirecionando = true;
                Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
            }    
            
            if (!$redirecionando) {
                $tokenApiRn = new \Models\Modules\Cadastro\TokenApiRn();
                $tokenCliente = $tokenApiRn->getClienteByToken($token, false);
            }  
            
            if(empty($token) || $tokenCliente->idCliente != $cliente->id || $token != $tokenCliente->token){
                $redirecionando = true;                
                Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
            }
            
            if (!$redirecionando) {

                $cliente->status = \Utils\Constantes::CLIENTE_ATIVO;

                $clienteRn->confirmarEmail($cliente);
                $clienteRn->alterarStatusCliente($cliente);

                Layout::view("validar_cadastro", $params);
            } else {
                Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
            }
        } catch (\Exception $ex) {
           Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
        } 
    }

    public function ativarContaApp($params) {
        try {
            $get = \Utils\Get::getEncrypted($params, "at", null);

            if(empty($get)){
                Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
            }

            $dados = base64_decode($get);
            $dados = explode("?", $dados);

            //exit(print_r($dados));
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $result = $clienteRn->conexao->listar(" api_key = '{$dados[0]}' AND data_cadastro = '{$dados[1]}' ");

            if(sizeof($result) > 0){
                $cliente = $result->current();
                $clienteRn->conexao->update(Array("email_confirmado" => 1), Array("id" => $cliente->id ));
                echo "Cliente ativado.";
            } else {
                echo "Verifique seu link de ativação.";
            }

        } catch (\Exception $ex) {
            Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
        }
    }
}