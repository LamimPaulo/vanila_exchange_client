<?php

namespace Modules\acesso\Controllers;

class Docs {
    
    private $idioma = null;
    
    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("docs_aceitacao", IDIOMA);
        if (!\Utils\Geral::isLogado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_LOGIN);
        }
        
        if (!\Utils\Geral::isAutenticado()) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_TWOFACTORAUTH);
        }
        
        
        $cliente = \Utils\Geral::getCliente();
        
        $documentoSistemaRn = new \Models\Modules\Cadastro\DocumentoSistemaRn();
        $clienteHaDocumentoSistemaRn = new \Models\Modules\Cadastro\ClienteHasDocumentoSistemaRn();
        $aceitou = true;
        
        $codigos = $documentoSistemaRn->getCodigos();
        foreach ($codigos as $cod) {
            $doc = $documentoSistemaRn->getDocumentoSistema($cod);
            
            if ($doc != null) {
                $aceite = $clienteHaDocumentoSistemaRn->getAceiteCliente($cliente, $doc);

                if ($aceite == null) {
                    $aceitou = false;
                }
            }
        }        
        if ($aceitou) {        
            $notificacaoMoedaHasLidoRn = new \Models\Modules\Cadastro\NotificacaoMoedaHasLidoRn();
            $result = $notificacaoMoedaHasLidoRn->notificacoesNaoLidas($cliente);
            if(sizeof($result) > 0){
                \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_NOTIFICACAO_MOEDA_CLIENTE);
            } else {
                \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
            }            
        }
    }
    
    
    public function index($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $documentoSistemaRn = new \Models\Modules\Cadastro\DocumentoSistemaRn();
            $clienteHaDocumentoSistemaRn = new \Models\Modules\Cadastro\ClienteHasDocumentoSistemaRn();
            $lista = Array();
            
            $codigos = $documentoSistemaRn->getCodigos(true);
            foreach ($codigos as $cod) {
                $doc = $documentoSistemaRn->getDocumentoSistema($cod);

                if ($doc != null) {
                    $aceite = $clienteHaDocumentoSistemaRn->getAceiteCliente($cliente, $doc);

                    if ($aceite == null) {
                        $lista[] = $doc;
                    }
                }
            }
            
            
            $params["lista"] = $lista;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("aceite_docs", $params);
    }
    
    
    public function salvar($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            
            $documentoSistemaRn = new \Models\Modules\Cadastro\DocumentoSistemaRn();
            $clienteDocumentoSistemaRn = new \Models\Modules\Cadastro\ClienteHasDocumentoSistemaRn();
            
            $codigos = $documentoSistemaRn->getCodigos();
            
            $json["aceites"] = Array();
            $arrayFaltou = Array();
            foreach ($codigos as $cod) {
                $doc = $documentoSistemaRn->getDocumentoSistema($cod);
                
                if ($doc != null) {
                    $aceite = $clienteDocumentoSistemaRn->getAceiteCliente($cliente, $doc);

                    if ($aceite == null) {
                        $a = \Utils\Post::getBooleanAsInt($params, $doc->codigo, 0);
                        
                        if ($a > 0) {
                            $clienteHasDocumentoSistema = new \Models\Modules\Cadastro\ClienteHasDocumentoSistema(Array(
                                "id_cliente" => $cliente->id,
                                "id_documento_sistema" => $doc->id
                            ));
                            $clienteDocumentoSistemaRn->salvar($clienteHasDocumentoSistema);
                            
                            $json["aceites"][] = $doc->codigo;
                        } else {
                            $arrayFaltou[] = $doc->descricao;
                        }
                        
                    }
                }
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}
