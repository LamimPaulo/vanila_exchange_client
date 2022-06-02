<?php

namespace Models\Modules\Cadastro;

class Cliente {
    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $nome;
    
    /**
     *
     * @var String 
     */
    public $email;
    
    /**
     *
     * @var String 
     */
    public $cidade;
    
    /**
     *
     * @var String 
     */
    public $celular;
    
    /**
     *
     * @var String 
     */
    public $anotacoes;
    
    /**
     *
     * @var Integer 
     */
    public $status;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    /**
     *
     * @var Double 
     */
    public $comissao;
    
    /**
     *
     * @var String 
     */
    public $senha;
    
    /**
     *
     * @var String 
     */
    public $nomePai;
    
    /**
     *
     * @var String 
     */
    public $nomeMae;
    
    /**
     *
     * @var String 
     */
    public $documentoTipo;
    
    /**
     *
     * @var String 
     */
    public $documento;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $documentoEmissao;
    
    /**
     *
     * @var String 
     */
    public $endereco;
    
    /**
     String
     * @var type 
     */
    public $numero;
    
    /**
     *
     * @var String 
     */
    public $complemento;
    
    /**
     *
     * @var String 
     */
    public $bairro;
    
    /**
     *
     * @var String 
     */
    public $cep;
    
    /**
     *
     * @var String 
     */
    public $estado;
    
    /**
     *
     * @var Integer 
     */
    public $cardReload;
    
    /**
     *
     * @var String 
     */
    public $fotoDocumento;
    
    /**
     *
     * @var String 
     */
    public $fotoDocumentoVerso;
    
    /**
     *
     * @var String 
     */
    public $fotoResidencia;
    
    /**
     *
     * @var String 
     */
    public $codigoPais;
    
    /**
     *
     * @var String 
     */
    public $fotoCliente;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataExpiracao;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataCadastro;
    
    /**
     *
     * @var String 
     */
    public $rg;
    
    /**
     *
     * @var String 
     */
    public $sexo;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataNascimento;
    
    
    /**
     *
     * @var String 
     */
    public $foto;
    
    /**
     *
     * @var Integer 
     */
    public $twoFactorAuth;
    
    /**
     *
     * @var Integer
     */
    public $tipoAutenticacao;
    
    /**
     *
     * @var String 
     */
    public $fraseSeguranca;
    
    /**
     *
     * @var String 
     */
    public $pin;
    
    /**
     *
     * @var Integer 
     */
    public $fotoDocumentoVerificada;
    
    /**
     *
     * @var Integer 
     */
    public $fotoResidenciaVerificada;
    
    /**
     *
     * @var Integer 
     */
    public $fotoClienteVerificada;
    
    /**
     *
     * @var type 
     */
    public $fotoOutroDocumento;
    
    /**
     *
     * @var Integer
     */
    public $fotoOutroDocumentoVerificada;
    
    /**
     *
     * @var Integer 
     */
    public $idReferencia;
    
    /**
     *
     * @var Double 
     */
    public $taxaComissaoSaque;
    
    
    /**
     *
     * @var Double 
     */
    public $taxaComissaoDeposito;
    
    /**
     *
     * @var Integer 
     */
    public $considerarTaxaSaqueCliente;
    
    /**
     *
     * @var Integer 
     */
    public $considerarTaxaDepositoCliente;
    
    
    /**
     *
     * @var Integer 
     */
    public $emailConfirmado;
    
    /**
     *
     * @var String 
     */
    public $motivoRecusaFotoResidencia;
    
    /**
     *
     * @var String 
     */
    public $motivoRecusaFotoDocumento;
    
    /**
     *
     * @var String 
     */
    public $motivoRecusaFotoOutroDocumento;
    
    /**
     *
     * @var String 
     */
    public $motivoRecusaFotoCliente;
    
    
    /**
     *
     * @var String 
     */
    public $recebimentoAlertaMovimentacaoConta;
    
    
    
    /**
     *
     * @var Double 
     */
    public $taxaTransferenciaRemota;
    
    /**
     *
     * @var Integer 
     */
    public $statusFranquia;

    /**
     *
     * @var Integer 
     */
    public $idUsuarioAprovacaoFranquia;
    
