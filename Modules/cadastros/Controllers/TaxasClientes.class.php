<?php

namespace Modules\cadastros\Controllers;

class TaxasClientes {
    
    public function carregarTaxasClientes($params) {
        
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            try {
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente inválido");
            }
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->conexao->listar("id > 1", "principal DESC, nome");
            
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();
            
            ob_start();
            foreach ($moedas as $moeda) {
                $clienteHasTaxa = $clienteHasTaxaRn->getByMoeda($cliente, $moeda->id);
                if ($clienteHasTaxa == null) {
                    $clienteHasTaxa = new \Models\Modules\Cadastro\ClienteHasTaxa();
                }
                ?>
                <tr>
                    <td style="vertical-align: middle;">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="width: 25px; height: 25px;" />
                        <?php echo $moeda->nome ?>
                    </td>
                    <td>
                        <div class="form-group">
                            <input type="text" id="dialogTaxasClienteTaxaCompraDireta-<?php echo $moeda->id ?>" class="form-control modal-taxas-clientes-currency text-center" value="<?php echo number_format($clienteHasTaxa->taxaCompraDireta, 2, ",", "") ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <input type="text" id="dialogTaxasClienteTaxaCompraIndireta-<?php echo $moeda->id ?>" class="form-control modal-taxas-clientes-currency text-center" value="<?php echo number_format($clienteHasTaxa->taxaCompraIndireta, 2, ",", "") ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <input type="text" id="dialogTaxasClienteTaxaVendaDireta-<?php echo $moeda->id ?>" class="form-control modal-taxas-clientes-currency text-center" value="<?php echo number_format($clienteHasTaxa->taxaVendaDireta, 2, ",", "") ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <input type="text" id="dialogTaxasClienteTaxaVendaIndireta-<?php echo $moeda->id ?>" class="form-control modal-taxas-clientes-currency text-center" value="<?php echo number_format($clienteHasTaxa->taxaVendaIndireta, 2, ",", "") ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="checkbox m-r-xs">
                            <input type="checkbox" id="dialog-taxas-cliente-coin-id-<?php echo $moeda->id ?>"  class="dialog-taxas-cliente-coin-id" value="<?php echo $moeda->id ?>" <?php echo ($clienteHasTaxa->utilizar ? "checked" : "")?>>
                            <label for="dialog-taxas-cliente-coin-id-<?php echo $moeda->id ?>">
                                Utilizar
                            </label>
                        </div>
                    </td>
                </tr>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
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
    
    public function salvar($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            $taxas = \Utils\Post::getArray($params, "taxas", Array());
            
            $clienteHasTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();
            $clienteHasTaxaRn->salvarTaxas($cliente, $taxas);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Taxas Atualizadas com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}