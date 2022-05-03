<?php

namespace Modules\contasempresa\Controllers;


class TransferenciaEmpresa {
    
    private $modulo = "constasempresa";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            
            $saldoBtc = $contaCorrenteBtcEmpresaRn->calcularSaldoConta($moeda->id);
            $saldoReais = $contaCorrenteReaisEmpresaRn->calcularSaldoConta();
            
            $params["saldobtc"] = number_format($saldoBtc, $moeda->casasDecimais, ".", "");
            $params["saldoreais"] = number_format($saldoReais, 2, ".", "");
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("transferencia_empresa", $params);
    }
    
    
    public function calcularSaldoEmpresa($params) {
        try {
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            
            $saldoBtc = $contaCorrenteBtcEmpresaRn->calcularSaldoConta($moeda->id);
            $saldoReais = $contaCorrenteReaisEmpresaRn->calcularSaldoConta();
            
            $json["saldobtc"] = number_format($saldoBtc, $moeda->casasDecimais, ".", "");
            $json["saldoreais"] = number_format($saldoReais, 2, ".", "");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function transferirCurrency($params) {
        try {
            
            
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA_TRANSFERENCIA, \Utils\Constantes::CADASTRAR)) {
                throw new \Exception("Você não tem permissão para realizar transferências");
            }
            
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $enderecoBitcoin = \Utils\Post::get($params, "enderecoBitcoin", null);
            $descricao = \Utils\Post::get($params, "descricao", null);
            
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteBtcEmpresaRn->transferir($enderecoBitcoin, $valor, $descricao, $moeda->id);

            $saldo = $contaCorrenteBtcEmpresaRn->calcularSaldoConta($moeda->id);
            
            $json["saldo"] = number_format($saldo, $moeda->casasDecimais, ",", "");
            $json["sucesso"] = true;
            $json["mensagem"] = "Transferência realizada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function filtrarTransferenciasCurrency($params) {
        try {
            
                                                
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $nresultado = \Utils\Post::get($params, "nregistros", "T");
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $dados = $contaCorrenteBtcEmpresaRn->filtrar($dataInicial, $dataFinal, \Utils\Constantes::SAIDA, $filtro, $moeda->id, "S", $nresultado);
            
            $d = $this->htmlListaTransferenciasCurrency($dados);
            
            $json["html"] = $d["html"];
            $json["popover"] = $d["popover"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    private function htmlListaTransferenciasCurrency($lista) {
        $total  = 0;
        $popover = Array();
        $moeda = \Modules\principal\Controllers\Principal::getCurrency();
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $contaCorrenteBtcEmpresa) {
                
                $popover[$contaCorrenteBtcEmpresa->id] = $this->popoverTransferenciaBtc($contaCorrenteBtcEmpresa);
                                

                $total += $contaCorrenteBtcEmpresa->valor;
                $this->itemListaTransferenciaCurrency($contaCorrenteBtcEmpresa);
            }
            
            ?>
            <tr>
                <td colspan='2'>
                    <strong>Valor total: </strong>
                </td>
                <td colspan='3' style='text-align: right;'>
                    <strong><?php echo number_format($total, $moeda->casasDecimais, ".", ""); ?> <?php echo $moeda->simbolo ?></strong>
                </td>
            </tr>
            <?php
            
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "popover" => $popover);
    }
    
    
    private function itemListaTransferenciaCurrency(\Models\Modules\Cadastro\ContaCorrenteBtcEmpresa $contaCorrenteBtcEmpresa) { 
        
        ?>
            <tr >
                <td><?php echo $contaCorrenteBtcEmpresa->id ?></td>
                <td class="text-center"><?php echo $contaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                <td><?php echo $contaCorrenteBtcEmpresa->descricao ?></td>
                <td class="text-center"><?php echo number_format($contaCorrenteBtcEmpresa->valor, 8, ".", "")?></td>
                <td class="text-center">
                    <a tabindex="0" class="btn btn-xs btn-info btn-popover-currency" role="button" data-controle='<?php echo $contaCorrenteBtcEmpresa->id?>' data-toggle="popover" data-trigger="focus" style="font-size: 10px">Log</a>
                </td>
            </tr>
            
        <?php
    }
    
    
    private function popoverTransferenciaBtc(\Models\Modules\Cadastro\ContaCorrenteBtcEmpresa $contaCorrenteBtcEmpresa) {
        
        $logContaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\LogContaCorrenteBtcEmpresaRn();
        $result = $logContaCorrenteBtcEmpresaRn->lista("id_conta_corrente_btc_empresa = {$contaCorrenteBtcEmpresa->id}", "data", null, null, TRUE, false);
        ob_start();
        if (sizeof($result) > 0) {
            
            foreach ($result as $logContaCorrenteBtcEmpresa) {
                
                ?>
            <strong><?php echo $logContaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?> - <?php echo $logContaCorrenteBtcEmpresa->usuario->nome ?> : 
                <?php echo $logContaCorrenteBtcEmpresa->descricao?>.</strong> <br>
                <?php
            }
        } else {
            ?>
            <br><br>
            <strong>Nenhum log para o registro selecionado.</strong>
            <br><br>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function transferirBrl($params) {
        try {
            
            
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "idClienteTo", 0);
            $descricao = \Utils\Post::get($params, "descricao", null);
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->transferir($cliente, $valor, $descricao);

            $saldo = $contaCorrenteReaisEmpresaRn->calcularSaldoConta();
            
            $json["saldo"] = number_format($saldo, 2, ",", "");
            $json["sucesso"] = true;
            $json["mensagem"] = "Transferência realizada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function filtrarTransferenciasBrl($params) {
        try {
                                                            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");

            $filtro = \Utils\Post::get($params, "filtro", "T");
            $nresultado = \Utils\Post::get($params, "nregistros", "T");

;
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $dados = $contaCorrenteReaisEmpresaRn->filtrar($dataInicial, $dataFinal, \Utils\Constantes::SAIDA, $filtro, "S", $nresultado);
            
            $d = $this->htmlListaTransferenciasBrl($dados);
            
            $json["html"] = $d["html"];
            $json["popover"] = $d["popover"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    private function htmlListaTransferenciasBrl($lista) {
        $total = 0;
        $popover = Array();
        ob_start();
        
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $contaCorrenteReaisEmpresa) {
                $popover[$contaCorrenteReaisEmpresa->id] = $this->popoverTransferenciaBrl($contaCorrenteReaisEmpresa);
                $total += $contaCorrenteReaisEmpresa->valor;
                $this->itemListaTransferenciaBrl($contaCorrenteReaisEmpresa);
            }
            
            ?>
            <tr>
                <td colspan='2'>
                    <strong>Valor total: </strong>
                </td>
                <td colspan='3' style='text-align: right;'>
                    <strong>R$ <?php echo number_format($total, 2, ",", ""); ?></strong>
                </td>
            </tr>
            <?php
            
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html"=> $html, "popover" => $popover);
    }
    
    
    private function itemListaTransferenciaBrl(\Models\Modules\Cadastro\ContaCorrenteReaisEmpresa $contaCorrenteReaisEmpresa) {
        ?>

            <tr>
                <td><?php echo $contaCorrenteReaisEmpresa->id ?></td>
                <td class="text-center"><?php echo $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                <td ><?php echo $contaCorrenteReaisEmpresa->descricao ?></td>
                <td class="text-center"><?php echo number_format($contaCorrenteReaisEmpresa->valor, 2, ",", ".")?></td>
                <td class="text-center">
                    <a tabindex="0" class="btn btn-xs btn-info btn-popover-brl" role="button" data-controle='<?php echo $contaCorrenteReaisEmpresa->id?>' data-toggle="popover" data-trigger="focus" style="font-size: 10px">Log</a>
                </td>
            </tr>
        <?php
    }
    
    
    private function popoverTransferenciaBrl(\Models\Modules\Cadastro\ContaCorrenteReaisEmpresa $contaCorrenteReaisEmpresa) {
        $logContaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\LogContaCorrenteReaisEmpresaRn();
        $result = $logContaCorrenteReaisEmpresaRn->lista("id_conta_corrente_reais_empresa = {$contaCorrenteReaisEmpresa->id}", "data", null, null, TRUE, false);
        ob_start();
        if (sizeof($result) > 0) {
            foreach ($result as $contaCorrenteReaisEmpresa) {
                ?>
            <strong><?php echo $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?> - <?php echo $contaCorrenteReaisEmpresa->usuario->nome ?> : 
                <?php echo $contaCorrenteReaisEmpresa->descricao?>.</strong> <br>
                <?php
            }
        } else {
            ?>
            <br><br>
            <strong>Nenhum log para o registro selecionado.</strong>
            <br><br>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
}