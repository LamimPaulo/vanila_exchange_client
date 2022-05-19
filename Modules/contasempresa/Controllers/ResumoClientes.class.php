<?php

namespace Modules\contasempresa\Controllers;

class ResumoClientes {
    
    
    private $codigoModulo = "contasempresa";
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("id > 1 AND ativo > 0", "principal DESC, simbolo");
            
            $params["moedas"] = $moedas;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_resumo_clientes", $params);
    }
    
    public function listarContas($params) {
        
        try {
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            $saldoMinBrl = \Utils\Post::getNumeric($params, "saldoMinBrl", 0);
            $saldoMinBtc = \Utils\Post::getNumeric($params, "saldoMinCurrency", 0);
            $filtro = \Utils\Post::get($params, "filtro", NULL);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $lista = $contaCorrenteBtcRn->resumoClientes($filtro, $saldoMinBrl, $saldoMinBtc);
            //exit(print_r($lista));
            $dados = $this->htmlListaContas($lista);
            
            $json = $dados;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaContas($clientes) {
        $dados = Array();
        
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moedas = $moedaRn->listar("id > 1 AND ativo > 0", "principal DESC, simbolo");
        
        $saldos = Array();
        ob_start();
        if (sizeof($clientes)) {
            foreach ($clientes as $cliente) {
                $m = $cliente["moedas"];
                foreach ($m as $d) {
                    if (!isset($saldos[$d["moeda"]])) {
                        $saldos[$d["moeda"]] = 0;
                    }
                    
                    $saldos[$d["moeda"]] += number_format($d["saldo"], 25, ".", "");
                }
                
                $this->htmlConta($cliente, $moedas);
            }
        } else {
            ?>
            <tr>
                <td colspan="<?php echo "".(sizeof($moedas)+1) ?>">Nenhum cadastro para os filtros informados</td>
            </tr>
            <?php
        }
        
        
        $dados["html"] = ob_get_contents();
        ob_end_clean();
        $dados["saldos"] = Array();
        
        foreach ($saldos as $key=>$s) {
            if ($key == "real") {
                $s = number_format($s, 2, ",", ".");
            } else {
                $s = number_format($s, 8, ".", ",");
            }
            $dados["saldos"][] = Array("moeda" => str_replace("$", "", $key), "saldo" => $s);
        }
        
        return $dados;
    }
    
    
    private function htmlConta($cliente, $moedas) {
        
        $m = $cliente["moedas"];
        ?>
        <tr>
            <td id="cli-<?php echo $cliente["id"] ?>">
                <?php echo $cliente["nome"] ?>
            </td>
            <td style="color: #1ab394; text-align: center;">
                <strong><?php echo number_format((isset($m[1]) ? $m[1]["saldo"] : 0), 2, ",", ".") ?></strong>
            </td>
            <?php 
            foreach ($moedas as $moeda) {
                if (isset($m[$moeda->id])) {
                    $saldo = number_format($m[$moeda->id]["saldo"], 8, ".", ",");
                } else {
                    $saldo = number_format(0, $moeda->casasDecimais, ".", ",");
                }
                ?>
                <td style="color: <?php echo $moeda->mainColor ?>; text-align: center;">
                    <strong><?php echo $saldo ?></strong>
                </td>
                <?php
            }
            ?>
        </tr>
        <?php
    }
    
    
}