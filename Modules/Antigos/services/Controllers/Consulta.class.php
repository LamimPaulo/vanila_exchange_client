<?php

namespace Modules\services\Controllers;

class Consulta {
    
    private static $chave = "715UK-33DPJ-D633R-59518";
     
    
    public static function cpf ($cpf) {
        try {
            $cpf = str_replace(Array(".", "-"), "", $cpf);
            if (strlen($cpf) != 11) {
                throw new \Exception("CPF inválido");
            }
            
            if (!\Utils\Validacao::cpf($cpf)) {
                throw new \Exception("CPF inválido");
            }
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "http://ws.iwebservice.com.br/CPF/",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"chave\"\r\n\r\n".self::$chave."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cpf\"\r\n\r\n{$cpf}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"formato\"\r\n\r\njson\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $err = "Erro na consulta do CPF. Por favor, tente novamente mais tarde";
                throw new \Exception($err);
            }
            
            $json = json_decode($response);
            //exit(print_r($json));
            if (isset($json->RetornoCpf->msg->Creditos) && $json->RetornoCpf->msg->Creditos != null) {
                $statusConsumivelRn = new \Models\Modules\Cadastro\StatusConsumivelRn();
                $statusConsumivelRn->upateStatusConsultaDocumentos(str_replace(Array(".", ","), "", $json->RetornoCpf->msg->Creditos));
            }
            
            if ($json->RetornoCpf->msg->Resultado == 1) {
                
                $consultaCpf = new \Models\Modules\Cadastro\ConsultaCpf();
                $consultaCpf->anoObito = $json->RetornoCpf->DadosObito->AnoObito;
                $consultaCpf->bairro = $json->RetornoCpf->EnderecoTitular->Bairro;
                $consultaCpf->cep = $json->RetornoCpf->EnderecoTitular->Cep;
                $consultaCpf->cidade = $json->RetornoCpf->EnderecoTitular->Cidade;
                $consultaCpf->codigoControle = $json->RetornoCpf->DadosTitular->CodigoControle;
                $consultaCpf->complemento = $json->RetornoCpf->EnderecoTitular->Complemento;
                $consultaCpf->cpf = $json->RetornoCpf->DadosTitular->Cpf;
                $consultaCpf->creditos = $json->RetornoCpf->msg->Creditos;
                $consultaCpf->dataConsulta = $json->RetornoCpf->DadosTitular->DataConsulta;
                $consultaCpf->dataInscricao = $json->RetornoCpf->DadosTitular->DataInscricao;
                $consultaCpf->dataNascimento = $json->RetornoCpf->DadosTitular->DataNascimento;
                $consultaCpf->digitoVerificador = $json->RetornoCpf->DadosTitular->DigitoVerificador;
                $consultaCpf->genero = $json->RetornoCpf->DadosTitular->Genero;
                $consultaCpf->horaConsulta = $json->RetornoCpf->DadosTitular->HoraConsulta;
                $consultaCpf->logradouro = $json->RetornoCpf->EnderecoTitular->Logradouro;
                $consultaCpf->msgObito = $json->RetornoCpf->DadosObito->msgObito;
                $consultaCpf->nomeMae = $json->RetornoCpf->DadosTitular->NomeMae;
                $consultaCpf->numero = $json->RetornoCpf->EnderecoTitular->Numero;
                $consultaCpf->resultado = $json->RetornoCpf->msg->Resultado;
                $consultaCpf->resultadoTxt = $json->RetornoCpf->msg->ResultadoTXT;
                $consultaCpf->situacao = $json->RetornoCpf->DadosTitular->Situacao;
                $consultaCpf->tempoConsulta = $json->RetornoCpf->msg->TempoConsulta;
                $consultaCpf->titular = $json->RetornoCpf->DadosTitular->Titular;
                $consultaCpf->uf = $json->RetornoCpf->EnderecoTitular->UF;
                
                $consultaCpfRn = new \Models\Modules\Cadastro\ConsultaCpfRn();
                $consultaCpfRn->salvar($consultaCpf);
                
                
                /*
                return Array(
                    "nome" => $json->RetornoCpf->DadosTitular->Titular,
                    "dataNascimento" => $json->RetornoCpf->DadosTitular->DataNascimento,
                    "genero" => $json->RetornoCpf->DadosTitular->Genero,
                    "endereco" => $json->RetornoCpf->DadosTitular->Logradouro,
                    "numero" => $json->RetornoCpf->DadosTitular->Numero,
                    "complemento" => $json->RetornoCpf->DadosTitular->Complemento,
                    "bairro" => $json->RetornoCpf->DadosTitular->Bairro,
                    "cidade" => $json->RetornoCpf->DadosTitular->Cidade,
                    "uf" => $json->RetornoCpf->DadosTitular->UF,
                    "cep" => $json->RetornoCpf->DadosTitular->Cep,
                    "obito" => ($json->RetornoCpf->DadosObito->AnoObito > 0),
                    "anoObito" => $json->RetornoCpf->DadosObito->AnoObito
                );
                */
                
                return $consultaCpf;
            } else {
                if ($json->RetornoCpf->msg->Resultado > 4) {
                    throw new \Exception("Não foi possível realizar a consulta do CPF neste momento. Por favor, tente novamente mais tarde.");
                } else {
                    throw new \Exception($json->RetornoCpf->msg->ResultadoTXT);
                }
            }
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
        return null;
    }
    
    
    
