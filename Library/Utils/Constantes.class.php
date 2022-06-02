<?php
/**
 * Classe para definição de constantes do sistema
 */
namespace Utils;

/****
 * Contém os métodos e funções que realizam conversões no sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Constantes {
    
    public static function getDataInicioICO() {
        return new Data("09/11/2018 00:00:00");
    }
    
    const BD_SLAVE_1 = "slave1";
    
    const NOTIFICACAO_MOEDA_ATIVADO = 1;
    const NOTIFICACAO_MOEDA_DESATIVADO = 0;
    
    const PROCESSO_SAQUE_1 = 1;
    const PROCESSO_SAQUE_2 = 2;
    
    const ID_ICO = 1;
    const ID_MOEDA_ICO = 33;
    const QTD_MIN_TOKENS_CADASTRO_MOEDA = 1000;
    const BRASIL = 30;
    
    const COTACAO_BTC = 0.00;
    const COTACAO_BTC_VENDA = 0.00;
    
    const COTACAO_BTC_VOL = 0.0;
    const COTACAO_BTC_ULTIMA_VENDA = 0.00;
    const COTACAO_BTC_ULTIMA_COMPRA = 0.00;
    
    const SEED_RECUPERACAO_SENHA = "#jfhfgduHSGjhsjkdsmqoieuraPoFoncbDqA4qw896wolmncfgdduuwuququeQaW#";
    const SEED_AUTH = "#EsAEuMaMeDidAdESeGuRanCaParaOsTOKENS#{token}#EsAEuMaMeDidAdESeGuRanCaParaOsTOKENS#";
    const SEED_SENHA = "#$%_sistema_cadastro_referencias_#$%";
    const SEED_ZENDESK = "4804C0B6B2C86C9F075B74C30ED7C6FF5CBD4324C3F390C2407F85B39B8AA879";
    const ADMINISTRADOR = "A";
    const VENDEDOR = "V";
    
    
    const CLIENTE_AGUARDANDO = 0;
    const CLIENTE_ATIVO = 1;
    const CLIENTE_NEGADO = 2;
    
    
    const STATUS_PEDIDO_CARTAO_AGUARDANDO = "A";
    const STATUS_PEDIDO_CARTAO_CANCELADO = "C";
    const STATUS_PEDIDO_CARTAO_PAGO = "P";
    
    const DOCUMENTO_IDENTIDADE = 1;
    const DOCUMENTO_CLT = 2;
    const DOCUMENTO_CARTEIRA_PROFISSIONAL = 3;
    const DOCUMENTO_PASSAPORTE = 4;
    const DOCUMENTO_IDENTIFICACAO_FUNCIONAL = 5;
    const DOCUMENTO_CPF = 6;
    const DOCUMENTO_CARTEIRA_MOTORISTA = 7;
    const DOCUMENTO_CNPJ = 8;
    const DOCUMENTO_SSN = 9;
    
    
    
    const STATUS_RECARGA_CARTAO_AGUARDANDO = "A";
    const STATUS_RECARGA_CARTAO_CANCELADO = "C";
    const STATUS_RECARGA_CARTAO_PAGO = "P";
    const STATUS_RECARGA_CARTAO_FINALIZADO = "F";
    
    
    const STATUS_BOLETO_CLIENTE_AGUARDANDO = "A";
    const STATUS_BOLETO_CLIENTE_CANCELADO = "C";
    const STATUS_BOLETO_CLIENTE_PAGO = "P";
    const STATUS_BOLETO_CLIENTE_FINALIZADO = "F";
    
    const STATUS_REMESSA_DINHEIRO_AGUARDANDO = "A";
    const STATUS_REMESSA_DINHEIRO_CANCELADO = "C";
    const STATUS_REMESSA_DINHEIRO_PAGO = "P";
    const STATUS_REMESSA_DINHEIRO_FINALIZADO = "F";
    
    const STATUS_BOLETO_TIPO_DATA_VENCIMENTO = "V";
    const STATUS_BOLETO_TIPO_DATA_PAGAMENTO = "P";
    const STATUS_BOLETO_TIPO_DATA_CADASTRO = "C";
    
    
    const CONTA_CORRENTE = "C";
    const CONTA_POUPANCA = "P";
    
    
    const STATUS_REMESSA_DINHEIRO_DATA_PAGAMENTO = "P";
    const STATUS_REMESSA_DINHEIRO_DATA_CADASTRO = "C";
    
    const CARTAO_VISA = "visa";
    const CARTAO_MASTER = "master";
    
    const TIPO_DEPOSITO_TED = "TED";
    const TIPO_DEPOSITO_TEF = "TEF";
    const TIPO_DEPOSITO_DOC = "DOC";
    const TIPO_DEPOSITO_DINHEIRO = "DIN";
    
    
    const P2P_STATUS_AGUARDANDO_DEPOSITO = "A";
    const P2P_STATUS_PENDENTE = "B";
    const P2P_STATUS_PROCESSANDO = "P";
    const P2P_STATUS_CONCLUIDO = "F";
    const P2P_STATUS_NAO_CONFIRMADO = "N";
    const P2P_STATUS_CONFIRMADO = "C";
    
    
    
    const STATUS_MENSALIDADE_CARTAO_VENCIDA = "V";
    const STATUS_MENSALIDADE_CARTAO_AGUARDANDO = "A";
    const STATUS_MENSALIDADE_CARTAO_PAGO = "P";
    
    
    const TIPO_AUTH_EMAIL = "email";
    const TIPO_AUTH_SMS = "sms";
    const TIPO_AUTH_GOOGLE = "google";
    
    
    const P2P_ALTA = 1;
    const P2P_NORMAL = 2;
    const P2P_BAIXA = 3;
    const P2P_CONCLUIDA = 4;
    
    const TIPO_RESGATE = "R";
    const TIPO_TRANSFERENCIA = "T";
    
    
    const ENTRADA = "E";
    const SAIDA = "S";
    const DEPOSITO = "D";
    const SAQUE = "S";
    const ATUALIZACAO = "A";
    
    const STATUS_DEPOSITO_PENDENTE = "P";
    const STATUS_DEPOSITO_CONFIRMADO = "F";
    const STATUS_DEPOSITO_CANCELADO = "C";
    
    const STATUS_SAQUE_PENDENTE = "P";
    const STATUS_SAQUE_CONFIRMADO = "F";
    const STATUS_SAQUE_CANCELADO = "C";
    
    const DOC = "D";
    const TED = "T";
    const DINHEIRO = "R";
    const TRANSF_ENTRE_CONTAS = "E";
    const GERENCIA_NET = "G";
    
    const TRANF_INTERNA = 'I';
    const TRANF_EXTERNA = 'E';
    
    const ORDEM_COMPRA = "C";
    const ORDEM_VENDA = "V";
    
    const ORDEM_PASSIVA = "P";
    const ORDEM_ATIVA = "A";
    
    
    const STATUS_INVOICE_PDV_AGUARDANDO = "A";
    const STATUS_INVOICE_PDV_PAGO = "P";
    const STATUS_INVOICE_PDV_PAGOMAIS = "S";
    const STATUS_INVOICE_PDV_PAGOMENOS = "I";
    const STATUS_INVOICE_PDV_CANCELADO = "C";
    
    
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    
    
    const PERFIL_CLIENTE = "C";
    const PERFIL_VENDEDOR_COMISSIONADO = "V";
    const PERFIL_FRANQUIA_HW = "F";
    const PERFIL_OPEN_HOUSE = "O";
    
    
    const STATUS_NF_AGUARDANDO_AUTORIZACAO = "AguardandoAutorizacao";
    const STATUS_NF_SOLICITANDO_AUTORIZACAO = "SolicitandoAutorizacao";
    const STATUS_NF_AUTORIZACAO_SOLICITADA = "AutorizacaoSolicitada";
    const STATUS_NF_PROCESSANDO_AUTORIZACAO = "EmProcessoDeAutorizacao";
    const STATUS_NF_AUTORIZADA = "Autorizada";
    const STATUS_NF_AGUARDANDO_PDF = "AutorizadaAguardandoGeracaoPDF";
    const STATUS_NF_NEGADA = "Negada";
    const STATUS_NF_SOLICITANDO_CANCELAMENTO = "SolicitandoCancelamento";
    const STATUS_NF_PROCESSANDO_CANCELAMENTO = "EmProcessoDeCancelamento";
    const STATUS_NF_CANCELAMENTO_SOLICITADO = "CancelamentoSolicitado";
    const STATUS_NF_CANCELADA = "Cancelada";
    const STATUS_NF_CANCELADA_AGUARDANDO_PDF = "CanceladaAguardandoAtualizacaoPDF";
    const STATUS_NF_CANCELAMENTO_NEGADO = "CancelamentoNegado";
    
    const OPERACAO_DEPOSITO = "Deposito";
    const OPERACAO_SAQUE = "Saque";
    const OPERACAO_BOLETO = "Boleto";
    const OPERACAO_REMESSA_VALORES = "RemessaValores";

    const EXTORNO_PENDENTE = "P";
    const EXTORNO_REJEITADO = "R";
    const EXTORNO_CANCELADO = "C";
    const EXTORNO_FINALIZADO = "F";
    const EXTORNO_APROVADO = "A";
    
    const ACESSO = 1;
    const CADASTRAR = 2;
    const EDITAR = 3;
    const EXCLUIR = 4;
    const ALTERAR_STATUS = 5;
    const TRANSFERIR_PARA_EMPRESA = 6;
    const PERMISSOES = 7;
    
    const LICENCA_PENDENTE = "P";
    const LICENCA_APROVADO = "A";
    const LICENCA_NEGADA = "N";
    
    const QUANTIDADE_TENTATIVAS_SEGUNDO_FATOR = 10;
    
    const ORIGENS_COMISSAO_REAIS = Array(3,4,5,7,8);
    const ORIGENS_COMISSAO_BTC = Array(2);
    const ORIGEM_ICO_BTC = Array(4,5);
    const ORIGEM_VOTACAO = 6;
    const ORIGEM_RECOMPENSA_ICO = 8;
    
    
    const ORIGEM_SITE = "site";
    const ORIGEM_ICO_NEWC = "newctoken";
    const ORIGEM_ICO_APPNEWC = "appnewctoken";
    
    
    const TIPO_CREDITO_ICO_COMPRA = 1;
    const TIPO_CREDITO_ICO_BONUS = 2;
    
    const NOTIFICACAO_TIPO_DESATIVAR = 1;
    
    const COMPROVANTE_RESIDENCIA_FOTO = "comprovantes_residencia";
    const DOCUMENTOS_FOTO = "documentos";
    const SELFIE_FOTO = "selfies";
    const PROFILE_FOTO = "profile";
    const OUTROS_FOTO = "outros";
    
    const DOCUMENTO_VERIFICADO = 1;
    const DOCUMENTO_PENDENTE = 2;
    const DOCUMENTO_RECUSADO = 3;
    
    const DOCUMENTO_FRENTE = "DocumentoFrente";
    const DOCUMENTO_VERSO = "DocumentoVerso";
    const DOCUMENTO_COMP_RESIDENCIA = "ComprovanteResidencia";
    const DOCUMENTO_SELFIE = "Selfie";
    const DOCUMENTO_PJ = "DocumentoPJ";
    
    
    const MODO_TRADER = "trader";
    const MODO_BASIC = "basic";
    
    const REDE_ERC20 = "ERC20";
    const REDE_BEP20 = "BEP20";
    const REDE_WAVES = "WAVES";
    const REDE_BITCOIN = "BITCOIN";
    const REDE_DASH = "DASH";
    
    
    
    const QUEUE_COMANDO_USER_CADASTRAR = "user.cadastrar";
    const QUEUE_COMANDO_DADOS_CRIPTOGRAFAR = "dados.criptografar";
    const QUEUE_COMANDO_DADOS_DESCRIPTOGRAFAR = "dados.descriptografar";
    
    
}