<?php

namespace Modules\principal\Controllers;

class DashboardAdm {
    
    public function getSaldosMoedasClientes($params) {
        
        try {
            
            $listaSaldo = Array();
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            $saldosCliente = $contaCorrenteBtcRn->calcularSaldoCurrencies();
            $reaisCliente = $contaCorrenteReaisRn->calcularSaldoSistema();
            
            $listaCliente = Array();
            
            $listaCliente[] = $reaisCliente;
            foreach ($saldosCliente as $moeda) {
                $listaCliente[] = $moeda;
            }
            
            $contaCorrenteBtcEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            
            $saldosEmpresa = $contaCorrenteBtcEmpresaRn->calcularSaldoCurrencies();
            $reaisEmpresa = $contaCorrenteReaisEmpresaRn->calcularSaldoSistema();
            
            $listaEmpresa = Array();
            
            $listaEmpresa[] = $reaisEmpresa;
            foreach ($saldosEmpresa as $moeda) {
                $listaEmpresa[] = $moeda;
            }
            
            $saldoCore = Array();
            $statusCoreRn = new \Models\Modules\Cadastro\StatusCoreRn();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("id > 1");
            $listaSaldo[] = Array("moeda" => "real", "id" => "1", "saldo" => number_format(($reaisCliente["saldo"] + $reaisEmpresa["saldo"]), 2, ".", ""));
            $datas = Array();
            
            foreach ($moedas as $moeda) {
                $c = (isset($saldosCliente[$moeda->simbolo]) ? $saldosCliente[$moeda->simbolo]["saldo"] : 0);
                $e = (isset($saldosEmpresa[$moeda->simbolo]) ? $saldosEmpresa[$moeda->simbolo]["saldo"] : 0);
                
                $listaSaldo[] = Array("moeda" => $moeda->simbolo, "id" => $moeda->id, "saldo" => number_format(($c + $e), $moeda->casasDecimais, ".", ""));
                
                $alertaApi = false;
                $alertaCore = false;
                
                $statusCore = $statusCoreRn->getByIdMoeda($moeda);
                //$statusCore = new \Models\Modules\Cadastro\StatusCore();
                if ($moeda->idMoedaPrincipal == null) {
                    if ($statusCore == null) {
                        $alertaApi = true;
                        $alertaCore = true;
                    } else {

                        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));

                        if ($statusCore->dataUltimaAtualizacao == null) {
                            $alertaApi = true;
                        } else {
                            $difApi = $dataAtual->diferenca($statusCore->dataUltimaAtualizacao);

                            if ($difApi->d > 0 || $difApi->m > 0 || $difApi->y > 0 || $difApi->h > 1) {
                                $alertaApi = true;
                            }
                        }

                        if ($statusCore->dataUltimaAtualizacaoCore == null) {
                            $alertaCore = true;
                        } else {
                            $difCore = $dataAtual->diferenca($statusCore->dataUltimaAtualizacaoCore);
                            if ($difCore->d > 0 || $difCore->m > 0 || $difCore->y > 0 || $difCore->h > 1) {
                                $alertaCore = true;
                            }
                        }

                    }
                }
                
                $saldoCore[] = Array("moeda" => $moeda->simbolo, "id" => $moeda->id, "saldo" => number_format(($statusCore != null ? $statusCore->balance : 0), $moeda->casasDecimais, ".", ""));
                
                $datas[] = Array(
                    "moeda" => $moeda->id,
                    "api" => (($statusCore != null && $statusCore->dataUltimaAtualizacao != null) ? $statusCore->dataUltimaAtualizacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "Sem Leitura"),
                    "core" => (($statusCore != null && $statusCore->dataUltimaAtualizacaoCore != null) ? $statusCore->dataUltimaAtualizacaoCore->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "Sem Leitura"),
                    "alertaApi" => $alertaApi,
                    "alertaCore" => $alertaCore
                );
            }
            
            
            //Total Investido
            $listaInvestido = Array();
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $result = $cofreRn->somaSaldoInvestido();
            foreach ($result as $investido){
                $listaInvestido[] = $investido;
            }
            
            //Soma do TOTAL - Investido + Saldo
            for ($i = 0; $i < sizeof($listaSaldo); $i++){
                for ($a = 0; $a < sizeof($listaInvestido); $a++) {
                    if ($listaSaldo[$i]["id"] == $listaInvestido[$a]["id_moeda"]) {
                        $listaSaldo[$i]["saldo"] = number_format($listaSaldo[$i]["saldo"] + $listaInvestido[$a]["volume_total"], 8);
                    }
                }
            }
           
            
            $json["datas"] = $datas;
            $json["clientes"] = $listaCliente;
            $json["empresa"] = $listaEmpresa;
            $json["investido"] = $listaInvestido;
            $json["saldo"] = $listaSaldo;
            $json["core"] = $saldoCore;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    
    public function getSaldosMoedasEmpresa($params) {
        
        try {
            
            
            
            $json["saldos"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}

