<?php

namespace Modules\monitoramento\Controllers;


class Painel {
    
    private  $codigoModulo = "monitoramento";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            $logado = \Utils\Geral::getLogado();
            
            $rotinaRn = new \Models\Modules\Acesso\RotinaRn();
            $rotinas = $rotinaRn->getRotinas($logado);
            
            $array = Array();
            foreach ($rotinas as $r) {
                $array[$r->id] = $r->id;
            }
            
            $params["rotinas"] = $array;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("painel_monitoramento", $params);
    }
    
    
    
    public function getDados($params) {
        try {
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteHasLicencaRn = new \Models\Modules\Cadastro\ClienteHasLicencaRn();
            
            $dadosClientes = $clienteRn->getQuantidadeClientesPorStatus();
            
            $dadosFranquias = $clienteHasLicencaRn->getQuantidadeFranquiasPorStatus();
            
            $clientesAtivos = $dadosClientes["ativos"];
            $clientesAguardando = $dadosClientes["aguardando"];
            $clientesInativos = $dadosClientes["inativos"];
            $clientesTotal = $clientesAtivos + $clientesAguardando + $clientesInativos;
            
            $solicitacoesFranquias = $dadosFranquias["solicitacoes"];
            $franquiasAprovadas = $dadosFranquias["aprovadas"];
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $dadosPedidos = $pedidoCartaoRn->getQuantidadeCartoesPorStatus();
            
            
            $cartoesAtivos = $dadosPedidos["ativos"];
            $cartoesAguardando = $dadosPedidos["aguardando"];
            $cartoesPagos = $dadosPedidos["pagos"];
            $cartoesCancelados = $dadosPedidos["cancelados"];
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $dadosRecargas = $recargaCartaoRn->getQuantidadePorStatus();
            
            $recargaAguardando = $dadosRecargas["aguardando"];
            $recargaCanceladas = $dadosRecargas["canceladas"];
            $recargaPagas = $dadosRecargas["pagos"];
            $recargaFinalizadas = $dadosRecargas["finalizadas"];
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $dadosRemessa = $remessaDinheiroRn->getQuantidadePorStatus();
            
            $remessaAguardando = $dadosRemessa["aguardando"];
            $remessaPaga = $dadosRemessa["pago"];
            $remessaFinalizada = $dadosRemessa["finalizado"];
            $remessaCancelada = $dadosRemessa["cancelado"];
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $dadosBoleto = $boletoClienteRn->getQuantidadePorStatus();
            
            $boletoAguardando = $dadosBoleto["aguardando"];
            $boletoPago = $dadosBoleto["pago"];
            $boletoFinalizado = $dadosBoleto["finalizado"];
            $boletoCancelado = $dadosBoleto["cancelado"];
            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $dadosDepositos = $depositoRn->getQuantidadePorStatus();
            
            $depositosPendentes = $dadosDepositos["pendente"];
            $depositosCancelados = $dadosDepositos["cancelado"];
            $depositosConfirmados = $dadosDepositos["confirmado"];
            
            $saqueRn = new \Models\Modules\Cadastro\SaqueRn();
            $dadosSaques = $saqueRn->getQuantidadePorStatus();
            
            $saquesPendentes = $dadosSaques["pendente"];
            $saquesCancelados = $dadosSaques["cancelado"];
            $saquesConfirmados = $dadosSaques["confirmado"];
            
            $transacaoPendenteBtcRn = new \Models\Modules\Cadastro\TransacaoPendenteBtcRn();
            
            
            $statusConsumivelRn = new \Models\Modules\Cadastro\StatusConsumivelRn();
            $statusConsumivel = $statusConsumivelRn->getStatus();
            
            $json["qtdSms"] = $statusConsumivel->quantidadeSms;
            $json["valSms"] = ($statusConsumivel->validadeSms != null ? $statusConsumivel->validadeSms->formatar(\Utils\Data::FORMATO_PT_BR) : "");
            $json["consultas"] = number_format($statusConsumivel->creditosConsultaDocumentos, 0);
            
            $json["transacoesPendentes"] = $transacaoPendenteBtcRn->getQuantidadePorStatus();
            
            $json["clientesAtivos"] = $clientesAtivos;
            $json["clientesAguardando"] = $clientesAguardando;
            $json["clientesInativos"] = $clientesInativos;
            $json["clientesTotal"] = $clientesTotal;
            
            
            $json["cartoesAtivos"] = $cartoesAtivos;
            $json["cartoesAguardando"] = $cartoesAguardando;
            $json["cartoesPagos"] = $cartoesPagos;
            $json["cartoesCancelados"] = $cartoesCancelados;
            $json["cartoesTotal"] = $cartoesAtivos + $cartoesAguardando + $cartoesPagos + $cartoesCancelados;
            
            $json["recargasAguardando"] = $recargaAguardando;
            $json["recargasCanceladas"] = $recargaCanceladas;
            $json["recargasPagas"] = $recargaPagas;
            $json["recargasFinalizadas"] = $recargaFinalizadas;
            
            
            $json["remessasAguardando"] = $remessaAguardando;
            $json["remessasCanceladas"] = $remessaCancelada;
            $json["remessasPagas"] = $remessaPaga;
            $json["remessasFinalizadas"] = $remessaFinalizada;
            
            
            $json["boletosAguardando"] = $boletoAguardando;
            $json["boletosCancelados"] = $boletoCancelado;
            $json["boletosPagos"] = $boletoPago;
            $json["boletosFinalizados"] = $boletoFinalizado;
            
            
            $json["saquesCancelados"] = $saquesCancelados;
            $json["saquesPendentes"] = $saquesPendentes;
            $json["saquesConfirmados"] = $saquesConfirmados;
            
            
            $json["depositosCancelados"] = $depositosCancelados;
            $json["depositosPendentes"] = $depositosPendentes;
            $json["depositosConfirmados"] = $depositosConfirmados;
            
            
            $json["solicitacoesFranquias"] = $solicitacoesFranquias;
            $json["franquiasAprovadas"] = $franquiasAprovadas;
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
}