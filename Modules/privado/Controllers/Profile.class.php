<?php

namespace Modules\privado\Controllers;

class Profile {
    private $method = null;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function atualizarCliente($params) {       
        $httpResponse = new HttpResult();

        try {            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            
            if($cliente->documentoVerificado == 1){
                throw new \Exception("Dados não podem ser alterados." , 400);
            }

            if (!empty($cliente)) {                
                
                $json = file_get_contents('php://input');            
                $object = json_decode($json);
                
                $name = \Utils\SQLInjection::clean($object->name);
                $gender = \Utils\SQLInjection::clean($object->gender);
                $birthDate = \Utils\SQLInjection::clean($object->birthDate);
                $nationality =\Utils\SQLInjection::clean($object->nationality);
                $ddi = \Utils\SQLInjection::clean($object->ddi);
                $cellPhone = \Utils\SQLInjection::clean($object->cellPhone);
                $document = \Utils\SQLInjection::clean($object->document);

                if (!empty($name)) {
                    $cliente->nome = $name;
                }
                
                if (!empty($gender)) {
                    $gender = \Utils\Validacao::limparString($gender);
                    if ($gender == "M" || $gender == "F") {
                        $cliente->sexo = $gender;
                    } else {
                        throw new \Exception("Request parameter error - " . "gender" , 400);
                    }
                }

                if (!empty($birthDate)) { //Receber em timestamp
                    $birthDate = \Utils\Validacao::limparString($birthDate);
                    if (is_numeric($birthDate)) {
                        $birthDate = date("Y-m-d H:i:s", $birthDate);
                        if ($birthDate != FALSE) {
                            $cliente->dataNascimento = new \Utils\Data($birthDate);                            
                        } else {
                            throw new \Exception("Request parameter error - " . "birthDate", 400);
                        }
                    } else {
                        throw new \Exception("Request parameter error - " . "birthDate", 400);
                    }
                }

                if (!empty($nationality)) {
                    $nationality = \Utils\Validacao::limparString($nationality);
                    if (is_numeric($nationality)) {
                        $paisRn = new \Models\Modules\Cadastro\PaisRn();
                        $pais = $paisRn->conexao->listar(" codigo = {$nationality} ", null);

                        if (sizeof($pais) > 0) {
                            $pais = $pais->current();
                            $cliente->idPaisNaturalidade = $pais->id;
                        } else {
                            throw new \Exception("Request parameter error - " . "nationality", 400);
                        }
                    } else {
                        throw new \Exception("Request parameter error - " . "nationality", 400);
                    }
                }


                if (!empty($ddi)) {
                    $ddi = \Utils\Validacao::limparString($ddi);
                    if(is_numeric($ddi)){
                         $cliente->ddi = $ddi;
                    } else {
                        throw new \Exception("Request parameter error - " . "ddi", 400);
                    }                   
                }

                if (!empty($cellPhone)) {
                    $cellPhone = \Utils\Validacao::limparString($cellPhone);
                    if(is_numeric($cellPhone)){
                         $cliente->celular = $cellPhone;
                    } else {
                        throw new \Exception("Request parameter error - " . "cellPhone", 400);
                    }                   
                }

                if (!empty($document)) {
                    $document = \Utils\Validacao::limparString($document);
                    if(is_numeric($document) && \Utils\Validacao::cpf($document)){
                        $cliente->documento = $document;
                    } else {
                        throw new \Exception("Request parameter error - " . "document", 400);
                    }                    
                }

                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

                $clienteRn->salvar($cliente, $cliente->senha);

                $httpResponse->addBody("customer", $this->montarCliente($cliente));
                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    
    public function getCliente($params) {
        $httpResponse = new HttpResult();

        try {
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "GET") {
                throw new \Exception("Invalid Method", 403);
            }

            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());

            $httpResponse->addBody("customer", $this->montarCliente($cliente));

            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }    
    
    
    private function montarCliente(\Models\Modules\Cadastro\Cliente $cliente) {

        $pais = null;
        if (!empty($cliente->idPaisNaturalidade)) {
            $paisRn = new \Models\Modules\Cadastro\PaisRn();
            $pais = $paisRn->conexao->listar(" id = {$cliente->idPaisNaturalidade} ", null);

            if (sizeof($pais) > 0) {
                $pais = $pais->current();
                $pais = $pais->nome;
            }
        }

        $dados = Array(            
            "name" => $cliente->nome,
            "email" => $cliente->email,
            "birthDate" => !empty($cliente->dataNascimento) ? strtotime($cliente->dataNascimento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) : null,
            "document" => $cliente->documento,
            "nationality" => $pais,
            "gender" => $cliente->sexo,
            "ddi" => $cliente->ddi,
            "cellPhone" => $cliente->celular,   
            "updateProfile" => $cliente->documentoVerificado == 1 ? false : true
        );
        
        return $dados;
    }
    
    public function listarPaises($params) {
        $httpResponse = new HttpResult();

        try {          
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

            if (strtoupper($method) != "GET") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            
            if (!empty($cliente)) {
                $paisRn = new \Models\Modules\Cadastro\PaisRn();
                $result = $paisRn->conexao->listar(" ativo = 1 ", "codigo ASC");
                $paises = Array();
                
                if (sizeof($result) > 0) {
                    foreach ($result as $dados) {
                        $pais = Array(
                            "code" => $dados->codigo,
                            "name" => $dados->nome
                        );

                        $paises[] = $pais;
                        $pais = null;
                    }
                }

                $httpResponse->addBody("countries", $paises);

                $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }
    
    public function redefinirSenha($params) {       
        $httpResponse = new HttpResult();
        
        try {            
            $method = strtoupper($_SERVER['REQUEST_METHOD']);
            
            if (strtoupper($method) != "POST") {
                throw new \Exception("Invalid Method", 403);
            }
            
            $auth = new Auth();
            $cliente = $auth->logarWithMobile(apache_request_headers());
            
            if (!empty($cliente)) {                
                
                $json = file_get_contents('php://input');            
                $object = json_decode($json);
                
                $senhaAtual = \Utils\SQLInjection::clean(($object->currentPassword));
                $novaSenha = \Utils\SQLInjection::clean(($object->newPassword));
                $confirmaSenha = \Utils\SQLInjection::clean(($object->confirmPassword));

                $retorno = \Utils\Senha::forca($senha);
            
                if($retorno < 4){
                    throw new \Exception("Weak password.", 400);
                }
                
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

                $cliente->senha = $novaSenha;
                
                $confirmacao = $clienteRn->alterarSenha($cliente, $confirmaSenha, $senhaAtual);

                if($confirmacao) {
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE200, "Password changed successfully.");
                } else {
                    $httpResponse->setSuccessful(HTTPResponseCode::$CODE400, "Please, try again.");
                }
                
            } else {
                throw new \Exception("Request parameter error", 400);
            }
        } catch (\Exception $ex) {
            $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
        }
        $httpResponse->printResult();
    }

}