<?php

namespace Modules\invoices\Controllers;

class Orders {
    
    public function listarInvoicesPendentes($params) {
        try {
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $result = $pedidoCartaoRn->conexao->listar("status = '".\Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO."'", NULL, NULL, NULL);
            
            foreach ($result as $pedidoCartao) {
                try {
                    //$pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
                    $this->updateStatus($pedidoCartao->idInvoice);
                } catch (\Exception $ex) {

                }
            }
            
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            
            $result = $recargaCartaoRn->conexao->listar("status = '".\Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO."'", NULL, NULL, NULL);
            foreach ($result as $recargaCartao) {
                try {
                    //$pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
                    $this->updateStatusRecarga($recargaCartao->idInvoice);
                } catch (\Exception $ex) {

                }
            }
            
            $invoiceBoletoRn = new \Models\Modules\Cadastro\InvoiceBoletoRn();
            
            $result = $invoiceBoletoRn->conexao->listar("status = '".\Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO."'", NULL, NULL, NULL);
            foreach ($result as $invoiceBoleto) {
                try {
                    //$pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
                    $this->updateStatusBoletoCliente($invoiceBoleto);
                } catch (\Exception $ex) {

                }
            }
            
            
            $invoiceRemessaRn = new \Models\Modules\Cadastro\InvoiceRemessaDinheiroRn();
            
            $result = $invoiceRemessaRn->conexao->listar("status = '".\Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO."'", NULL, NULL, NULL);
            foreach ($result as $invoiceRemessa) {
                try {
                    //$pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao();
                    $this->updateStatusRemessaDinheiro($invoiceRemessa);
                } catch (\Exception $ex) {

                }
            }
            
            
            $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
            $idsInvoicesMensalidades = $mensalidadeCartaoRn->getIdsInvoicesPendentes();
            foreach ($idsInvoicesMensalidades as $idInvoice) {
                $this->updateStatusMensalidadeCartao($idInvoice);
            }
        } catch (\Exception $ex) {
            
        }
    }
    
    public function updateStatus($idInvoice) {
        
        try {
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($idInvoice);
            //print_r($order);
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $pedidoCartao = $pedidoCartaoRn->getByIdInvoice($order->InvoiceId);

            if ($pedidoCartao != null) {
                $pedidoCartao->address = $order->DigitalCurrencyAddress;
                $pedidoCartao->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));

                if (in_array($order->Status, Array("NEW", "UNDERPAID", "RECEIVED"))) {
                    $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_AGUARDANDO;
                } else if (in_array($order->Status, Array("PAID", "OVERPAID"))) {
                    $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_PAGO;
                } else if (in_array($order->Status, Array("CANCELED", "EXPIRED"))) {
                    $pedidoCartao->status = \Utils\Constantes::STATUS_PEDIDO_CARTAO_CANCELADO;
                }
                
                $pedidoCartao->digitalCurrencyAmount = $order->DigitalCurrencyAmount;
                $pedidoCartao->digitalCurrency = $order->DigitalCurrency;
                $pedidoCartao->redirectUrl = $order->RedirectUrl;
                $pedidoCartao->expirationTimestamp = $order->ExpirationTimestamp;
                $pedidoCartao->currencyTotal = $order->CurrencyTotal;
                $pedidoCartao->digitalCurrencyAmountPaid = $order->DigitalCurrencyAmountPaid;
                $pedidoCartao->customId = $order->CustomID;
                $pedidoCartao->digitalCurrencyQuotation = $order->DigitalCurrencyQuotation;
                $pedidoCartao->notificationEmail = $order->NotificationEmail;
                $pedidoCartao->transferToAccountEstimateDate = ($order->TransferToAccountEstimateDate != null ? 
                        new \Utils\Data(str_replace("T", " ", $order->TransferToAccountEstimateDate)) : null);
                $pedidoCartao->redirectUrlReturn = $order->RedirectUrlReturn;

                $pedidoCartaoRn->salvar($pedidoCartao);
                
                return $pedidoCartao;
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        return null;
    }
    
    
    
