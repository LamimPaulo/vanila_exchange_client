<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Configuracao {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     * Percentual de comissão pago nos depósitos
     * @var Double
     */
    public $percentualDepositos;

    
    /**
     *
     * @var Double 
     */
    public $percentualVenda;
    
    /**
     *
     * @var Double 
     */
    public $percentualCompra;
    
    /**
     *
     * @var Double 
     */
    public $valorCartao;
    
    /**
     *
     * @var Double 
     */
    public $valorCartaoEx;
    
    /**
     *
     * @var String 
     */
    public $emailSmtp;
    
    /**
     *
     * @var Integer 
     */
    public $emailPorta;
    
    
    /**
     *
     * @var String 
     */
    public $emailUsuario;
    
    
    /**
     *
     * @var String 
     */
    public $emailSenha;
    
    
    /**
     *
     * @var String 
     */
    public $emailNome;
    
    /**
     * 
     * @var Integer 
     */
    public $emailSmtpAuth;
    
    /**
     *
     * @var Double 
     */
    public $valorMensalidadeCartao;
    
    /**
     *
     * @var Double 
     */
    public $taxaTransferenciaInternaReais;
    
    /**
     *
     * @var Double 
     */
    public $taxaTransferenciaInternaBtc;
    
    /**
     *
     * @var Double 
     */
    public $taxaDeposito;
    
    /**
     *
     * @var Double 
     */
    public $taxaSaque;
    
    /**
     *
     * @var Double 
     */
    public $tarifaTed;
    
    /**
     *
     * @var Double 
     */
    public $percentualCompraPassiva;
    
    /**
     *
     * @var Double 
     */
    public $valorMaximoRemessa;
    
    /**
     *
     * @var Double 
     */
    public $valorMinimoRemessa;
    
    /**
     *
     * @var Double 
     */
    public $valorMaximoBoleto;
    
    /**
     *
     * @var Double 
     */
    public $taxaRemessa;
    
    /**
     *
     * @var Double 
     */
    public $depositoDoisCinco;
    
    /**
     *
     * @var Double 
     */
    public $taxaBoleto;
    
    /**
     *
     * @var Integer 
     */
    public $prazoEfetuacaoRemessa;
    
    /**
     *
     * @var Double 
     */
    public $depositoCincoDez;
    
    /**
     *
     * @var Double 
     */
    public $depositoDezCinquenta;
    
    /**
     *
     * @var Double 
     */
    public $depositoCinquentaAcima;
    
    /**
     *
     * @var Integer 
     */
    public $tempoRecargaCartao;
    
    /**
     *
     * @var Double 
     */
    public $valorMinimoBoleto;
    
    /**
     *
     * @var Integer 
     */
    public $prazoPagamentoBoleto;
    
    /**
     *
     * @var Double 
     */
    public $percentualVendaPassiva;
    
    /**
     *
     * @var Double 
     */
    public $taxaRecargaCartao;
    
    /**
     *
     * @var Double 
     */
    public $taxaAdesaoClube;
    
    /**
     *
     * @var Double 
     */
    public $mensalidadeClube;
    
    /**
     *
     * @var Integer 
     */
    public $qtdMinConfirmacoesTransacao;
    
    
    /**
     *
     * @var Integer 
     */
    public $qtdMaxConfirmacoesTransacao;
    
    
    /**
     *
     * @var Numeric 
     */
    public $valorMaximoSaqueBtc;
    
    
    /**
     *
     * @var Integer 
     */
    public $statusNovosCadastros;
    
    
    
    /**
     *
     * @var Integer 
     */
    public $statusLoginSistema;
    
    
    /**
     *
     * @var Double 
     */
    public $valorMinSaqueReais;
    
    /**
     *
     * @var Double 
     */
    public $valorMinimoNegociacaoBrl;
    
    /**
     *
     * @var Double 
     */
    public $volumeMinimoNegociacaoBtc;
    
    /**
     *
     * @var String 
     */
    public $alertaSite;
    
    /**
     *
     * @var String 
     */
    public $tipoAlertaSite;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataFinalAlertaSite;
    
    /**
     *
     * @var Doube 
     */
    public $comissaoUsoCofre;
    
    /**
     *
     * @var Integer 
     */
    public $prazoDiasSaqueCofre;
    
    /**
     *
     * @var Integer 
     */
    public $exibirMensagemSite;
    
    /**
     *
     * Flag que força o envio de autenticação em dois fatores por um único meio
     * 
     * email = Força o envio por email
     * sms = Força o envio por sms
     * nao = desabilita o forçamento do envio
     * 
     * Caso essa flag esteja desabilitada prevalecerá o envio pelo configurado no perfil do cliente.
     * 
     * @var String 
     */
    public $forcarEnvioToken;
    
    /**
     *
     * @var Double 
     */
    public $percentualEstornoDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $prazoHorasValidacaoDepositos;
    
    
    /**
     *
     * @var Integer 
     */
    public $prazoHorasAtendimento;
    
    
    /**
     *
     * @var Integer 
     */
    public $prazoHorasValidacaoSaques;
    
    /**
     *
     * @var Integer
     */
    public $statusUpgradePerfil;
    
    /**
     *
     * @var Integer 
     */
    public $prazoHorasValidacaoConta;
    
    /**
     *
     * @var Integer 
     */
    public $statusCarteiras;
    
    /**
     *
     * @var Double 
     */
    public $comissaoConvite;
    
    /**
     *
     * @var Integer 
     */
    public $statusTransferenciaBrl;
    
    /**
     *
     * @var Integer 
     */
    public $statusTransferenciaCurrency;
    
    
    
    /**
     *
     * @var Integer 
     */
    public $statusDepositoBrl;
    
    
    
    /**
     *
     * @var Integer 
     */
    public $statusDepositoCurrency;
    
    /**
     *
     * @var Double 
     */
    public $taxaRedeBtc;
    
    /**
     *
     * @var Integer 
     */
    public $qtdCasasDecimais;
    
    /**
     *
     * @var Double 
     */
    public $taxaDepositoBoleto;
    
    /**
     *
     * @var Integer 
     */
    public $prazoDiasContratoCofre;
    
    /**
     *
     * @var Integer 
     */
    public $nightMode;
    
    
    /**
     *
     * @var Numeric 
     */
    public $tarifaDepositoBoleto;
    
    /**
     *
     * @var Double 
     */
    public $votacaoMinimoSaldo;
    
    /**
     *
     * @var Integer 
     */
    public $votacaoIdmoedaCompra;
    
    /**
     *
     * @var String 
     */
    public $votacaoSiteMoeda;
    
    /**
     *
     * @var Double 
     */
    public $valorMinimoDepositoReais;
    
    /**
     *
     * @var Integer 
     */
    public $kyc;
    
    /**
     *
     * @var Integer 
     */
    public $aparelhos;
    
    //ATAR
    
    /**
     *
     * @var Integer 
     */
    public $atarAtivo;
    
    /**
     *
     * @var Double 
     */
    public $atarTaxaSaque;
    
    /**
     *
     * @var Double 
     */
    public $atarTarifaSaque;
    
    /**
     *
     * @var String
     */
    public $atarIdEmpresa;
    
    /**
     *
     * @var String
     */
    public $atarDocumentEmpresa;
    
    /**
     *
     * @var Double 
     */
    public $atarTaxaDeposito;
    
    /**
     *
     * @var Double 
     */
    public $atarTarifaDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $sincErc20;
    
    
    /**
     *
     * @var Datetime 
     */
    public $dataUltimaSincErc20;
    
    
    /**
     *
     * @var Double 
     */
    public $atarSaldo;
    
    /**
     *
     * @var Double 
     */
    public $atarPorcenSaldoSaque;
    
    /**
     *
     * @var Double 
     */
    public $atarMaxSaque;
    
    /**
     *
     * @var Double 
     */
    public $valorMaxBoleto;
    
    /**
     *
     * @var Integer 
     */
    public $depositoEthExecutando;
    
    /**
     *
     * @var Integer
     */
    public $horasUpdatePin;
    
    /**
     *
     * @var Integer
     */
    public $horasUpdateFraseSeguranca;
    
    /**
     *
     * @var Integer
     */
    public $horasUpdateSenha;
    
    /**
     *
     * @var Integer
     */
    public $horasUpdateTwofa;
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->emailNome = ((isset($dados ['email_nome'])) ? ($dados ['email_nome']) : (null));
        $this->emailPorta = ((isset($dados ['email_porta'])) ? ($dados ['email_porta']) : (null));
        $this->emailSenha = ((isset($dados ['email_senha'])) ? ($dados ['email_senha']) : (null));
        $this->emailSmtp = ((isset($dados ['email_smtp'])) ? ($dados ['email_smtp']) : (null));
        $this->emailSmtpAuth = ((isset($dados ['email_smtp_auth'])) ? ($dados ['email_smtp_auth']) : (null));
        $this->emailUsuario = ((isset($dados ['email_usuario'])) ? ($dados ['email_usuario']) : (null));
        $this->percentualCompra = ((isset($dados ['percentual_compra'])) ? ($dados ['percentual_compra']) : (null));
        $this->percentualDepositos = ((isset($dados ['percentual_depositos'])) ? ($dados ['percentual_depositos']) : (null));
        $this->percentualVenda = ((isset($dados ['percentual_venda'])) ? ($dados ['percentual_venda']) : (null));
        $this->valorCartao = ((isset($dados ['valor_cartao'])) ? ($dados ['valor_cartao']) : (null));
        $this->valorCartaoEx = ((isset($dados ['valor_cartao_ex'])) ? ($dados ['valor_cartao_ex']) : (null));
        $this->valorMensalidadeCartao = ((isset($dados ['valor_mensalidade_cartao'])) ? ($dados ['valor_mensalidade_cartao']) : (null));
        $this->valorMinimoResgateComissao = ((isset($dados ['valor_minimo_resgate_comissao'])) ? ($dados ['valor_minimo_resgate_comissao']) : (null));
        $this->taxaTransferenciaInternaBtc = ((isset($dados ['taxa_transferencia_interna_btc'])) ? ($dados ['taxa_transferencia_interna_btc']) : (null));
        $this->taxaTransferenciaInternaReais = ((isset($dados ['taxa_transferencia_interna_reais'])) ? ($dados ['taxa_transferencia_interna_reais']) : (null));
        $this->taxaDeposito = ((isset($dados ['taxa_deposito'])) ? ($dados ['taxa_deposito']) : (null));
        $this->taxaSaque = ((isset($dados ['taxa_saque'])) ? ($dados ['taxa_saque']) : (null));
        $this->tarifaTed = ((isset($dados ['tarifa_ted'])) ? ($dados ['tarifa_ted']) : (null));
        $this->percentualCompraPassiva = ((isset($dados ['percentual_compra_passiva'])) ? ($dados ['percentual_compra_passiva']) : (null));
        $this->valorMaximoRemessa = ((isset($dados ['valor_maximo_remessa'])) ? ($dados ['valor_maximo_remessa']) : (null));
        $this->valorMinimoRemessa = ((isset($dados ['valor_minimo_remessa'])) ? ($dados ['valor_minimo_remessa']) : (null));
        $this->valorMaximoBoleto = ((isset($dados ['valor_maximo_boleto'])) ? ($dados ['valor_maximo_boleto']) : (null));
        $this->taxaRemessa = ((isset($dados ['taxa_remessa'])) ? ($dados ['taxa_remessa']) : (null));
        $this->depositoDoisCinco = ((isset($dados ['deposito_dois_cinco'])) ? ($dados ['deposito_dois_cinco']) : (null));
        $this->taxaBoleto = ((isset($dados ['taxa_boleto'])) ? ($dados ['taxa_boleto']) : (null));
        $this->prazoEfetuacaoRemessa = ((isset($dados ['prazo_efetuacao_remessa'])) ? ($dados ['prazo_efetuacao_remessa']) : (null));
        $this->depositoCincoDez = ((isset($dados ['deposito_cinco_dez'])) ? ($dados ['deposito_cinco_dez']) : (null));
        $this->depositoDezCinquenta = ((isset($dados ['deposito_dez_cinquenta'])) ? ($dados ['deposito_dez_cinquenta']) : (null));
        $this->depositoCinquentaAcima = ((isset($dados ['deposito_cinquenta_acima'])) ? ($dados ['deposito_cinquenta_acima']) : (null));
        $this->tempoRecargaCartao = ((isset($dados ['tempo_recarga_cartao'])) ? ($dados ['tempo_recarga_cartao']) : (null));
        $this->valorMinimoBoleto = ((isset($dados ['valor_minimo_boleto'])) ? ($dados ['valor_minimo_boleto']) : (null));
        $this->prazoPagamentoBoleto = ((isset($dados ['prazo_pagamento_boleto'])) ? ($dados ['prazo_pagamento_boleto']) : (null));
        $this->percentualVendaPassiva = ((isset($dados ['percentual_venda_passiva'])) ? ($dados ['percentual_venda_passiva']) : (null));
        $this->taxaRecargaCartao = ((isset($dados ['taxa_recarga_cartao'])) ? ($dados ['taxa_recarga_cartao']) : (null));
        $this->taxaAdesaoClube = ((isset($dados ['taxa_adesao_clube'])) ? ($dados ['taxa_adesao_clube']) : (null));
        $this->mensalidadeClube = ((isset($dados ['mensalidade_clube'])) ? ($dados ['mensalidade_clube']) : (null));
        $this->qtdMinConfirmacoesTransacao = ((isset($dados ['qtd_min_confirmacoes_transacao'])) ? ($dados ['qtd_min_confirmacoes_transacao']) : (null));
        $this->qtdMaxConfirmacoesTransacao = ((isset($dados ['qtd_max_confirmacoes_transacao'])) ? ($dados ['qtd_max_confirmacoes_transacao']) : (null));
        $this->valorMaximoSaqueBtc = ((isset($dados ['valor_maximo_saque_btc'])) ? ($dados ['valor_maximo_saque_btc']) : (null));
        $this->statusNovosCadastros = ((isset($dados ['status_novos_cadastros'])) ? ($dados ['status_novos_cadastros']) : (null));
        $this->valorMinSaqueReais = ((isset($dados ['valor_min_saque_reais'])) ? ($dados ['valor_min_saque_reais']) : (null));
        $this->volumeMinimoNegociacaoBtc = ((isset($dados ['volume_minimo_negociacao_btc'])) ? ($dados ['volume_minimo_negociacao_btc']) : (null));
        $this->valorMinimoNegociacaoBrl = ((isset($dados ['valor_minimo_negociacao_brl'])) ? ($dados ['valor_minimo_negociacao_brl']) : (null));
        $this->statusLoginSistema = ((isset($dados ['status_login_sistema'])) ? ($dados ['status_login_sistema']) : (null));
        $this->alertaSite = ((isset($dados ['alerta_site'])) ? ($dados ['alerta_site']) : (null));
        $this->tipoAlertaSite = ((isset($dados ['tipo_alerta_site'])) ? ($dados ['tipo_alerta_site']) : (null));
        $this->dataFinalAlertaSite = ((isset($dados ['data_final_alerta_site'])) ? ($dados ['data_final_alerta_site'] instanceof \Utils\Data ? $dados ['data_final_alerta_site'] : 
            new \Utils\Data(substr($dados ['data_final_alerta_site'], 0, 19))) : (null));
        
        $this->comissaoUsoCofre = ((isset($dados ['comissao_uso_cofre'])) ? ($dados ['comissao_uso_cofre']) : (null));
        $this->prazoDiasSaqueCofre = ((isset($dados ['prazo_dias_saque_cofre'])) ? ($dados ['prazo_dias_saque_cofre']) : (null));
        $this->percentualEstornoDeposito = ((isset($dados ['percentual_estorno_deposito'])) ? ($dados ['percentual_estorno_deposito']) : (null));
        $this->exibirMensagemSite = ((isset($dados ['exibir_mensagem_site'])) ? ($dados ['exibir_mensagem_site']) : (null));
        
        
        $this->prazoHorasAtendimento = ((isset($dados ['prazo_horas_atendimento'])) ? ($dados ['prazo_horas_atendimento']) : (null));
        $this->prazoHorasValidacaoDepositos = ((isset($dados ['prazo_horas_validacao_depositos'])) ? ($dados ['prazo_horas_validacao_depositos']) : (null));
        $this->prazoHorasValidacaoSaques = ((isset($dados ['prazo_horas_validacao_saques'])) ? ($dados ['prazo_horas_validacao_saques']) : (null));
        $this->prazoHorasValidacaoConta = ((isset($dados ['prazo_horas_validacao_conta'])) ? ($dados ['prazo_horas_validacao_conta']) : (null));
        $this->statusUpgradePerfil = ((isset($dados ['status_upgrade_perfil'])) ? ($dados ['status_upgrade_perfil']) : (null));
        $this->statusCarteiras = ((isset($dados ['status_carteiras'])) ? ($dados ['status_carteiras']) : (null));
        $this->comissaoConvite = ((isset($dados ['comissao_convite'])) ? ($dados ['comissao_convite']) : (null));
        
        $this->statusTransferenciaBrl = ((isset($dados ['status_transferencia_brl'])) ? ($dados ['status_transferencia_brl']) : (null));
        $this->statusTransferenciaCurrency = ((isset($dados ['status_transferencia_currency'])) ? ($dados ['status_transferencia_currency']) : (null));
        $this->statusDepositoBrl = ((isset($dados ['status_deposito_brl'])) ? ($dados ['status_deposito_brl']) : (null));
        $this->statusDepositoCurrency = ((isset($dados ['status_deposito_currency'])) ? ($dados ['status_deposito_currency']) : (null));
        $this->taxaRedeBtc = ((isset($dados ['taxa_rede_btc'])) ? ($dados ['taxa_rede_btc']) : (null));
        $this->qtdCasasDecimais = ((isset($dados ['qtd_casas_decimais'])) ? ($dados ['qtd_casas_decimais']) : (null));
        $this->taxaDepositoBoleto = ((isset($dados ['taxa_deposito_boleto'])) ? ($dados ['taxa_deposito_boleto']) : (null));
        $this->prazoDiasContratoCofre = ((isset($dados['prazo_dias_contrato_cofre'])) ? ($dados['prazo_dias_contrato_cofre']) : (null));
        $this->tarifaDepositoBoleto = ((isset($dados['tarifa_deposito_boleto'])) ? ($dados['tarifa_deposito_boleto']) : (null));
        $this->nightMode = ((isset($dados['night_mode'])) ? ($dados['night_mode']) : (null));
        $this->votacaoMinimoSaldo = ((isset($dados['votacao_minimo_saldo'])) ? ($dados['votacao_minimo_saldo']) : (null));
        $this->votacaoIdmoedaCompra = ((isset($dados['votacao_id_moeda_compra'])) ? ($dados['votacao_id_moeda_compra']) : (null));
        $this->votacaoSiteMoeda = ((isset($dados['votacao_site_moeda'])) ? ($dados['votacao_site_moeda']) : (null));
        $this->valorMinimoDepositoReais = ((isset($dados['valor_minimo_deposito_reais'])) ? ($dados['valor_minimo_deposito_reais']) : (null));
        $this->kyc = ((isset($dados['kyc'])) ? ($dados['kyc']) : (null));
        $this->aparelhos = ((isset($dados['aparelhos'])) ? ($dados['aparelhos']) : (null));
        $this->atarAtivo = ((isset($dados['atar_ativo'])) ? ($dados['atar_ativo']) : (null));
        $this->atarTaxaSaque = ((isset($dados['atar_taxa_saque'])) ? ($dados['atar_taxa_saque']) : (null));
        $this->atarTarifaSaque = ((isset($dados['atar_tarifa_saque'])) ? ($dados['atar_tarifa_saque']) : (null));
        $this->atarIdEmpresa = ((isset($dados['atar_id_empresa'])) ? ($dados['atar_id_empresa']) : (null));
        $this->atarTaxaDeposito = ((isset($dados['atar_taxa_deposito'])) ? ($dados['atar_taxa_deposito']) : (null));
        $this->atarTarifaDeposito = ((isset($dados['atar_tarifa_deposito'])) ? ($dados['atar_tarifa_deposito']) : (null));
        $this->atarDocumentEmpresa = ((isset($dados['atar_document_empresa'])) ? ($dados['atar_document_empresa']) : (null));
        $this->sincErc20 = ((isset($dados['sinc_erc20'])) ? ($dados['sinc_erc20']) : (null));
        $this->atarSaldo = ((isset($dados['atar_saldo'])) ? ($dados['atar_saldo']) : (null));
        $this->dataUltimaSincErc20 = ((isset($dados ['data_ultima_sinc_erc20'])) ? ($dados ['data_ultima_sinc_erc20'] instanceof \Utils\Data ? $dados ['data_ultima_sinc_erc20'] : 
            new \Utils\Data(substr($dados ['data_ultima_sinc_erc20'], 0, 19))) : (null));
        $this->valorMaxBoleto = ((isset($dados['valor_max_boleto'])) ? ($dados['valor_max_boleto']) : (null));
        $this->atarPorcenSaldoSaque = ((isset($dados['atar_porcen_saldo_saque'])) ? ($dados['atar_porcen_saldo_saque']) : (null));
        $this->atarMaxSaque = ((isset($dados['atar_max_saque'])) ? ($dados['atar_max_saque']) : (null));
        $this->depositoEthExecutando = ((isset($dados['deposito_eth_executando'])) ? ($dados['deposito_eth_executando']) : (null));
        
        $this->horasUpdateFraseSeguranca = ((isset($dados['horas_update_frase_seguranca'])) ? ($dados['horas_update_frase_seguranca']) : (null));
        $this->horasUpdatePin = ((isset($dados['horas_update_pin'])) ? ($dados['horas_update_pin']) : (null));
        $this->horasUpdateSenha = ((isset($dados['horas_update_senha'])) ? ($dados['horas_update_senha']) : (null));
        $this->horasUpdateTwofa = ((isset($dados['horas_update_twofa'])) ? ($dados['horas_update_twofa']) : (null));

    }
    
    public function getTable() {
        return "configuracoes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Configuracao();
    }


}

?>