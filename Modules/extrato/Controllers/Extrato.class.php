<?php

namespace Modules\extrato\Controllers;

class Extrato {

    private $codigoModulo = "extrato";
    private $idioma = null;

    public function __construct() {
        if(\Utils\Geral::isUsuario()){
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("extrato", IDIOMA);
    }

    public function index($params) {

        $moedaRn = new \Models\Modules\Cadastro\MoedaRn(); 
        $moedas = $moedaRn->listar(" id = 1 OR ativo = 1 AND (visualizar_deposito = 1 OR visualizar_saque = 1)", "nome ASC");
        $cliente = \Utils\Geral::getCliente();

        if(empty($cliente->moedaFavorita)){
            $cliente->moedaFavorita = 2; //Bitcoin
        }

        $dados = Array();

        $object = (object)null;
        $object->text = "Todos";
        $object->id = \Utils\Criptografia::encriptyPostId("todos");
        $object->icone = IMAGES . "transferencia.png";
        $dados[] = $object;

        foreach ($moedas as $m) {
            $object = (object)null;
            $object->text = $m->simbolo . " - " .$m->nome;
            $object->simbolo = $m->simbolo;
            $object->icone = IMAGES . "currencies/" .$m->icone;
            $object->id = \Utils\Criptografia::encriptyPostId($m->id);

            if($cliente->moedaFavorita == $m->id){
                $object->selected = true;
            }

            $dados[] = $object;
        }

        $params["moedas"] = json_encode($dados);

        \Utils\Layout::view("index_extrato", $params);
    }

    public function listarExtrato($params) {
        try {
            $cliente = \Utils\Geral::getCliente(); 
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", null);
            $limite = \Utils\Post::get($params, "registros", null);

            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();

            $listaReais = null;
            $listaCripto = null;
            $listaGeral = Array();
            $real = false;
            $cripto = false;
            $todos = false;
            $anexo[] = '';

            switch ($idMoeda) {
                case "todos":
                    $real = true;
                    $cripto = true;
                    $todos = true;
                    break;

                case $idMoeda == 1:
                    $real = true;
                    break;

                case $idMoeda > 1:
                    $cripto = true;
                    break;
                default:
                    throw new \Exception("Opção inválida.");
                    break;
            }
            
            if($limite == "T"){
                $limite = null;
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("Data inicial não pode ser maior que a data final.");
            }

            if ($real) {
                $listaReais = $contaCorrenteReaisRn->lista(" id_cliente = {$cliente->id} AND data_cadastro <= '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id DESC", null, null, false, false);
                
                if (sizeof($listaReais) > 0) {
                    $i = 0;
                    $saldo = 0;
                    
                    $listaReais = array_reverse($listaReais);
                    foreach ($listaReais as $reais) {
                        
                        $i++;
                        $tipo = $reais->tipo == \Utils\Constantes::ENTRADA ? 2 : 1;
                        
                        $saldo = $reais->tipo == \Utils\Constantes::ENTRADA ? $reais->valor + $saldo : $saldo - $reais->valor;
                        
                        $reais->saldo = $saldo;
                        
                        if($reais->dataCadastro->maiorIgual($dataInicial)){
                            $ordenar = strtotime($reais->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) . "-{$i}-{$reais->tipo}";
                            $listaGeral[$ordenar] = $reais;
                        }
                    }
                }
            }

            if($cripto){

                $moeda = "";
                if(!$todos){
                    $moeda = " id_moeda = {$idMoeda} AND ";
                }

                $listaCripto = $contaCorrenteBtcRn->lista(" {$moeda} id_cliente = {$cliente->id} AND data_cadastro <= '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "id DESC", null, null, false, true);

                if (sizeof($listaCripto) > 0) {
                    $i = 0;
                    $saldo = 0;
                    
                    $arraySaldoMoeda = Array();
                    
                    $listaCripto = array_reverse($listaCripto);
                    foreach ($listaCripto as $criptomoeda) {
                        
                        $i++;
                        $tipo = $criptomoeda->tipo == \Utils\Constantes::ENTRADA ? 2 : 1;
                        
                        
                        if(!isset($arraySaldoMoeda[$criptomoeda->moeda->id])){
                            $arraySaldoMoeda[$criptomoeda->moeda->id] = 0;
                        }
                        
                        $arraySaldoMoeda[$criptomoeda->moeda->id] = $criptomoeda->tipo == \Utils\Constantes::ENTRADA ? $criptomoeda->valor + $arraySaldoMoeda[$criptomoeda->moeda->id] : $arraySaldoMoeda[$criptomoeda->moeda->id] - $criptomoeda->valor;
                        
                        $criptomoeda->saldo = $arraySaldoMoeda[$criptomoeda->moeda->id];
                        
                        if($criptomoeda->dataCadastro->maiorIgual($dataInicial)){
                            $ordenar = strtotime($criptomoeda->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)) . "-{$i}-{$criptomoeda->tipo}";
                            $listaGeral[$ordenar] = $criptomoeda;
                        }
                    }
                }
            }

            krsort($listaGeral);

            //$anexo = [$this->htmlExtrato($listaGeral, $dataInicial, $dataFinal, $limite)];

            $json["html"] = $this->htmlExtrato($listaGeral, $dataInicial, $dataFinal, $limite);
            $json["anexo"] = $this->anexoExtrato($listaGeral, $dataInicial, $dataFinal, $limite);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    private function htmlExtrato($lista, $dataInicial, $dataFinal, $limite) {
        ob_start();
        if (sizeof($lista) > 0) {
            ?>
                        <!-- <div class="card-title"> -->
                            <h5 class="card-title">Período de <?php echo $dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) . " até " . $dataFinal->formatar(\Utils\Data::FORMATO_PT_BR) ?></h5>
                        <!-- </div> -->
                        <?php
                        $i = 0;
                        foreach ($lista as $extrato) {

                            if (!is_numeric($limite)) {
                                $this->htmlItemExtrato($extrato);
                            } else {
                                if ($i < $limite) {
                                    $this->htmlItemExtrato($extrato);
                                } else {
                                    break;
                                }
                            }
                            $i++;
                        }
                    } else {
                        ?>
                <div class="text-center m-t-xs">
                    <?php echo "Nenhum histórico disponível." ?>
                </div>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    private function htmlItemExtrato($extrato) {

        if ($extrato->tipo == \Utils\Constantes::ENTRADA) {
            $color = "color: #1ab394;";
            $sinal = "+";
        } else {
            $color = "color: #ff1e1e;";
            $sinal = "-";
        }
        if($extrato->confirmacoes >= $extrato->confirmacoesNecessarias){
            $confirmColor = "color: #1ab394";

        } else {
            $confirmColor = "color: #ffe585";
        }

        if($extrato->confirmations ?? 0 > $extrato->confirmationsRequired ?? ''){
            $confirmColor2 = "color: #1ab394";

        } else {
            $confirmColor2 = "color: #ffe585";
        }
        $brlConfirmations = $extrato->confirmations ?? 0;
        $brlConfirmationsRequired = $extrato->confirmationsRequired ?? 0;

        if($extrato instanceof \Models\Modules\Cadastro\ContaCorrenteReais){
            $extrato->moeda = \Models\Modules\Cadastro\MoedaRn::get(1);
            ?>
            <div class="ibox-content">
                <div class="row m-l-xs">
                    <div class="col-sm-1">
                        <small class="stats-label">Moeda</small></br>
                        <img src="<?php echo IMAGES . "currencies/" . $extrato->moeda->icone ?>" width="20" height="20"/>
                    </div>
                    <div class="col-sm-2">
                        <small class="stats-label">Descrição</small>
                        <h6><?php echo $extrato->descricao; ?></h6>
                    </div>
                    <?php if($extrato->txid){?>
                        <div class="col-sm-2">
                            <small class="stats-label">TXID</small>
                            <h6><a target="_blank" href="<?php echo str_replace("{hash}", $extrato->txid, $_ENV['BSCSCAN_URL']);  ?>"> Explorer</a></h6>
                        </div>
                    <?php } else{ ?>
                        <div class="col-sm-2">
                            <small class="stats-label">TXID</small>
                            <h6><a disabled>Interna</a></h6>
                        </div>
                    <?php } ?>
                    <div class="col-sm-2">
                        <small class="stats-label">Data</small>
                        <h6><?php echo $extrato->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO); ?></h6>
                    </div>
                    <div class="col-sm-2">
                        <small class="stats-label">Valor</small>
                        <h6 style="<?php echo $color ?>"><strong><?php echo $sinal . " CBRL " . number_format($extrato->valor, 2, ",", "."); ?></strong></h6>
                    </div> 
                    <div class="col-sm-2">
                        <small class="stats-label">Saldo</small>
                        <h6 style="<?php echo $color ?>"><strong><?php echo "CBRL " . number_format($extrato->saldo, 2, ",", "."); ?></strong></h6>
                    </div> 
                    <div class="col-sm-1">
                        <small class="stats-label">Confirmações</small>
                        <!-- <h6 style="<?php echo $confirmColor2 ?>"> <strong><?php echo $brlConfirmations.'/'.$brlConfirmationsRequired ?></strong></h6> -->
                        <h6 style="color: #1ab394"> <strong>1/1</strong></h6>
                    </div>
                </div>
            </div>

        <?php } else if ($extrato instanceof \Models\Modules\Cadastro\ContaCorrenteBtc) {

                if(!empty($extrato->descricao)){
                    $descricao = $extrato->descricao;
                } else {
                    if($extrato->tipo == \Utils\Constantes::SAIDA){
                        $descricao = "Saque de {$extrato->moeda->nome}";
                    }
                }
            ?>

            <div class="ibox-content">
                <div class="row m-l-xs">
                    <div class="col-sm-1">
                        <small class="stats-label">Moeda</small></br>
                        <img src="<?php echo IMAGES . "currencies/" . $extrato->moeda->icone ?>" width="20" height="20"/>
                    </div>
                    <div class="col-sm-2">
                        <small class="stats-label">Descrição</small>
                        <h6><?php echo $descricao; ?></h6>
                    </div>

                    <?php if($extrato->direcao == 'I') {?>
                        <div class="col-sm-2">
                            <small class="stats-label">TXID</small>
                            <h6><a disabled>Interna</a></h6>
                        </div>

                    <?php } else{ ?>
                        <?php if($extrato->moeda->id == '2') {?>
                            <div class="col-sm-2">
                                <small class="stats-label">TXID</small>
                                <h6><a target="_blank" href="<?php echo str_replace("{hash}", $extrato->hash, $_ENV['BITCOIN_EXPLORER_URL']);  ?>"> Explorer</a></h6>
                            </div>
                        <?php } else if($extrato->rede == 'BEP20' ){?>
                            <div class="col-sm-2">
                                <small class="stats-label">TXID</small>
                                <h6><a target="_blank" href="<?php echo str_replace("{hash}", $extrato->hash, $_ENV['BSCSCAN_URL']);  ?>"> Explorer</a></h6>
                            </div>
                        <?php } else if($extrato->rede == 'POLYGON' ){?>
                            <div class="col-sm-2">
                                <small class="stats-label">TXID</small>
                                <h6><a target="_blank" href="<?php echo str_replace("{hash}", $extrato->hash, $_ENV['POLYGONSCAN_URL']);  ?>"> Explorer</a></h6>
                            </div>
                        <?php } else if($extrato->rede == 'ERC20' ){?>
                            <div class="col-sm-2">
                                <small class="stats-label">TXID</small>
                                <h6><a target="_blank" href="<?php echo str_replace("{hash}", $extrato->hash, $_ENV['ETHERSCAN_URL']);  ?>"> Explorer</a></h6>
                            </div>
                            <?php } else { ?>
                                <div class="col-sm-2">
                                    <small class="stats-label">TXID</small>
                                    <h6> <?php echo $extrato->hash  ?></h6>
                                </div>
                            <?php } ?>
                    <?php } ?>

                    <div class="col-sm-2">
                        <small class="stats-label">Data</small>
                        <h6><?php echo $extrato->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO); ?></h6>
                    </div>
                    <div class="col-sm-2">
                        <small class="stats-label">Quantidade</small>
                        <h6 style="<?php echo $color ?>"><strong><?php echo $sinal . " {$extrato->moeda->simbolo} " . number_format($extrato->valor, $extrato->moeda->casasDecimais, ",", "."); ?></strong></h6>
                    </div>
                    <div class="col-sm-2">
                        <small class="stats-label">Saldo</small>
                        <h6><strong><?php echo $extrato->moeda->simbolo . " ". number_format($extrato->saldo, $extrato->moeda->casasDecimais, ",", "."); ?></strong></h6>
                    </div>
                    <div class="col-sm-1">
                        <small class="stats-label">Confirmações</small>
                        <h6 style="<?php echo $confirmColor ?>"><strong><?php echo $extrato->confirmacoes?>/<?php echo $extrato->confirmacoesNecessarias?></strong></h6>
                    </div>
                </div>
            </div>
        <?php } 
    }

    private function anexoExtrato($lista, $limite) {
        $result = array();
        if (sizeof($lista) > 0) {

            $i = 0;
            foreach ($lista as $extrato) {

                if (!is_numeric($limite)) {
                    $result[] = $this->anexoItemExtrato($extrato);
                } else {
                    if ($i < $limite) {
                        $result[] = $this->anexoItemExtrato($extrato);
                    } else {
                        break;
                    }
                }
                $i++;
            }
        } else {
            
        }

        return $result;
    }

    private function anexoItemExtrato($extrato) {

        $anexo = array();

        if ($extrato->tipo == \Utils\Constantes::ENTRADA) {
            $sinal = "+";
        } else {
            $sinal = "-";
        }

        if ($extrato instanceof \Models\Modules\Cadastro\ContaCorrenteReais) {
            $extrato->moeda = \Models\Modules\Cadastro\MoedaRn::get(1);
            if ($extrato->tipo == 'S') {
                $operacaoBrl = number_format($extrato->valor * -1, 2, ",", ".");
            } else {
                $operacaoBrl = number_format($extrato->valor, 2, ",", ".");
            }

            $anexo['data'] = $extrato->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
            $anexo['tipo'] = $extrato->descricao;
            $anexo['moeda'] = $extrato->moeda->simbolo;
            $anexo['valor'] = $operacaoBrl;
            $anexo['total'] = number_format($extrato->saldo, 2, ",", ".");
            
        } else if ($extrato instanceof \Models\Modules\Cadastro\ContaCorrenteBtc) {

            if (!empty($extrato->dataCadastro)) {
                if ($extrato->tipo == 'S') {
                    $operacaoCripto = number_format($extrato->valor * -1, $extrato->moeda->casasDecimais, ",", ".");
                } else {
                    $operacaoCripto = number_format($extrato->valor, $extrato->moeda->casasDecimais, ",", ".");
                }
                $anexo['data'] = $extrato->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
                $anexo['tipo'] = $extrato->descricao;
                $anexo['moeda'] = $extrato->moeda->simbolo;
                $anexo['valor'] = $operacaoCripto;
                $anexo['total'] = number_format($extrato->saldo, $extrato->moeda->casasDecimais, ",", ".");
            } else {
                
            }
        }

        return $anexo;
    }
}