    /**
     *
     * @var String 
     */
    public $cnpj;
    
    /**
     *
     * @var Integer 
     */
    public $qtdEnviosEmailsRecuperacao;
    
    /**
     *
     * @var String 
     */
    public $apiKey;
    
    /**
     *
     * @var Integer 
     */
    public $statusMercado;
    
    /**
     *
     * @var Integer 
     */
    public $analiseCliente;
    
    /**
     *
     * @var String 
     */
    public $idAnaliseClienteAdm;
    
    /**
     *
     * @var Integer 
     */
    public $statusDepositoCurrency;
    
    /**
     *
     * @var Integer 
     */
    public $statusDepositoBrl;
    
    /**
     *
     * @var Integer 
     */
    public $statusResgatePdv;
    
    /**
     *
     * @var Integer 
     */
    public $statusSaqueCurrency;
    
    /**
     *
     * @var Integer 
     */
    public $statusSaqueBrl;
    
    /**
     *
     * @var Integer 
     */
    public $emAnalise;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataInicioAnalise;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuarioTerminoAnalise;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataFimAnalise;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuarioInicioAnalise;
    
    
    /**
     *
     * @var Integer
     */
    public $idClienteConvite;
    
    /**
     *
     * @var Integer
     */
    public $comissaoConvitePago;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $ultimaAtividade;
    
    /**
     *
     * @var String
     */
    public $paginaAtual;
    
    /**
     *
     * @var Integer 
     */
    public $idMoedaAtual;
    
    /**
     *
     * @var String 
     */
    public $hashValidacaoEmail;
    
    /**
     *
     * @var Integer 
     */
    public $bloquearRecuperacaoSenha;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $validadeHashRecuperacaoSenha;
    
    /**
     *
     * @var Integer 
     */
    public $bloquearLogin;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $validadeHashValidacaoEmail;
    
    /**
     *
     * @var Integer 
     */
    public $quantidadeTentativasRecuperacao;    
    
    /**
     *
     * @var String 
     */
    public $hashRecuperacaoSenha;
    
    /**
     *
     * @var Integer 
     */
    public $quantidadeTentativasLogin;
    
    /**
     *
     * @var String 
     */
    public $retornoAnaliseEmail;
    
    /**
     *
     * @var String 
     */
    public $ipUltimoAcesso;
    
    /**
     *
     * @var String 
     */
    public $webkit;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimoLogin;
    
    
    /**
     *
     * @var Integer 
     */
    public $googleAuthAtivado;
    
    
    /**
     *
     * @var String 
     */
    public $googleAuthSecret;
    
    /**
     *
     * @var String 
     */
    public $quantidadeTentativasSegundoFator;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaTentativaSegundoFator;
    
    /**
     *
     * @var Integer
     */
    public $idPromocao;
    
    /**
     *
     * @var String 
     */
    public $ddi;
    
    
    /**
     *
     * @var Integer 
     */
    public $idPais;
    
    /**
     *
     * @var Integer 
     */
    public $idPaisNaturalidade;
    
    /**
     *
     * @var Integer 
     */
    public $utilizaSaqueDepositoBrl;
    
    /**
     *
     * @var String 
     */
    public $origemCadastro;
    
    /**
     *
     * @var Double 
     */
    public $taxaComissaoTransfenciaCurrency;
    
    
    /**
     *
     * @var Integer 
     */
    public $considerarTaxaTransferenciaCurrency;
    
    /**
     *
     * @var String 
     */
    public $ipCadastro;
    
    /**
     *
     * @var Integer 
     */
    public $forcarAnaliseSaques;
    
    /**
     *
     * @var Double 
     */
    public $taxaInvoicesPdv;
    
    /**
     *
     * @var String 
     */
    public $tipoTaxaCarteiraRemota;
    
    /**
     *
     * @var String 
     */
    public $tipoTaxaInvoicePdv;
    
    /**
     *
     * @var Integer 
     */
    public $clienteP2p;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaTentativaLogin;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUltimaTentativaRecuperar;
    
    /**
     *
     * @var String 
     */
    public $modoOperacao;
    
    /**
     *
     * @var Integer
     */
    public $moedaFavorita;
    