    public function updateStatusRecarga($idInvoice) {
        
        try {
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($idInvoice);
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $recargaCartao = $recargaCartaoRn->getByIdInvoice($order->InvoiceId);

            if ($recargaCartao != null) {
                $recargaCartao->address = $order->DigitalCurrencyAddress;
                $recargaCartao->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));

                if (in_array($order->Status, Array("NEW", "UNDERPAID", "RECEIVED"))) {
                    $recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_AGUARDANDO;
                } else if (in_array($order->Status, Array("PAID", "OVERPAID"))) {
                    $recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO;
                    $recargaCartao->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));
                } else if (in_array($order->Status, Array("CANCELED", "EXPIRED", "CANCELED_TEMP"))) {
                    $recargaCartao->status = \Utils\Constantes::STATUS_RECARGA_CARTAO_CANCELADO;
                }
                
                $recargaCartao->valorBtc = $order->DigitalCurrencyAmount;
                $recargaCartao->idInvoice = $order->InvoiceId;

                $recargaCartaoRn->salvar($recargaCartao);
                
                //Cards::executaRecargaVisa($recargaCartao);
                
                return $recargaCartao;
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        return null;
    }
    
    
    
    
    
    public function updateStatusBoletoCliente(\Models\Modules\Cadastro\InvoiceBoleto $invoiceBoleto) {
        
        try {
            
            $invoiceBoletoRn = new \Models\Modules\Cadastro\InvoiceBoletoRn();
            $invoiceBoletoRn->conexao->carregar($invoiceBoleto);
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($invoiceBoleto->idInvoice);
            
            $boletoClienteRn = new \Models\Modules\Cadastro\BoletoClienteRn();
            $boletoCliente = new \Models\Modules\Cadastro\BoletoCliente(Array("id" => $invoiceBoleto->idBoletoCliente));
            $boletoClienteRn->conexao->carregar($boletoCliente);

            if ($order != null) {
                $invoiceBoleto->address = $order->DigitalCurrencyAddress;
                $invoiceBoleto->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));

                $status = $invoiceBoleto->status;
                if (in_array($order->Status, Array("NEW", "UNDERPAID", "RECEIVED"))) {
                    $status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_AGUARDANDO;
                } else if (in_array($order->Status, Array("PAID", "OVERPAID"))) {
                    $status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_PAGO;
                    $invoiceBoleto->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));
                } else if (in_array($order->Status, Array("CANCELED", "EXPIRED", "CANCELED_TEMP"))) {
                    $status = \Utils\Constantes::STATUS_BOLETO_CLIENTE_CANCELADO;
                }
                $invoiceBoleto->status = $status;
                $invoiceBoleto->valorBtc = $order->DigitalCurrencyAmount;
                $invoiceBoleto->idInvoice = $order->InvoiceId;

                $invoiceBoletoRn->salvar($invoiceBoleto);
                $boletoClienteRn->conexao->update(
                    Array(
                        "status" => $status,
                        "data_pagamento" => ($invoiceBoleto->dataPagamento != null ? 
                        $invoiceBoleto->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : null)
                    )
                );
                
                $mailStatusInvoices = new \Modules\emails\Controllers\EmailStatusInvoices();
                $mailStatusInvoices->emailInvoiceBoletoRemessa($invoiceBoleto);
                
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        return null;
    }
    
    
    
    public function updateStatusRemessaDinheiro(\Models\Modules\Cadastro\InvoiceRemessaDinheiro $invoiceRemessaDinheiro) {
        
        try {
            
            $invoiceRemessaDinheiroRn = new \Models\Modules\Cadastro\InvoiceRemessaDinheiroRn();
            $invoiceRemessaDinheiroRn->conexao->carregar($invoiceRemessaDinheiro);
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($invoiceRemessaDinheiro->idInvoice);
            
            $remessaDinheiroRn = new \Models\Modules\Cadastro\RemessaDinheiroRn();
            $remessaDinheiro = new \Models\Modules\Cadastro\RemessaDinheiro(Array("id" => $invoiceRemessaDinheiro->idRemessaDinheiro));
            $remessaDinheiroRn->conexao->carregar($remessaDinheiro);

            if ($order != null) {
                $invoiceRemessaDinheiro->address = $order->DigitalCurrencyAddress;
                $invoiceRemessaDinheiro->dataExpiracaoInvoice = new \Utils\Data(str_replace("T", " ", $order->ExpirationDate));

                $status = $invoiceRemessaDinheiro->status;
                if (in_array($order->Status, Array("NEW", "UNDERPAID", "RECEIVED"))) {
                    $status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_AGUARDANDO;
                } else if (in_array($order->Status, Array("PAID", "OVERPAID"))) {
                    $status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_PAGO;
                    $invoiceRemessaDinheiro->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));
                } else if (in_array($order->Status, Array("CANCELED", "EXPIRED", "CANCELED_TEMP"))) {
                    $status = \Utils\Constantes::STATUS_REMESSA_DINHEIRO_CANCELADO;
                }
                $invoiceRemessaDinheiro->status = $status;
                $invoiceRemessaDinheiro->valorBtc = $order->DigitalCurrencyAmount;
                $invoiceRemessaDinheiro->idInvoice = $order->InvoiceId;

                $invoiceRemessaDinheiroRn->salvar($invoiceRemessaDinheiro);
                $remessaDinheiroRn->conexao->update(
                    Array(
                        "status" => $status,
                        "data_pagamento" => ($invoiceRemessaDinheiro->dataPagamento != null ? 
                        $invoiceRemessaDinheiro->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : null)
                    )
                );
                
                
                $mailStatusInvoices = new \Modules\emails\Controllers\EmailStatusInvoices();
                $mailStatusInvoices->emailInvoiceBoletoRemessa($invoiceRemessaDinheiro);
                
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        return null;
    }
    
    
    
    
    public function updateStatusMensalidadeCartao($idInvoice) {
        
        try {
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($idInvoice);
            
            if ($order != null) {
                
                $mensalidadeCartaoRn = new \Models\Modules\Cadastro\MensalidadeCartaoRn();
                
                if (in_array($order->Status, Array("PAID", "OVERPAID"))) {
                    $mensalidadeCartaoRn->conexao->adapter->iniciar();
                    $mensalidades = $mensalidadeCartaoRn->conexao->listar("id_invoice = {$idInvoice}");
                    
                    foreach ($mensalidades as $mensalidadeCartao) {
                        $mensalidadeCartao->status = \Utils\Constantes::STATUS_MENSALIDADE_CARTAO_PAGO;
                        $mensalidadeCartao->dataPagamento = new \Utils\Data(date("d/m/Y H:i:s"));
                        
                        $mensalidadeCartaoRn->conexao->update(
                                Array(
                                    "status" => $mensalidadeCartao->status,
                                    "data_pagamento" => $mensalidadeCartao->dataPagamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                                ),
                                Array("id" => $mensalidadeCartao->id));
                    }
                    
                    $mensalidadeCartaoRn->conexao->adapter->finalizar();
                }
                
            }
        } catch (\Exception $ex) {
            //exit(print_r($ex));
        }
        return null;
    }
}