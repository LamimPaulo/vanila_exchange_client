<?php
require_once __DIR__ . '/Io/BancoDados.php' ;
require_once __DIR__ . '/Atar/AtarApi.class.php' ;
require_once __DIR__ . '/Ethereum/Ethereum.class.php' ;
require_once __DIR__ . '/Ethereum/BitWalletAPI.class.php' ;
require_once __DIR__ . '/Utils/Idiomas.class.php' ;
require_once __DIR__ . '/Utils/ValidarSeguranca.class.php' ;
require_once __DIR__ . '/Utils/Notificacao.class.php' ;
require_once __DIR__ . '/Utils/Converter.class.php' ;
require_once __DIR__ . '/GoogleAuth/GoogleAuthenticator.class.php' ;
require_once __DIR__ . '/GoogleAuth/Recaptcha.class.php' ;
require_once __DIR__ . '/LambdaAWS/LambdaNotificacao.class.php' ;
require_once __DIR__ . '/LambdaAWS/LambdaMain.class.php' ;
require_once __DIR__ . '/LambdaAWS/QueueKYC.class.php' ;
require_once __DIR__ . '/Redis/RedisMain.class.php' ;
require_once __DIR__ . '/Pusher/vendor/autoload.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/Acao.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/AcaoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/Modulo.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/ModuloRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/LogErro.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/LogErroRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoCliente.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoClienteRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoUsuario.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoUsuarioRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/Rotina.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/RotinaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/RotinaHasAcao.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/RotinaHasAcaoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/ModuloHasAcao.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/ModuloHasAcaoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoModuloCliente.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoModuloClienteRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoModuloUsuario.class.php' ;
require_once __DIR__ . '/Models/Modules/Acesso/PermissaoModuloUsuarioRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarLog.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarLogRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarClientes.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarClientesRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarContas.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AtarContasRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/CategoriaMoeda.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/CategoriaMoedaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TokenApi.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TokenApiRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Auth.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/AuthRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Brand.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/BrandRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Banco.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/BancoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Carteira.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/CarteiraRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Cliente.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasComissao.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasComissaoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Configuracao.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ConfiguracaoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaBancaria.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaBancariaEmpresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaBancariaEmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaBancariaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteBtc.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteBtcEmpresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteBtcEmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteBtcRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteReais.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteReaisEmpresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteReaisEmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ContaCorrenteReaisRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Deposito.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/DepositoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/EmailManager.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/EmailManagerRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Empresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/EmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Layout.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LayoutRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogAcesso.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogAcessoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteBtc.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteBtcEmpresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteBtcEmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteBtcRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteReais.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteReaisEmpresa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteReaisEmpresaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LimiteOperacional.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LimiteOperacionalRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/LogContaCorrenteReaisRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/MarketingImagemRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/MarketingImagem.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/MarketingImagemHasLidoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/MarketingImagemHasLido.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Saque.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/SaqueRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Usuario.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/UsuarioRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasCredito.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasCreditoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ReferenciaCliente.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ReferenciaClienteRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TokenGatewayLog.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TokenGatewayLogRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Moeda.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/MoedaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TaxaMoeda.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/TaxaMoedaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/DocumentoSistema.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/DocumentoSistemaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasDocumentoSistema.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasDocumentoSistemaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/OrderBook.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/OrderBookRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/OrdemExecutada.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/OrdemExecutadaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/NotificacaoMoeda.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/NotificacaoMoedaHasLido.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/NotificacaoMoedaHasLidoRn.class.php';
require_once __DIR__ . '/Models/Modules/Cadastro/CarteiraGerada.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/CarteiraGeradaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ObservacaoCliente.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ObservacaoClienteRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasTaxa.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteHasTaxaRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Paridade.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ParidadeRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Navegador.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/NavegadorRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/Pais.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/PaisRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/StatusCore.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/StatusCoreRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteConvidadoRn.class.php' ;
require_once __DIR__ . '/Models/Modules/Cadastro/ClienteConvidado.class.php' ;
require_once __DIR__ . '/Models/Modules/Model/GenericModel.class.php' ;
require_once __DIR__ . '/Models/Modules/Model/Table.class.php' ;
require_once __DIR__ . '/Models/Modules/Model/Attribute.class.php';
require_once __DIR__ . '/Models/Modules/Model/Query.class.php';
require_once __DIR__ . '/Models/Modules/Model/CommomRegex.class.php';
require_once __DIR__ . '/Models/Modules/Model/Join.class.php';
require_once __DIR__ . '/Models/Modules/Model/Where.class.php';
require_once __DIR__ . '/phpqrcode/qrlib.php' ;
require_once __DIR__ . '/Utils/Arquivo.class.php' ;
require_once __DIR__ . '/Utils/Constantes.class.php' ;
require_once __DIR__ . '/Utils/Conversao.class.php' ;
require_once __DIR__ . '/Utils/ConversaoMoeda.class.php' ;
require_once __DIR__ . '/Utils/Criptografia.class.php' ;
require_once __DIR__ . '/Utils/Data.class.php' ;
require_once __DIR__ . '/Utils/Excecao.class.php' ;
require_once __DIR__ . '/Utils/Geral.class.php' ;
require_once __DIR__ . '/Utils/Mail.class.php' ;
require_once __DIR__ . '/Utils/Imagem.class.php' ;
require_once __DIR__ . '/Utils/Layout.class.php' ;
require_once __DIR__ . '/Utils/Senha.class.php' ;
require_once __DIR__ . '/Utils/Session.class.php' ;
require_once __DIR__ . '/Utils/Post.class.php' ;
require_once __DIR__ . '/Utils/Get.class.php' ;
require_once __DIR__ . '/Utils/Mascara.class.php' ;
require_once __DIR__ . '/Utils/Validacao.class.php' ;
require_once __DIR__ . '/Utils/Rotas.class.php' ;
require_once __DIR__ . '/Utils/CodigoRotinas.class.php' ;
require_once __DIR__ . '/Utils/SQLInjection.class.php' ;
require_once __DIR__ . '/Utils/PropertiesUtils.class.php' ;
require_once __DIR__ . '/Utils/SessionHandler.class.php' ;
require_once __DIR__ . '/Utils/ValidarLimiteOperacional.class.php' ;
require_once __DIR__ . '/Utils/EmailBlacklist.class.php' ;
require_once __DIR__ . '/Utils/DownloadManager.class.php' ;

require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php' ;
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php' ;
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php' ;

require_once getcwd() . '/Modules/principal/Controllers/Principal.class.php';
require_once getcwd() . '/Modules/error/Controllers/Error.class.php';
require_once getcwd() . '/Modules/ws/Controllers/Tokens.class.php';
require_once getcwd() . '/Modules/ws/Controllers/Lambda.class.php';
require_once getcwd() . '/Modules/ws/Controllers/GerenciaNet.class.php';
require_once getcwd() . '/Modules/ws/Controllers/Atar.class.php';




