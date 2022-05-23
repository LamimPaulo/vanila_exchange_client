<?php

namespace Modules\acesso\Controllers;


use Io\BancoDados;
use Models\Modules\Cadastro\TokenApi;

class Cadastro {
    
    public $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("login", IDIOMA);
        header('Access-Control-Allow-Origin: *');
    }

    public function cadastroCliente($params)
    {
        try {

            echo 'Processo de cadastro iniciando';
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "POST") {
                throw new \Exception("Cadastro inválido", 500);
            }



            $nome = \Utils\Post::get($params, "nome", null);
            $email = strtolower(\Utils\Post::get($params, "email", null));
            $senha = base64_decode(\Utils\Post::get($params, "senha", null));
            /*$confirmarSenha = base64_decode(\Utils\Post::get($params, "confirmarSenha", null));*/
            $referencia = \Utils\Post::get($params, "referencia", NULL);

            /* $googleCode = \Utils\Post::get($params, "code", null);

         if (!empty($googleCode)) {
               $validate = \GoogleAuth\Recaptcha::validarRecaptcha($googleCode);
               if (!$validate) {
                   throw new \Exception("Recaptcha inválido.");
               }
           } else {
               throw new \Exception("Recaptcha inválido.");
           }*/

            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            if (!$configuracao->statusNovosCadastros > 0) {
                throw new \Exception("No momento o sistema não está aberto para novos cadastros. Tente novamente mais tarde. Obrigado!");
            }

            if (empty($email) || !\Utils\Validacao::email($email) || \Utils\EmailBlacklist::isBlacklist($email)) {
                throw new \Exception("O e-mail deve ser informado.");
            }

            if (empty($nome)) {
                throw new \Exception("O nome deve ser informado.");
            }

            $senha = $this->politicaSenha($senha, $senha);

            if (empty($senha)) {
                throw new \Exception("Senha não aceita");
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->conexao->listar("email = '{$email}'");

            if (sizeof($cliente) > 0) {
                throw new \Exception("Cadastro de e-mail não autorizado.");

            }
            $bodyMail = [
                'nome' => $nome,
                'email' => $email,
                'referencia' => $referencia,
                'senha' => \Utils\Criptografia::encriptyPostId($senha)
            ];


            $rabbit = new \RabbitMq\Client();
            $result = $rabbit->sendQueue('user_new', $bodyMail);

            if (!$result) {
                throw new \Exception("Por favor, tente novamente mais tarde.");
            }

            echo 'Processo de cadastro aconteceu';

            $json["sucesso"] = true;
            /* $json["mensagem"] = $this->idioma->getText("cadastroSucesso") ?? 'Cadastrado com sucesso';*/
            $json["mensagem"] = 'Cadastrado com sucesso';

        } catch (\Exception $ex) {
            exit($ex->getMessage());
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function politicaSenha($senha, $confirmarSenha){
        
        $forca = 0;
        
        if($senha === $confirmarSenha){
            
            //String
            if(preg_match( '/[a-zA-Z]/', $senha )){
                $forca++;
            }
            
            //Numero
            if(preg_match( '/\d/', $senha )){
                $forca++;
            }
            
            //Caractere especial
            if(preg_match('/[^a-zA-Z\d]/', $senha)){
                $forca++;
            }
            
            //Tamanho senha
            if (strlen($senha) >= 8) {
                $forca++;
                
                if ($forca == 4) {
                    return $senha;
                }
            }
        }
        return null;
    }
    public static function criarNovoCliente($object)
    {
        try {
            $object = json_decode($object);
            $cliente = new \Models\Modules\Cadastro\Cliente();

            $cliente->email = strtolower(\Utils\SQLInjection::clean($object->email));

            $cliente->nome = \Utils\SQLInjection::clean($object->nome);
            $cliente->senha = \Utils\Criptografia::decriptyPostId($object->senha);

            $cliente->senha = sha1($cliente->senha . \Utils\Constantes::SEED_SENHA);

            $cliente->idReferencia = \Utils\Criptografia::decriptyPostId($object->referencia);

            if (!is_numeric($cliente->idReferencia)) {
                $cliente->idReferencia = null;
            }

            if (empty($cliente->email)) {
                throw new \Exception("O email deve ser informado");
            }

            if (empty($cliente->nome)) {
                throw new \Exception("O nome deve ser informado");
            }
            $db_ = new BancoDados() ;
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn($db_);
            $result = $clienteRn->conexao->listar("email = '{$cliente->email}'");

            if (sizeof($result) > 0) {
                throw new \Exception("O email já está cadastrado no sistema");

            } else {
                $cliente->id = 0;
            }


            $where = "tipo IN ('C', 'UC') ";
            $rotinaHasAcaoRn = new \Models\Modules\Acesso\RotinaHasAcaoRn($db_);
            $rotinasHasAcoes = $rotinaHasAcaoRn->conexao->listar($where);
            $permissoesRotinas = array();
            foreach ($rotinasHasAcoes as $rha) {
                $permissoesRotinas[] = $rha->id;
            }


            $moduloHasAcaoRn = new \Models\Modules\Acesso\ModuloHasAcaoRn($db_);
            $modulosHasAcoes = $moduloHasAcaoRn->listar($where, null, null, null, true);


            $permissoesModulos = array();
            foreach ($modulosHasAcoes as $mha) {
                if ($mha->idModulo != 12) {  // modulo 12 = Recebimentos PDV
                    if ($mha->acao->codigo != "TPE") {
                        $permissoesModulos[] = $mha->id;
                    }
                }
            }

            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']);

            if (strpos($ip, ',') !== false) {
                $ip = substr($ip, 0, strpos($ip, ','));
            }

            $_SESSION["cadastrado"] = true;

            $cliente->comissaoConvitePago = 0;
            $cliente->documentoVerificado = 0;
            $cliente->documentoTipo = \Utils\Constantes::DOCUMENTO_CPF;
            $cliente->status = \Utils\Constantes::CLIENTE_AGUARDANDO;
            $cliente->statusDepositoBrl = 1;
            $cliente->statusDepositoCurrency = 1;
            $cliente->statusResgatePdv = 1;
            $cliente->statusSaqueBrl = 1;
            $cliente->statusSaqueCurrency = 1;
            $cliente->idPaisNaturalidade = 30;
            $cliente->utilizaSaqueDepositoBrl = 1;
            $cliente->modoOperacao = \Utils\Constantes::MODO_TRADER;
            $cliente->origemCadastro = \Utils\Constantes::ORIGEM_SITE;
            $cliente->ipCadastro = $ip;
            $cliente->retornoAnaliseEmail = "E-mail consultado com sucesso. - E-mail válido.";
            $clienteRn->salvar($cliente, $cliente->senha, $permissoesRotinas, $permissoesModulos);

            //\Models\Modules\Cadastro\
            $db_ = new BancoDados() ;
            $tokenApiRn = new \Models\Modules\Cadastro\TokenApiRn($db_);
            $tokenApi = new TokenApi();


            $tokenApi->idCliente = $cliente->id;
            $tokenApiRn->salvar($tokenApi);

            $token = \Utils\Criptografia::encriptyPostId($tokenApi->token);

            $token2 = \Utils\Criptografia::encriptyPostId(base64_encode($token . "]" . $cliente->email));

            $cliente_url = URLBASE_CLIENT . \Utils\Rotas::R_ATIVACAO . "?at=" . $token2;

            return [
                'sucesso' => true,
                'mensagem' => 'Cadastrado com sucesso',
                'dados' => [
                    'cliente_url' => $cliente_url,
                    'cliente_nome' => $cliente->nome,
                    'cliente_email' => $cliente->email
                ]
            ];
        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => sprintf('Falha no cadastro %s', $e->getMessage())
            ];
        }

    }
    
    
}