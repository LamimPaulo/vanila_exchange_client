<?php

namespace Modules\contasempresa\Controllers;

class ResumoContas {
    
    
    private $codigoModulo = "contasempresa";
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar(null, "principal DESC, simbolo");
            
            $params["moedas"] = $moedas;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_resumo_contas", $params);
    }
    
    public function listarContas($params) {
        
        try {
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params, "idMoeda", 0);
            
            $saldoMinBtc = \Utils\Post::getNumeric($params, "saldoMinCurrency", 0);
            $filtro = \Utils\Post::get($params, "filtro", NULL);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contas = $contaCorrenteBtcRn->resumo($filtro, $moeda->id, $saldoMinBtc);
            
            $dados = $this->htmlListaContas($contas);
            
            $json = $dados;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaContas($contas) {
        $saldoCurrency = 0;
        $moeda = \Modules\principal\Controllers\Principal::getCurrency();
        ob_start();
        if (sizeof($contas)) {
            $i = 0;
            foreach ($contas as $conta) {
                $saldoCurrency += number_format(($conta["saldoCurrency"]+$conta["bloqueadoCurrency"]), $moeda->casasDecimais, ".", "");
                $i++;
                $this->htmlConta($conta, $i);
            }
        } else {
            ?>
            <tr>
                <td colspan="5">Nenhum cadastro para os filtros informados</td>
            </tr>
            <?php
        }
        
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return Array("html" => $html, "currency" => $saldoCurrency);
    }
    
    
    private function htmlConta($conta, $i = 0) {
        $moeda = \Modules\principal\Controllers\Principal::getCurrency();
        $cliente = $conta["cliente"];
        ?>
        <tr>
            <td id="cli-<?php echo $cliente->id ?>">
                <?php echo ($i > 0 ? "{$i} - " : "")?><?php echo $cliente->nome ?>
            </td>
            <td style="color: <?php echo $moeda->mainColor ?>; text-align: center;">
                <strong><?php echo number_format($conta["saldoCurrency"], $moeda->casasDecimais, ".", "") ?> <?php echo $moeda->simbolo ?></strong>
            </td>
            <td style="color: <?php echo $moeda->mainColor ?>; text-align: center;">
                <strong><?php echo number_format($conta["bloqueadoCurrency"], $moeda->casasDecimais, ".", "") ?> <?php echo $moeda->simbolo ?></strong>
            </td> 
            <td style="color: <?php echo $moeda->mainColor ?>; text-align: center;">
                <strong><?php echo number_format($conta["saldoCurrency"] + $conta["bloqueadoCurrency"], $moeda->casasDecimais, ".", "") ?> <?php echo $moeda->simbolo ?></strong>
            </td>     
            <td class="text-center"> 
                <button class="btn btn-info" type="button" onclick="modalCobranca(<?php echo $cliente->id ?>);">
                    Lançar Cobrança
                </button>
            </td>
        </tr>
        <?php
    }
    
    
    public function cobrar($params) {
        
        try {
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params, "moeda", 0);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::get($params, "cliente", 0);
            $descricaoCliente = \Utils\Post::get($params, "descricaoCliente", NULL);
            $descricaoEmpresa = \Utils\Post::get($params, "descricaoEmpresa", NULL);
            
            
            if ($moeda->id == 1) {
                $contaCorrenteBrlRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $contaCorrenteBrlRn->cobranca($cliente, $descricaoCliente, $descricaoEmpresa, $valor);
                
            } else {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get($moeda->id);
                
                if ($moeda == null) {
                    throw new \Exception("Moeda inválida");
                }
                
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $contaCorrenteBtcRn->cobranca($cliente, $descricaoCliente, $descricaoEmpresa, $valor, $moeda);
            }
            
            $json["sucesso"] = true;
            
            $json["mensagem"] = "Cobrança efetuada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}