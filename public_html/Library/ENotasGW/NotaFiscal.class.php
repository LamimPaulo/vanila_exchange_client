<?php

namespace ENotasGW;

class NotaFiscal {
    
    /**
     * 
     * @param Object $object pode ser depósito 
     * @return type
     * @throws \Exception
     */
    public static function emitir($object, $incluirCliente = false) {
        
        $post = Array();
        
        
        $post["enviarPorEmail"] = true;
        $post["id"] = "";
        $post["ambienteEmissao"] = (AMBIENTE == "desenvolvimento" ? "Homologacao" : "Producao");
        $post["tipo"] = "NFS-e";
        
        $post["consumidorFinal"] = true;
        $post["indicadorPresencaConsumidor"] = "";
        
        
        //$post["idExternoSubstituir"] = "string";
        //$post["nfeIdSubstitituir"] = "string";
        $descricao = "Intermediação de Negócios Entre Terceiros. Data da negociação: ";
        
        $idExterno = "";
        
        
        
        if ($object instanceof \Models\Modules\Cadastro\Deposito) {
            
            if ($object->status == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) {
                throw new \Exception("Não é possível emitir nota fiscal de um depósito cancelado");
            }
            
            if ($object->status == \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                throw new \Exception("Não é possível emitir nota fiscal de um depósito pendente");
            }
            
            $descricao .= "{$object->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR)}. Total do depósito: R$ " . number_format($object->valorDepositado, 2, ",", ".") . ".";
            $idExterno = "DEP-{$object->id}";
            $post["valorTotal"] = $object->valorComissao;
        } else if ($object instanceof \Models\Modules\Cadastro\Saque) {
            
            if ($object->status == \Utils\Constantes::STATUS_SAQUE_CANCELADO) {
                throw new \Exception("Não é possível emitir nota fiscal de um saque cancelado");
            }
            
            if ($object->status == \Utils\Constantes::STATUS_SAQUE_PENDENTE) {
                throw new \Exception("Não é possível emitir nota fiscal de um saque pendente");
            }
            
            $descricao .= "{$object->dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR)}. Total do saque: R$ " . number_format($object->valorSaque, 2, ",", ".") . ".";
            $idExterno = "SAQ-{$object->id}";
            $post["valorTotal"] = $object->valorComissao;
        } else if ($object instanceof \Models\Modules\Cadastro\BoletoCliente) {
            
            if ($object->status == \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO) {
                throw new \Exception("Não é possível emitir nota fiscal de um boleto cancelado");
            }
            
            if ($object->status != \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO) {
                throw new \Exception("Não é possível emitir nota fiscal de um boleto pendente");
            }
            
            $descricao .= "{$object->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR)}. Total do boleto: R$ " . number_format($object->valor, 2, ",", ".") . ".";
            $idExterno = "BOL-{$object->id}";
            $post["valorTotal"] = $object->valorTaxa;
        } else if ($object instanceof \Models\Modules\Cadastro\RemessaDinheiro) {
            
            if ($object->status == \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO) {
                throw new \Exception("Não é possível emitir nota fiscal de uma remessa cancelada");
            }
            
            if ($object->status != \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO) {
                throw new \Exception("Não é possível emitir nota fiscal de uma remessa pendente");
            }
            
            $descricao .= "{$object->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR)}. Total da remessa: R$ " . number_format($object->valor, 2, ",", ".") . ".";
            $idExterno = "REM-{$object->id}";
            $post["valorTotal"] = $object->valorTaxa;
        } else {
            throw new \Exception("Tipo de serviço inválido");
        }
        
        $descricao .= " Valor da comissão: R$ " . number_format($post["valorTotal"], 2, ",", ".") . ".";
        
        $post["servico"] = Array(
            "descricao" => $descricao,
            "aliquotaIss" => 2,
            "issRetidoFonte" => false,
            //"cnae" => "",
            //"codigoServicoMunicipio" => "string",         
            //"descricaoServicoMunicipio" => "string",
            //"itemListaServicoLC116" => "string",
            "ufPrestacaoServico" => "SP",
            "municipioPrestacaoServico" => "Jundiaí",
            "valorCofins" => 0,
            "valorCsll" => 0,
            "valorInss" => 0,
            "valorIr" => 0,
            "valorPis" => 0
        );
        $post["idExterno"] = $idExterno . "-" . time();
        
        if ($incluirCliente) { 
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $object->idCliente));
            try {

                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);

            } catch (\Exception $ex) {
                throw new \Exception("Cliente inválido ou não localizado no sistema");
            }
            
            $cidade = new \Models\Modules\Cadastro\Cidade(Array("codigo" => $cliente->cidade));
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidadeRn->carregar($cidade, true, true);
            
            $post["cliente"] = Array (
                
                    "endereco" => Array (
                        "pais" => "Brasil",
                        "uf" => $cidade->estado->sigla,
                        "cidade" => $cidade->nome,
                        "logradouro" => $cliente->endereco,
                        "numero" => $cliente->numero,
                        "complemento" => $cliente->complemento,
                        "bairro" => $cliente->bairro,
                        "cep" => $cliente->cep
                    ),

                    "tipoPessoa" => "F",
                    "nome" => $cliente->nome,
                    "email" => $cliente->email,
                    "cpfCnpj" => $cliente->documento,
                    //"inscricaoMunicipal" => "",
                    //"inscricaoEstadual" => "",
                    //"indicadorContribuinteICMS" => "",
                    "telefone" => $cliente->celular

                );
            
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.enotasgw.com.br/v1/empresas/".Credenciais::ID."/nfes",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($post),
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "Authorization: Basic " . Credenciais::KEY . "",
              "cache-control: no-cache",
              "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response);
        
        if (is_array($json) && isset($json[0]->mensagem)) {
            throw new \Exception($json[0]->mensagem);
        }
        
        return $json;
    }
    
    
    
    public static function consultar($codigoNota) {
        
        if (empty($codigoNota)) {
            throw new \Exception("Código da nota inválido");
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.enotasgw.com.br/v1/empresas/".Credenciais::ID."/nfes/{$codigoNota}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "accept: application/json",
              "authorization: Basic " . Credenciais::KEY . "",
              "cache-control: no-cache",
              "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response);
        
        
        if (is_array($json) && isset($json[0]->mensagem)) {
            throw new \Exception($json[0]->mensagem);
        }
        
        return $json;
    }
    
    
    public static function cancelar($codigoNota) {
        
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.enotasgw.com.br/v1/empresas/".Credenciais::ID."/nfes/{$codigoNota}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "DELETE",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic " . Credenciais::KEY . "",
            "cache-control: no-cache",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        
        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response);
        
        
        if (is_array($json) && isset($json[0]->mensagem)) {
            throw new \Exception($json[0]->mensagem);
        }
        
        return $json;
    }
    
    
    
    public static function imprimir($codigoNota, $tipo) {
        
        $tipos = Array("pdf", "xml");
        if (!in_array($tipo, $tipos)) {
            throw new \Exception("Tipo de arquivo inválido");
        }
        
        if (empty($codigoNota)) {
            throw new \Exception("Código da nota inválido");
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.enotasgw.com.br/v1/empresas/".Credenciais::ID."/nfes/{$codigoNota}/{$tipo}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic " . Credenciais::KEY . "",
            "cache-control: no-cache",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response);
        
        if (is_array($json) && isset($json[0]->mensagem)) {
            throw new \Exception($json[0]->mensagem);
        }
        
        
        return $json;
    }
    
    
    
    public static function listar($pageNumber, $pageSize, $filtro, $sortBy, $sortDirection) {
       
        $filtro = str_replace(" ", "%20", $filtro);
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://api.enotasgw.com.br/v1/empresas/".Credenciais::ID."/nfes?pageNumber={$pageNumber}&pageSize={$pageSize}&filter={$filtro}&sortBy={$sortBy}&sortDirection={$sortDirection}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic " . Credenciais::KEY . "",
            "cache-control: no-cache",
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        } 
        
        $json = json_decode($response);
        
        
        if (is_array($json) && isset($json[0]->mensagem)) {
            throw new \Exception($json[0]->mensagem);
        }
        
        
        return $json;
    }
}