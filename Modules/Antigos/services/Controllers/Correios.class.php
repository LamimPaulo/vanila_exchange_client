<?php

namespace Modules\services\Controllers;

use Zend\Json\Json;
use Models\Logradouro;
use Models\LogradouroModel;
use Models\Bairro;
use Models\BairroModel;
use Models\Cidade;
use Models\CidadeModel;
use Models\Estado;
use Models\EstadoModel;

class Correios {

    /**
     * Função que retorna o logradouro por cep
     * @param array $_parameters 
     */
    function enderecoPorCep($params) {
        try {
            //Removo o traço caso tenha sido passado
            $cep = str_replace("-", '', \Utils\Post::get($params, "cep", ""));
            
            if (empty(trim($cep))) {
                throw new \Exception("CEP não informado");
            }
            
            if (strlen($cep) != 8) {
                throw new \Exception("CEP Inválido");
            }
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://viacep.com.br/ws/{$cep}/json/",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception($err);
            } 
            
            $dados = json_decode($response);
            
            if (isset($dados->erro) && $dados->erro) {
                throw new \Exception("CEP Inexistente");
            }
            
            $cidade = new \Models\Modules\Cadastro\Cidade();
            $cidade->codigo = $dados->ibge;
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidadeRn->carregar($cidade, true, true);
            
            $respostaJson = $this->requisicao("{$dados->logradouro} - {$dados->bairro}, {$cidade->nome} - {$cidade->estado->sigla}, República Federativa do Brasil");
            //Verifico se a api encontrou a descrição do endereço
            
            $coordenadas = "";
            if ($respostaJson['status'] == 'OK') {
                $coordenadas = str_replace(",", ".", $respostaJson['results'][0]['geometry']['location']['lat']) . ", " . str_replace(",", ".", $respostaJson['results'][0]['geometry']['location']['lng']);
                
            } 
            
            $json['endereco'] = $dados;
            $json['cidade'] = $cidade;
            $json['estado'] = \Utils\Criptografia::encriptyPostId($cidade->idEstado);
            $json['coordenadas'] = $coordenadas;
            $json['sucesso'] = true;
        } catch (\Exception $e) {
            $json['sucesso'] = false;
            $json['mensagem'] = \Utils\Excecao::mensagem($ex);
        }
        echo (Json::encode($json));
    }

    
    public function getCidadeByCep($cep) {
        $cidade = null;
        try {
            //Removo o traço caso tenha sido passado
            $cep = str_replace("-", '', $cep);
            
            if (empty(trim($cep))) {
                throw new \Exception("CEP não informado");
            }
            
            if (strlen($cep) != 8) {
                throw new \Exception("CEP Inválido");
            }
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://viacep.com.br/ws/{$cep}/json/",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception($err);
            } 
            
            $dados = json_decode($response);
            
            if (isset($dados->erro) && $dados->erro) {
                throw new \Exception("CEP Inexistente");
            }
            
            $cidade = new \Models\Modules\Cadastro\Cidade();
            $cidade->codigo = $dados->ibge;
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidadeRn->carregar($cidade, true, true);
            
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return $cidade;
    }
    
    /**
     * Efetua a busca das coordenadas (Latitude e longitude)
     * @param array $params 
     */
    function buscarCoordenadas($params) {
        try {
            $logradouro = \Utils\Post::get($params, "logradouro", "");
            $bairro = \Utils\Post::get($params, "bairro", "");
            $numero = \Utils\Post::get($params, "numero", "");
            
            $cidade = new \Models\Modules\Cadastro\Cidade();
            $cidade->codigo = \Utils\Post::get($params, "codigoCidade", NULL);
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidadeRn->carregar($cidade, true, true);
            
            if (!empty($numero)) {
                $numero = ", " . $numero;
            }
            
            $respostaJson = $this->requisicao("{$logradouro} {$numero} - {$bairro}, {$cidade->nome} - {$cidade->estado->sigla}, República Federativa do Brasil");
            //Verifico se a api encontrou a descrição do endereço
            
            $coordenadas = "";
            if ($respostaJson['status'] == 'OK') {
                $coordenadas = str_replace(",", ".", $respostaJson['results'][0]['geometry']['location']['lat']) . ", " . str_replace(",", ".", $respostaJson['results'][0]['geometry']['location']['lng']);
                
            } else {
                throw new \Exception("Falha ao buscar coordenadas geográficas.");
            }
            $json['coordenadas'] = $coordenadas;
            $json['sucesso'] = true;
        } catch (\Exception $e) {
            $json['sucesso'] = false;
            $json['mensagem'] = "Coordenadas geográficas não encontradas.";
        }
        echo (Json::encode($json));
    }

    /**
     * Efetua a busca do endereço pelas coordenadas
     * @param array $_parameters Array com os dados do controller passado automaticamente pelo route
     */
    function buscarCepPorCoordenada($_parameters) {
        //Array de resposta
        $json = array();
        $json['sucesso'] = false;
        try {
            $respostaJson = $this->requisicao("{$_parameters['_POST']['latitude']},{$_parameters['_POST']['longitude']}");
            //Verifico se a api encontrou a descrição do endereço
            if ($respostaJson['status'] == 'OK') {
                //Resposta JSON
                $cep = $respostaJson['results'][0]['address_components'][7]['long_name'];
                $json['cep'] = $cep;
                $json['sucesso'] = true;
            } else {
                throw new \Exception("Falha ao buscar CEP.", 99);
            }
        } catch (\Exception $e) {
            //Caso não encontre o cep, retorno a resposta
            $json['conteudo'] = \Utils\Excecao::mensagem($ex);
        }
        echo (Json::encode($json));
    }

    //Efetua a requisição no serviço do google de enderecos
    private function requisicao($parametros) {
        //Encodo os parâmetros com espaço
        $urlEncode = urlencode($parametros);
        //Efetuo a requisição ao serviço do google
        $resposta = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address={$urlEncode}");
        //Decodo a resposta em formato JSON
        $respostaJson = Json::decode($resposta, Json::TYPE_ARRAY);
        return $respostaJson;
    }

}