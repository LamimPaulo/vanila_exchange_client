<?php

namespace Utils;

class Idiomas {
    
    private static $default = "pt-BR";
    
    public static function get($chave, $idioma) {
        
        if (isset(self::$lang[$idioma][$chave])) {
            return self::$lang[$idioma][$chave];
        } else if (isset(self::$lang[self::$default][$chave])) {
            return self::$lang[self::$default][$chave];
        }
        return "undefined";
    }
    
    
    private static $lang = Array(
        
        
        "pt-BR" => Array(
            "depositoStatusPendente" => "Pendente",
            "depositoStatusConfirmado" => "Confirmado",
            "depositoStatusCancelado" => "Cancelado",
            
            "cofreSaldoInvestido" => "Investido",
            "cofreSaldoEmProvisionamento" => "Saldo em provisionamento",
            "cofreSaldoSacado" => "Sacado",
            
            //Boleto e Remessa
            "aguardandoPagamento" => "Aguardando Pagamento",
            "solicitacaoCancelada" => "Solicitação Cancelada",
            "transferenciaEfetuada" => "Transferência Efetuada",
            "pagamentoRecebido" => "Pagamento Recebido",
            "boletoPago" => "Boleto Pago",
            "pagamentoRecebido" => "Pagamento Recebido",
            "desconhecido" => "Desconhecido",
            
            "saqueStatusPendente" => "Pendente",
            "saqueStatusConfirmado" => "Confirmado",
            "saqueStatusCancelado" => "Cancelado",
            
            
        ),
        
        
        
        "en-US" => Array(
            "depositoStatusPendente" => "Pending",
            "depositoStatusConfirmado" => "Confirmed",
            "depositoStatusCancelado" => "Canceled",
            
            
            "cofreSaldoInvestido" => "Invested",
            "cofreSaldoEmProvisionamento" => "In Provisioning",
            "cofreSaldoSacado" => "Withdrawn",
            
            //Boleto e Remessa
            "aguardandoPagamento" => "Awaiting Payment",
            "solicitacaoCancelada" => "Request Canceled",
            "transferenciaEfetuada" => "Transfer Done",
            "pagamentoRecebido" => "Payment Received",
            "boletoPago" => "Paid Boleto",
            "desconhecido" => "Unknown",
            
            "saqueStatusPendente" => "Pending",
            "saqueStatusConfirmado" => "Confirmed",
            "saqueStatusCancelado" => "Canceled",
        ),
        
        
        
        "es-ES" => Array(
            "depositoStatusPendente" => "Pendiente",
            "depositoStatusConfirmado" => "Confirmado",
            "depositoStatusCancelado" => "Cancelado",
            
            
            "cofreSaldoInvestido" => "Invertido",
            "cofreSaldoEmProvisionamento" => "Saldo en provisión",
            "cofreSaldoSacado" => "Sacado",
            
            //Boleto e Remessa
            "aguardandoPagamento" => "Esperando Pago",
            "solicitacaoCancelada" => "Solicitud Cancelada",
            "transferenciaEfetuada" => "Transferencia Efectiva",
            "pagamentoRecebido" => "Pago Recibido",
            "boletoPago" => "Boleto Pago",
            "desconhecido" => "Desconocido",
            
            "saqueStatusPendente" => "Pendiente",
            "saqueStatusConfirmado" => "Confirmado",
            "saqueStatusCancelado" => "Cancelado",
        )
        
    );
    
}