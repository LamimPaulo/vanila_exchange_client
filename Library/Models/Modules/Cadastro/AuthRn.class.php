<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
use Utils\Mail;

/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class AuthRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Auth());
        } else {
            $this->conexao = new GenericModel($adapter, new Auth());
        }
    }
    
    public function salvar(Auth &$auth, $tipo = null, $enviar = true) {
        
        $google = false;
        if ($auth->idCliente > 0 && $tipo == null) {
            $cliente = new Cliente(Array("id" => $auth->idCliente));

            try {
                $clienteRn = new ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
                if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                    $google = true;
                }
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteInvalido"));
            }
            
        }
        
        if (!$google) {
            try {
                $this->conexao->adapter->iniciar();
                if (!($auth->idUsuario > 0) && !($auth->idCliente > 0)) {
                    throw new \Exception($this->idioma->getText("eNecessarioInformarIdentificaoUsuario"));
                }


                $this->conexao->delete(($auth->idUsuario > 0 ? "id_usuario = {$auth->idUsuario}" : "id_cliente={$auth->idCliente}"));

                $auth->data = new \Utils\Data(date("d/m/Y H:i:s"));

                $num1 = rand(100, 999);
                $num2 = rand(100, 999);

                $codigo = "{$num1}{$num2}";

                $auth->codigo = $codigo;

                $celular = "";

                $nome = "";
                $email = "";
                $tipoAuth = "";

                if ($auth->idUsuario > 0) {
                    try {
                        $usuario = new Usuario(Array("id" => $auth->idUsuario));
                        $usuarioRn = new UsuarioRn($this->conexao->adapter);
                        $usuarioRn->conexao->carregar($usuario);

                        $celular = str_replace(Array("(", ")", " ", "-"), "", $usuario->celular);

                        $tipoAuth = $usuario->tipoAutenticacao;
                        $nome = $usuario->nome;
                        $email = $usuario->email;
                    } catch (\Exception $e) {
                        throw new \Exception($this->idioma->getText("usuarioInvalido"));
                    }

                } else {
                    try {
                        $cliente = new Cliente(Array("id" => $auth->idCliente));
                        $clienteRn = new ClienteRn($this->conexao->adapter);
                        $clienteRn->conexao->carregar($cliente);

                        $celular = str_replace(Array("(", ")", " ", "-"), "", $cliente->celular);

                        $tipoAuth = $cliente->tipoAutenticacao;
                        $nome = $cliente->nome;
                        $email = $cliente->email;
                    } catch(\Exception $ex) {
                        throw new \Exception($this->idioma->getText("clienteInvalido"));
                    }
                }

                $forcarEnvio = ConfiguracaoRn::getMeioAutenticacao();
                if ($forcarEnvio == "sms" || $forcarEnvio == "email") {
                    $tipoAuth = $forcarEnvio;
                } else {
                    if (!empty($tipo)) {
                        $tipoAuth = $tipo;
                    }
                }

                if ($tipoAuth == \Utils\Constantes::TIPO_AUTH_SMS && strlen($celular) != 10 && strlen($celular) != 11) {
                    throw new \Exception($this->idioma->getText("celularInvalido"));
                }

                \Utils\Constantes::SEED_RECUPERACAO_SENHA;

                $auth->codigo = sha1(str_replace("{token}", $auth->codigo,  \Utils\Constantes::SEED_AUTH));

                $dados = Array(
                    "codigo" => $auth->codigo,
                    "data" => $auth->data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                    "id_usuario" => $auth->idUsuario,
                    "id_cliente" => $auth->idCliente
                );
               
                $this->conexao->insert($dados);

                if ($enviar) {
                    if ($tipoAuth == \Utils\Constantes::TIPO_AUTH_EMAIL) {
                        $this->enviarPorEmail($codigo, $cliente);
                    } else {
                        if ($auth->idUsuario > 0) {
                            
                        } else if ($cliente->tipoAutenticacao != \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                            //$this->enviarPorSms($celular, $codigo, $nome, $email);
                            $this->enviarPorEmail($codigo, $cliente);
                        }
                    }
                }

                $this->conexao->adapter->finalizar();
                
                return $codigo;
            } catch(\Exception $e) {
                $this->conexao->adapter->cancelar();
                throw new \Exception(\Utils\Excecao::mensagem($e));
            }
        }
    }
    
    
    private function enviarPorEmail($codigo, $cliente) {

        $listaEnvio = Array(
            Array("nome" => $cliente->nome, "email" => $cliente->email)
        );

        $conteudo = Array(
            "Token" => $codigo
        );

        $conteudo = Mail::template($conteudo, "Token 2FA", "Token", $cliente->nome);

        $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Token 2FA", $conteudo, $listaEnvio);
        $mail->send();

    }
    
    private function enviarPorSms($celular, $codigo, $nome, $email) {
        try {
            
            $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
            $EnviaSMSResult = $api->EnviaSMS("55{$celular}", TITULO .": Token {$codigo}");
            
            if (trim($EnviaSMSResult) != "OK") {
                throw new \Exception();
            }
            
            try {
                $creditos = $api->VerCredito();
                $data = $api->VerValidade();
                
                $validade = null;
                if (strlen($data) == 19) {
                    $validade = new \Utils\Data(str_replace("T", " ", $data));
                }
                
                $statusConsumiveisRn = new StatusConsumivelRn();
                $statusConsumiveisRn->updateStatusSms($creditos, $validade);
                
            } catch (\Exception $ex) {
                //exit(print_r($ex));
            }
        } catch (\Exception $ex) {
             //$this->enviarPorEmail($codigo, $nome, $email);
             throw new \Exception($this->idioma->getText("noMomentoEstamosProblemaSms") . $email . " ." . $this->idioma->getText("retomaremos"));
        }
    }
    
    public function validar($codigo, $cliente = null, $tempoValidacao = 2) {
        $clienteRn = new ClienteRn();
        
        if ($cliente == null && \Utils\Geral::isCliente())  {
            $cliente = \Utils\Geral::getLogado();
        }
        
        $google = false;
        if ($cliente != null) {
            try {
                $clienteRn->conexao->carregar($cliente);
                if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                    $google = true;
                }
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteInvalido"));
            }
        
        }
        
        if (!$google ) {
            if (empty($codigo)) {
                throw new \Exception($this->idioma->getText("porFavorInformeCodigoValidacao"));
            }

            $codigo = sha1(str_replace("{token}", $codigo,  \Utils\Constantes::SEED_AUTH));

            $auth = null;
            $result = null;
            if (\Utils\Geral::isUsuario()) {
                $usuarioLogado = \Utils\Geral::getLogado();

                $result = $this->conexao->select("id_usuario = {$usuarioLogado->id}");
            } else if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $result = $this->conexao->select("id_cliente = {$cliente->id}");
            }

            if (sizeof($result) > 0) {
                $auth = $result->current();
            } else {
                $result = $this->conexao->select("codigo = '{$codigo}'");
                if (sizeof($result) > 0) {
                    $auth = $result->current();
                }
            }

            if ($auth == null) {
                throw new \Exception($this->idioma->getText("codigoNaoEncontrado"));
            }

            if ($auth->codigo != $codigo) {
                throw new \Exception($this->idioma->getText("codigoInvalido"));
            }

            $dataLimite = new \Utils\Data($auth->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
            $dataLimite->somar(0, 0, 0, 0, $tempoValidacao, 0);

            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            if ($dataAtual->maior($dataLimite)) {
                throw new \Exception($this->idioma->getText("codigoExpirado"));
            }
        } else {
            /*
            if ($cliente->quantidadeTentativasSegundoFator >= \Utils\Constantes::QUANTIDADE_TENTATIVAS_SEGUNDO_FATOR) {
                $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
                
                $diff = $dataAtual->diferenca($cliente->dataUltimaTentativaSegundoFator);
                
                if ($diff->d < 1 && $diff->m < 1 && $diff->y < 1 && $diff->i < 15) {
                    throw new \Exception("Você atingiu o limite máximo de tentativas e deve aguardar 15 minutos para tentar novamente.");
                }
            }
            */
            
            if(empty($cliente->googleAuthSecret)){
                throw new \Exception("Por favor, contate o suporte para ativação.");
            }
            
            $ga = new \GoogleAuth\GoogleAuthenticator();
            if ($ga->verifyCode($cliente->googleAuthSecret, $codigo, 1320)) {
                
                $clienteRn->conexao->update(Array("quantidade_tentativas_segundo_fator" => 0, "data_ultima_tentativa_segundo_fator" => null), Array("id" => $cliente->id));
                
            } else {
                $cliente = new Cliente();
                $cliente->quantidadeTentativasSegundoFator++;
                $cliente->dataUltimaTentativaSegundoFator = date("Y-m-d H:i:s");
                
                $clienteRn->conexao->update(Array("quantidade_tentativas_segundo_fator" => $cliente->quantidadeTentativasSegundoFator, "data_ultima_tentativa_segundo_fator" => $cliente->dataUltimaTentativaSegundoFator), Array("id" => $cliente->id));
                
                throw new \Exception($this->idioma->getText("authRn1"));
            }
        }
        \Utils\Geral::setAutenticado(true);
    }
}

?>