    /**
     *
     * @var Integer
     */
    public $documentoVerificado;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $kycUpdateData;
    
    /**
     *
     * @var String 
     */
    public $clientid;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUpdateApiKey;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUpdatePin;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUpdateFraseSeguranca;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUpdateSenha;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataUpdateTwofa;

    /**
     *
     * @var \Utils\Data
     */
        public $kycSmsResendData;

    /**
     *
     * @var \Utils\Integer
     */
    public $campanhaMarketReceber;
    
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
        $this->celular = ((isset($dados ['celular'])) ? ($dados ['celular']) : (null));
        $this->cidade = ((isset($dados ['cidade'])) ? ($dados ['cidade']) : (null));
        $this->comissao = ((isset($dados ['comissao'])) ? ($dados ['comissao']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->anotacoes = ((isset($dados ['anotacoes'])) ? ($dados ['anotacoes']) : (null));
        $this->senha = ((isset($dados ['senha'])) ? ($dados ['senha']) : (null));
        $this->nomePai = ((isset($dados ['nome_pai'])) ? ($dados ['nome_pai']) : (null));
        $this->nomeMae = ((isset($dados ['nome_mae'])) ? ($dados ['nome_mae']) : (null));
        $this->documentoTipo = ((isset($dados ['documento_tipo'])) ? ($dados ['documento_tipo']) : (null));
        $this->documento = ((isset($dados ['documento'])) ? ($dados ['documento']) : (null));
        $this->documentoEmissao = ((isset($dados['documento_emissao'])) ? ($dados['documento_emissao'] instanceof \Utils\Data ? $dados['documento_emissao'] :
            new \Utils\Data(substr($dados['documento_emissao'], 0, 19))) : (null));
        $this->endereco = ((isset($dados ['endereco'])) ? ($dados ['endereco']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->complemento = ((isset($dados ['complemento'])) ? ($dados ['complemento']) : (null));
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->estado = ((isset($dados ['estado'])) ? ($dados ['estado']) : (null));
        $this->cardReload = ((isset($dados ['card_reload'])) ? ($dados ['card_reload']) : (null));
        $this->dataExpiracao = ((isset($dados['data_expiracao'])) ? ($dados['data_expiracao'] instanceof \Utils\Data ? $dados['data_expiracao'] :
            new \Utils\Data(substr($dados['data_expiracao'], 0, 19))) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
        new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));
        $this->fotoCliente = ((isset($dados ['foto_cliente'])) ? ($dados ['foto_cliente']) : (null));
        $this->fotoDocumento = ((isset($dados ['foto_documento'])) ? ($dados ['foto_documento']) : (null));
        $this->fotoDocumentoVerso = ((isset($dados ['foto_documento_verso'])) ? ($dados ['foto_documento_verso']) : (null));
        $this->fotoResidencia = ((isset($dados ['foto_residencia'])) ? ($dados ['foto_residencia']) : (null));
        $this->codigoPais = ((isset($dados ['codigo_pais'])) ? ($dados ['codigo_pais']) : (null));
        $this->rg = ((isset($dados ['rg'])) ? ($dados ['rg']) : (null));
        $this->sexo = ((isset($dados ['sexo'])) ? ($dados ['sexo']) : (null));
        $this->dataNascimento = ((isset($dados['data_nascimento'])) ? ($dados['data_nascimento'] instanceof \Utils\Data ? $dados['data_nascimento'] :
            new \Utils\Data(substr($dados['data_nascimento'], 0, 19))) : (null));
        $this->foto = ((isset($dados ['foto'])) ? ($dados ['foto']) : (null));
        $this->twoFactorAuth = ((isset($dados ['two_factor_auth'])) ? ($dados ['two_factor_auth']) : (null));
        $this->tipoAutenticacao = ((isset($dados ['tipo_autenticacao'])) ? ($dados ['tipo_autenticacao']) : (null));
        ((isset($dados ['frase_seguranca'])) ? $this->setDecriptFraseSeguranca($dados ['frase_seguranca']) : (null));
        ((isset($dados ['pin'])) ? $this->setDecriptPin($dados ['pin']) : (null));
        $this->fotoClienteVerificada = ((isset($dados ['foto_cliente_verificada'])) ? ($dados ['foto_cliente_verificada']) : (null));
        $this->fotoDocumentoVerificada = ((isset($dados ['foto_documento_verificada'])) ? ($dados ['foto_documento_verificada']) : (null));
        $this->fotoResidenciaVerificada = ((isset($dados ['foto_residencia_verificada'])) ? ($dados ['foto_residencia_verificada']) : (null));
        $this->taxaComissaoDeposito = ((isset($dados ['taxa_comissao_deposito'])) ? ($dados ['taxa_comissao_deposito']) : (null));
        $this->taxaComissaoSaque = ((isset($dados ['taxa_comissao_saque'])) ? ($dados ['taxa_comissao_saque']) : (null));
        $this->considerarTaxaDepositoCliente = ((isset($dados ['considerar_taxa_deposito_cliente'])) ? ($dados ['considerar_taxa_deposito_cliente']) : (null));
        $this->considerarTaxaSaqueCliente = ((isset($dados ['considerar_taxa_saque_cliente'])) ? ($dados ['considerar_taxa_saque_cliente']) : (null));
        $this->idReferencia = ((isset($dados ['id_referencia'])) ? ($dados ['id_referencia']) : (null));
        $this->emailConfirmado = ((isset($dados ['email_confirmado'])) ? ($dados ['email_confirmado']) : (null));
        $this->fotoOutroDocumento = ((isset($dados ['foto_outro_documento'])) ? ($dados ['foto_outro_documento']) : (null));
        $this->fotoOutroDocumentoVerificada = ((isset($dados ['foto_outro_documento_verificada'])) ? ($dados ['foto_outro_documento_verificada']) : (null));
        $this->motivoRecusaFotoCliente = ((isset($dados ['motivo_recusa_foto_cliente'])) ? ($dados ['motivo_recusa_foto_cliente']) : (null));
        $this->motivoRecusaFotoDocumento = ((isset($dados ['motivo_recusa_foto_documento'])) ? ($dados ['motivo_recusa_foto_documento']) : (null));
        $this->motivoRecusaFotoOutroDocumento = ((isset($dados ['motivo_recusa_foto_outro_documento'])) ? ($dados ['motivo_recusa_foto_outro_documento']) : (null));
        $this->motivoRecusaFotoResidencia = ((isset($dados ['motivo_recusa_foto_residencia'])) ? ($dados ['motivo_recusa_foto_residencia']) : (null));
        $this->recebimentoAlertaMovimentacaoConta = ((isset($dados ['recebimento_alerta_movimentacao_conta'])) ? ($dados ['recebimento_alerta_movimentacao_conta']) : (null));
        $this->taxaTransferenciaRemota = ((isset($dados ['taxa_transferencia_remota'])) ? ($dados ['taxa_transferencia_remota']) : (null));
        $this->statusFranquia = ((isset($dados ['status_franquia'])) ? ($dados ['status_franquia']) : (null));
        $this->idUsuarioAprovacaoFranquia = ((isset($dados ['id_usuario_aprovacao_franquia'])) ? ($dados ['id_usuario_aprovacao_franquia']) : (null));
        $this->cnpj = ((isset($dados ['cnpj'])) ? ($dados ['cnpj']) : (null));
        $this->qtdEnviosEmailsRecuperacao = ((isset($dados ['qtd_envios_emails_recuperacao'])) ? ($dados ['qtd_envios_emails_recuperacao']) : (null));
        $this->apiKey = ((isset($dados ['api_key'])) ? ($dados ['api_key']) : (null));
        $this->statusMercado = ((isset($dados ['status_mercado'])) ? ($dados ['status_mercado']) : (null));
        $this->analiseCliente = ((isset($dados ['analise_cliente'])) ? ($dados ['analise_cliente']) : (null));
        $this->idAnaliseClienteAdm = ((isset($dados ['id_analise_cliente_adm'])) ? ($dados ['id_analise_cliente_adm']) : (null));
        
        $this->statusDepositoBrl = ((isset($dados ['status_deposito_brl'])) ? ($dados ['status_deposito_brl']) : (null));
        $this->statusDepositoCurrency = ((isset($dados ['status_deposito_currency'])) ? ($dados ['status_deposito_currency']) : (null));
        $this->statusSaqueBrl = ((isset($dados ['status_saque_brl'])) ? ($dados ['status_saque_brl']) : (null));
        $this->statusSaqueCurrency = ((isset($dados ['status_saque_currency'])) ? ($dados ['status_saque_currency']) : (null));
        $this->statusResgatePdv = ((isset($dados ['status_resgate_pdv'])) ? ($dados ['status_resgate_pdv']) : (null));
        $this->paginaAtual = ((isset($dados ['pagina_atual'])) ? ($dados ['pagina_atual']) : (null));
        
        
        $this->emAnalise = ((isset($dados ['em_analise'])) ? ($dados ['em_analise']) : (null));
        $this->dataInicioAnalise = ((isset($dados['data_inicio_analise'])) ? ($dados['data_inicio_analise'] instanceof \Utils\Data ? $dados['data_inicio_analise'] :
            new \Utils\Data(substr($dados['data_inicio_analise'], 0, 19))) : (null));
        $this->dataFimAnalise = ((isset($dados['data_fim_analise'])) ? ($dados['data_fim_analise'] instanceof \Utils\Data ? $dados['data_fim_analise'] :
            new \Utils\Data(substr($dados['data_fim_analise'], 0, 19))) : (null));
        $this->idUsuarioInicioAnalise = ((isset($dados ['id_usuario_inicio_analise'])) ? ($dados ['id_usuario_inicio_analise']) : (null));
        $this->idUsuarioTerminoAnalise = ((isset($dados ['id_usuario_termino_analise'])) ? ($dados ['id_usuario_termino_analise']) : (null));
        
        $this->idClienteConvite = ((isset($dados ['id_cliente_convite'])) ? ($dados ['id_cliente_convite']) : (null));
        $this->comissaoConvitePago = ((isset($dados ['comissao_convite_pago'])) ? ($dados ['comissao_convite_pago']) : (null));
        $this->ultimaAtividade = ((isset($dados['ultima_atividade'])) ? ($dados['ultima_atividade'] instanceof \Utils\Data ? $dados['ultima_atividade'] :
            new \Utils\Data(substr($dados['ultima_atividade'], 0, 19))) : (null));
        $this->idMoedaAtual = ((isset($dados ['id_moeda_atual'])) ? ($dados ['id_moeda_atual']) : (null));
        
        
        $this->hashValidacaoEmail = ((isset($dados ['hash_validacao_email'])) ? ($dados ['hash_validacao_email']) : (null));
        
        $this->hashRecuperacaoSenha = ((isset($dados ['hash_recuperacao_senha'])) ? ($dados ['hash_recuperacao_senha']) : (null));
        
        $this->quantidadeTentativasLogin = ((isset($dados ['quantidade_tentativas_login'])) ? ($dados ['quantidade_tentativas_login']) : (null));
        
        $this->quantidadeTentativasRecuperacao = ((isset($dados ['quantidade_tentativas_recuperacao'])) ? ($dados ['quantidade_tentativas_recuperacao']) : (null));
        
        $this->bloquearLogin = ((isset($dados ['bloquear_login'])) ? ($dados ['bloquear_login']) : (null));
        
        $this->bloquearRecuperacaoSenha = ((isset($dados ['bloquear_recuperacao_senha'])) ? ($dados ['bloquear_recuperacao_senha']) : (null));
        
        $this->validadeHashRecuperacaoSenha = ((isset($dados['validade_hash_recuperacao_senha'])) ? ($dados['validade_hash_recuperacao_senha'] instanceof \Utils\Data ? $dados['validade_hash_recuperacao_senha'] :
            new \Utils\Data(substr($dados['validade_hash_recuperacao_senha'], 0, 19))) : (null));
        $this->validadeHashValidacaoEmail = ((isset($dados['validade_hash_validacao_email'])) ? ($dados['validade_hash_validacao_email'] instanceof \Utils\Data ? $dados['validade_hash_validacao_email'] :
            new \Utils\Data(substr($dados['validade_hash_validacao_email'], 0, 19))) : (null));
        
        $this->retornoAnaliseEmail = ((isset($dados ['retorno_analise_email'])) ? ($dados ['retorno_analise_email']) : (null));
        $this->ipUltimoAcesso = ((isset($dados ['ip_ultimo_acesso'])) ? ($dados ['ip_ultimo_acesso']) : (null));
        $this->webkit = ((isset($dados ['webkit'])) ? ($dados ['webkit']) : (null));
        
        $this->dataUltimoLogin = ((isset($dados['data_ultimo_login'])) ? ($dados['data_ultimo_login'] instanceof \Utils\Data ? $dados['data_ultimo_login'] :
            new \Utils\Data(substr($dados['data_ultimo_login'], 0, 19))) : (null));
        
        
        
        $this->googleAuthSecret = ((isset($dados ['google_auth_secret'])) ? ($dados ['google_auth_secret']) : (null));
        $this->googleAuthAtivado = ((isset($dados ['google_auth_ativado'])) ? ($dados ['google_auth_ativado']) : (null));
        $this->quantidadeTentativasSegundoFator = ((isset($dados['quantidade_tentativas_segundo_fator'])) ? ($dados['quantidade_tentativas_segundo_fator']) : (null));
        $this->dataUltimaTentativaSegundoFator = ((isset($dados['data_ultima_tentativa_segundo_fator'])) ? ($dados['data_ultima_tentativa_segundo_fator'] instanceof \Utils\Data ? 
                $dados['data_ultima_tentativa_segundo_fator'] : new \Utils\Data(substr($dados['data_ultima_tentativa_segundo_fator'], 0, 19))) : (null));
        
        $this->idPromocao = ((isset($dados ['id_promocao'])) ? ($dados ['id_promocao']) : (null));
        $this->ddi = ((isset($dados ['ddi'])) ? ($dados ['ddi']) : (null));
        $this->idPais = ((isset($dados ['id_pais'])) ? ($dados ['id_pais']) : (null));
        $this->idPaisNaturalidade = ((isset($dados ['id_pais_naturalidade'])) ? ($dados ['id_pais_naturalidade']) : (null));
        $this->utilizaSaqueDepositoBrl = ((isset($dados ['utiliza_saque_deposito_brl'])) ? ($dados ['utiliza_saque_deposito_brl']) : (null));
        $this->origemCadastro = ((isset($dados ['origem_cadastro'])) ? ($dados ['origem_cadastro']) : (null));
        $this->considerarTaxaTransferenciaCurrency = ((isset($dados ['considerar_taxa_transferencia_currency'])) ? ($dados ['considerar_taxa_transferencia_currency']) : (null));
        $this->taxaComissaoTransfenciaCurrency = ((isset($dados ['taxa_comissao_transfencia_currency'])) ? ($dados ['taxa_comissao_transfencia_currency']) : (null));
        $this->ipCadastro = ((isset($dados ['ip_cadastro'])) ? ($dados ['ip_cadastro']) : (null));
        
        $this->forcarAnaliseSaques = ((isset($dados ['forcar_analise_saques'])) ? ($dados ['forcar_analise_saques']) : (null));
        $this->taxaInvoicesPdv = ((isset($dados ['taxa_invoices_pdv'])) ? ($dados ['taxa_invoices_pdv']) : (null));
        $this->tipoTaxaCarteiraRemota = ((isset($dados ['tipo_taxa_carteira_remota'])) ? ($dados ['tipo_taxa_carteira_remota']) : (null));
        $this->tipoTaxaInvoicePdv = ((isset($dados ['tipo_taxa_invoice_pdv'])) ? ($dados ['tipo_taxa_invoice_pdv']) : (null));
        $this->clienteP2p = ((isset($dados ['cliente_p2p'])) ? ($dados ['cliente_p2p']) : (null));
        
        $this->modoOperacao = ((isset($dados ['modo_operacao'])) ? ($dados ['modo_operacao']) : (null));
        $this->moedaFavorita = ((isset($dados ['moeda_favorita'])) ? ($dados ['moeda_favorita']) : (null));
        $this->documentoVerificado = ((isset($dados ['documento_verificado'])) ? ($dados ['documento_verificado']) : (null));
        $this->campanhaMarketReceber = ((isset($dados ['campanha_market_receber'])) ? ($dados ['campanha_market_receber']) : (null));

        
        $this->dataUltimaTentativaLogin = ((isset($dados['data_ultima_tentativa_login'])) ? ($dados['data_ultima_tentativa_login'] instanceof \Utils\Data ? 
                $dados['data_ultima_tentativa_login'] : new \Utils\Data(substr($dados['data_ultima_tentativa_login'], 0, 19))) : (null));
        $this->dataUltimaTentativaRecuperar = ((isset($dados['data_ultima_tentativa_recuperar'])) ? ($dados['data_ultima_tentativa_recuperar'] instanceof \Utils\Data ? 
                $dados['data_ultima_tentativa_recuperar'] : new \Utils\Data(substr($dados['data_ultima_tentativa_recuperar'], 0, 19))) : (null));
        $this->clientid = ((isset($dados ['clientid'])) ? ($dados ['clientid']) : (null));
        
        $this->dataUpdateApiKey = ((isset($dados['data_update_api_key'])) ? ($dados['data_update_api_key'] instanceof \Utils\Data ? 
                $dados['data_update_api_key'] : new \Utils\Data(substr($dados['data_update_api_key'], 0, 19))) : (null));
        
        $this->dataUpdatePin = ((isset($dados['data_update_pin'])) ? ($dados['data_update_pin'] instanceof \Utils\Data ? 
                $dados['data_update_pin'] : new \Utils\Data(substr($dados['data_update_pin'], 0, 19))) : (null));
        
        $this->dataUpdateFraseSeguranca = ((isset($dados['data_update_frase_seguranca'])) ? ($dados['data_update_frase_seguranca'] instanceof \Utils\Data ? 
                $dados['data_update_frase_seguranca'] : new \Utils\Data(substr($dados['data_update_frase_seguranca'], 0, 19))) : (null));
        
        $this->dataUpdateSenha = ((isset($dados['data_update_senha'])) ? ($dados['data_update_senha'] instanceof \Utils\Data ? 
                $dados['data_update_senha'] : new \Utils\Data(substr($dados['data_update_senha'], 0, 19))) : (null));
        
        $this->dataUpdateTwofa = ((isset($dados['data_update_twofa'])) ? ($dados['data_update_twofa'] instanceof \Utils\Data ? 
                $dados['data_update_twofa'] : new \Utils\Data(substr($dados['data_update_twofa'], 0, 19))) : (null));
        
        $this->kycUpdateData = ((isset($dados['kyc_update_data'])) ? ($dados['kyc_update_data'] instanceof \Utils\Data ? $dados['kyc_update_data'] :
            new \Utils\Data(substr($dados['kyc_update_data'], 0, 19))) : (null));

        $this->kycSmsResendData = ((isset($dados['kyc_sms_resend_data'])) ? ($dados['kyc_sms_resend_data'] instanceof \Utils\Data ? $dados['kyc_sms_resend_data'] :
            new \Utils\Data(substr($dados['kyc_sms_resend_data'], 0, 19))) : (null));


    }
    
    public function getTable() {
        return "clientes";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Cliente();
    }
    
    
    public function getPercentualPerfil() {
        
        $percentualPerfil = 0;
        
        if (!empty($this->nome)) {
            $percentualPerfil++;
        }
        if (!empty($this->email)) {
            $percentualPerfil++;
        }
        if (!empty($this->celular)) {
            $percentualPerfil++;
        }
        if (!empty($this->rg)) {
            $percentualPerfil++;
        }
        if (!empty($this->nomeMae)) {
            $percentualPerfil++;
        }

        if (!empty($this->endereco)) {
            $percentualPerfil++;
        }

        if (!empty($this->bairro)) {
            $percentualPerfil++;
        }

        if (!empty($this->cep)) {
            $percentualPerfil++;
        }

        if (!empty($this->codigoPais)) {
            $percentualPerfil++;
        }

        if (!empty($this->cidade)) {
            $percentualPerfil++;
        }

        if (!empty($this->estado)) {
            $percentualPerfil++;
        }


        if (!empty($this->documento)) {
            $percentualPerfil++;
        }


        if ($this->dataNascimento != null) {
            $percentualPerfil++;
        }


        if (!empty($this->pin)) {
            $percentualPerfil++;
        }


        if (!empty($this->fraseSeguranca)) {
            $percentualPerfil++;
        }


        if (!empty($this->fotoDocumento)) {
            $percentualPerfil++;
        }


        if (!empty($this->fotoResidencia)) {
            $percentualPerfil++;
        }


        if (!empty($this->fotoCliente)) {
            $percentualPerfil++;
        }
        
        $percentual = ceil(($percentualPerfil / 18) * 100);
        
        return $percentual;
    }
    
    
    public function getTipoPerfil() {
        switch ($this->tipoPerfil) {
            case \Utils\Constantes::PERFIL_CLIENTE:
                return "Cliente";
            case \Utils\Constantes::PERFIL_VENDEDOR_COMISSIONADO:
                return "Light";
            case \Utils\Constantes::PERFIL_FRANQUIA_HW:
                return "Standard";
            case \Utils\Constantes::PERFIL_OPEN_HOUSE:
                return "Premium";

            default:
                return "Não Solicitado";
        }
    }
    
    public function getPerfilSolicitado() {
        switch ($this->solicitacaoUpgradePerfil) {
            case \Utils\Constantes::PERFIL_CLIENTE:
                return "Cliente";
            case \Utils\Constantes::PERFIL_VENDEDOR_COMISSIONADO:
                return "Light";
            case \Utils\Constantes::PERFIL_FRANQUIA_HW:
                return "Standard";
            case \Utils\Constantes::PERFIL_OPEN_HOUSE:
                return "Premium";
            default:
                return "Não Solicitado";
        }
    }
    
    public function getAtividades() {
        $online = false;
        $ultimaAtividade = "";
        $string = "";
        if ($this->ultimaAtividade != null) {
            $dataatual = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataLimite = new \Utils\Data($this->ultimaAtividade->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
            $dataLimite->somar(0, 0, 0, 0, 1, 0);
            
            $ultimaAtividade = $this->ultimaAtividade->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
            if ($dataLimite->maior($dataatual)) {
                $online = true;
                
                
            }
            
            $dif = $dataatual->diferenca($this->ultimaAtividade);
                
            if ($dif->y > 0) {
                $string = "{$dif->y} ano(s) atrás";
            } else if ($dif->m > 0) {
                $string = "{$dif->m} mes(es) atrás";
            } else if ($dif->d > 0) {
                $string = "{$dif->d} dia(s) atrás";
            } else if ($dif->h > 0) {
                $string = "{$dif->h} hora(s) atrás";
            } else if ($dif->i > 0) {
                $string = "{$dif->i} minuto(s) atrás";
            } else if ($dif->s > 0) {
                $string = "{$dif->s} segundo(s) atrás";
            }
        }
        
        return Array("online" => $online, "ultima" => $ultimaAtividade, "string" => $string);
    }
    
    
    public function getEncriptedPin() {
        return \Utils\Criptografia::encriptyPostId($this->pin);
    }
    
    public function setDecriptPin($encriptedPin) {
        $this->pin = \Utils\Criptografia::decriptyPostId($encriptedPin, false);
    }
    
    
    public function getEncriptedFraseSeguranca() {
        return \Utils\Criptografia::encriptyPostId($this->fraseSeguranca);
    }
    
    public function setDecriptFraseSeguranca($encriptedFraseSeguranca) {
        $this->fraseSeguranca = \Utils\Criptografia::decriptyPostId($encriptedFraseSeguranca, false);
    }
    
    
    public function getStatusDocumento() {
        if (empty($this->fotoDocumento)) {
            return 0;
        } else {
            if ($this->fotoDocumentoVerificada > 0) {
                return 2;
            } else {
                return 1;
            }
        }
        return 0;
    }
    
    public function getStatusSelfie() {
        if (empty($this->fotoCliente)) {
            return 0;
        } else {
            if ($this->fotoClienteVerificada > 0) {
                return 2;
            } else {
                return 1;
            }
        }
        return 0;
    }
    
    public function getStatusComprovanteResidencia() {
        if (empty($this->fotoResidencia)) {
            return 0;
        } else {
            if ($this->fotoResidenciaVerificada > 0) {
                return 2;
            } else {
                return 1;
            }
        }
        return 0;
    }
}
