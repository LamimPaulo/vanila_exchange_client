<?php

namespace Modules\contas\Controllers;

class BtcEmpresa {
    
    private  $codigoModulo = "bitcoin";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0", "principal DESC, simbolo ASC", NULL, null);
            //exit(print_r($moedas));
            $params["moedas"] = $moedas;
        } catch (\Exception $ex) {

        }
        \Utils\Layout::view("conta_corrente_btc_empresa", $params);
    }
    
    
    
    public function filtrar($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", 0);
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $dados = $contaCorrenteBtcEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro, $idMoeda);
            
            $html = $this->htmlListaContaCorrente($dados);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaContaCorrente($dados) {
        $entradas = $dados["entradas"];
        $saidas = $dados["saidas"];
        $saldo = $entradas - $saidas;
        $lista = $dados["lista"];
        ob_start();
        
        ?>
        <li class="list-group-item" style="font-size: 10px; font-weight: bold;">
            <div class="row">
                <div class="col col-lg-3">
                    Outros lançamentos
                </div>
                <div class="col col-lg-3">
                    Entradas: R$<?php echo number_format($entradas, 8, ",", ".")?>
                </div>
                <div class="col col-lg-3">
                    Saídas: R$<?php echo number_format($saidas, 8, ",", ".")?>
                </div>
                <div class="col col-lg-3">
                    Saldo: R$<?php echo number_format($saldo, 8, ",", ".")?>
                </div>
            </div>
        </li>
        <?php
        
        if (sizeof($lista) > 0) {
            ?>
            <li class="list-group-item" style="font-size: 10px; font-weight: bold;" >
                <div class="row">
                    <div class="col col-lg-1 text-center">
                        <strong>Moeda</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Controle</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Data</strong>
                    </div>
                    <div class="col col-lg-3">
                        <strong>Descrição</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Valor</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Saldo</strong>
                    </div>
                    
                    <div class="col col-lg-1 text-center">
                        <strong>Editar</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Excluir</strong>
                    </div>
                </div>
            </li>
            <?php
            
            foreach ($lista as $contaCorrenteBtcEmpresa) {
                
                if ($contaCorrenteBtcEmpresa->tipo == \Utils\Constantes::ENTRADA) {
                    $saldo += $contaCorrenteBtcEmpresa->valor;
                } else {
                    $saldo -= $contaCorrenteBtcEmpresa->valor;
                }
                $this->itemListaContaCorrente($contaCorrenteBtcEmpresa, $saldo);
            }
            
        }
        
        ?>
            <li class="list-group-item" style="font-weight: bold; font-size: 10px;">
                <div class="row">
                    <div class="col col-lg-8">
                        Saldo: 
                    </div>
                    <div class="col col-lg-4 text-center">
                        <?php echo number_format($saldo, 8, ",", ".")?>
                    </div>
                </div>
            </li>
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaContaCorrente(\Models\Modules\Cadastro\ContaCorrenteBtcEmpresa $contaCorrenteBtcEmpresa, $saldo) {
        ?>
        <li class="list-group-item" style="color: <?php echo ($contaCorrenteBtcEmpresa->tipo == \Utils\Constantes::ENTRADA ? "green" : "red")?>; font-size: 10px;" >
            <div class="row">
                <div class="col col-lg-1 text-center">
                    <?php echo $contaCorrenteBtcEmpresa->moeda->simbolo ?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php echo $contaCorrenteBtcEmpresa->id ?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php echo $contaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                </div>
                <div class="col col-lg-3">
                    <?php echo $contaCorrenteBtcEmpresa->descricao ?>
                </div>
                <div class="col col-lg-2 text-center">
                    <?php echo number_format($contaCorrenteBtcEmpresa->valor, $contaCorrenteBtcEmpresa->moeda->casasDecimais, ",", ".")?>
                </div>
                <div class="col col-lg-2 text-center">
                    <?php echo number_format($saldo, $contaCorrenteBtcEmpresa->moeda->casasDecimais, ",", ".")?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php if (!$contaCorrenteBtcEmpresa->transferencia > 0) { ?>
                    <button class="btn btn-info" onclick="cadastro(<?php echo $contaCorrenteBtcEmpresa->id ?>)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <?php } ?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php if (!$contaCorrenteBtcEmpresa->transferencia > 0) { ?>
                    <button class="btn btn-danger" onclick="modalExcluir(<?php echo $contaCorrenteBtcEmpresa->id ?>)">
                        <i class="fa fa-trash"></i>
                    </button>
                    <?php } ?>
                </div>
            </div>
        </li>
        <?php
    }
    
    
    public function cadastro($params) {
        try {
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            if ($contaCorrenteBtcEmpresa->id > 0) {
                $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
                $contaCorrenteBtcEmpresaRn->conexao->carregar($contaCorrenteBtcEmpresa);
            } else {
                $contaCorrenteBtcEmpresa->idMoeda = 0;
            }
            
            $contaCorrenteBtcEmpresa->data = ($contaCorrenteBtcEmpresa->data != null ? $contaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) : date("d/m/Y"));
            $contaCorrenteBtcEmpresa->valor = number_format($contaCorrenteBtcEmpresa->valor, 8, ",", "");
            $contaCorrenteBtcEmpresa->idMoeda = \Utils\Criptografia::encriptyPostId($contaCorrenteBtcEmpresa->idMoeda);
            
            $json["conta"] = $contaCorrenteBtcEmpresa;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
                       
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            $contaCorrenteBtcEmpresa->data = \Utils\Post::getData($params, "data", null,  "00:00:00");
            
            $contaCorrenteBtcEmpresa->descricao = \Utils\Post::get($params, "descricao", NULL);
            $contaCorrenteBtcEmpresa->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteBtcEmpresa->valor = \Utils\Post::getNumeric($params, "valor", 0);
            $contaCorrenteBtcEmpresa->idMoeda = \Utils\Post::getEncrypted($params, "idMoeda", 0);
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteBtcEmpresaRn->excluir($contaCorrenteBtcEmpresa);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function imprimir($params) {
        try {
            $d = \Utils\Get::get($params, 0, "");
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));
            
            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ?
                    new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ?
                    new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $tipo = isset($a[2]) ? $a[2] : "T";
            $filtro = isset($a[3]) ? $a[3] : "T";
            $idMoeda = isset($a[4]) ? \Utils\Criptografia::decriptyPostId($a[4]) : 0;
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $dados = $contaCorrenteBtcEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro, $idMoeda);
            
            $params["entrada"] = $dados["entradas"];
            $params["saida"] = $dados["saidas"];
            $params["lancamentos"] = $dados["lista"];
            
            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_btc_empresa", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_conta_corrente_btc_empresa.pdf", "D", false, false, false);
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1>Ops...</h1>
                    <h3><?php echo \Utils\Excecao::mensagem($ex) ?></h3>
                </body>
            </html>
            <?php
        }
    }
    
    
}