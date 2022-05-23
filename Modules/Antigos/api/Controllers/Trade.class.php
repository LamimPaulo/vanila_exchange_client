<?php

namespace Modules\api\Controllers;

class Trade {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function fees($params) {
        
        try {
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $di = new \Utils\Data(date("d/m/Y H:i:s"));
            $di->subtrair(0, 0, 0, 24);
            $df = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
            
            $processosSaque = $saqueRn->calcularQuantiadeHorasMediasValidacaoSaque($di, $df);
            $processosDeposito = $depositoRn->calcularQuantiadeHorasMediasValidacaoDeposito($di, $df);

            
            $json["saque"] = Array(
                /* Saque */
                "valorTarifaTed" => number_format($configuracao->tarifaTed, 2, ".", ""),
                "comissaoSaque" => number_format($configuracao->taxaSaque, 2, ".", ""),
                "valorMinimoSaque" => number_format($configuracao->valorMinSaqueReais, 2, ".", ""),
                "percentualSaque" => number_format($configuracao->taxaSaque, 2, ".", ""),
                "tarifaTed" => number_format($configuracao->tarifaTed, 2, ".", ""),
                "prazoMaximoSaque" => $processosSaque["max"],
                "prazoMedioSaque" => $processosSaque["media"]                
            );

            $json["dinheiro"] = Array(
                "percentualDepositoZeroAteDoisMil" => number_format($configuracao->percentualDepositos, 2, ".", ""),
                "percentualDepositoDoisAteCincoMil" => number_format($configuracao->depositoDoisCinco, 2, ".", ""),
                "percentualDepositoCincoAteDezMil" => number_format($configuracao->depositoCincoDez, 2, ".", ""),
                "percentualDepositoDezAteCinquentaMil" => number_format($configuracao->depositoDezCinquenta, 2, ".", ""),
                "percentualDepositoAcimaCinquentaMil" => number_format($configuracao->depositoCinquentaAcima, 2, ".", ""),
                "prazoMaximoDeposito" => $processosDeposito["max"],
                "prazoMedioDeposito" => $processosDeposito["media"]
            );
            
            $json["deposito"] = Array(
                // Guia Dinheiro do Painel de Controle
                "taxaDeposito" =>  number_format($configuracao->taxaDeposito, 2, ".", ""),
                "estornoDeposito" => number_format($configuracao->percentualEstornoDeposito, 2, ".", ""),
                "prazoMaximoDeposito" => $processosDeposito["max"],
                "prazoMedioDeposito" => $processosDeposito["media"]
            );
            
            $json["negociacoes"] = Array(
                /* Exchange */
                "percentualCompraAtiva" => number_format($configuracao->percentualCompra, 2, ".", ""),
                "percentualCompraPassiva" => number_format($configuracao->percentualCompraPassiva, 2, ".", ""),
                "percentualVendaAtiva" => number_format($configuracao->percentualVenda, 2, ".", ""),
                "percentualVendaPassiva" => number_format($configuracao->percentualVendaPassiva, 2, ".", "")
            );

            /*
            $json["cartao"] = Array(
                // Cartao Pre-pago 
                "valorAquisicao" => number_format($configuracao->valorCartao, 2, ".", ""),
                "taxaRecarga" => number_format($configuracao->taxaRecargaCartao, 2, ".", ""),
                "cartaoMensalidade" => number_format($configuracao->valorMensalidadeCartao, 2, ".", ""),
                "tempoRecarga" => $configuracao->tempoRecargaCartao,
                "prazoPAC" => "21"
            );
            */

            //Remessa
            $json["remessa"] = Array(
                
                "prazoDiasRemessa" => $configuracao->prazoEfetuacaoRemessa,
                "comissaoRemessa" => number_format($configuracao->taxaRemessa, 2, ".", ""),
                "valorMinimoRemessa" => number_format($configuracao->valorMinimoRemessa, 2, ".", ""),
                "valorMaximoRemessa" => number_format($configuracao->valorMaximoRemessa, 2, ".", "")
            );
            
            
             //Boleto 
            $json["boleto"] = Array(
               
                "prazoDiasBoleto" => $configuracao->prazoPagamentoBoleto,
                "comissaoBoleto" => number_format($configuracao->taxaBoleto, 2, ".", ""),
                "valorMinimoBoleto" => number_format($configuracao->valorMinimoBoleto, 2, ".", ""),
                "valorMaximoBoleto" => number_format($configuracao->valorMaximoBoleto, 2, ".", "") 
            );
            

            $json["convidado"] = Array(
                /* convidados */
                "comissao" => number_format($configuracao->comissaoConvite, 2, ".", "")
            );

            
            $json["transferencia"] = Array(
                /* Transferencias */
                "comissaoTransferenciaInternaReais" => number_format($configuracao->taxaTransferenciaInternaReais, 2, ".", ""),
                "comissaoTransferenciaReaisMinima" => number_format($configuracao->taxaTransferenciaInternaReais + 0.01, 2, ".", "")
            );

            
            $json["processos"] = Array(
                "prazoMaximoAtendimento" => $configuracao->prazoHorasAtendimento,
                "prazoMaximoValidaConta" => $configuracao->prazoHorasValidacaoConta
            );
            
            
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancarias = $contaBancariaEmpresaRn->listar("ativo > 0", "id", null, null, true);
            $json["bancos"] = Array( );
            foreach ($contasBancarias as $conta) {
                $json["bancos"][] = Array(
                    "nome" => "{$conta->banco->codigo} - {$conta->banco->nome}"
                );
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function price($params) {
        
        try {
            
            $simbolo = \Utils\Get::get($params, 0, null);
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $sWhere = "";
            if (!empty($simbolo)) {
                $simbolo = strtoupper($simbolo);
                $sWhere = " AND simbolo = '{$simbolo}' ";
            }
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->conexao->listar("ativo > 0 AND status_mercado > 0 AND id != 33 {$sWhere}", "principal DESC, simbolo ASC");
            
            
            if (!empty($simbolo) && sizeof($moedas) < 0) {
                throw new \Exception("Moeda inválida ou desativada no sistema.");
            }
            
            $moedasTemp = Array();
            $moedasRef = Array();
            foreach ($moedas as $moeda) {
                $moedasTemp[] = $moeda;
                $moedasRef[] = $moeda->simbolo;
            }
            
            $lista = Array();
            
            
            
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $configuracao->taxaTransferenciaInternaBtc;
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            
            foreach ($moedasTemp as $moeda) {
                //$moeda = new \Models\Modules\Cadastro\Moeda();
                
                $paridade = $paridadeRn->find($moeda->id, 1);
                //$paridade = new \Models\Modules\Cadastro\Paridade();
                
                if ($paridade != null) { 
                    
                    $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);
                    $taxaTransferencia = number_format(($moeda->id == 2 ? $configuracao->taxaTransferenciaInternaBtc : ($taxaMoeda != null ? $taxaMoeda->taxaTransferencia : 0)), $moeda->casasDecimais, ".", "");
                    $minConfirmacoes = ($moeda->id == 2 ? $configuracao->qtdMinConfirmacoesTransacao : ($taxaMoeda != null ? $taxaMoeda->minConfirmacoes : 0));

                    if ($moeda->statusMercado > 0) {
                        //$taxaMoeda = new \Models\Modules\Cadastro\TaxaMoeda();

                        if ($paridade->precoCompra > 0 && $paridade->precoVenda > 0) { 
                            if ($paridade->precoCompra >  $paridade->precoVenda) { 
                                $spred = (($paridade->precoCompra / $paridade->precoVenda) - 1) * 100;
                            } else {
                                $spred = (($paridade->precoVenda / $paridade->precoCompra) - 1) * 100;
                            }
                        } else {
                            $spred = 0;
                        }

                        $str = $moeda->nome;
                        $str = strtolower(trim($str));
                        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
                        $str = preg_replace('/-+/', "-", $str);


                        $lista[] = Array(
                            "nome" => $moeda->nome,
                            "slug" => $str,
                            "simbolo" => $moeda->simbolo,
                            "mercadoAtivo" => ($moeda->statusMercado > 0),
                            "compra" => number_format($paridade->precoCompra, $configuracao->qtdCasasDecimais, ".", ""),
                            "venda" => number_format($paridade->precoVenda, $configuracao->qtdCasasDecimais, ".", ""),
                            "volume" => number_format($paridade->volume, $configuracao->qtdCasasDecimais, ".", ""),
                            "ultPreco" => number_format($paridade->ultimaCompra, $configuracao->qtdCasasDecimais, ".", ""),
                            "spred" => number_format($spred, 2, ".", ""),
                            "logo" => IMAGES . "currencies/". $moeda->icone,
                            "taxaTransferencia" => $taxaTransferencia,
                            "minConfirmacoes" => $minConfirmacoes
                        );

                    } else {
                        $currencies = $this->getMoedasFromCoinMarketCap($moedasRef);

                        $spred = (($currencies[$moeda->simbolo]["precoBrl"] / $currencies[$moeda->simbolo]["precoBrl"]) - 1) * 100;

                        if ($spred < 0) {
                            $spred = $spred * (-1);
                        }

                        if (isset($currencies[$moeda->simbolo])) {
                            $lista[] = Array(
                                "nome" => $moeda->nome,
                                "simbolo" => $moeda->simbolo,
                                "mercadoAtivo" => ($moeda->statusMercado > 0),
                                "compra" => number_format($currencies[$moeda->simbolo]["precoBrl"], $configuracao->qtdCasasDecimais, ".", ""),
                                "venda" => number_format($currencies[$moeda->simbolo]["precoBrl"], $configuracao->qtdCasasDecimais, ".", ""),
                                "volume" => $currencies[$moeda->simbolo]["vol24hBrl"],
                                "ultCompra" => number_format($currencies[$moeda->simbolo]["precoBrl"], $configuracao->qtdCasasDecimais, ".", ""),
                                "ultVenda" => number_format($currencies[$moeda->simbolo]["precoBrl"], $configuracao->qtdCasasDecimais, ".", ""),
                                "spred" => number_format($spred, 2, ".", ""),
                                "logo" => IMAGES . "currencies/". $moeda->icone,
                                "taxaTransferencia" => $taxaTransferencia,
                                "minConfirmacoes" => $minConfirmacoes
                            );
                        }
                    }
                }
            }
                                                                                                                                                                                                 
            
            $json["currencies"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    
    private function getMoedasFromCoinMarketCap($moedas) {
        //exit(print_r($moedas));
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.coinmarketcap.com/v1/ticker/?limit=2000&convert=BRL",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: c914111e-e897-4e16-16e5-3b0ffcddb492"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception($err);
        }
        
        $json = json_decode($response, true);
        
        if (isset($json->error)) {
            throw new \Exception($json->error);
        }
        
        $currencies = Array();
        foreach ($json as $j) {
            
            if (in_array($j["symbol"], $moedas)) {
                
                $currencies[$j["symbol"]] = Array(
                    "nome" => $j["name"],
                    "simbolo" => $j["symbol"],
                    "vol24hUsd" => $j["24h_volume_usd"],
                    "percentualMudancaUltHora" => $j["percent_change_1h"],
                    "precoBrl" => $j["price_brl"],
                    "vol24hBrl" => $j["24h_volume_brl"]
                );
                
            }
            
        }
        return $currencies;
        
    }
    
    public function dados ($params) {
        try {
            
            $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataInicial->subtrair(0, 6, 0, 24, 0, 0);
            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
            $dadosVolume = $ordemExecutadaRn->calcularReais($dataInicial, $dataFinal);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $dadosClientes = $clienteRn->getQuantidadeClientesPorStatus();
            
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->conexao->listar("ativo > 0 AND status_mercado > 0", "principal DESC, simbolo");
            
            foreach ($moedas as $moeda) {
                $json[$moeda->simbolo] = Array(
                    "nome" => $moeda->nome,
                    "volume" => number_format($moeda->volume, 25, ".", "")
                );
            }
            
            $online = $clienteRn->getQuantidadeClientesOnline();
            
            $json["online"] = sizeof($online);
            
            $json["clientesAtivos"] = $dadosClientes["ativos"];
            $json["clientesBloqueados"] = $dadosClientes["inativos"];
            $json["clientesAguardando"] = $dadosClientes["aguardando"];
            $json["volumeReais"] = number_format($dadosVolume,2, ".", "");
            
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
//    public function bot ($params) {
//        try {
//            
//            $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
//            $dataInicial->subtrair(0, 6, 0, 24, 0, 0);
//            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
//            
//            $configuracoes = \Models\Modules\Cadastro\ConfiguracaoRn::get();
//            
//            
//            $ordemExecutadaRn = new \Models\Modules\Cadastro\OrdemExecutadaRn();
//            
//            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
//            $moedas = $moedaRn->conexao->listar("ativo > 0 AND status_mercado > 0", "principal DESC, simbolo");
//            
//            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
//            
//            foreach ($moedas as $moeda) {
//                $volume = $ordemExecutadaRn->calcularVolumeMoeda($moeda->id, $dataInicial, $dataFinal);
//                $precos = $orderBookRn->getPrecos($moeda->id);
//                
//                $json[$moeda->simbolo] = Array(
//                    "nome" => $moeda->nome,
//                    "volume" => number_format($volume, $moeda->casasDecimais, ".", ""),
//                    "compra" => number_format($precos["compra"], $configuracoes->qtdCasasDecimais, ",", "."),
//                    "venda" => number_format($precos["venda"], $configuracoes->qtdCasasDecimais, ",", "."),
//                    "ultimo" => number_format($precos["ultimo"], $configuracoes->qtdCasasDecimais, ",", ".")
//                );
//            }
//            
//            $json["sucesso"] = true;
//        } catch (Exception $ex) {
//            $json["sucesso"] = false;
//            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
//        }
//        print json_encode($json);
//    }
    
}
