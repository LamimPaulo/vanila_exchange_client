<?php
namespace Modules\api\Controllers;

class Auth {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function index($params) {
        try {
            $email = \Utils\Post::get($params, "email", null);
            $senha = \Utils\Post::get($params, "senha", null);
            
            $tokenApiRn = new \Models\Modules\Cadastro\TokenApiRn();
            $token = $tokenApiRn->login($email, $senha);
            
            $cliente = $tokenApiRn->getClienteByToken($token->token);
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            if ($cliente != null) {
                
                if ($configuracao->statusLoginSistema < 1) {
                    throw new \Exception("Sistema temporariamente indisponível."); 
                }
                
                if ($cliente->fotoDocumentoVerificada < 1 || $cliente->fotoClienteVerificada < 1 || $cliente->fotoResidenciaVerificada < 1) {
                    //throw new \Exception("Existem pendências na sua conta. O acesso ao recuso está bloqueado. Por favor, contate-nos."); 
                }
                
            }
            
            
            if ($token->idUsuario > 0) {
                $usuario = new \Models\Modules\Cadastro\Usuario(Array("id" => $token->idUsuario));
                $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
                $usuarioRn->conexao->carregar($usuario);
                
                if ($usuario->tipo == \Utils\Constantes::ADMINISTRADOR) {
                    

                    $json["minConfirmations"] = $configuracao->qtdMinConfirmacoesTransacao;
                    $json["maxConfirmations"] = $configuracao->qtdMaxConfirmacoesTransacao;
                    
                    $json["taxas"] = Array(
                        "BTC" => number_format($configuracao->taxaRedeBtc, 8, ".", "")
                    );
                    
                    $json["confirmacoes"] = Array(
                        "BTC" => Array(
                            "min" => $configuracao->qtdMinConfirmacoesTransacao,
                            "max" => $configuracao->qtdMaxConfirmacoesTransacao
                        )
                    );
                    
                    $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
                    $taxas = $taxaMoedaRn->listar(NULL, null, NULL, NULL, true);
                    
                    foreach ($taxas as $taxaMoeda) {
                        if ($taxaMoeda->moeda->ativo > 0) {
                            $json["taxas"][$taxaMoeda->moeda->simbolo] = Array(
                                    "taxa" => number_format($taxaMoeda->taxaRede, $taxaMoeda->moeda->casasDecimais, ".", ""),
                                    "minConfirmacoes" => $taxaMoeda->minConfirmacoes,
                                    "maxConfirmacoes" => $taxaMoeda->maxConfirmacoes
                                );
                        }
                    }
                }
            }
            
            $json["token"] = $token->token;
            $json["validade"] = $token->validade->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private static function logar($authorization) {
        if (empty($authorization)) {
            throw new \Exception("400 Solicitação Inválida");
        }
        
        $auth = base64_decode($authorization);
        $dados = explode(":", $auth);
        
        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception("400 Solicitação Inválida");
        }
        
        try {
            \Utils\SQLInjection::clean($dados[0]);
            \Utils\SQLInjection::clean($dados[1]);
        } catch(\Exception $ex) {
            throw new \Exception("400 Solicitação Inválida");
        }
        
        $dados[0] = $dados[0];
        $dados[1] = sha1($dados[1].\Utils\Constantes::SEED_SENHA);
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "email" => $dados[0],
            "senha" => $dados[1]
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception("401 Não Autorizado");
        }
        
        $cliente = $result->current();
        return $cliente;
    }
    
    public function login() {
        try {
            
            $method = $_SERVER['REQUEST_METHOD'];
            if (strtoupper($method) != "POST") {
                throw new \Exception("405 Método não permitido");
            }
            
            $this->headers = apache_request_headers();
            
            if (isset($this->headers["Authorization"])) {

                $authorization = trim(str_replace("Basic", "", $this->headers["Authorization"]));
                
                $cliente = $this->logar($authorization);
                
                if ($cliente->status == 0) {
                    throw new \Exception("401 Cadastro em análise");
                }
                
                if ($cliente->status == 2) {
                    throw new \Exception("401 Não Autorizado. Cadastro bloqueado");
                }
                
                $json = Array(
                    "codigo" => \Utils\Criptografia::encriptyPostId($cliente->id),
                    "nome" => $cliente->nome,
                    "email" => $cliente->email,
                    "celular" => $cliente->celular,
                    "clienteDesde" => $cliente->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                    "foto" => URLBASE_CLIENT . UPLOADS . $cliente->foto,
                    "sexo" => $cliente->sexo
                );
                
                
                print json_encode($json);
            } else {
                throw new \Exception("401 Não Autorizado");
            }
            
        } catch (\Exception $ex) {
            $mensagem = \Utils\Excecao::mensagem($ex);
            header("HTTP/1.1 {$mensagem}");
        }
    }
    
}
