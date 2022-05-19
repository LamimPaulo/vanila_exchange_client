<?php

namespace Utils;



class ModulesFactory {
    
    public static function getInstance($file, $params) {
        
        $file = str_replace("Modules", "", $file);
        
        if ($file == '/acesso/Controllers/Cadastro.class.php') { 
            return new \Modules\acesso\Controllers\Cadastro($params);
        } else 
        if ($file == '/acesso/Controllers/Acesso.class.php') { 
            return new \Modules\acesso\Controllers\Acesso($params);
        } else if ($file == '/acesso/Controllers/Acesso.class.php') { 
            return new \Modules\acesso\Controllers\Acesso($params);
        } else if ($file == '/acesso/Controllers/FilesManager.class.php' ) {
            return new \Modules\acesso\Controllers\FilesManager($params);
        } else if ($file == '/acesso/Controllers/Sms.class.php' ) { 
            return new \Modules\acesso\Controllers\Sms($params);
        } else
        if ($file == '/acesso/Controllers/Docs.class.php' ) { 
            return new \Modules\acesso\Controllers\Docs($params);            
        } else
        if ($file == '/acesso/Controllers/NotificacaoMoeda.class.php' ) { 
            
            return new \Modules\acesso\Controllers\NotificacaoMoeda($params);
        } else
        if ($file == '/acesso/Controllers/MarketingImagem.class.php' ) { 
            
            return new \Modules\acesso\Controllers\MarketingImagem($params);
        } else
        if ($file == '/cadastros/Controllers/Contas.class.php' ) {
            
            return new \Modules\cadastros\Controllers\Contas($params);
        } else
        if ($file == '/cadastros/Controllers/Carteiras.class.php' ) {
            
            return new \Modules\cadastros\Controllers\Carteiras($params);
        } else
        if ($file == '/cadastros/Controllers/Clientes.class.php' ) { 
            
            return new \Modules\cadastros\Controllers\Clientes($params);
        } else
        if ($file == '/cadastros/Controllers/Atar.class.php' ) {
            
            return new \Modules\cadastros\Controllers\Atar($params);
        } else    
        if ($file == '/cadastros/Controllers/Usuarios.class.php' ) {
            
            return new \Modules\cadastros\Controllers\Usuarios($params);
        } else
        if ($file == '/cadastros/Controllers/AnalisePerfis.class.php' ) { 
            return new \Modules\cadastros\Controllers\AnalisePerfis($params);
        } else
        if ($file == '/cadastros/Controllers/TaxasClientes.class.php' ) { 
            return new \Modules\cadastros\Controllers\TaxasClientes($params);
        } else
        if ($file == '/cadastros/Controllers/ComissoesClientes.class.php' ) { 
            return new \Modules\cadastros\Controllers\ComissoesClientes($params);
        } else
        if ($file == '/configuracoes/Controllers/ComissoesClientes.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\ComissoesClientes($params);
        } else
        if ($file == '/cadastros/Controllers/CreditosClientes.class.php' ) { 
            return new \Modules\cadastros\Controllers\CreditosClientes($params);
        } else
        if ($file == '/configuracoes/Controllers/PainelControle.class.php' ) { 
            return new \Modules\configuracoes\Controllers\PainelControle($params);
        } else
        if ($file == '/configuracoes/Controllers/PainelSite.class.php' ) {
            
            return new \Modules\configuracoes\Controllers\PainelSite($params);
        } else
        if ($file == '/configuracoes/Controllers/TaxasMoedas.class.php' ) {
            
            return new \Modules\configuracoes\Controllers\TaxasMoedas($params);
        } else
        if ($file == '/configuracoes/Controllers/Contas.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\Contas($params);
        } else
        if ($file == '/configuracoes/Controllers/PainelNotasFiscais.class.php' ) {
            
            return new \Modules\configuracoes\Controllers\PainelNotasFiscais($params);
        } else
        if ($file == '/configuracoes/Controllers/LicencasSoftware.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\LicencasSoftware($params);
        } else
        if ($file == '/configuracoes/Controllers/RecursosLicencas.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\RecursosLicencas($params);
        } else
        if ($file == '/configuracoes/Controllers/Roadmap.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\Roadmap($params);
        } else
        if ($file == '/configuracoes/Controllers/NotificacaoMoeda.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\NotificacaoMoeda($params);
        } else
        if ($file == '/configuracoes/Controllers/NotificacaoMoedaOnlyView.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\NotificacaoMoedaOnlyView($params);
        } else
        if ($file == '/configuracoes/Controllers/TelegramBots.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\TelegramBots($params);
        } else
        if ($file == '/configuracoes/Controllers/TelegramGrupos.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\TelegramGrupos($params);
        } else
        if ($file == '/configuracoes/Controllers/TelegramBots.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\TelegramBots($params);
        } else
        if ($file == '/configuracoes/Controllers/TelegramMensagens.class.php' ) { 
            
            return new \Modules\configuracoes\Controllers\TelegramMensagens($params);
        } else
        if ($file == '/emails/Controllers/EmailAutenticacao.class.php' ) {
            
            return new \Modules\emails\Controllers\EmailAutenticacao($params);
        } else
        if ($file == '/emails/Controllers/EmailBoasVindas.class.php' ) {
            
            return new \Modules\emails\Controllers\EmailBoasVindas($params);
        } else
        if ($file == '/emails/Controllers/EmailStatusInvoices.class.php' ) { 
            
            return new \Modules\emails\Controllers\EmailStatusInvoices($params);
        } else
        if ($file == '/error/Controllers/Error.class.php' ) { 
            
            return new \Modules\error\Controllers\Error($params);
        } else
        if ($file == '/pdfs/Controllers/PDFComprovanteInvoice.class.php' ) { 
            
            return new \Modules\pdfs\Controllers\PDFComprovanteInvoice($params);
        } else
        if ($file == '/perfil/Controllers/MeusDados.class.php' ) { 
            
            return new \Modules\perfil\Controllers\MeusDados($params);
        } else
        if ($file == '/perfil/Controllers/DispositivosMobile.class.php' ) { 
            
            return new \Modules\perfil\Controllers\DispositivosMobile($params);
        } else
        if ($file == '/perfil/Controllers/Afiliados.class.php' ) { 
            
            return new \Modules\perfil\Controllers\Afiliados($params);
        } else
        if ($file == '/principal/Controllers/Dashboard.class.php' ) { 
            
            return new \Modules\principal\Controllers\Dashboard($params);
        } else
        if ($file == '/principal/Controllers/DashboardAdm.class.php' ) { 
            
            return new \Modules\principal\Controllers\DashboardAdm($params);
        } else
        if ($file == '/principal/Controllers/Indicacoes.class.php' ) {
            
            return new \Modules\principal\Controllers\Indicacoes($params);
        } else
        if ($file == '/principal/Controllers/Manual.class.php' ) { 
            
            return new \Modules\principal\Controllers\Manual($params);
        } else
        if ($file == '/principal/Controllers/Principal.class.php' ) { 
            
            return new \Modules\principal\Controllers\Principal($params);
        } else
        if ($file == '/recharge/Controllers/Cards.class.php' ) { 
            
            return new \Modules\recharge\Controllers\Cards($params);
        } else
        if ($file == '/services/Controllers/QRCode.class.php' ) {
            
            return new \Modules\services\Controllers\QRCode($params);
        } else
        if ($file == '/services/Controllers/SaldoService.class.php' ) { 
            
            return new \Modules\services\Controllers\SaldoService($params);
        } else
        if ($file == '/services/Controllers/Consulta.class.php' ) { 
            return new \Modules\services\Controllers\Consulta($params);
            
        } else
        if ($file == '/servicos/Controllers/Boletos.class.php' ) { 
            
            return new \Modules\servicos\Controllers\P2p($params);
        } else
        if ($file == '/contasempresa/Controllers/P2p.class.php' ) { 
            
            return new \Modules\contasempresa\Controllers\P2p($params);
        } else
        if ($file == '/servicos/Controllers/Remessas.class.php' ) { 
            
            return new \Modules\servicos\Controllers\Remessas($params);
        } else
        if ($file == '/servicos/Controllers/CategoriasServicos.class.php' ) { 
            
            return new \Modules\servicos\Controllers\CategoriasServicos($params);
        } else
        if ($file == '/servicos/Controllers/IdentificacaoPagamentos.class.php' ) { 
            
            return new \Modules\servicos\Controllers\IdentificacaoPagamentos($params);
        } else
        if ($file == '/site/Controllers/Cadastro.class.php' ) { 
            
            return new \Modules\site\Controllers\Cadastro($params);
        } else
        if ($file == '/contas/Controllers/Btc.class.php' ) { 
            
            return new \Modules\contas\Controllers\Btc($params);
        } else
        if ($file == '/contas/Controllers/LogBtc.class.php' ) { 
            
            return new \Modules\contas\Controllers\LogBtc($params);
        } else
        if ($file == '/contas/Controllers/LogReais.class.php' ) { 
            return new \Modules\contas\Controllers\LogReais($params);
        } else
        if ($file == '/contas/Controllers/Reais.class.php' ) { 
            return new \Modules\contas\Controllers\Reais($params);
        } else
        if ($file == '/contas/Controllers/PainelBtc.class.php' ) { 
            return new \Modules\contas\Controllers\PainelBtc($params);
        } else
        if ($file == '/acesso/Controllers/Login.class.php' ) { 
            return new \Modules\acesso\Controllers\Login($params);
        } else
        if ($file == '/pdvs/Controllers/Estabelecimentos.class.php' ) { 
            return new \Modules\pdvs\Controllers\Estabelecimentos($params);
        } else
        if ($file == '/pdvs/Controllers/Chaves.class.php' ) { 
            return new \Modules\pdvs\Controllers\Chaves($params);
        } else
        if ($file == '/convidados/Controllers/Convidados.class.php' ) { 
            
            return new \Modules\convidados\Controllers\Convidados($params);
        } else
        if ($file == '/contas/Controllers/Btc.class.php' ) { 
            
            return new \Modules\contas\Controllers\Btc($params);
        } else
        if ($file == '/contas/Controllers/BtcEmpresa.class.php' ) { 
            
            return new \Modules\contas\Controllers\BtcEmpresa($params);
        } else
        if ($file == '/contas/Controllers/LogBtc.class.php' ) { 
            
            return new \Modules\contas\Controllers\LogBtcEmpresa($params);
        } else
        if ($file == '/contas/Controllers/LogBtcEmpresa.class.php' ) { 
            
            return new \Modules\contas\Controllers\LogBtcEmpresa($params);
        } else
        if ($file == '/contas/Controllers/LogReais.class.php' ) { 
            
            return new \Modules\contas\Controllers\LogReaisEmpresa($params);
        } else
        if ($file == '/contas/Controllers/LogReaisEmpresa.class.php' ) { 
            
            return new \Modules\contas\Controllers\LogReaisEmpresa($params);
        } else
        if ($file == '/contas/Controllers/Reais.class.php' ) { 
            
            return new \Modules\contas\Controllers\ReaisEmpresa($params);
        } else
        if ($file == '/contas/Controllers/ReaisEmpresa.class.php' ) { 
            return new \Modules\contas\Controllers\ReaisEmpresa($params);
            
        } else
        if ($file == '/contas/Controllers/Estorno.class.php' ) { 
            return new \Models\Modules\Cadastro\Estorno($params);
        } else
        if ($file == '/contas/Controllers/GerenciaNet.class.php' ) { 
            
            return new \Modules\contas\Controllers\GerenciaNet($params);
        } else
        if ($file == '/contas/Controllers/LaraBoleto.class.php' ) { 
            
            return new \Modules\contas\Controllers\LaraBoleto($params);
        } else
        if ($file == '/contas/Controllers/Depositos.class.php' ) { 
            return new \Modules\contas\Controllers\Depositos($params);
        } else
        if ($file == '/contas/Controllers/Atar.class.php' ) { 
            
            return new \Modules\contas\Controllers\Atar($params);
        } else
        if ($file == '/contas/Controllers/Saques.class.php' ) { 
            
            return new \Modules\contas\Controllers\Saques($params);
        } else
        if ($file == '/contas/Controllers/Transferencias.class.php' ) { 
            
            return new \Modules\contas\Controllers\Transferencias($params);
        } else  if ($file == '/doc/Controllers/Carteira.class.php' ) { 
            return new \Modules\doc\Controllers\Carteira();
        } else if ($file == '/doc/Controllers/Pdv.class.php' ) { 
            return new \Modules\doc\Controllers\Pdv;
        } else if ($file == '/doc/Controllers/Transferencia.class.php' ) {
            return new \Modules\doc\Controllers\Transferencia();            
        } else if ($file == '/doc/Controllers/Trade.class.php' ) {
            return new \Modules\doc\Controllers\Trade();            
        } else if ($file == '/doc/Controllers/Calc.class.php' ) { 
            return new \Modules\doc\Controllers\Calc();
        } else if ($file == '/doc/Controllers/Tabelas.class.php' ) {
           return new \Modules\doc\Controllers\Tabelas();           
        } else if ($file == '/doc/Controllers/Payment.class.php' ) {
            return new \Modules\doc\Controllers\Payment();            
        } else if ($file == '/doc/Controllers/Newsletter.class.php' ) {
            return new \Modules\doc\Controllers\Newsletter();            
        } else if ($file == '/doc/Controllers/Contato.class.php' ) {
            return new \Modules\doc\Controllers\Contato();            
        } else if ($file == '/comercios/Controllers/Comercios.class.php' ) {
            return new \Modules\comercios\Controllers\Comercios($params);
        } else if ($file == '/comercios/Controllers/Segmentos.class.php' ) {
            return new \Modules\comercios\Controllers\Segmentos($params);            
         } else if ($file == '/perfil/Controllers/MeusDados.class.php' ) {
            return new \Modules\perfil\Controllers\MeusDados($params);            
         } else  if ($file == '/perfil/Controllers/Conta.class.php' ) {
            return new \Modules\perfil\Controllers\Conta($params);
        } else if ($file == '/principal/Controllers/Dashboard.class.php' ) {
            return new \Modules\principal\Controllers\Dashboard($params);            
        } else if ($file == '/principal/Controllers/Manual.class.php' ) {
            return new \Modules\principal\Controllers\Manual($params);            
        } else if ($file == '/principal/Controllers/Principal.class.php' ) {
            return new \Modules\principal\Controllers\Principal($params);            
        } else if ($file == '/recharge/Controllers/Cards.class.php' ) {
            return new \Modules\recharge\Controllers\Cards($params);            
        } else if ($file == '/services/Controllers/QRCode.class.php' ) {
            return new \Modules\services\Controllers\QRCode($params);            
        } else if ($file == '/services/Controllers/Correios.class.php' ) {
            return new \Modules\services\Controllers\Correios($params);                    
        } else if ($file == '/services/Controllers/SaldoService.class.php' ) {
            return new \Modules\services\Controllers\SaldoService($params);            
        } else if ($file == '/servicos/Controllers/Boletos.class.php' ) {
            return new \Modules\servicos\Controllers\Boletos($params);            
        } else if ($file == '/servicos/Controllers/P2p.class.php' ) {
            return new \Modules\servicos\Controllers\P2p($params);            
        } else if ($file == '/servicos/Controllers/Remessas.class.php' ) {
            return new \Modules\servicos\Controllers\Remessas($params);            
        } else if ($file == '/principal/Controllers/Indicacoes.class.php' ) {
            return new \Modules\principal\Controllers\Indicacoes($params);            
        } else if ($file == '/testes/Controllers/TesteEmailContaAceita.class.php' ) {
            return new \Modules\testes\Controllers\TesteEmailContaAceita($params);            
        } else if ($file == '/testes/Controllers/TesteNf.class.php' ) {
            return new \Modules\testes\Controllers\TesteNf($params);            
        } else if ($file == '/testes/Controllers/TesteRetornoCallback.class.php' ) {
            return new \Modules\testes\Controllers\TesteRetornoCallback($params);            
        } else if ($file == '/testes/Controllers/TesteCallbacksInvoicePdv.class.php' ) {
            return new \Modules\testes\Controllers\TesteCallbacksInvoicePdv($params);            
        } else if ($file == '/testes/Controllers/TesteApiWallets.class.php' ) {
            return new \Modules\testes\Controllers\TesteApiWallets($params);            
        } else if ($file == '/testes/Controllers/TesteArvoreComissao.class.php' ) {
            return new \Modules\testes\Controllers\TesteArvoreComissao($params);            
        } else if ($file == '/testes/Controllers/TesteEmails.class.php' ) {
            return new \Modules\testes\Controllers\TesteEmails($params);            
        } else if ($file == '/monitoramento/Controllers/Painel.class.php' ) {
            return new \Modules\monitoramento\Controllers\Painel($params);            
        } else if ($file == '/monitoramento/Controllers/PainelIco.class.php' ) {
            return new \Modules\monitoramento\Controllers\PainelIco($params);            
        } else if ($file == '/api/Controllers/Buy.class.php' ) {
            return new \Modules\api\Controllers\Buy($params);            
        } else if ($file == '/api/Controllers/Sell.class.php' ) {
            return new \Modules\api\Controllers\Sell($params);            
        } else if ($file == '/api/Controllers/Auth.class.php' ) {
            return new \Modules\api\Controllers\Auth($params);            
        } else if ($file == '/api/Controllers/Btc.class.php' ) {
            return new \Modules\api\Controllers\Btc($params);
        } else if ($file == '/api/Controllers/Reais.class.php' ) {
            return new \Modules\api\Controllers\Reais($params);
        } else if ($file == '/api/Controllers/Pdv.class.php' ) {
            return new \Modules\api\Controllers\Pdv($params);            
        } else if ($file == '/api/Controllers/Wallet.class.php' ) {
            return new \Modules\api\Controllers\Wallet($params);
        } else if ($file == '/api/Controllers/Trade.class.php' ) {
            return new \Modules\api\Controllers\Trade($params);            
        } else if ($file == '/api/Controllers/Calc.class.php' ) {
            return new \Modules\api\Controllers\Calc($params);            
        } else if ($file == '/api/Controllers/Payment.class.php' ) {
            return new \Modules\api\Controllers\Payment($params);            
        } else if ($file == '/api/Controllers/Status.class.php' ) {
            return new \Modules\api\Controllers\Status($params);            
        } else if ($file == '/api/Controllers/Roadmap.class.php' ) {
            return new \Modules\api\Controllers\Roadmap($params);            
        } else if ($file == '/api/Controllers/Newsletter.class.php' ) {
            return new \Modules\api\Controllers\Newsletter($params);            
        } else if ($file == '/api/Controllers/Contato.class.php' ) {
            return new \Modules\api\Controllers\Contato($params);            
        } else if ($file == '/api/Controllers/Signup.class.php' ) {
            return new \Modules\api\Controllers\Signup($params);            
        } else if ($file == '/api/Controllers/Core.class.php' ) {
            return new \Modules\api\Controllers\Core($params);            
        } else if ($file == '/api/Controllers/Ticket.class.php' ) {
            return new \Modules\api\Controllers\Ticket($params);            
        } else if ($file == '/api/Controllers/Book.class.php' ) {
            return new \Modules\api\Controllers\Book($params);            
        } else if ($file == '/api/Controllers/ProcedimentosPerigosos.class.php' ) {
            return new \Modules\api\Controllers\ProcedimentosPerigosos($params);            
        } else if ($file == '/api/Controllers/RoadmapIco.class.php' ) {
            return new \Modules\api\Controllers\RoadmapIco($params);            
        } else if ($file == '/financeiro/Controllers/Deposito.class.php' ) {
            return new \Modules\financeiro\Controllers\Deposito($params);            
        } else if ($file == '/financeiro/Controllers/Saque.class.php' ) {
            return new \Modules\financeiro\Controllers\Saque($params);            
        } else if ($file == '/financeiro/Controllers/Operacoes.class.php' ) { 
            return new \Modules\financeiro\Controllers\Operacoes($params);
        } else if ($file == '/financeiro/Controllers/Processos.class.php' ) {
            return new \Modules\financeiro\Controllers\Processos($params);            
        } else if ($file == '/financeiro/Controllers/Moedas.class.php' ) {
            return new \Modules\financeiro\Controllers\Moedas($params);            
        } else if ($file == '/emails/Controllers/Template.class.php' ) {
            return new \TemplatesEmails\TemplateEmailContato($params);            
        } else if ($file == '/pdfs/Controllers/PDFComprovanteInvoice.class.php' ) {
            return new \Modules\pdfs\Controllers\PDFComprovanteInvoice($params);
        } else if ($file == '/franquiado/Controllers/Administrativo.class.php' ) {
            return new \Modules\franquiado\Controllers\Administrativo($params);            
        } else if ($file == '/franquiado/Controllers/Clientes.class.php' ) {
            return new \Modules\cadastros\Controllers\Clientes($params);            
        } else if ($file == '/franquiado/Controllers/Financas.class.php' ) {
            return new \Modules\franquiado\Controllers\Financas($params);            
        } else if ($file == '/franquiado/Controllers/Portal.class.php' ) {
            return new \Modules\franquiado\Controllers\Portal($params);            
        } else if ($file == '/notificacoes/Controllers/Notificacoes.class.php' ) {
            return new \Modules\notificacoes\Controllers\Notificacoes($params);            
        } else if ($file == '/ws/Controllers/NotaFiscal.class.php' ) {
            return new \Modules\ws\Controllers\NotaFiscal($params);            
        } else if ($file == '/ws/Controllers/GerenciaNet.class.php' ) {
            return new \Modules\ws\Controllers\GerenciaNet($params);            
        } else if ($file == '/ws/Controllers/Convites.class.php' ) {
            return new \Modules\ws\Controllers\Convites();            
        } else if ($file == '/ws/Controllers/Telegram.class.php' ) {
            return new \Modules\ws\Controllers\Telegram($params);            
        } else if ($file == '/ws/Controllers/Calculos.class.php' ) {
            return new \Modules\ws\Controllers\Calculos($params);
            
        } else if ($file == '/ws/Controllers/Dinamize.class.php' ) {
            return new \Modules\ws\Controllers\Dinamize($params);
            
        } else if ($file == '/ws/Controllers/Tokens.class.php' ) {
            return new \Modules\ws\Controllers\Tokens($params);    
            
        } else if ($file == '/ws/Controllers/Pdvs.class.php' ) {
            return new \Modules\ws\Controllers\Pdvs($params);
            
        } else 
            
        if ($file == '/ws/Controllers/Atar.class.php' ) { 
            return new \Modules\ws\Controllers\Atar($params);
        } else
            
        if ($file == '/ws/Controllers/LaraBoleto.class.php' ) { 
            return new \Modules\ws\Controllers\LaraBoleto($params);
        } else
            
        if ($file == '/ws/Controllers/BookSocketIo.class.php' ) { 
            return new \Modules\ws\Controllers\BookSocketIo($params);
        } else
            
        if ($file == '/ws/Controllers/BookSocketHistory.class.php' ) { 
            return new \Modules\ws\Controllers\BookSocketHistory($params);
        } else 
            
        if ($file == '/ws/Controllers/Lambda.class.php' ) { 
            return new \Modules\ws\Controllers\Lambda($params);
        } else
            
        if ($file == '/ws/Controllers/AppAdmin.class.php' ) { 
            return new \Modules\ws\Controllers\AppAdmin($params);
        } else
        if ($file == '/trade/Controllers/BookWeb.class.php' ) {
            return new \Modules\trade\Controllers\BookWeb($params);
        } else
        if ($file == '/trade/Controllers/Book.class.php' ) {
            return new \Modules\trade\Controllers\Book($params);
        } else
        if ($file == '/trade/Controllers/BookNew.class.php') {
            return new \Modules\trade\Controllers\BookNew($params);
        } else
        if ($file == '/trade/Controllers/OrderBookTest.class.php' ) { 
            return new \Modules\trade\Controllers\OrderBookTest($params);
        } else
        if ($file == '/trade/Controllers/CompraVendaDireta.class.php' ) { 
            return new \Modules\trade\Controllers\CompraVendaDireta($params);
        } else
        if ($file == '/trade/Controllers/Extrato.class.php' ) {
            
            return new \Modules\trade\Controllers\Extrato($params);
        } else
        if ($file == '/trade/Controllers/Negociacoes.class.php' ) { 
            return new \Modules\trade\Controllers\Negociacoes($params);
        } else
        if ($file == '/trade/Controllers/CompraIco.class.php' ) { 
            return new \Modules\trade\Controllers\CompraIco($params);
        } else
        if ($file == '/trade/Controllers/PreVendas.class.php' ) { 
            return new \Modules\trade\Controllers\PreVendas($params);
        } else
        if ($file == '/cartoes/Controllers/MeusCartoes.class.php' ) { 
            return new \Modules\cartoes\Controllers\MeusCartoes($params);
        } else
        if ($file == '/cartoes/Controllers/Mensalidades.class.php' ) { 
            return new \Modules\cartoes\Controllers\Mensalidades($params);
        } else
        if ($file == '/cartoes/Controllers/Administrativo.class.php' ) { 
            return new \Modules\cartoes\Controllers\Administrativo($params);
        } else
        if ($file == '/invoices/Controllers/Cards.class.php' ) { 
            return new \Modules\invoices\Controllers\Cards($params);
        } else
        if ($file == '/invoices/Controllers/Orders.class.php' ) {
            return new \Modules\apiv2\Controllers\Ordens($params);
            
        } else
        if ($file == '/invoices/Controllers/Remessa.class.php' ) { 
            return new \Modules\invoices\Controllers\Remessa($params);
        } else
        if ($file == '/invoices/Controllers/Retorno.class.php' ) { 
            return new \Modules\invoices\Controllers\Retorno($params);
        } else
        if ($file == '/votacao/Controllers/Votacao.class.php' ) { 
            return new \Modules\votacao\Controllers\Votacao($params);
        } else
        if ($file == '/contasempresa/Controllers/TransferenciaEmpresa.class.php' ) { 
            return new \Modules\contasempresa\Controllers\TransferenciaEmpresa($params);
        } else
        if ($file == '/contasempresa/Controllers/ContaCorrente.class.php' ) { 
            
            return new \Modules\contasempresa\Controllers\ContaCorrente($params);
        } else
        if ($file == '/contasempresa/Controllers/ResumoContas.class.php' ) { 
            return new \Modules\contasempresa\Controllers\ResumoContas($params);
        } else
        if ($file == '/contasempresa/Controllers/ResumoClientes.class.php' ) { 
            return new \Modules\contasempresa\Controllers\ResumoClientes($params);
        } else 
        if ($file == '/cofre/Controllers/Cofre.class.php' ) { 
            return new \Modules\cofre\Controllers\Cofre($params);
        } else
        if ($file == '/cofre/Controllers/InvestimentoContrato.class.php' ) { 
            return new \Modules\cofre\Controllers\InvestimentoContrato($params);
        } else    
        if ($file == '/apiv2/Controllers/Auth.class.php' ) { 
            return new \Modules\apiv2\Controllers\Auth($params);
        } else
        if ($file == '/apiv2/Controllers/BookUtils.class.php' ) { 
            
            return new \Modules\apiv2\Controllers\BookUtils($params);
        } else
        if ($file == '/apiv2/Controllers/Charts.class.php' ) { 
            return new \Modules\apiv2\Controllers\Charts($params);
        } else
        if ($file == '/apiv2/Controllers/HTTPResponseCode.class.php' ) { 
            return new \Modules\apiv2\Controllers\HTTPResponseCode($params);
        } else
        if ($file == '/apiv2/Controllers/HttpResult.class.php' ) { 
            return new \Modules\apiv2\Controllers\HttpResult($params);
        } else
        if ($file == '/apiv2/Controllers/Methods.class.php' ) { 
            return new \Modules\apiv2\Controllers\Methods($params);
        } else
        if ($file == '/apiv2/Controllers/ChartInterval.class.php' ) { 
            return new \Modules\apiv2\Controllers\ChartInterval($params);
        } else
        if ($file == '/apiv2/Controllers/Coins.class.php' ) { 
            return new \Modules\apiv2\Controllers\Coins($params);
        } else
        if ($file == '/apiv2/Controllers/Ordens.class.php' ) { 
            return new \Modules\apiv2\Controllers\Ordens($params);
        } else
        if ($file == '/apiv2/Controllers/Trades.class.php' ) { 
            return new \Modules\apiv2\Controllers\Trades($params);
        } else
        if ($file == '/apiv2/Controllers/UDFChart.class.php' ) { 
            return new \Modules\apiv2\Controllers\UDFChart($params);
        } else
        if ($file == '/apiv2/Controllers/Core.class.php' ) { 
            return new \Modules\apiv2\Controllers\Core($params);
        } else
        if ($file == '/apiv2/Controllers/Account.class.php' ) { 
            return new \Modules\apiv2\Controllers\Account($params);
        } else
        if ($file == '/apiv2/Controllers/Book.class.php' ) { 
            return new \Modules\apiv2\Controllers\Book($params);
        } else
        if ($file == '/apiv2/Controllers/Ticket.class.php' ) { 
            return new \Modules\apiv2\Controllers\Ticket($params);
        } else
        if ($file == '/ico/Controllers/Auth.class.php' ) {
            return new \Modules\ico\Controllers\Auth($params);
            
        } else
        if ($file == '/ico/Controllers/Signup.class.php' ) { 
            return new \Modules\ico\Controllers\Signup($params);
            
        } else
        if ($file == '/ico/Controllers/Password.class.php' ) { 
            return new \Modules\ico\Controllers\Password($params);
        } else
        if ($file == '/ico/Controllers/Wallet.class.php' ) { 
            return new \Modules\ico\Controllers\Wallet($params);
        } else
        if ($file == '/ico/Controllers/Account.class.php' ) { 
            return new \Modules\ico\Controllers\Account($params);
        } else
        if ($file == '/ico/Controllers/Phases.class.php' ) { 
            return new \Modules\ico\Controllers\Phases($params);
        } else
        if ($file == '/ico/Controllers/Chart.class.php' ) { 
            return new \Modules\ico\Controllers\Chart($params);
        } else
        if ($file == '/ico/Controllers/TwoFactorAuth.class.php' ) { 
            return new \Modules\ico\Controllers\TwoFactorAuth($params);
        } else
        if ($file == '/ico/Controllers/MultiNivel.class.php' ) { 
            return new \Modules\ico\Controllers\MultiNivel($params);
        } else
        if ($file == '/ico/Controllers/Contact.class.php' ) { 
            return new \Modules\ico\Controllers\Contact($params);
        } else
        if ($file == '/ico/Controllers/Newsletter.class.php' ) { 
            return new \Modules\ico\Controllers\Newsletter($params);
        } else
        if ($file == '/ico/Controllers/Blog.class.php' ) { 
            return new \Modules\ico\Controllers\Blog($params);
        } else
        if ($file == '/ico/Controllers/ICO.class.php' ) { 
            return new \Modules\ico\Controllers\ICO($params);
        } else
        if ($file == '/ico/Controllers/Voting.class.php' ) { 
            return new \Modules\ico\Controllers\Voting($params);
        } else
        if ($file == '/ico/Controllers/Withdraw.class.php' ) { 
            return new \Modules\ico\Controllers\Withdraw($params);
        } else
        if ($file == '/ico/Controllers/Devices.class.php' ) { 
            return new \Modules\ico\Controllers\Devices($params);
        } else
        if ($file == '/extrato/Controllers/Extrato.class.php' ) { 
            return new \Modules\extrato\Controllers\Extrato($params);
        } else
        if ($file == '/referencia/Controllers/Referencia.class.php' ) { 
            return new \Modules\referencia\Controllers\Referencia($params);
        }
        
        
        throw new \Exception("Class not found");
    }


    
}