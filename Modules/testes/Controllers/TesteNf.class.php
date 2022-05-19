<?php

namespace Modules\testes\Controllers;

class TesteNf {
    
    public function emitir() {
        try { 
            
            $retorno = \ENotasGW\NotaFiscal::emitir();
            
            print_r($retorno);
            
            
            /* {"nfeId":"5f18c113-c7a6-478e-880e-7fc020850300"} */
        } catch (\Exception $ex) {
            print_r($ex);
        }
    }
    
    
    
    public function consultar($params) {
        
        try { 
                "e2dde6e7-c621-4bdf-83e5-2a301c850300";
                "77c1e0bd-e2cd-426d-a00d-5c4b1e850300";
                "5f18c113-c7a6-478e-880e-7fc020850300";
                "7f5a51d1-928c-4bd9-b055-b58424850300";
                "a1335ab9-3779-4744-ab58-b8de25850300";
                "adb4a001-f9ab-439c-8d91-f6e826850300";
                "4fa49a98-c637-49ec-98dd-288e2d850300";
                "06e9074e-ed22-4a9e-9402-793d39850300";
                
                $get = $params["_parameters"];

                $codigoNota = isset($get[0]) ? $get[0] : "";

                $retorno = \ENotasGW\NotaFiscal::consultar($codigoNota);
                print_r($retorno);
                
                
                    /*
                     * 
                     * {
                "id": "5f18c113-c7a6-478e-880e-7fc020850300",
                "tipo": "NFS-e",
                "idExterno": "teste-03",
                "status": "AutorizacaoSolicitada",
                "motivoStatus": null,
                "ambienteEmissao": "Homologacao",
                "enviadaPorEmail": false,
                "dataCriacao": "2018-02-20T18:14:24Z",
                "dataUltimaAlteracao": "2018-02-20T18:14:27Z",
                "cliente": {
                    "tipoPessoa": "F",
                    "nome": "DOUGLAS POMPERMAYER",
                    "email": "vagnercarvalho.vfc@gmail.com",
                    "cpfCnpj": "05070222546",
                    "inscricaoMunicipal": null,
                    "telefone": "19997449613",
                    "endereco": {
                        "uf": "SP",
                        "cidade": "Louveira",
                        "logradouro": "Estrada Francisco Pagoto",
                        "numero": "1295",
                        "complemento": "Chacara Italia",
                        "bairro": "Ipiranga",
                        "cep": "13290000"
                    }
                },
                "servico": {
                    "descricao": "Teste de Serviço 1",
                    "aliquotaIss": 5,
                    "issRetidoFonte": false,
                    "codigoServicoMunicipio": "586",
                    "itemListaServicoLC116": "10.05",
                    "cnae": null,
                    "municipioPrestacaoServico": 3525904
                },
                "naturezaOperacao": "1",
                "valorCofins": 0,
                "valorCsll": 0,
                "valorInss": 0,
                "valorIr": 0,
                "valorPis": 0,
                "valorTotal": 100,
                "observacoes": null
            }
                     * 
                     */
                
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    
    
    public static function pdf($params) {
        
        try { 
            $get = $params["_parameters"];
            $codigoNota = isset($get[0]) ? $get[0] : "";

            $retorno = \ENotasGW\NotaFiscal::imprimir($codigoNota, "pdf");
            
            print_r($retorno);
            
            
            /* {"nfeId":"5f18c113-c7a6-478e-880e-7fc020850300"} */
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    
    
    public static function cancelar($params) {
        
        try { 
            $get = $params["_parameters"];
            $codigoNota = isset($get[0]) ? $get[0] : "";

            $retorno = \ENotasGW\NotaFiscal::cancelar($codigoNota);
            
            print_r($retorno);
            
            
            /* {
                "nfeId": "string"
              } 
             */
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    
    public static function listar($params) {
        
        try { 
            $get = $params["_parameters"];

            $pageNumber = "0";
            $pageSize = "50"; 
            $filtro = "status eq 'EmProcessoDeAutorizacao'";
            $sortBy = "dataCriacao";
            $sortDirection = "ASC";
            
            $retorno = \ENotasGW\NotaFiscal::listar($pageNumber, $pageSize, $filtro, $sortBy, $sortDirection);
            
            print_r($retorno);
            
            
            /* 
             * 
             * {
    "totalRecords": 1,
    "data": [
        {
            "id": "adb4a001-f9ab-439c-8d91-f6e826850300",
            "tipo": "NFS-e",
            "idExterno": "teste-06",
            "status": "EmProcessoDeAutorizacao",
            "motivoStatus": null,
            "ambienteEmissao": "Homologacao",
            "enviadaPorEmail": false,
            "dataCriacao": "2018-02-20T18:58:29Z",
            "dataUltimaAlteracao": "2018-02-20T18:58:32Z",
            "cliente": {
                "tipoPessoa": "F",
                "nome": "DOUGLAS POMPERMAYER",
                "email": "vagnercarvalho.vfc@gmail.com",
                "cpfCnpj": "05070222546",
                "inscricaoMunicipal": null,
                "telefone": "19997449613",
                "endereco": {
                    "uf": "SP",
                    "cidade": "Louveira",
                    "logradouro": "Estrada Francisco Pagoto",
                    "numero": "1295",
                    "complemento": "Chacara Italia",
                    "bairro": "Ipiranga",
                    "cep": "13290000"
                }
            },
            "numeroRps": 6,
            "serieRps": "1",
            "dataCompetenciaRps": "2018-02-20T18:58:29Z",
            "servico": {
                "descricao": "Teste de Serviço 1",
                "aliquotaIss": 5,
                "issRetidoFonte": false,
                "codigoServicoMunicipio": "10.05.03 / 594",
                "itemListaServicoLC116": "10.05",
                "cnae": null,
                "municipioPrestacaoServico": 3525904
            },
            "naturezaOperacao": "1",
            "valorCofins": 0,
            "valorCsll": 0,
            "valorInss": 0,
            "valorIr": 0,
            "valorPis": 0,
            "valorTotal": 100,
            "observacoes": null
        }
    ]
}
             * 
             */
        } catch (\Exception $ex) {
            print_r($ex);
        }
        
    }
    
    
    public function upgradePerfilNegado() {
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $cliente = $clienteRn->getByEmail("vagnercarvalho-02@hotmail.com");
        
        \Email\BoasVindas::send($cliente);
        
    }
    
}