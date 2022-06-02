<?php

namespace Documento;

class IWebService {
   
    protected $chave;
    
    public function __construct() {
        $this->chave = "X1U5K-3D4PJ-D63R2-REPL8";
    }
    
    public function consultar($documento = null, $dataNascimento = null) {
        
        $retorno = Array();
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://ws.iwebservice.com.br/CPF/?chave={$this->chave}&cpf={$documento}&dataNascimento={$dataNascimento}&formato=json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        if($httpCode != 200){
            \Utils\Notificacao::notificar("Falha na consulta de documento", true, false, null, true);
            
            return null;
        }
        
        $object = json_decode($response);
        
        if($object->RetornoCpf->msg->Resultado == 1){
            switch ($object->RetornoCpf->DadosTitular->Situacao) {
                case "REGULAR":
                    $retorno["status"] = "regular";
                    break;
                case "CANCELADA":
                    $retorno["status"] = "cancelada";
                    break;
                case "NULA":
                    $retorno["status"] = "nula";
                    break;
                case "TITULAR FALECIDO":
                    $retorno["status"] = "falecido";
                    break;
                default:
                    $retorno["status"] = null;
            }
            
            $retorno["nome"] = $object->RetornoCpf->DadosTitular->Titular;
            $retorno["mae"] = $object->RetornoCpf->DadosTitular->NomeMae;
            $retorno["genero"] = $object->RetornoCpf->DadosTitular->Genero;
            $retorno["dataNascimento"] = $object->RetornoCpf->DadosTitular->DataNascimento;
            $retorno["cpf"] = $object->RetornoCpf->DadosTitular->Cpf;
            $retorno["logradouro"] = $object->RetornoCpf->EnderecoTitular->Logradouro;
            $retorno["numero"] = $object->RetornoCpf->EnderecoTitular->Numero;
            $retorno["complemento"] = $object->RetornoCpf->EnderecoTitular->Complemento;
            $retorno["bairro"] = $object->RetornoCpf->EnderecoTitular->Bairro;
            $retorno["uf"] = $object->RetornoCpf->EnderecoTitular->UF;
            $retorno["cep"] = $object->RetornoCpf->EnderecoTitular->Cep;
            $retorno["response"] = $response;
            
            
           return $retorno; 
        } else {
            return null;
        }
    }
    
    public function consultarCnpj($cnpj = null) {
        
        $retorno = Array();
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "http://ws.iwebservice.com.br/CNPJ/?chave={$this->chave}&cnpj={$cnpj}&formato=json",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $object = json_decode($response);
        
        if($object->RetornoCnpj->msg->Resultado == 1){
            
            $retorno["Cnpj"] = $object->RetornoCnpj->DadosEmpresa->Cnpj;
            $retorno["Tipo"] = $object->RetornoCnpj->DadosEmpresa->Tipo;
            $retorno["CapitalSocial"] = $object->RetornoCnpj->DadosEmpresa->CapitalSocial;
            $retorno["NomeEmpresa"] = $object->RetornoCnpj->DadosEmpresa->NomeEmpresa;
            $retorno["NomeFantasia"] = $object->RetornoCnpj->DadosEmpresa->NomeFantasia;
            $retorno["DataAbertura"] = $object->RetornoCnpj->DadosEmpresa->DataAbertura;
            $retorno["SituacaoCadastral"] = $object->RetornoCnpj->DadosEmpresa->SituacaoCadastral;
            $retorno["DataSituacaoCadastral"] = $object->RetornoCnpj->DadosEmpresa->DataSituacaoCadastral;
            $retorno["MotivoSituacaoCadastral"] = $object->RetornoCnpj->DadosEmpresa->MotivoSituacaoCadastral;
            $retorno["SituacaoEspecial"] = $object->RetornoCnpj->DadosEmpresa->SituacaoEspecial;
            $retorno["DataSituacaoEspecial"] = $object->RetornoCnpj->DadosEmpresa->DataSituacaoEspecial;
            $retorno["AtividadePrincipal"] = $object->RetornoCnpj->DadosEmpresa->AtividadePrincipal;
            $retorno["NaturezaJuridica"] = $object->RetornoCnpj->DadosEmpresa->NaturezaJuridica;
            
            
            //Endereço
            $retorno["Logradouro"] = $object->RetornoCnpj->EnderecoEmpresa->Logradouro;
            $retorno["Numero"] = $object->RetornoCnpj->EnderecoEmpresa->Numero;
            $retorno["Complemento"] = $object->RetornoCnpj->EnderecoEmpresa->Complemento;
            $retorno["Bairro"] = $object->RetornoCnpj->EnderecoEmpresa->Bairro;
            $retorno["Municipio"] = $object->RetornoCnpj->EnderecoEmpresa->Municipio;
            $retorno["UF"] = $object->RetornoCnpj->EnderecoEmpresa->UF;
            $retorno["Cep"] = $object->RetornoCnpj->EnderecoEmpresa->Cep;
            $retorno["Telefone"] = $object->RetornoCnpj->EnderecoEmpresa->Telefone;
            $retorno["E-mail"] = $object->RetornoCnpj->EnderecoEmpresa->{'E-mail'};
            
            //Dados responsáveis
            $retorno["CpfResponsavel"] = $object->RetornoCnpj->DadosResponsavel->CpfResponsavel;
            $retorno["NomeResponsavel"] = $object->RetornoCnpj->DadosResponsavel->NomeResponsavel;
                        
            $retorno["response"] = $response;
            
            
           return $retorno; 
        } else {
            return null;
        }
    }
    

}
