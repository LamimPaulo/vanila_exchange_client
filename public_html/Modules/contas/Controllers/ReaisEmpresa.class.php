<?php

namespace Modules\contas\Controllers;

class ReaisEmpresa {
    
    private  $codigoModulo = "reais";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {

        } catch (\Exception $ex) {

        }
        \Utils\Layout::view("conta_corrente_reais_empresa", $params);
    }
    
    
    
    public function filtrar($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $dados = $contaCorrenteReaisEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro);
            
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
                    Entradas: R$<?php echo number_format($entradas, 2, ",", ".")?>
                </div>
                <div class="col col-lg-3">
                    Saídas: R$<?php echo number_format($saidas, 2, ",", ".")?>
                </div>
                <div class="col col-lg-3">
                    Saldo: R$<?php echo number_format($saldo, 2, ",", ".")?>
                </div>
            </div>
        </li>
        <?php
        
        if (sizeof($lista) > 0) {
            ?>
            <li class="list-group-item" style="font-size: 10px; font-weight: bold;" >
                <div class="row">
                    <div class="col col-lg-1 text-center">
                        <strong>Controle</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Data</strong>
                    </div>
                    <div class="col col-lg-4">
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
            
            foreach ($lista as $contaCorrenteReaisEmpresa) {
                
                if ($contaCorrenteReaisEmpresa->tipo == \Utils\Constantes::ENTRADA) {
                    $saldo += $contaCorrenteReaisEmpresa->valor;
                } else {
                    $saldo -= $contaCorrenteReaisEmpresa->valor;
                }
                $this->itemListaContaCorrente($contaCorrenteReaisEmpresa, $saldo);
            }
            
        }
        
        ?>
            <li class="list-group-item" style="font-weight: bold; font-size: 10px;">
                <div class="row">
                    <div class="col col-lg-8">
                        Saldo: 
                    </div>
                    <div class="col col-lg-4 text-center">
                        R$ <?php echo number_format($saldo, 2, ",", ".")?>
                    </div>
                </div>
            </li>
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaContaCorrente(\Models\Modules\Cadastro\ContaCorrenteReaisEmpresa $contaCorrenteReaisEmpresa, $saldo) {
        ?>
        <li class="list-group-item" style="color: <?php echo ($contaCorrenteReaisEmpresa->tipo == \Utils\Constantes::ENTRADA ? "green" : "red")?>; font-size: 10px;" >
            <div class="row">
                <div class="col col-lg-1 text-center">
                    <?php echo $contaCorrenteReaisEmpresa->id ?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php echo $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                </div>
                <div class="col col-lg-4">
                    <?php echo $contaCorrenteReaisEmpresa->descricao ?>
                </div>
                <div class="col col-lg-2 text-center">
                    <?php echo number_format($contaCorrenteReaisEmpresa->valor, 2, ",", ".")?>
                </div>
                <div class="col col-lg-2 text-center">
                    <?php echo number_format($saldo, 8, ",", ".")?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php if (!$contaCorrenteReaisEmpresa->transferencia > 0) { ?>
                    <button class="btn btn-info" onclick="cadastro(<?php echo $contaCorrenteReaisEmpresa->id ?>)">
                        <i class="fa fa-edit"></i>
                    </button>
                    <?php } ?>
                </div>
                <div class="col col-lg-1 text-center">
                    <?php if (!$contaCorrenteReaisEmpresa->transferencia > 0) { ?>
                    <button class="btn btn-danger" onclick="modalExcluir(<?php echo $contaCorrenteReaisEmpresa->id ?>)">
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
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            if ($contaCorrenteReaisEmpresa->id > 0) {
                $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
                $contaCorrenteReaisEmpresaRn->conexao->carregar($contaCorrenteReaisEmpresa);
            }
            
            $contaCorrenteReaisEmpresa->data = ($contaCorrenteReaisEmpresa->data != null ? $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) : date("d/m/Y"));
            $contaCorrenteReaisEmpresa->valor = number_format($contaCorrenteReaisEmpresa->valor, 2, ",", "");
            
            $json["conta"] = $contaCorrenteReaisEmpresa;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            $contaCorrenteReaisEmpresa->data = \Utils\Post::getData($params, "data", null, "00:00:00");
            $contaCorrenteReaisEmpresa->descricao = \Utils\Post::get($params, "descricao", null);
            $contaCorrenteReaisEmpresa->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteReaisEmpresa->valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::get($params, "id", 0);
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->excluir($contaCorrenteReaisEmpresa);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function imprimir($params) {
        try {
            $d = \Utils\Get::get($params, 0, 0);
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));
            
            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ?
                    new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ?
                    new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $tipo = isset($a[2]) ? $a[2] : "T";
            $filtro = isset($a[3]) ? $a[3] : "T";
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $dados = $contaCorrenteReaisEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro);
            
            $params["entrada"] = $dados["entradas"];
            $params["saida"] = $dados["saidas"];
            $params["lancamentos"] = $dados["lista"];
            
            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_reais_empresa", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_conta_corrente_reais_empresa.pdf", "D", false, false, false);
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