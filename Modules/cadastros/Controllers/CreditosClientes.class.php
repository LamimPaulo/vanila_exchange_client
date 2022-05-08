<?php

namespace Modules\cadastros\Controllers;

class CreditosClientes {
    
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
            
            $clienteHasCreditoRn = new \Models\Modules\Cadastro\ClienteHasCreditoRn();
            $result = $clienteHasCreditoRn->conexao->listar("id_cliente = {$cliente->id}", "id");
            
            $lista = Array();
            foreach ($result as $clienteHasCredito) {
                $lista[$clienteHasCredito->idMoeda] = $clienteHasCredito;
            }
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("id = 1 OR ativo > 0", "principal DESC, id", null, null);
            
            $html = $this->listaHtmlCreditos($lista, $moedas);
            
            $cliente->id = \Utils\Criptografia::encriptyPostId($cliente->id);
            $json["cliente"] = $cliente;
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function listaHtmlCreditos($lista, $moedas) {
        ob_start();
        foreach ($moedas as $moeda) {
            $clienteHasCredito = (isset($lista[$moeda->id]) ? $lista[$moeda->id] : new \Models\Modules\Cadastro\ClienteHasCredito());
            $this->itemCredito($clienteHasCredito, $moeda);
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    private function itemCredito(\Models\Modules\Cadastro\ClienteHasCredito $clienteHasCredito, \Models\Modules\Cadastro\Moeda $moeda) {
        
        ?>
        <tr>
            <td>
                <input class="form-control moedaCreditoCliente" type="hidden" value="<?php echo $moeda->id ?>" />
                <?php echo $moeda->nome ?>
            </td>
            <td>
                <div class="form-group">
                    <input class="form-control modal-creditos-clientes-currency" type="text" id="moedaCreditoClienteCredito<?php echo $moeda->id ?>" value="<?php echo number_format($clienteHasCredito->volumeCredito, 8, ",", "") ?>" />
                </div>
            </td>
            <td>
                <div class="checkbox m-r-xs">
                    <input type="checkbox" id="moedaCreditoClienteAtivo<?php echo $moeda->id ?>" <?php echo ($clienteHasCredito->ativo > 0 ? "checked" : "") ?> >
                    <label for="ativo<?php echo $moeda->id ?>">
                        Ativo
                    </label>
                </div>
            </td>
        </tr>
        <?php
        
    }
    
    public function salvar($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            
            $moedas = \Utils\Post::getArray($params, "moedas", Array());
            
            $clienteHasCreditoRn = new \Models\Modules\Cadastro\ClienteHasCreditoRn();
            $clienteHasCreditoRn->salvarCreditos($cliente, $moedas);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Taxas Atualizadas com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}