    public static function cnpj ($cnpj) {
        $cnpj = str_replace(Array(".", "-", "/"), "", $cnpj);
        if (strlen($cnpj) != 14) {
            throw new \Exception("CNPJ inválido");
        }

        if (!\Utils\Validacao::cnpj($cnpj)) {
            throw new \Exception("CNPJ inválido");
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://ws.iwebservice.com.br/CNPJ/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"chave\"\r\n\r\n".self::$chave."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cnpj\"\r\n\r\n{$cnpj}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"formato\"\r\n\r\njson\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }

        $json = json_decode($response);

        if ($json->RetornoCnpj->msg->Resultado == 1) {

            $consultaCnpj = new \Models\Modules\Cadastro\ConsultaCnpj();


            $consultaCnpj->creditos = ($json->RetornoCnpj->msg->Creditos != nulll ? $json->RetornoCnpj->msg->Creditos : "");
            $consultaCnpj->resultado = ($json->RetornoCnpj->msg->Resultado != null ? $json->RetornoCnpj->msg->Resultado : "");
            $consultaCnpj->resultadoTxt = ($json->RetornoCnpj->msg->ResultadoTXT != null ? $json->RetornoCnpj->msg->ResultadoTXT : "");
            $consultaCnpj->tempoConsulta = ($json->RetornoCnpj->msg->TempoConsulta != null ? $json->RetornoCnpj->msg->TempoConsulta : "");


            $consultaCnpj->atividadePrincipal = ($json->RetornoCnpj->DadosEmpresa->AtividadePrincipal != null ? $json->RetornoCnpj->DadosEmpresa->AtividadePrincipal : "");
            $consultaCnpj->atividadesSecundarias = ($json->RetornoCnpj->DadosEmpresa->AtividadeSecundarias != null ? implode(", ", $json->RetornoCnpj->DadosEmpresa->AtividadeSecundarias) : "");
            $consultaCnpj->capitalSocial = ($json->RetornoCnpj->DadosEmpresa->CapitalSocial != null ? $json->RetornoCnpj->DadosEmpresa->CapitalSocial : "");
            $consultaCnpj->cnpj = ($json->RetornoCnpj->DadosEmpresa->Cnpj != null ? str_replace(Array(".", "/", "-"), "", $json->RetornoCnpj->DadosEmpresa->Cnpj) : "");
            $consultaCnpj->dataAbertura = ($json->RetornoCnpj->DadosEmpresa->DataAbertura != null ? $json->RetornoCnpj->DadosEmpresa->DataAbertura : "");
            $consultaCnpj->dataSituacaoCadastral = ($json->RetornoCnpj->DadosEmpresa->DataSituacaoCadastral != null ? $json->RetornoCnpj->DadosEmpresa->DataSituacaoCadastral : "");
            $consultaCnpj->dataSituacaoEspecial = ($json->RetornoCnpj->DadosEmpresa->DataSituacaoEspecial != null ? $json->RetornoCnpj->DadosEmpresa->DataSituacaoEspecial : "");
            $consultaCnpj->naturezaJuridica = ($json->RetornoCnpj->DadosEmpresa->NaturezaJuridica != null ? $json->RetornoCnpj->DadosEmpresa->NaturezaJuridica : "");
            $consultaCnpj->nomeEmpresa = ($json->RetornoCnpj->DadosEmpresa->NomeEmpresa != null ? $json->RetornoCnpj->DadosEmpresa->NomeEmpresa : "");
            $consultaCnpj->nomeFantasia = ($json->RetornoCnpj->DadosEmpresa->NomeFantasia != null ? $json->RetornoCnpj->DadosEmpresa->NomeFantasia : "");
            $consultaCnpj->motivoSituacaoCadastral = ($json->RetornoCnpj->DadosEmpresa->MotivoSituacaoCadastral != null ? $json->RetornoCnpj->DadosEmpresa->MotivoSituacaoCadastral : "");
            $consultaCnpj->situacaoCadastral = ($json->RetornoCnpj->DadosEmpresa->SituacaoCadastral != null ? $json->RetornoCnpj->DadosEmpresa->SituacaoCadastral : "");
            $consultaCnpj->situacaoEspecial = ($json->RetornoCnpj->DadosEmpresa->SituacaoEspecial != null ? $json->RetornoCnpj->DadosEmpresa->SituacaoEspecial : "");
            $consultaCnpj->tipo = ($json->RetornoCnpj->DadosEmpresa->Tipo != null ? $json->RetornoCnpj->DadosEmpresa->Tipo : "");


            $consultaCnpj->bairro = ($json->RetornoCnpj->EnderecoEmpresa->Bairro != null ? $json->RetornoCnpj->EnderecoEmpresa->Bairro : "");
            $consultaCnpj->cep = ($json->RetornoCnpj->EnderecoEmpresa->Cep != null ? $json->RetornoCnpj->EnderecoEmpresa->Cep : "");
            $consultaCnpj->complemento = ($json->RetornoCnpj->EnderecoEmpresa->Complemento != null ? $json->RetornoCnpj->EnderecoEmpresa->Complemento : "");
            $consultaCnpj->email = ($json->RetornoCnpj->EnderecoEmpresa->E-mail != null ? $json->RetornoCnpj->EnderecoEmpresa->E-mail : "");
            $consultaCnpj->logradouro = ($json->RetornoCnpj->EnderecoEmpresa->Logradouro != null ? $json->RetornoCnpj->EnderecoEmpresa->Logradouro : "");
            $consultaCnpj->municipio = ($json->RetornoCnpj->EnderecoEmpresa->Municipio != null ? $json->RetornoCnpj->EnderecoEmpresa->Municipio : "");
            $consultaCnpj->numero = ($json->RetornoCnpj->EnderecoEmpresa->Numero != null ? $json->RetornoCnpj->EnderecoEmpresa->Numero : "");
            $consultaCnpj->telefone = ($json->RetornoCnpj->EnderecoEmpresa->Telefone != null ? $json->RetornoCnpj->EnderecoEmpresa->Telefone : "");
            $consultaCnpj->uf = ($json->RetornoCnpj->EnderecoEmpresa->UF != null ? $json->RetornoCnpj->EnderecoEmpresa->UF : "");


            $consultaCnpj->cpfResponsavel = ($json->RetornoCnpj->DadosResponsavel->CpfResponsavel != null ? $json->RetornoCnpj->DadosResponsavel->CpfResponsavel : "");
            $consultaCnpj->nomeResponsavel = ($json->RetornoCnpj->DadosResponsavel->NomeResponsavel != null ? $json->RetornoCnpj->DadosResponsavel->NomeResponsavel : "");


            $consultaCnpj->socios = ($json->RetornoCnpj->QuadroSocios->Socios != null ? implode(", ", $json->RetornoCnpj->QuadroSocios->Socios) : "");

            $consultaCnpjRn = new \Models\Modules\Cadastro\ConsultaCnpjRn();
            $consultaCnpjRn->salvar($consultaCnpj);

            $statusConsumivelRn = new \Models\Modules\Cadastro\StatusConsumivelRn();
            $statusConsumivelRn->upateStatusConsultaDocumentos(str_replace(Array(".", ","), "", $json->RetornoCnpj->msg->Creditos));
            
            return $consultaCnpj;


        } else {
            if ($json->RetornoCnpj->msg->Resultado > 4) {
                throw new \Exception("Não foi possível realizar a consulta do CNPJ neste momento. Por favor, tente novamente mais tarde.");
            } else {
                throw new \Exception($json->RetornoCnpj->msg->ResultadoTXT);
            }
        }
    }
    
    
    
    public static function cep ($cep) {
        $cep = str_replace(Array("-"), "", $cep);
        if (strlen($cep) != 8) {
            throw new \Exception("CEP inválido");
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://ws.iwebservice.info/CEP/?chave=".self::$chave."&cep={$cep}&formato=JSON",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "postman-token: 9c70c647-89d1-5d27-299e-78356b9a50f7"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }

        $json = json_decode($response);
        
        if ($json->RetornoCep->msg->Resultado == 1) {

            $cMsg = $json->RetornoCep->msg;
            $cDadosCorreios = $json->RetornoCep->DadosCorreios;
            $cDadosIbge = $json->RetornoCep->DadosIbge;

            
            $endereco = Array(
                "cep" => ((isset($cDadosCorreios->Cep) && $cDadosCorreios->Cep != null) ? $cDadosCorreios->Cep : ""),
                "endereco" => ((isset($cDadosCorreios->Logradouro) && $cDadosCorreios->Logradouro != null) ? $cDadosCorreios->Logradouro : ""),
                "numero" => ((isset($cDadosCorreios->Numero) && $cDadosCorreios->Numero != null) ? $cDadosCorreios->Numero : ""),
                "bairro" => ((isset($cDadosCorreios->Bairro) && $cDadosCorreios->Bairro != null) ? $cDadosCorreios->Bairro : ""),
                "cidade" => ((isset($cDadosCorreios->Cidade) && $cDadosCorreios->Cidade != null) ? $cDadosCorreios->Cidade : ""),
                "uf" => ((isset($cDadosCorreios->UF) && $cDadosCorreios->UF != null)? $cDadosCorreios->UF : ""),
                "ibge" => (isset($cDadosIbge->CodIbgeMunicipio) ? $cDadosIbge->CodIbgeMunicipio : ""),
            );
            
            return $endereco;


        } else {
            /*
            if ($json->RetornoCnpj->msg->Resultado > 4) {
                throw new \Exception("Não foi possível realizar a consulta do CNPJ neste momento. Por favor, tente novamente mais tarde.");
            } else {
                throw new \Exception($json->RetornoCnpj->msg->ResultadoTXT);
            }
             * */
        }
        
        return null;
    }
    
}