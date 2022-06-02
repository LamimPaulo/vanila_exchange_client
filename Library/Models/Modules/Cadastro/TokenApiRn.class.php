<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TokenApiRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
     private $idioma = null;
    
    const SEED = "COINT%ASD%&*SEED2*$5&THrHd-Ingr*#";
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TokenApi()); 
        } else {
            $this->conexao = new GenericModel($adapter, new TokenApi()); 
        }
    }
    
    public function salvar(TokenApi  &$tokenApi) {
        
        try {
            $this->conexao->adapter->iniciar();
            if (!$tokenApi->idUsuario > 0 && !$tokenApi->idCliente > 0) {
                throw new \Exception($this->idioma->getText("identificacaoInvalida"));
            }
            
            $where = new \Zend\Db\Sql\Where();
            if ($tokenApi->idUsuario > 0) {
                $where->equalTo("id_usuario", $tokenApi->idUsuario);
            } else {
                $where->isNull("id_usuario");
            }
            
            if ($tokenApi->idCliente > 0) {
                $where->equalTo("id_cliente", $tokenApi->idCliente);
            } else {
                $where->isNull("id_cliente");
            }
            
            $this->conexao->delete($where);
            
            $tokenApi->token = sha1(base64_encode(rand(0, 10000) . "/" . date("d/m/Y H:i:s") . self::SEED . rand(0, 50000)));
            
            $tokenApi->validade = new \Utils\Data(date("d/m/Y H:i:s"));
            
            if ($tokenApi->idUsuario == 1483023334) {
                $tokenApi->validade->somar(0, 0, 0, 0, 5, 0);
            } else {
                $tokenApi->validade->somar(0, 0, 0, 0, 5, 0);
            }
            
            $this->conexao->insert(Array(
                "validade" => $tokenApi->validade->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                "id_cliente" => $tokenApi->idCliente,
                "token" => $tokenApi->token,
                "id_usuario" => $tokenApi->idUsuario
            ));
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    public function login($email, $senha) {
        // Valida se o email foi informado
        if (empty($email)) {
            throw new \Exception($this->idioma->getText("emailDeveSerInformado"));
        }

        // Valida se a senha foi informada
        if (empty($senha)) {
            throw new \Exception($this->idioma->getText("loginSenhaErr"));
        }
        
        $clienteRn = new ClienteRn();

        $senha = sha1($senha . \Utils\Constantes::SEED_SENHA);


        $configuracao = ConfiguracaoRn::get();

        if ($configuracao->statusLoginSistema < 1) {
            throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"));
        }

        $result = $clienteRn->conexao->listar("email = '" . $email . "' and senha = '" . $senha . "'", null, null, 1);

        if (sizeof($result) > 0) {
            $cliente = $result->current();

            if ($cliente->status == \Utils\Constantes::CLIENTE_AGUARDANDO) {
                throw new \Exception($this->idioma->getText("cadastroEmAnaliseErr"));
            }
            
            if ($cliente->status == \Utils\Constantes::CLIENTE_NEGADO) {
                throw new \Exception($this->idioma->getText("cadastroRecusadoErr"));
            }

            $token = new TokenApi();
            $token->idCliente = $cliente->id;

            $this->salvar($token);

            return $token;
        } else {
            throw new \Exception($this->idioma->getText("loginESenhaInvalidosErr"));
        }
    }

    public function getUserByToken($token) {
        $result = $this->conexao->select(Array(
            "token" => $token
        ));
        
        if (sizeof($result) > 0) {
            
            $tokenApi = $result->current();
            
            if ($tokenApi->idCliente != null) {
                $cliente = new Cliente(Array("id" => $tokenApi->idCliente));
                $clienteRn = new ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
                return $cliente;
            } else {
                $usuario = new Usuario(Array("id" => $tokenApi->idUsuario));
                $usuarioRn = new UsuarioRn();
                $usuarioRn->conexao->carregar($usuario);
                
                return $usuario;
            }
            
        }
        
        return null;
    }
    
    
    
    public function getClienteByToken($token, $carregarCliente = true) {
        $result = $this->conexao->select(Array(
            "token" => $token
        ));
        
        if (sizeof($result) > 0) {
            
            $tokenApi = $result->current();
            
            if ($tokenApi->idCliente != null) {
                if ($carregarCliente) {
                    $cliente = new Cliente(Array("id" => $tokenApi->idCliente));
                    $clienteRn = new ClienteRn();
                    $clienteRn->conexao->carregar($cliente);

                    return $cliente;
                } else {
                    return $tokenApi;
                }
            }
        }
        
        return null;
    }
    
    public function validar($token) {
        $result = $this->conexao->select(Array(
            "token" => $token
        ));
        
        if (sizeof($result) > 0) {
            $tokenApi = $result->current();
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            
            if ($dataAtual->maior($tokenApi->validade)) {
                throw new \Exception($this->idioma->getText("tokenExpirado"));
            }
            
            return $tokenApi;
        } else {
            throw new \Exception($this->idioma->getText("tokenInvalido"));
        }
    }
}

?>