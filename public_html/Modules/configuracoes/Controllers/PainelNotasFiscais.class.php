<?php

namespace Modules\configuracoes\Controllers;

class PainelNotasFiscais {
    
    private  $codigoModulo = "configuracoes";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        
        \Utils\Layout::view("painel_notas_fiscais", $params);
    }
    
    public function listar($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", NULL, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", NULL, "23:59:59");
            $status = \Utils\Post::get($params, "status", null);
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            $lista = $notaFiscalRn->filtrar($dataInicial, $dataFinal, $filtro, $status);
            
            $dados = $this->htmlLista($lista);
            
            $json = $dados;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function htmlLista($lista) {
        
        $notas =  0;
        $depositos = 0;
        $saques = 0;
        
        $autorizadas = 0;
        $autorizacaoSolicitada = 0;
        $canceladas = 0;
        $cancelamentoNegado = 0;
        $cancelamentoSolicitado = 0;
        $negada = 0;
        
        
        ob_start();
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $dadosNf) {
                $notaFiscal = $dadosNf["nf"];
                
                $notas++;
                
                if ($notaFiscal->idDeposito > 0) {
                    $depositos++;
                }
                
                if ($notaFiscal->idSaque > 0) {
                    $saques++;
                }
                
                switch ($notaFiscal->status) {
                    case \Utils\Constantes::STATUS_NF_AUTORIZADA:
                    case \Utils\Constantes::STATUS_NF_AGUARDANDO_PDF:
                        $autorizadas++;
                        break;
                    case \Utils\Constantes::STATUS_NF_AGUARDANDO_AUTORIZACAO:
                    case \Utils\Constantes::STATUS_NF_AUTORIZACAO_SOLICITADA:
                    case \Utils\Constantes::STATUS_NF_PROCESSANDO_AUTORIZACAO:
                    case \Utils\Constantes::STATUS_NF_SOLICITANDO_AUTORIZACAO:
                        $autorizacaoSolicitada++;
                        break;
                    case \Utils\Constantes::STATUS_NF_CANCELADA:
                        $canceladas++;
                        break;
                    case \Utils\Constantes::STATUS_NF_CANCELAMENTO_NEGADO:
                        $cancelamentoNegado++;
                        break;
                    case \Utils\Constantes::STATUS_NF_CANCELAMENTO_SOLICITADO:
                    case \Utils\Constantes::STATUS_NF_PROCESSANDO_CANCELAMENTO:
                    case \Utils\Constantes::STATUS_NF_SOLICITANDO_CANCELAMENTO:
                        $cancelamentoSolicitado++;
                        break;
                    case \Utils\Constantes::STATUS_NF_NEGADA:
                    case \Utils\Constantes::STATUS_NF_CANCELADA_AGUARDANDO_PDF:
                        $negada++;
                        break;
                }
                
                $this->htmlItemNota($dadosNf);
            }
            
        } else {
            ?>
            <tr >
                <td class="text-center" colspan="13">
                    Nenhum registro encontrado com os filtros informados
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return Array(
            "html" => $html, 
            "notas" => $notas, 
            "depositos" => $depositos, 
            "saques" => $saques, 
            "autorizadas" => $autorizadas, 
            "autorizacaoSolicitada" => $autorizacaoSolicitada, 
            "canceladas" => $canceladas,
            "cancelamentoNegado" => $cancelamentoNegado,
            "cancelamentoSolicitado" => $cancelamentoSolicitado,
            "negada" => $negada
        );
    }
    
    private function htmlItemNota($dadosNf) {
        $notaFiscal = $dadosNf["nf"];
        $controle = "";
        $tipo = "";
        $t = "";
        $valor = 0;

        $cliente = null;
        
        if (isset($dadosNf["saque"])) {
            $tipo = "Saque";
            $controle = $dadosNf["saque"]->id;
            $cliente = $dadosNf["saque"]->cliente;
            $valor = $dadosNf["saque"]->valorSaque;
            $t = "s";
        } if (isset($dadosNf["deposito"])) {
            $tipo = "Depósito";
            $controle = $dadosNf["deposito"]->id;
            $cliente = $dadosNf["deposito"]->cliente;
            $valor = $dadosNf["deposito"]->valorDepositado;
            $t = "d";
        }

        $bg = "";
        if ($notaFiscal == null || $notaFiscal->status == \Utils\Constantes::STATUS_NF_NEGADA || $notaFiscal->status == \Utils\Constantes::STATUS_NF_CANCELAMENTO_NEGADO) {
            $bg = "bg-danger";
        }

        $statusCancelada = Array(
            \Utils\Constantes::STATUS_NF_CANCELADA,
            \Utils\Constantes::STATUS_NF_CANCELADA_AGUARDANDO_PDF,
            \Utils\Constantes::STATUS_NF_CANCELAMENTO_NEGADO,
            \Utils\Constantes::STATUS_NF_CANCELAMENTO_SOLICITADO,
            \Utils\Constantes::STATUS_NF_SOLICITANDO_CANCELAMENTO,
            \Utils\Constantes::STATUS_NF_PROCESSANDO_CANCELAMENTO
        );
        
        //$notaFiscal = new \Models\Modules\Cadastro\NotaFiscal();
        
        ?>

        <tr style='' class="" id="<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>" >
            <td class='text-center'>(<?php echo $notaFiscal->ambiente ?>) <?php echo $controle?></td>
            <td class='text-center'><?php echo $notaFiscal->dataCriacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO); ?></td> 
            <td class='text-center'><?php echo $cliente->nome ?></td> 
            <td class='text-center'><?php echo $notaFiscal->idnf ?></td> 
            <td class='text-center'><?php echo $tipo?></td> 
            <td class='text-center'>R$ <?php echo number_format($valor, 2, ",", ".")?></td> 
            <td class='text-center'>R$ <?php echo number_format($notaFiscal->valorTotal, 2, ",", ".")?></td> 
            <td class='text-center'><?php echo $notaFiscal->numeroNf ?></td> 
            <td class='text-center'><?php echo $notaFiscal->status ?></td> 
            
            <td class='text-center'>
                <?php if (!empty($notaFiscal->linkDownloadPdf)) { ?>
                <a href="<?php echo $notaFiscal->linkDownloadPdf ?>" target="_BLANK_PDF">
                    <i class="fa fa-file-pdf-o fa-2x"></i>
                </a>
                <?php } ?>
            </td>
            
            <td class='text-center'>
                <?php if (!empty($notaFiscal->linkDownloadXml)) { ?>
                <a href="<?php echo $notaFiscal->linkDownloadXml ?>" target="_BLANK_XML">
                    <i class="fa fa-file-excel-o fa-2x"></i>
                </a>
                <?php } ?>
            </td>
            
            <td class='text-center'>
                <img src="<?php echo IMAGES ?>loading.gif" style="width: 30px; height: 30px; display: none;" id="loading-<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>" />
                <button class="btn btn-info btn-xs" type="button" onclick="atualizarStatusNota('<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>');" id="btn-update-<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>">
                    Atualizar
                </button>
            </td>
            
            
            <td class='text-center'>
                <?php if ($notaFiscal->status != null && !in_array($notaFiscal->status, $statusCancelada)) { ?>
                <img src="<?php echo IMAGES ?>loading.gif" style="width: 30px; height: 30px; display: none;" id="loading-cancel-<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>" />
                <button class="btn btn-danger btn-xs" type="button" onclick="cancelarNota('<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>');" id="btn-cancel-<?php echo \Utils\Criptografia::encriptyPostId("{$t}-{$controle}")?>">
                    Cancelar
                </button>
                <?php } ?>
            </td> 
            
            
        </tr>


        <?php
    }
    
    
    function atualizar($params) {
        try {
            
            
            $identificacao = \Utils\Post::getEncrypted($params, "identificacao", null);
            $id = explode("-", $identificacao);
            
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            $dadosNf = Array();
            $ref = "";
            $notaFiscal = null;
            if ($id[0] == "s") {
                
                $saque = new \Models\Modules\Cadastro\Saque(Array("id" => $id[1]));
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saqueRn->carregar($saque, true, false, false, true);
                
                $result = $notaFiscalRn->conexao->select(Array("id_saque" => $saque->id));
                
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } else {
                    $dados = \ENotasGW\NotaFiscal::emitir($saque, $saque->aceitaNota);
                    $notaFiscal = new \Models\Modules\Cadastro\NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idSaque = $saque->id;
                    $notaFiscal->idCliente = $saque->idCliente;
                    $notaFiscal->idnf = $dados->nfeId;
                }
                $dadosNf["saque"] = $saque;
                $ref = \Utils\Criptografia::encriptyPostId("s-{$saque->id}");
            } else {
                
                $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $id[1]));
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->carregar($deposito, true, false, false, true);
                $result = $notaFiscalRn->conexao->select(Array("id_deposito" => $deposito->id));
                
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } else {
                    $dados = \ENotasGW\NotaFiscal::emitir($deposito, $deposito->aceitaNota);
                    $notaFiscal = new \Models\Modules\Cadastro\NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idDeposito = $deposito->id;
                    $notaFiscal->idCliente = $deposito->idCliente;
                    $notaFiscal->idnf = $dados->nfeId;
                }
                $ref = \Utils\Criptografia::encriptyPostId("d-{$deposito->id}");
                $dadosNf["deposito"] = $deposito;
            }
            
            
            $dados = \ENotasGW\NotaFiscal::consultar($notaFiscal->idnf);
            $notaFiscalRn->setNotaFiscalFromJson($notaFiscal, $dados);
            
            $notaFiscalRn->salvar($notaFiscal);
            
            $dadosNf["nf"] = $notaFiscal;
            ob_start();
            $this->htmlItemNota($dadosNf);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["ref"] = $ref;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    function emitir($params) {
        try {
            $controle = \Utils\Post::get($params, "controle", null);
            $tipo = \Utils\Post::get($params, "tipo", null);
            
            
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            
            $ref = "";
            $notaFiscal = null;
            if ($tipo == "s") {
                
                $saque = new \Models\Modules\Cadastro\Saque(Array("id" => $controle));
                
                try {
                    $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                    $saqueRn->carregar($saque, true, false, false, true);
                } catch (\Exception $ex) {
                    throw new \Exception("Saque não localizado no sistema");
                }
                //c$result = $notaFiscalRn->conexao->select(Array("id_saque" => $saque->id));
                $result=null;
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } else {
                    $dados = \ENotasGW\NotaFiscal::emitir($saque, $saque->aceitaNota);
                    $notaFiscal = new \Models\Modules\Cadastro\NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idSaque = $saque->id;
                    $notaFiscal->idCliente = $saque->idCliente;
                    $notaFiscal->idnf = $dados->nfeId;
                }
                
                $ref = \Utils\Criptografia::encriptyPostId("s-{$saque->id}");
            } else {
                
                $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $controle));
                try {
                    $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                    $depositoRn->carregar($deposito, true, false, false, true);
                } catch (\Exception $ex) {
                    throw new \Exception("Depósito não localizado no sistema");
                }
                //$result = $notaFiscalRn->conexao->select(Array("id_deposito" => $deposito->id));
                $result = null;
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } else {
                    $dados = \ENotasGW\NotaFiscal::emitir($deposito, $deposito->aceitaNota);
                    $notaFiscal = new \Models\Modules\Cadastro\NotaFiscal();
                    $notaFiscal->id = 0;
                    $notaFiscal->idDeposito = $deposito->id;
                    $notaFiscal->idCliente = $deposito->idCliente;
                    $notaFiscal->idnf = $dados->nfeId;
                }
                $ref = \Utils\Criptografia::encriptyPostId("d-{$deposito->id}");
                
            }
            
            $dados = \ENotasGW\NotaFiscal::consultar($notaFiscal->idnf);
            $notaFiscalRn->setNotaFiscalFromJson($notaFiscal, $dados);
            
            $notaFiscalRn->salvar($notaFiscal);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Nota emitida com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    function cancelar($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PAINELCONTROLE, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar cancelar a nota fiscal");
            }
            
            $identificacao = \Utils\Post::getEncrypted($params, "identificacao", null);
            $id = explode("-", $identificacao);
            
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            $dadosNf = Array();
            $ref = "";
            $notaFiscal = null;
            if ($id[0] == "s") {
                
                $saque = new \Models\Modules\Cadastro\Saque(Array("id" => $id[1]));
                $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
                $saqueRn->carregar($saque, true, false, false, true);
                
                $result = $notaFiscalRn->conexao->select(Array("id_saque" => $saque->id));
                
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } 
                
                $dadosNf["saque"] = $saque;
                $ref = \Utils\Criptografia::encriptyPostId("s-{$saque->id}");
            } else {
                
                $deposito = new \Models\Modules\Cadastro\Deposito(Array("id" => $id[1]));
                $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
                $depositoRn->carregar($deposito, true, false, false, true);
                $result = $notaFiscalRn->conexao->select(Array("id_deposito" => $deposito->id));
                
                if (sizeof($result) > 0) {
                    $notaFiscal = $result->current();
                } 
                $ref = \Utils\Criptografia::encriptyPostId("d-{$deposito->id}");
                $dadosNf["deposito"] = $deposito;
            }
            
            if ($notaFiscal == null) {
                throw new \Exception("Nota fiscal não encontrada para o registro solicitado");
            }
            
            $dadosC = \ENotasGW\NotaFiscal::cancelar($notaFiscal->idnf);
            
            $dados = \ENotasGW\NotaFiscal::consultar($notaFiscal->idnf);
            $notaFiscalRn->setNotaFiscalFromJson($notaFiscal, $dados);
            
            $notaFiscalRn->salvar($notaFiscal);
            
            $dadosNf["nf"] = $notaFiscal;
            ob_start();
            $this->htmlItemNota($dadosNf);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["ref"] = $ref;
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Cancelamento solicitado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}