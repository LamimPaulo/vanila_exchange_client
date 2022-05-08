<?php

namespace Modules\monitoramento\Controllers;

class PainelIco {
    
    private  $codigoModulo = "monitoramento";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        try {
            $brl = \Models\Modules\Cadastro\MoedaRn::get(1);
            $bitcoin = \Models\Modules\Cadastro\MoedaRn::get(2);
            $litecoin = \Models\Modules\Cadastro\MoedaRn::get(4);
            $ethereum = \Models\Modules\Cadastro\MoedaRn::get(3);
            $dash = \Models\Modules\Cadastro\MoedaRn::get(7);
            
            $params["brl"] = $brl;
            $params["bitcoin"] = $bitcoin;
            $params["litecoin"] = $litecoin;
            $params["ethereum"] = $ethereum;
            $params["dash"] = $dash;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_painel_ico", $params);
    }
    
    
    public function getUltimasComprasEfetuadas($params) {
        try {
            $idIco = \Utils\Constantes::ID_ICO;
            
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
            $result = $distribuicaoTokenRn->listar("tipo = 1 AND id_ico = {$idIco}", "data DESC", NULL, 10, true, true, false);
            
            ob_start();
            if (sizeof($result) > 0) {
                foreach ($result as $distribuicao) {
                    //$distribuicao = new \Models\Modules\ICO\DistribuicaoToken();
                    ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->cliente->nome ?>
                        </td>
                        <td style="vertical-align: middle;">               
                            <img src="<?php echo IMAGES ?>currencies/<?php echo $distribuicao->moeda->icone?>" style="width: 20px; height: 20px;"/>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo number_format($distribuicao->valorTotal, 8, ".", ""); ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo number_format($distribuicao->volumeToken, 8, ".", "");?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5">Nenhuma compra efetuada</td>
                </tr>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getUltimasBonificacoes($params) {
        try {
            $idIco = \Utils\Constantes::ID_ICO;
            
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
            $result = $distribuicaoTokenRn->listar("tipo = 2 AND id_ico = {$idIco}", "data DESC", NULL, 10, true, true, false);
            
            ob_start();
            if (sizeof($result) > 0) {
                foreach ($result as $distribuicao) {
                    //$distribuicao = new \Models\Modules\ICO\DistribuicaoToken();
                    ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->cliente->nome ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo number_format($distribuicao->volumeToken, 8, ".", "");?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5">Nenhuma compra efetuada</td>
                </tr>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function getRankingPosseTokensNewc($params) {
        try {
            $idIco = \Utils\Constantes::ID_ICO;
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
            $result = $distribuicaoTokenRn->listar("tipo = 2 AND id_ico = {$idIco}", "data DESC", NULL, 10, true, true, false);
            
            ob_start();
            if (sizeof($result) > 0) {
                foreach ($result as $distribuicao) {
                    //$distribuicao = new \Models\Modules\ICO\DistribuicaoToken();
                    ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo $distribuicao->cliente->nome ?>
                        </td>
                        <td style="vertical-align: middle;">
                            <?php echo number_format($distribuicao->volumeToken, 8, ".", "");?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5">Nenhuma compra efetuada</td>
                </tr>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function progressoFases($params) {
        try {
            $idIco = \Utils\Constantes::ID_ICO;
            
            $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn();
            $fases = $faseIcoRn->conexao->listar("id_ico = {$idIco}");
            
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
            
            $dadosFase = $distribuicaoTokenRn->getDadosVendaTokens($idIco, 0, 0, null, null);
            $distribuicaoPorOrigem = $distribuicaoTokenRn->getDistribuicaoTokensPorOrigem();
            
            $btcArrecadadoTotal = 0;
            $ethArrecadadoTotal = 0;
            $ltcArrecadadoTotal = 0;
            $dashArrecadadoTotal = 0;
            $brlArrecadadoTotal = 0;
            
            $distribuicaoTotal = 0;
            $disponiveisTotal = 0;
            $qtdTokensTotal = 0;
            $vendidosTotal = 0;
            $bonificadosTotal = 0;
            
            $json["fases"] = Array();
            foreach ($fases as $faseIco) {
                //$faseIco = new \Models\Modules\ICO\FaseIco();
                
                $distribuidos = $faseIco->tokensVendidos;
                $disponiveis = $faseIco->tokensParaVenda - $faseIco->tokensVendidos;
                $vendidos = $dadosFase[$faseIco->id]["tokensVendidos"];
                $bonificados = $dadosFase[$faseIco->id]["tokensBonificados"];
                $arrecadados = $dadosFase[$faseIco->id]["arrecadacao"];
                
                $brlArrecadado = (isset($arrecadados["BRL"]) ? $arrecadados["BRL"]["valorArrecadado"] : 0);
                $btcArrecadado = (isset($arrecadados["BTC"]) ? $arrecadados["BTC"]["valorArrecadado"] : 0);
                $ethArrecadado = (isset($arrecadados["ETH"]) ? $arrecadados["ETH"]["valorArrecadado"] : 0);
                $ltcArrecadado = (isset($arrecadados["LTC"]) ? $arrecadados["LTC"]["valorArrecadado"] : 0);
                $dashArrecadado = (isset($arrecadados["DASH"]) ? $arrecadados["DASH"]["valorArrecadado"] : 0);
                
                $brlArrecadadoTotal += $brlArrecadado;
                $btcArrecadadoTotal += $btcArrecadado;
                $ethArrecadadoTotal += $ethArrecadado;
                $ltcArrecadadoTotal += $ltcArrecadado;
                $dashArrecadadoTotal += $dashArrecadado;

                $distribuicaoTotal += $distribuidos;
                $disponiveisTotal += $disponiveis;
                $qtdTokensTotal += $faseIco->tokensParaVenda;
                $vendidosTotal += $vendidos;
                $bonificadosTotal += $bonificados;
                
                $json["fases"][] = Array (
                    "distribuidos" => number_format($distribuidos, 8, ",", "."),
                    "disponiveis" => number_format($disponiveis, 8, ",", "."),
                    "percentual" => number_format(($faseIco->tokensVendidos / $faseIco->tokensParaVenda * 100), 2, ".", ""),
                    "codigo" => $faseIco->ordem,
                    "vendidos" => number_format($vendidos, 8, ",", "."),
                    "bonificados" => number_format($bonificados, 8, ",", "."),
                    "btcArrecadado" => number_format($btcArrecadado, 8, ",", "."),
                    "ethArrecadado" => number_format($ethArrecadado, 8, ",", "."),
                    "ltcArrecadado" => number_format($ltcArrecadado, 8, ",", "."),
                    "brlArrecadado" => number_format($brlArrecadado, 8, ",", "."),
                    "dashArrecadado" => number_format($dashArrecadado, 8, ",", ".")
                );
            }
            
            $percentualIco = number_format(($qtdTokensTotal > 0 ? ($distribuicaoTotal / $qtdTokensTotal * 100) : 0), 2, ".", "");
            
            $json["ico"] = Array(
                "distribuidos" => number_format($distribuicaoTotal, 8, ",", "."),
                "disponiveis" => number_format($disponiveisTotal, 8, ",", "."),
                "percentual" => $percentualIco,
                "vendidos" => number_format($vendidosTotal, 8, ",", "."),
                "bonificados" => number_format($bonificadosTotal, 8, ",", "."),
                "btcArrecadado" => number_format($btcArrecadadoTotal, 8, ",", "."),
                "ethArrecadado" => number_format($ethArrecadadoTotal, 8, ",", "."),
                "ltcArrecadado" => number_format($ltcArrecadadoTotal, 8, ",", "."),
                "brlArrecadado" => number_format($brlArrecadadoTotal, 8, ",", "."),
                "dashArrecadado" => number_format($dashArrecadadoTotal, 8, ",", ".")
            );
            
            $json["origens"] = Array();
            foreach ($distribuicaoPorOrigem as $contaCorrenteBtc) {
                $json["origens"][] = Array(
                    "codigo" => $contaCorrenteBtc->origem,
                    "total" => number_format($contaCorrenteBtc->valor, 8, ".", ""),
                    "descricao" => $contaCorrenteBtc->getDescricaoOrigem()
                );
            }
            
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
}