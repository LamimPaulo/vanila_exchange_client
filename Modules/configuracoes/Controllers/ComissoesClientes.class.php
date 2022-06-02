<?php

namespace Modules\configuracoes\Controllers;

class ComissoesClientes {
    
    public function index($params) {
        
        try {
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("comissoes_clientes", $params);
    }
    
    public function salvar($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            
            $clienteHasComissaoRn = new \Models\Modules\Cadastro\ClienteHasComissaoRn();
            
            $clienteHasComissao = new \Models\Modules\Cadastro\ClienteHasComissao();
            $clienteHasComissao->id = 0;
            $clienteHasComissao->idCliente = null;
            $clienteHasComissao->boleto = \Utils\Post::getNumeric($params, "boleto", 0);
            $clienteHasComissao->compra = \Utils\Post::getNumeric($params, "compra", 0);
            $clienteHasComissao->remessa = \Utils\Post::getNumeric($params, "remessa", 0);
            $clienteHasComissao->saque = \Utils\Post::getNumeric($params, "saque", 0);
            $clienteHasComissao->deposito = \Utils\Post::getNumeric($params, "deposito", 0);
            $clienteHasComissao->venda = \Utils\Post::getNumeric($params, "venda", 0);
            $clienteHasComissao->utilizar = \Utils\Post::getBooleanAsInt($params, "utilizar", 0);
            
            
            $clienteHasComissaoRn->salvar($clienteHasComissao);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Taxas Atualizadas com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}