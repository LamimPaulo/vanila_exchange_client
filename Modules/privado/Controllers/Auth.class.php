<?php
namespace Modules\privado\Controllers;

class Auth {
    
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("exception");
        header('Access-Control-Allow-Origin: *');
        header("Cache-Control:max-age=0");
    }
    
    public function cadastrar($params) {
      
        $httpResponse = new HttpResult();
        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }

            $json = file_get_contents('php://input');            
            $object = json_decode($json);
            
            $nome = \Utils\SQLInjection::clean($object->name);
            $email = \Utils\SQLInjection::clean($object->email);
            $senha = \Utils\SQLInjection::clean($object->password);
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->nome = $nome;
            $cliente->senha = $senha;
            $cliente->email = $email;
            
            $retorno = \Utils\Senha::forca($senha);
            
            if($retorno < 4){
                throw new \Exception($this->idioma->getText("senhaMaisForte"), 400);
            }
                                    
            $this->cadastrarCliente($cliente);
            
            $token = new \Models\Modules\Cadastro\Auth();
            $token->idCliente = $cliente->id;
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $codigo = $authRn->salvar($token, null, false);
            
            $hash = base64_encode($cliente->clientid . ":" . $cliente->apiKey . "]" . $codigo);
            
            $httpResponse->addBody("hash", $hash);
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        
        $httpResponse->printResult();       
    }
    
    private function cadastrarCliente(\Models\Modules\Cadastro\Cliente $cliente) {
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        if (!$configuracao->statusNovosCadastros > 0) {
            throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"), 400);
        }

        if (empty($cliente->email)) {
            throw new \Exception($this->idioma->getText("oEmailDeveInformado"), 400);
        }
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->listar("email = '{$cliente->email}'");
        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("oEmailJaCadastrado"), 400);
        } else {
            $cliente->id = 0;
        }

        $cliente->documentoTipo = 0;
        $cliente->status = \Utils\Constantes::CLIENTE_ATIVO;
       
        if (!is_numeric($cliente->idReferencia)) {
            $cliente->idReferencia = null;
        }
        
        if (empty($cliente->idReferencia)) { //Se vazaio -> Referencia Financeiro
            $cliente->idReferencia = 15093064546903;
        }
        
        $cliente->comissaoConvitePago = 0;

        if (empty($cliente->nome)) {
            throw new \Exception($this->idioma->getText("necessarioInformarNomeCliente"), 400);
        }

        $where = "tipo IN ('C', 'UC') ";
        $rotinaHasAcaoRn = new \Models\Modules\Acesso\RotinaHasAcaoRn();
        $rotinasHasAcoes = $rotinaHasAcaoRn->conexao->listar($where);
        $permissoesRotinas = Array();
        foreach ($rotinasHasAcoes as $rha) {
            $permissoesRotinas[] = $rha->id;
        }

        $moduloHasAcaoRn = new \Models\Modules\Acesso\ModuloHasAcaoRn();
        $modulosHasAcoes = $moduloHasAcaoRn->listar($where, null, null, null, true);
        $permissoesModulos = Array();
        foreach ($modulosHasAcoes as $mha) {
            //$mha = new \Models\Modules\Acesso\ModuloHasAcao();
            if ($mha->idModulo != 12) {  // modulo 12 = Recebimentos PDV
                if ($mha->acao->codigo != "TPE") {
                    $permissoesModulos[] = $mha->id;
                }
            }
        }
        //$ip = $_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']);

        if(strpos($ip,',') !== false) {
            $ip = substr($ip,0,strpos($ip,','));
        }
        
        $cliente->statusDepositoBrl = 1;
        $cliente->statusDepositoCurrency = 1;
        $cliente->statusResgatePdv = 1;
        $cliente->statusSaqueBrl = 1;
        $cliente->statusSaqueCurrency = 1;
        $cliente->idPaisNaturalidade = 30;
        $cliente->utilizaSaqueDepositoBrl = 1;
        $cliente->modoOperacao = "trade";
        
        $cliente->origemCadastro = \Utils\Constantes::ORIGEM_APP;
        $cliente->ipCadastro = $ip;
                
        $clienteRn->salvar($cliente, $cliente->senha, $permissoesRotinas, $permissoesModulos);
        
        return $cliente;
    }
    
    
    public function parear($params) {
        
        $httpResponse = new HttpResult();
        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $cliente = $this->validaMobile(apache_request_headers(), true);
            
            $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();            

            $json = file_get_contents('php://input');            
            $object = json_decode($json);


            $dispositivoMobile = new \Models\Modules\Cadastro\DispositivoMobile();

            $dispositivoMobile->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $dispositivoMobile->id = 0;
            $dispositivoMobile->status = 1;
            $dispositivoMobile->marcaFabricante = \Utils\SQLInjection::clean($object->brand);
            $dispositivoMobile->modelo = \Utils\SQLInjection::clean($object->model);
            $dispositivoMobile->numeroSerial = \Utils\SQLInjection::clean($object->serialNumber);
            $dispositivoMobile->sistemaOperacional = \Utils\SQLInjection::clean($object->so);
            $dispositivoMobile->versaoSo = \Utils\SQLInjection::clean($object->soVersion);
            $dispositivoMobile->numeroDispositivo = \Utils\SQLInjection::clean($object->deviceNumber);
            $dispositivoMobile->idCliente = $cliente->id;

            $dispositivoMobileRn->salvar($dispositivoMobile);

            //$httpResponse->addBody("idMobile", \Utils\Criptografia::encriptyPostId($dispositivoMobile->id));

            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200, "Registered device and authenticated client.");
            /*}*/
            
            //$httpResponse->setSuccessful(HTTPResponseCode::$CODE400, "Cliente não autenticado.");
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }
    
    private function validaMobile($headers, $tokenAtivo = false) {
        
        if (is_array($headers)) {
            $authorization = Auth::extractAutorizationMobile($headers, $tokenAtivo);
        }
        
        if (empty($authorization)) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
       
        if ($tokenAtivo) {
            $token = \Utils\SQLInjection::clean($authorization["token"]);
        } 
            $auth = base64_decode($authorization["authorization"]);
            $dados = explode(":", $auth);

        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }

        try {
            \Utils\SQLInjection::clean($dados[0]);
            \Utils\SQLInjection::clean($dados[1]);
        } catch(\Exception $ex) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
       
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "clientid" => $dados[0],
            "api_key" => $dados[1],
            "status" => \Utils\Constantes::CLIENTE_ATIVO
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception($this->idioma->getText("naoAutorizado"), 400);
        }
             
        $cliente = $result->current();
                        
        if($cliente->status != 1){
            throw new \Exception($this->idioma->getText("naoAutorizado"), 400);
        }
        
        if($tokenAtivo) {
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token, $cliente, 59);
        }
        
        $clienteRn->setUltimaAtividade($cliente);
        
        return $cliente;
    }
    
    //Cliente insere email e senha para fazer login direto no celular
    public function loginClienteMobile($params) {
        
        $httpResponse = new HttpResult();
        try {
            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $retorno = $this->logarMobile(apache_request_headers());
            
            $cliente = $retorno[0];
            $dispositivo = $retorno[1];
            
            if((!empty($cliente) && $cliente instanceof \Models\Modules\Cadastro\Cliente) && (!empty($dispositivo) && $dispositivo instanceof \Models\Modules\Cadastro\DispositivoMobile)){
               
                $hash = base64_encode($cliente->clientid. ":" .$cliente->apiKey);                
                
                $httpResponse->addBody("hash", $hash);
                $httpResponse->addBody("mobile", \Utils\Criptografia::encriptyPostId($dispositivo->numeroDispositivo . ":" . $dispositivo->numeroSerial));
                
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200, "Authenticated client");
            } else {
                throw new \Exception("Login is not allowed", 400);
            }
     
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }

        $httpResponse->printResult();
    }
    
    
    //Autentica email e senha do cliente e autentica celular.
    private function logarMobile($headers) {
        
        if (is_array($headers)) {
            $authorization = Auth::extractAutorizationLogin($headers);
        }        
        
        if (empty($authorization)) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        $auth = base64_decode($authorization[0]);
        $dados = explode(":", $auth);
        
        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        try {
            $cliente = \Utils\SQLInjection::clean($dados[0]);
            $senha = \Utils\SQLInjection::clean($dados[1]);
        } catch(\Exception $ex) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        $dados[0] = $cliente;
        $dados[1] = sha1($senha . \Utils\Constantes::SEED_SENHA);
        
        $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "email" => $dados[0],
            "senha" => $dados[1],
            "status" => \Utils\Constantes::CLIENTE_ATIVO
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception($this->idioma->getText("naoAutorizado"), 400);
        }
        
        $cliente = $result->current();        
        
        
        $retorno = Array();
        
        $retorno[0] = $cliente;
        
        if($cliente->emailConfirmado < 1){
            throw new \Exception($this->idioma->getText("emailPrecisaConfirmadoErr"), 400);
        }
        
        if($cliente->status != 1){
            throw new \Exception($this->idioma->getText("permissaoAcessoSistemaErr"), 400);
        }
        
        $auth = base64_decode($authorization[1]);
        
        $mobile = explode(":", $auth);
        
        if (sizeof($mobile) != 2 || empty($mobile[0]) || empty($mobile[1])) {
             throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        $mobile[0] = \Utils\SQLInjection::clean($mobile[0]);
        $mobile[1] = \Utils\SQLInjection::clean($mobile[1]);
        
        $result = $dispositivoMobileRn->conexao->listar(" id_cliente = {$cliente->id} AND numero_dispositivo = '{$mobile[0]}' AND numero_serial = '{$mobile[1]}' ");
                
        if (sizeof($result) > 0) {
            $dispositivo = $result->current();
            $retorno[1] = $dispositivo;
        } else {
             throw new \Exception($this->idioma->getText("dispositivoNaoAutorizado"), 400);
        }
       
        return $retorno;
    }

    public static function logar($headers) {
        if (is_array($headers)) {
            $authorization = Auth::extractAutorization($headers);
        }
        
        if (empty($authorization)) {
            throw new \Exception("Not authenticated", 400);
        }
        
        $auth = base64_decode($authorization);
        $dados = explode(":", $auth);
        
        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception("Not authenticated", 400);
        }
        
        try {
            \Utils\SQLInjection::clean($dados[0]);
            \Utils\SQLInjection::clean($dados[1], false);
        } catch(\Exception $ex) {
            throw new \Exception("Not authenticated", 400);
        }
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "clientid" => $dados[0],
            "api_key" => $dados[1],
            "status" => \Utils\Constantes::CLIENTE_ATIVO,
            "email_confirmado" => 1
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception("Not authenticated", 400);
        }
        
        $cliente = $result->current();
        
        $clienteRn->setUltimaAtividade($cliente);
        
        return $cliente;
    }
    
    //Função para autenticar o cliente nos metodos
    public function logarWithMobile($headers) {
       
        if (is_array($headers)) {
            $authorization = Auth::extractAutorizationAuthMobile($headers);
        }
        
        if (empty($authorization)) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        $auth = base64_decode($authorization[0]);
        $dados = explode(":", $auth);
        
        if (sizeof($dados) != 2 || empty($dados[0]) || empty($dados[1])) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        try {
            $clientId = \Utils\SQLInjection::clean($dados[0]);
            $apiKey = \Utils\SQLInjection::clean($dados[1]);
        } catch(\Exception $ex) {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->select(Array(
            "clientid" => $clientId,
            "api_key" => $apiKey,
            "status" => \Utils\Constantes::CLIENTE_ATIVO
        ));
        
        if (sizeof($result) <= 0) {
            throw new \Exception("Não Autorizado", 401);
        }
        
        $cliente = $result->current();
        
          if($cliente->emailConfirmado < 1){
            throw new \Exception($this->idioma->getText("emailPrecisaConfirmadoErr"), 400);
        }
        
        $clienteRn->setUltimaAtividade($cliente);
        
        if (!empty($authorization[1])) {

            $result = \Utils\Criptografia::decriptyPostId($authorization[1]);
            
            $result = explode(":", $result);
            
            try {
                $numeroDispositivo = \Utils\SQLInjection::clean($result[0]);
                $numeroSerial = \Utils\SQLInjection::clean($result[1]);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
            }

            $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();
            $dados = $dispositivoMobileRn->conexao->listar(" id_cliente = {$cliente->id} AND numero_dispositivo = '{$numeroDispositivo}' AND numero_serial = '{$numeroSerial}' AND status = 1 ");

            if (sizeof($dados) <= 0) {
                throw new \Exception($this->idioma->getText("dispositivoNaoAutorizado"), 400);  //Verificar retorno celular
            }
        } else {
            throw new \Exception($this->idioma->getText("solicitacaoInvalida"), 400);
        }
        
        return $cliente;
    }
    
    
    
    public static function extractAutorization($headers) {
        
        $authorization = "";
        
        if (isset($headers["Authorization"]) || isset($headers["authorization"])) {
            $authorization = trim(str_replace("Basic", "", (isset($headers["Authorization"]) ? $headers["Authorization"] : $headers["authorization"])));
        }
        
        if (AMBIENTE == "desenvolvimento") {
                $authorization = trim(str_replace("Basic", "", $_SERVER["REDIRECT_HTTP_AUTHORIZATION"]));
        }
        
        return $authorization;
    }
    
    public static function extractAutorizationLogin($headers) {
        $result = Array();
        $authorization = "";
        $mobile = "";
       
        if (isset($headers["Authorization"]) || isset($headers["authorization"])) {
            $authorization = trim(str_replace("Basic", "", (isset($headers["Authorization"]) ? $headers["Authorization"] : $headers["authorization"])));
            $result[0] = $authorization;
        }
        
      
        if (isset($headers["hash"]) || isset($headers["Hash"])) {
            $mobile = trim((isset($headers["hash"]) ? $headers["hash"] : $headers["Hash"]));
            $result[1] = $mobile;
        }        
        
        return $result;
    }
    
    public static function extractAutorizationAuthMobile($headers) {
        $result = Array();
        $authorization = "";
        $mobile = "";
        
        if (isset($headers["Authorization"]) || isset($headers["authorization"])) {
            $authorization = trim(str_replace("Basic", "", (isset($headers["Authorization"]) ? $headers["Authorization"] : $headers["authorization"])));
            $result[0] = $authorization;
        }
        
      
        if (isset($headers["mobile"]) || isset($headers["Mobile"])) {
            $mobile = trim((isset($headers["mobile"]) ? $headers["mobile"] : $headers["Mobile"]));
            $result[1] = $mobile;
        }        
        
        return $result;
    }
    
    public static function extractAutorizationMobile($headers, $tokenAtivo = false) {
        $authorization = "";
        $token = "";
        if (isset($headers["Authorization"]) || isset($headers["authorization"])) {
            $authorization = trim(str_replace("Basic", "", (isset($headers["Authorization"]) ? $headers["Authorization"] : $headers["authorization"])));
            $dados["authorization"] = $authorization;
        }

        if ($tokenAtivo) {
            if (isset($headers["Token"]) || isset($headers["token"])) {
                $token = trim((isset($headers["Token"]) ? $headers["Token"] : $headers["token"]));
            }           
            $dados["token"] =  $token;
        }
        
        return $dados;
    }

}
