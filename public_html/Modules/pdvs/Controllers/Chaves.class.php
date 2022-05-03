<?php

namespace Modules\pdvs\Controllers;


class Chaves {
    
    private  $codigoModulo = "recebimentospdv";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function prepararGerar($params) {
        
        try {
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception("Vocë precisa registrar-se como cliente para efetuar essa operação");
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            if (empty($cliente->pin)) {
                throw new \Exception("Você precisa cadastrar um PIN para efetuar essa operação. Acesso o seu perfil e tente novamente");
            }
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function gerar($params) {
        try {
            
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para editar o PDV");
            }
            
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $pin = \Utils\Post::get($params, "pin", NULL);
            
            if (empty($pin)) {
                throw new \Exception("É necessário informar o PIN");
            }
            
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception("Vocë precisa registrar-se como cliente para efetuar essa operação");
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception("O PIN informado náo confere com o PIN cadastrado");
            }
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            try{
                $pontoPdvRn->carregar($pontoPdv, true, true);
            } catch (\Exception $ex) {
                throw new \Exception("PDV não localizado no sistema");
            }
            
            if ($pontoPdv->estabelecimento->idCliente != $cliente->id) {
                throw new \Exception("Não é permitido acessar chaves de outros clientes");
            }
            
            $chavePdv = new \Models\Modules\Cadastro\ChavePdv();
            $chavePdv->id = 0;
            $chavePdv->chave = sha1("NC-PDV-KEY". time()."NC_PDV_KEY");
            $chavePdv->chaveHomologacao = sha1("NC-PDV-KEYH". time()."NC_PDV_KEYH");
            $chavePdv->idPontoPdv = $pontoPdv->id;
            
            $chavePdvRn = new \Models\Modules\Cadastro\ChavePdvRn();
            $chavePdvRn->salvar($chavePdv);
            
            $json["chave"] = $chavePdv->chave;
            $json["chaveh"] = $chavePdv->chaveHomologacao;
            $json["sucesso"] = true;
            $json["mensagem"] = "Chave gerada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function mostrar($params) {
        try {
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $pin = \Utils\Post::get($params, "pin", NULL);
            
            if (empty($pin)) {
                throw new \Exception("É necessário informar o PIN");
            }
            
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception("Vocë precisa registrar-se como cliente para efetuar essa operação");
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception("O PIN informado náo confere com o PIN cadastrado");
            }
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            try{
                $pontoPdvRn->carregar($pontoPdv, true, true);
            } catch (\Exception $ex) {
                throw new \Exception("PDV não localizado no sistema");
            }
            
            if ($pontoPdv->estabelecimento->idCliente != $cliente->id) {
                throw new \Exception("Não é permitido acessar chaves de outros clientes");
            }
            
            $chavePdvRn = new \Models\Modules\Cadastro\ChavePdvRn();
            $chavePdv = $chavePdvRn->getByPontoPdv($pontoPdv);
            
            if ($chavePdv == null) {
                throw new \Exception("Você ainda não cadastrou uma API Key");
            }
            
            $json["chave"] = $chavePdv->chave;
            $json["chaveh"] = $chavePdv->chaveHomologacao;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function desativar($params) {
        try {
            
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para editar o PDV");
            }
            
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $pin = \Utils\Post::get($params, "pin", NULL);
            
            if (empty($pin)) {
                throw new \Exception("É necessário informar o PIN");
            }
            
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception("Vocë precisa registrar-se como cliente para efetuar essa operação");
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception("O PIN informado náo confere com o PIN cadastrado");
            }
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            try{
                $pontoPdvRn->carregar($pontoPdv, true, true);
            } catch (\Exception $ex) {
                throw new \Exception("PDV não localizado no sistema");
            }
            
            if ($pontoPdv->estabelecimento->idCliente != $cliente->id) {
                throw new \Exception("Não é permitido acessar chaves de outros clientes");
            }
            
            $chavePdvRn = new \Models\Modules\Cadastro\ChavePdvRn();
            $chavePdv = $chavePdvRn->getByPontoPdv($pontoPdv);
            
            if ($chavePdv == null) {
                throw new \Exception("Você ainda não cadastrou uma API Key");
            }
            
            $chavePdvRn->desativar($chavePdv);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}