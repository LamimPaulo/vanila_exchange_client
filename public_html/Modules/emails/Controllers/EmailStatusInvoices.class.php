<?php

namespace Modules\emails\Controllers;

class EmailStatusInvoices {
    
    public function emailInvoiceBoletoRemessa($object) {
        /*$object = new \Models\Modules\Cadastro\InvoiceBoleto(Array("id" => 1491875722));
        $invoiceBoletoRN = new \Models\Modules\Cadastro\InvoiceBoletoRn();
        $invoiceBoletoRN->conexao->carregar($object);*/
        try {
            $params = Array();
            $assunto = "";
            $emailCliente = null;
            
            if ($object instanceof \Models\Modules\Cadastro\InvoiceBoleto) {
                $params["titulo"] = "Pagamento de Boleto";
                $params["titulo2"] = "Alteração de status do pagamento";
                $params["urlInvoice"] = URLBASE_CLIENT . "site/boletos/consulta";
                
                switch ($object->status) {
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO:
                        $params["status"] = "<h1>Aguardando Pagamento da Invoice</h1>";
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO:
                        $params["status"] = "<strong>Solicitação Cancelada</strong>";
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_FINALIZADO:
                        $params["status"] = "<h1>Boleto Pago</h1>";
                        break;
                    case \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO:
                        $params["status"] = "<strong>Invoice Paga</strong>";
                        break;

                    default:
                        $params["status"] = "Desconhecido";
                }
                
                $boletoCliente = new \Models\Modules\Cadastro\BoletoCliente();
                $boletoCliente->id = $object->idBoletoCliente;
                $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
                $boletoClienteRn->conexao->carregar($boletoCliente);
                
                $emailCliente = $boletoCliente->email;
                $assunto = "Status Pagamento Boleto";
            } else if ($object instanceof \Models\Modules\Cadastro\InvoiceRemessaDinheiro) {
                $params["titulo"] = "Remessa de Dinheiro";
                $params["titulo2"] = "Alteração de status da transferência";
                $params["urlInvoice"] = URLBASE_CLIENT . "site/remessas/consulta";
                
                switch ($object->status) {
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO:
                        $params["status"] = "<strong>Aguardando Pagamento da Invoice</strong>";
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO:
                        $params["status"] = "<strong>Solicitação Cancelada</strong>";
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_FINALIZADO:
                        $params["status"] = "<strong>Tranferência Realizada</strong>";
                        break;
                    case \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO:
                        $params["status"] = "<strong>Invoice Paga</strong>";
                        break;

                    default:
                        $params["status"] = "Desconhecido";
                }
                
                $remessaDinheiro = new \Models\Modules\Cadastro\RemessaDinheiro();
                $remessaDinheiro->id = $object->idRemessaDinheiro;
                $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
                $remessaDinheiroRn->conexao->carregar($remessaDinheiro);
                
                $emailCliente = $remessaDinheiro->email;
                $assunto = "Status Remessa Dinheiro";
            }
            
            ob_start();
            \Utils\Layout::append("emails/atualizacao_status_invoice", $params);
            $conteudo = ob_get_contents();
            ob_end_clean();
            
            $listaEnvio = Array(
                Array(
                    "nome" => $emailCliente,
                    "email" => $emailCliente
                )
            );
            
            $mail = new \Utils\Mail($assunto, $conteudo, $listaEnvio);
            $mail->send();
            
            
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        
    }
    
}