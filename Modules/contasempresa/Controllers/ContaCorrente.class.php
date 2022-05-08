<?php

namespace Modules\contasempresa\Controllers;

class ContaCorrente {
    
    private $codigoModulo = "contasempresa";
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        try {
            
        } catch (\Exception $ex) {

        }
        \Utils\Layout::view("conta_corrente_empresa", $params);
    }
    
    
    
    public function filtrarBrl($params) {
        try {
                        
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $nresultado = \Utils\Post::get($params, "nregistros", "T");
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $dados = $contaCorrenteReaisEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro, "T", $nresultado);
            
            $d = $this->htmlListaContaCorrenteBrl($dados);
            
            $json["html"] = $d["html"];
            $json["popover"] = $d["popover"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaContaCorrenteBrl($lista) {
        ob_start();
        $popover = Array();
        $total = 0;
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $contaCorrenteReaisEmpresa) {
                
                if ($contaCorrenteReaisEmpresa->tipo == \Utils\Constantes::ENTRADA) {
                    $total += $contaCorrenteReaisEmpresa->valor;
                } else {
                    $total -= $contaCorrenteReaisEmpresa->valor;
                }
                
                $popover[$contaCorrenteReaisEmpresa->id] = $this->popoverBrl($contaCorrenteReaisEmpresa);
                $this->itemListaContaCorrenteBrl($contaCorrenteReaisEmpresa);
            }
            
        }
        
        ?>
            <tr>
                <th colspan="3">
                    Volume Total: 
                </th>
                <th colspan="4" class="text-right">
                    R$ <?php echo number_format($total, 2, ",", ".")?>
                </th>
            </tr>
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "popover" => $popover);
    }
    
    
    private function itemListaContaCorrenteBrl(\Models\Modules\Cadastro\ContaCorrenteReaisEmpresa $contaCorrenteReaisEmpresa) {
        $cor = ($contaCorrenteReaisEmpresa->tipo == \Utils\Constantes::ENTRADA ? "green" : "red");
        ?>
        <tr style="font-size: 10px; color: <?php echo $cor;?>">
            <td>
                <?php echo $contaCorrenteReaisEmpresa->id ?>
            </td>
            <td class="text-center">
                <?php echo $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?>
            </td>
            <td>
                <?php echo $contaCorrenteReaisEmpresa->descricao ?>
            </td>
            <td class="text-center">
                R$ <?php echo number_format($contaCorrenteReaisEmpresa->valor, 2, ",", ".")?>
            </td>
            <td class="text-center">
                <a tabindex="0" class="btn btn-xs btn-info btn-popover-brl" role="button" data-controle='<?php echo $contaCorrenteReaisEmpresa->id?>' data-toggle="popover" data-trigger="focus" style="font-size: 10px">Log</a>
            </td>
            <td>
                <?php 
                if(\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {  
                    if ($contaCorrenteReaisEmpresa->transferencia < 1 && $contaCorrenteReaisEmpresa->bloqueado < 1) { 
                ?>
                <button class="btn btn-xs btn-info" style="font-size: 10px" onclick="cadastroBrl('<?php echo \Utils\Criptografia::encriptyPostId($contaCorrenteReaisEmpresa->id) ?>')">
                    <i class="fa fa-edit"></i>
                </button>
                <?php 
                    } 
                }  
                ?>
            </td>
            <td>
                <?php 
                if(\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EXCLUIR)) {  
                    if ($contaCorrenteReaisEmpresa->transferencia < 1 && $contaCorrenteReaisEmpresa->bloqueado < 1) { 
                ?>
                <button class="btn btn-xs btn-danger" style="font-size: 10px" onclick="modalExcluirBrl('<?php echo  \Utils\Criptografia::encriptyPostId($contaCorrenteReaisEmpresa->id)  ?>')">
                    <i class="fa fa-trash"></i>
                </button>
                <?php 
                    } 
                }  
                ?>
            </td>
        </tr>
        <?php
    }
    
    
    
    
    private function popoverBrl(\Models\Modules\Cadastro\ContaCorrenteReaisEmpresa $contaCorrenteReaisEmpresa) {
        $logContaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\LogContaCorrenteReaisEmpresaRn();
        $result = $logContaCorrenteReaisEmpresaRn->lista("id_conta_corrente_reais_empresa = {$contaCorrenteReaisEmpresa->id}", "data", null, null, TRUE, false);
        ob_start();
        if (sizeof($result) > 0) {
            foreach ($result as $contaCorrenteReaisEmpresa) {
                ?>
            <strong><?php echo $contaCorrenteReaisEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?> - 
                <?php echo ($contaCorrenteReaisEmpresa->usuario != null ? $contaCorrenteReaisEmpresa->usuario->nome : ($contaCorrenteReaisEmpresa->cliente != null ? $contaCorrenteReaisEmpresa->cliente->nome : "")) ?> : 
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
    
    
    public function cadastroBrl($params) {
        try {
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($contaCorrenteReaisEmpresa->id > 0) {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {
                    $json["salvar"] = false;
                    throw new \Exception("Você não tem permissão para efetuar lançamentos");
                } else {
                    $json["salvar"] = true;
                }
            } else {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::CADASTRAR)) {
                    $json["salvar"] = false;
                    throw new \Exception("Você não tem permissão para editar lançamentos");
                } else {
                    $json["salvar"] = true;
                }
            }
            
            if ($contaCorrenteReaisEmpresa->id > 0) {
                $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
                $contaCorrenteReaisEmpresaRn->conexao->carregar($contaCorrenteReaisEmpresa);
            }
            
            $contaCorrenteReaisEmpresa->id = \Utils\Criptografia::encriptyPostId($contaCorrenteReaisEmpresa->id);
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
    
    
    public function salvarBrl($params) {
        try {
            
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($contaCorrenteReaisEmpresa->id > 0) {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para efetuar lançamentos");
                }
            } else {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para editar lançamentos");
                }
            }
            
            $contaCorrenteReaisEmpresa->data = \Utils\Post::getData($params, "data", NULL, "00:00:00");
            $contaCorrenteReaisEmpresa->descricao = \Utils\Post::get($params, "descricao", NULL);
            $contaCorrenteReaisEmpresa->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteReaisEmpresa->valor = \Utils\Post::getNumeric($params, "valor", 0);
            $contaCorrenteReaisEmpresa->bloqueado = 0;
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Lançamento efetuado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluirBrl($params) {
        try {
            
            if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir lançamentos");
            }
            
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresaRn->excluir($contaCorrenteReaisEmpresa);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Lançamento excluido com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    
    public function filtrarCurrency($params) {
        try {
            
                                    
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $filtro = \Utils\Post::get($params, "filtro", "T");
            $nresultado = \Utils\Post::get($params, "nregistros", "T");
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            
            //exit("Id Moeda: {$idMoeda}");
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $dados = $contaCorrenteBtcEmpresaRn->filtrar($dataInicial, $dataFinal, $tipo, $filtro, $moeda->id, "T", $nresultado);
            
            $d = $this->htmlListaContaCorrenteCurrency($dados);
            
            $json["html"] = $d["html"];
            $json["popover"] = $d["popover"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    private function htmlListaContaCorrenteCurrency($lista) {
        $moeda = \Modules\principal\Controllers\Principal::getCurrency();
        $popover = Array();
        $total = 0;
        ob_start();
        
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $contaCorrenteBtcEmpresa) {
                
                if ($contaCorrenteBtcEmpresa->tipo == \Utils\Constantes::ENTRADA) {
                    $total += $contaCorrenteBtcEmpresa->valor;
                } else {
                    $total -= $contaCorrenteBtcEmpresa->valor;
                }
                
                $popover[$contaCorrenteBtcEmpresa->id] = $this->popoverCurrency($contaCorrenteBtcEmpresa);
                $this->itemListaContaCorrenteCurrency($contaCorrenteBtcEmpresa);
            }
            
        }
        
        ?>
        <tr >
            <th colspan="2">
                Volume Total: 
            </th>
            <th colspan="5" class="text-right">
                <?php echo number_format($total, $moeda->casasDecimais, ",", ".")?>
            </th>
        </tr>
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return Array("html" => $html, "popover" => $popover);
    }
    
    
    private function itemListaContaCorrenteCurrency(\Models\Modules\Cadastro\ContaCorrenteBtcEmpresa $contaCorrenteBtcEmpresa) {
       
        ?>
        <tr style="color: <?php echo ($contaCorrenteBtcEmpresa->tipo == \Utils\Constantes::ENTRADA ? "green" : "red")?>; font-size: 10px">
            <td  class="text-right">
                <?php echo $contaCorrenteBtcEmpresa->id ?>
            </td>
            <td class="text-right">
                <?php echo $contaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR) ?>
            </td>
            <td >
                <?php echo $contaCorrenteBtcEmpresa->descricao ?>
            </td>
            <td  class="text-right">
                <?php echo number_format($contaCorrenteBtcEmpresa->valor, $contaCorrenteBtcEmpresa->moeda->casasDecimais, ",", ".")?>
            </td>
            <td  class="text-center">
                <a tabindex="0" class="btn btn-xs btn-info btn-popover-currency text-center" style="font-size: 10px" role="button" data-controle='<?php echo $contaCorrenteBtcEmpresa->id?>' data-toggle="popover" data-trigger="focus">Log</a>
            </td>
            <td  class="text-center">
                <?php 
                if(\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {  
                    if ($contaCorrenteBtcEmpresa->transferencia < 1 && $contaCorrenteBtcEmpresa->bloqueado < 1) { 
                ?>
                <button class="btn btn-info btn-xs text-center" onclick="cadastroCurrency('<?php echo  \Utils\Criptografia::encriptyPostId($contaCorrenteBtcEmpresa->id)  ?>')" style="font-size: 10px">
                    <i class="fa fa-edit"></i>
                </button>
                <?php 
                    } 
                } 
                ?>
            </td>
            <td  class="text-center">
                <?php 
                if(\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EXCLUIR)) {  
                    if ($contaCorrenteBtcEmpresa->transferencia < 1 && $contaCorrenteBtcEmpresa->bloqueado < 1) { 
                ?>
                <button class=" btn btn-xs btn-danger" onclick="modalExcluirCurrency('<?php echo \Utils\Criptografia::encriptyPostId($contaCorrenteBtcEmpresa->id)?>')" style="font-size: 10px">
                    <i class="fa fa-trash"></i>
                </button>
                <?php 
                    } 
                }  
                ?>
            </td>
        </tr>
        
        <?php
    }
    
    
    
    private function popoverCurrency(\Models\Modules\Cadastro\ContaCorrenteBtcEmpresa $contaCorrenteBtcEmpresa) {
        
        $logContaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\LogContaCorrenteBtcEmpresaRn();
        $result = $logContaCorrenteBtcEmpresaRn->lista("id_conta_corrente_btc_empresa = {$contaCorrenteBtcEmpresa->id}", "data", null, null, TRUE, false);
        ob_start();
        if (sizeof($result) > 0) {
            
            foreach ($result as $logContaCorrenteBtcEmpresa) {
                
                ?>
            <strong><?php echo $logContaCorrenteBtcEmpresa->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?> - 
                <?php echo ($logContaCorrenteBtcEmpresa->usuario != null ? $logContaCorrenteBtcEmpresa->usuario->nome : ($logContaCorrenteBtcEmpresa->cliente != null ? $logContaCorrenteBtcEmpresa->cliente->nome : "")) ?> : 
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
    
    
    
    public function cadastroCurrency($params) {
        try {
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($contaCorrenteBtcEmpresa->id > 0) {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {
                    $json["salvar"] = false;
                    throw new \Exception("Você não tem permissão para efetuar lançamentos");
                } else {
                    $json["salvar"] = true;
                }
            } else {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::CADASTRAR)) {
                    $json["salvar"] = false;
                    throw new \Exception("Você não tem permissão para editar lançamentos");
                } else {
                    $json["salvar"] = true;
                }
            }
            
            if ($contaCorrenteBtcEmpresa->id > 0) {
                $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
                $contaCorrenteBtcEmpresaRn->conexao->carregar($contaCorrenteBtcEmpresa);
            } else {
                $contaCorrenteBtcEmpresa->idMoeda = 0;
            }
            
            
            $contaCorrenteBtcEmpresa->id = \Utils\Criptografia::encriptyPostId($contaCorrenteBtcEmpresa->id);
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
    
    
    public function salvarCurrency($params) {
        try {
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($contaCorrenteBtcEmpresa->id > 0) {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para efetuar lançamentos");
                }
            } else {
                if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para editar lançamentos");
                }
            }
            
            $contaCorrenteBtcEmpresa->data = \Utils\Post::getData($params, "data", NULL, " 00:00:00");
            $contaCorrenteBtcEmpresa->descricao = \Utils\Post::get($params, "descricao", null);
            $contaCorrenteBtcEmpresa->tipo = \Utils\Post::get($params, "tipo", NULL);
            $contaCorrenteBtcEmpresa->valor = \Utils\Post::getNumeric($params, "valor", null);
            $contaCorrenteBtcEmpresa->bloqueado = 0;
            
            $moeda = \Modules\principal\Controllers\Principal::getCurrency();
            $contaCorrenteBtcEmpresa->idMoeda = $moeda->id;
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Lançamento cadastrado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluirCurrency($params) {
        try {
            if(!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTAS_EMPRESA, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir lançamentos");
            }
            
            $contaCorrenteBtcEmpresa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteBtcEmpresaRn->excluir($contaCorrenteBtcEmpresa);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Lançamento excluido com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
}