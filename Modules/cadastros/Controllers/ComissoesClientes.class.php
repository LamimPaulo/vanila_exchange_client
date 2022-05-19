<?php

namespace Modules\cadastros\Controllers;

class ComissoesClientes {
    
    public function carregar($params) {
        
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            try {
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente inválido");
            }
            
            $clienteHasComissaoRn = new \Models\Modules\Cadastro\ClienteHasComissaoRn();
            $clienteHasComissao = $clienteHasComissaoRn->getByCliente($cliente->id);
            
            if ($clienteHasComissao == null) {
                $clienteHasComissao = new \Models\Modules\Cadastro\ClienteHasComissao();
            }
            
            $cliente->id = \Utils\Criptografia::encriptyPostId($cliente->id);
            $json["cliente"] = $cliente;
            $json["boleto"] = number_format(($clienteHasComissao->boleto), 2, ",", "");
            $json["compra"] = number_format(($clienteHasComissao->compra), 2, ",", "");
            $json["deposito"] = number_format(($clienteHasComissao->deposito), 2, ",", "");
            $json["remessa"] = number_format(($clienteHasComissao->remessa), 2, ",", "");
            $json["saque"] = number_format(($clienteHasComissao->saque), 2, ",", "");
            $json["venda"] = number_format(($clienteHasComissao->venda), 2, ",", "");
            $json["utilizar"] = $clienteHasComissao->utilizar;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            
            $clienteHasComissaoRn = new \Models\Modules\Cadastro\ClienteHasComissaoRn();
            
            $clienteHasComissao = new \Models\Modules\Cadastro\ClienteHasComissao();
            $clienteHasComissao->id = 0;
            $clienteHasComissao->idCliente = \Utils\Post::getEncrypted($params, "cliente", 0);
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