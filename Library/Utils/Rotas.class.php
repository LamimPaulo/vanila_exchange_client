<?php
namespace Utils;

  //
class Rotas {
    
    public static $rotas = Array(
        
      
        
        //"socket-book"                                 => Array("url" => "ws/bookSocketIo/index" ,                                                 "modulo" => "",                    "rotina" => "" ),
        //"socket-history"                              => Array("url" => "ws/BookSocketHistory/index" ,                                          "modulo" => "",                    "rotina" => "" ),
        //"socket-ticker"                             => Array("url" => "ws/BookSocketIo/index" ,                                                "modulo" => "",                    "rotina" => "" ),
        
        //direto no BD
        //"book-history"                            => Array("url" => "ws/BookSocketHistory/bookOrdemExecutadas" ,                                          "modulo" => "",                    "rotina" => "" ),
        //"socket-book-v2"                             => Array("url" => "ws/bookSocketIo/bookSocket" ,                                                 "modulo" => "",                    "rotina" => "" ),

        //"lbd"                                   => Array("url" => "api/procedimentosPerigosos/testeLbd" ,                       "modulo" => "api",                    "rotina" => "coins" ),
        "testeEmail"                              => Array("url" => "api/procedimentosPerigosos/testeEmail" ,                     "modulo" => "api",                    "rotina" => "coins" ),

        //"negociacaoCliente"                    => Array("url" => "api/procedimentosPerigosos/negociacao" ,                     "modulo" => "api",                    "rotina" => "coins" ),
        //"testesql"                              => Array("url" => "api/procedimentosPerigosos/testeSql" ,                     "modulo" => "api",                    "rotina" => "coins" ),
        //"ws/teste/api"                           => Array("url" => "ws/atar/callBackTeste" ,                                            "modulo" => "",                    "rotina" => "" ),
        //"modulos"                              => Array("url" => "api/procedimentosPerigosos/modulos" ,                         "modulo" => "api",                    "rotina" => "coins" ),

        
        
        "api/trade/fees"                        => Array("url" => "api/trade/fees" ,                                            "modulo" => "api",                    "rotina" => "coins" ),

//        
//        "apiv2/ordens"                          => Array("url" => "apiv2/ordens/index" ,                                          "modulo" => "apiv2",                    "rotina" => "coins" ),
//        "apiv2/trades"                          => Array("url" => "apiv2/trades/index" ,                                          "modulo" => "apiv2",                    "rotina" => "coins" ),
//        "apiv2/book"                            => Array("url" => "apiv2/Book/index" ,                                            "modulo" => "apiv2",                    "rotina" => "book" ),
//        "apiv2/ticket/markets"                  => Array("url" => "apiv2/ticket/markets" ,                                        "modulo" => "api",                    "rotina" => "ticket" ),
//        "apiv2/ticket/market"                   => Array("url" => "apiv2/ticket/market" ,                                         "modulo" => "api",                    "rotina" => "ticket" ),
//        
        "apiv2/udfchart/history"                => Array("url" => "apiv2/UDFChart/history" ,                                      "modulo" => "apiv2",                    "rotina" => "udfchart" ),
        "apiv2/udfchart/symbols"                => Array("url" => "apiv2/UDFChart/symbols" ,                                      "modulo" => "apiv2",                    "rotina" => "udfchart" ),
        "apiv2/udfchart/symbol_info"            => Array("url" => "apiv2/UDFChart/symbol_info" ,                                  "modulo" => "apiv2",                    "rotina" => "udfchart" ),
        "apiv2/udfchart/config"                 => Array("url" => "apiv2/UDFChart/config" ,                                       "modulo" => "apiv2",                    "rotina" => "udfchart" ),
        "apiv2/udfchart/time"                   => Array("url" => "apiv2/UDFChart/time" ,                                         "modulo" => "apiv2",                    "rotina" => "udfchart" ),
        
        "filesmanager"                            => Array("url" => "acesso/FilesManager/file" ,                                            "modulo" => "acesso",                    "rotina" => "filesmanager" ),
 
        "ws/lbdmain"                           => Array("url" => "ws/Lambda/lambdaMain" ,                                      "modulo" => "api",                    "rotina" => "coins" ),

       // "ws/pdvs/callbacks"                            => Array("url" => "ws/pdvs/callbacks" ,                                            "modulo" => "apiv2",                    "rotina" => "auth" ),
        "ws/ranking/gerar"                     => Array("url" => "ws/ranking/gerar" ,                                          "modulo" => "apiv2",                    "rotina" => "auth" ),
        "ws/atar/saldo"                        => Array("url" => "ws/atar/index" ,                                             "modulo" => "",                    "rotina" => "" ),
        "ws/atar/extrato"                      => Array("url" => "ws/atar/extrato" ,                                           "modulo" => "",                    "rotina" => "" ),
        "ws/atar/inserir"                      => Array("url" => "ws/atar/inserir" ,                                           "modulo" => "",                    "rotina" => "" ),
        "ws/atar/callback"                     => Array("url" => "ws/atar/callback" ,                                          "modulo" => "",                    "rotina" => "" ),
        "ws/atar/checarDeposito"               => Array("url" => "ws/atar/checarDepositos" ,                                   "modulo" => "",                    "rotina" => "" ),

        //"ws/atar/checarDeposito"               => Array("url" => "ws/atar/checarDepositos" ,                                   "modulo" => "",                    "rotina" => "" ),
        //"ws/tokens/balance"                    => Array("url" => "ws/tokens/balanceErc20" ,                                     "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/ethereum"                   => Array("url" => "ws/tokens/callEthereum" ,                                     "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/empresa"                    => Array("url" => "ws/tokens/saldoEmpresaErc20" ,                                "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/taxa"                       => Array("url" => "ws/tokens/atualizarTaxaErc20" ,                               "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/webhook"                    => Array("url" => "ws/tokens/webhook" ,                                          "modulo" => "ws",                    "rotina" => "ws" ),
        //"ws/tokens/saque"                      => Array("url" => "ws/tokens/saqueEth" ,                                          "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/wallet"                     => Array("url" => "ws/tokens/criarWalletEth" ,                                    "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/erc20Ethereum"              => Array("url" => "ws/tokens/depositoErc20Ethereum" ,                             "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/internal"                   => Array("url" => "ws/tokens/ethereumBalance" ,                                   "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/movebalance"                => Array("url" => "ws/tokens/moverSaldo" ,                                        "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/tokens/teste"                      => Array("url" => "ws/tokens/testeJava" ,                                        "modulo" => "ws",                    "rotina" => "ws" ),

        "ws/laraboletos/campainha"              => Array("url" => "ws/laraBoleto/campainha" ,                                        "modulo" => "ws",                    "rotina" => "ws" ),

        "ws/dinamize/adicionar"                 => Array("url" => "ws/dinamize/adicionarClienteDia" ,                            "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/dinamize/atualizar"                 => Array("url" => "ws/dinamize/atualizarClientes" ,                            "modulo" => "ws",                    "rotina" => "ws" ),
        "ws/dinamize/remover"                   => Array("url" => "ws/dinamize/removerClientes" ,                            "modulo" => "ws",                    "rotina" => "ws" ),
        //"ws/dinamize/listar"                   => Array("url" => "ws/dinamize/listarCliente" ,                            "modulo" => "ws",                    "rotina" => "ws" ),

        
//        "votacao"                               => Array("url" => "votacao/votacao/index" ,                                       "modulo" => "votacao",                  "rotina" => "" ),
//        "votacao/listar"                        => Array("url" => "votacao/votacao/listar" ,                                      "modulo" => "votacao",                  "rotina" => "" ),
//        "votacao/salvar"                        => Array("url" => "votacao/votacao/salvar" ,                                      "modulo" => "votacao",                  "rotina" => "" ),
//        "votacao/votar"                         => Array("url" => "votacao/votacao/votar" ,                                       "modulo" => "votacao",                  "rotina" => "" ),
//        "votacao/aprovar"                       => Array("url" => "votacao/votacao/aprovar" ,                                     "modulo" => "votacao",                  "rotina" => "" ),
//        "votacao/negar"                         => Array("url" => "votacao/votacao/negar" ,                                       "modulo" => "votacao",                  "rotina" => "" ),
//        
//        "painel/ico"                            => Array("url" => "monitoramento/painelIco/index" ,                               "modulo" => "monitoramento",            "rotina" => "painel/ico" ),
//        "painel/ico/ultimascompras"             => Array("url" => "monitoramento/painelIco/getUltimasComprasEfetuadas" ,          "modulo" => "monitoramento",            "rotina" => "painel/ico" ),
//        "painel/ico/ultimasbonificacoes"        => Array("url" => "monitoramento/painelIco/getUltimasBonificacoes" ,              "modulo" => "monitoramento",            "rotina" => "painel/ico" ),
//        "painel/ico/dadosfases"                 => Array("url" => "monitoramento/painelIco/progressoFases" ,                       "modulo" => "monitoramento",            "rotina" => "painel/ico" ),
//        
        // Acesso confirmarEmail
        "app/account"                => Array("url" => "acesso/acesso/ativarContaApp" ,                    "modulo" => "acesso",                       "rotina" => "acesso"),
        "activation"                => Array("url" => "acesso/acesso/novoCadastro" ,                    "modulo" => "acesso",                       "rotina" => "acesso"),
        "idioma/change"                 => Array("url" => "principal/principal/mudarIdioma" ,               "modulo" => "acesso",                       "rotina" => "acesso"),
        "login"                         => Array("url" => "acesso/acesso/index" ,                           "modulo" => "acesso",                       "rotina" => "acesso"),
        "register"                      => Array("url" => "acesso/acesso/register" ,                        "modulo" => "acesso",                       "rotina" => "acesso"),
        "logar"                         => Array("url" => "acesso/acesso/logar" ,                           "modulo" => "acesso",                       "rotina" => "acesso"),
        "logarapi"                      => Array("url" => "acesso/acesso/logarapi" ,                        "modulo" => "acesso",                       "rotina" => "acesso"),
        "logout"                        => Array("url" => "acesso/acesso/logout" ,                          "modulo" => "acesso",                       "rotina" => "acesso"),
        "recovery"                   => Array("url" => "acesso/acesso/recuperar" ,                       "modulo" => "acesso",                       "rotina" => "acesso"),
        "revogar"                       => Array("url" => "acesso/acesso/revogarAcesso" ,                   "modulo" => "acesso",                       "rotina" => "acesso"),
        "recover"                       => Array("url" => "acesso/acesso/newPassword" ,                       "modulo" => "acesso",                       "rotina" => "acesso"),
        "mail/confirmation"             => Array("url" => "acesso/acesso/confirmarEmail" ,                       "modulo" => "acesso",                       "rotina" => "acesso"),
        "recovervalidation"             => Array("url" => "acesso/acesso/validarDadosRecuperacao" ,                       "modulo" => "acesso",                       "rotina" => "acesso"),
        "authrecover"                   => Array("url" => "acesso/acesso/authRecover" ,                       "modulo" => "acesso",                       "rotina" => "acesso"),
        "docs/aceitacao"                => Array("url" => "acesso/docs/index" ,                             "modulo" => "acesso",                       "rotina" => "acesso"),
        "docs/aceitacao/salvar"         => Array("url" => "acesso/docs/salvar" ,                            "modulo" => "acesso",                       "rotina" => "acesso"),
        "notificacaoMoeda/Cliente"      => Array("url" => "acesso/notificacaoMoeda/index" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        "notificacaomoeda/listarCliente" => Array("url" => "acesso/notificacaoMoeda/listarCliente" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        "notificacaomoeda/marcarComoLido" => Array("url" => "acesso/notificacaoMoeda/marcarComoLido" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        
        //Marketing
        "marketingImagem/onlyView"       => Array("url" => "acesso/marketingImagem/index" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        "marketingImagem/listar"         => Array("url" => "acesso/marketingImagem/listarCliente" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        "marketingImagem/marcarComoLido" => Array("url" => "acesso/marketingImagem/marcarComoLido" ,                  "modulo" => "acesso",                       "rotina" => "acesso"),
        
        
        //"confirmacao"                   => Array("url" => "acesso/acesso/confirmation" ,                    "modulo" => "acesso",                       "rotina" => "acesso"),
        //"qrcodeinvoice"                 => Array("url" => "acesso/cartoes/gerarQRCodeInvoice" ,             "modulo" => "acesso",                       "rotina" => "cartoes"),
        //"wsconvites"                    => Array("url" => "ws/convites/index" ,                             "modulo" => "ws",                           "rotina" => "convites"),
        "validar2f"                     => Array("url" => "acesso/sms/validate" ,                           "modulo" => "acesso",                       "rotina" => "sms"),
        "twofactorauth"                 => Array("url" => "acesso/sms/auth" ,                               "modulo" => "acesso",                       "rotina" => "sms"),
        "twofactorauth/token/send"      => Array("url" => "acesso/sms/reenviar" ,                           "modulo" => "acesso",                       "rotina" => "sms"),
        "initpage"                      => Array("url" => "principal/principal/init" ,                      "modulo" => "acesso",                       "rotina" => "sms"),
        "online"                        => Array("url" => "principal/principal/online" ,                      "modulo" => "acesso",                       "rotina" => "sms"),
        "currency"                      => Array("url" => "principal/principal/setCurrency" ,               "modulo" => "acesso",                       "rotina" => "principal"),
        "consultacpf"                   => Array("url" => "site/cadastro/cpf" ,                             "modulo" => "site",                         "rotina" => "cadastro"),
        "cadastro/senha/forca"          => Array("url" => "site/cadastro/forcaSenha" ,                      "modulo" => "site",                         "rotina" => "cadastro" ),
        "transferencias"                => Array("url" => "contas/btc/transferencia" ,                      "modulo" => "transferencias",                "rotina" => "cadastro" ),
        "clientes/promocao/pagar"       => Array("url" => "cadastros/clientes/enviarCreditosCampanha" ,                      "modulo" => "transferencias",               "rotina" => "transferencias" ),
        "cotacoes/get"                  => Array("url" => "principal/principal/getListaCotacoes" ,          "modulo" => "principal",               "rotina" => "principal" ),
        
        //"testeEmail"                  => Array("url" => "principal/principal/testeEmail" ,          "modulo" => "principal",               "rotina" => "principal" ),

        
        // Contas Bancárias
        "contasbancarias/listar"        => Array("url" => "cadastros/contas/getContasCliente" ,             "modulo" => "saques",               "rotina" => "contasbancarias" ),
        "contasbancarias/status/alterar"=> Array("url" => "cadastros/contas/alterarStatus" ,                "modulo" => "saques",               "rotina" => "contasbancarias" ),
        
        "service/correio/buscacep"         => Array("url" => "services/correios/enderecoPorCep" ,           "modulo" => "services",                     "rotina" => "correios"),
        "service/correio/buscacoordenadas" => Array("url" => "services/correios/buscarCoordenadas" ,        "modulo" => "services",                     "rotina" => "correios"),
        
        // DASHBOARD
        "dashboard"                     => Array("url" => "principal/dashboard/index" ,                     "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/saldos/clientes"     => Array("url" => "principal/dashboardAdm/getSaldosMoedasClientes" ,             "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/saldos/empresa"     => Array("url" => "principal/dashboardAdm/getSaldosMoedasEmpresa" ,             "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/ordens/executadas"   => Array("url" => "principal/dashboard/listarMinhasOrdensExecutadas" ,   "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/minhasmoedas"        => Array("url" => "principal/dashboard/htmlItemMinhasMoedas" ,           "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/minhasmoedas/novo"        => Array("url" => "principal/dashboard/minhasMoedas" ,               "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/setMoedaFavorita"    => Array("url" => "principal/dashboard/setMoedaFavorita" ,           "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/calculaSaldo"    => Array("url" => "principal/dashboard/calculaSaldoDashboard" ,           "modulo" => "principal",                    "rotina" => "dashboard"),
        "dashboard/redirect"            => Array("url" => "principal/principal/setCurrencyAndRedirect" ,           "modulo" => "principal",                    "rotina" => "dashboard"),
        //"dashboard/tasks/pendentes"     => Array("url" => "principal/principal/getTasksPendentes" ,           "modulo" => "principal",                    "rotina" => "dashboard"),
        
        // PERFIL
        "myprofile"                     => Array("url" => "perfil/meusDados/index" ,                        "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "postgetcidadesperfil"          => Array("url" => "perfil/meusDados/getCidades" ,                   "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "postremoverdocumentoperfil"    => Array("url" => "perfil/meusDados/removerDocumento" ,             "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "market/salvar"                 => Array("url" => "perfil/meusDados/marketupdate" ,             "modulo" => "perfil",                       "rotina" => "meusdados" ),
        
        /*
        // COMERCIOS
        "comercios"                     => Array("url" => "comercios/comercios/index" ,                        "modulo" => "comercios",                       "rotina" => "comercios" ),
        "comercios/cadastro"            => Array("url" => "comercios/comercios/cadastro" ,                     "modulo" => "comercios",                       "rotina" => "comercios" ),
        "comercios/salvar"              => Array("url" => "comercios/comercios/salvar" ,                       "modulo" => "comercios",                       "rotina" => "comercios" ),
        "comercios/excluir"             => Array("url" => "comercios/comercios/excluir" ,                      "modulo" => "comercios",                       "rotina" => "comercios" ),
        "comercios/listar"              => Array("url" => "comercios/comercios/listar" ,                       "modulo" => "comercios",                       "rotina" => "comercios" ),
        "comercios/cidades/listar"      => Array("url" => "comercios/comercios/getCidadesByEstado" ,           "modulo" => "comercios",                       "rotina" => "comercios" ),
        
        
        "comercios/administrativo"      => Array("url" => "comercios/comercios/administrativo" ,               "modulo" => "comercios",                       "rotina" => "comercios/administrativo" ),
        "comercios/administrativo/listar"      => Array("url" => "comercios/comercios/administrativoListar" ,  "modulo" => "comercios",                       "rotina" => "comercios/administrativo" ),
        "comercios/administrativo/status"      => Array("url" => "comercios/comercios/alterarStatus" ,         "modulo" => "comercios",                       "rotina" => "comercios/administrativo" ),
        
        "comercios/segmentos"                  => Array("url" => "comercios/segmentos/index" ,                 "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        "comercios/segmentos/listar"           => Array("url" => "comercios/segmentos/listar" ,                "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        "comercios/segmentos/cadastro"         => Array("url" => "comercios/segmentos/cadastro" ,              "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        "comercios/segmentos/excluir"          => Array("url" => "comercios/segmentos/excluir" ,               "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        "comercios/segmentos/salvar"           => Array("url" => "comercios/segmentos/salvar" ,                "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        "comercios/segmentos/status"           => Array("url" => "comercios/segmentos/status" ,                "modulo" => "comercios",                       "rotina" => "comercios/segmentos" ),
        */
        
        // CARTEIRAS
        "deposit"                     => Array("url" => "cadastros/carteiras/index" ,                     "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        

        // INDICACOES
        //"referal"                    => Array("url" => "principal/indicacoes/index" ,                    "modulo" => "principal",                    "rotina" => "indicacoes" ),
        
        // BITCOIN
        "contacorrentebtc"              => Array("url" => "contas/btc/lancamentos" ,                        "modulo" => "btc",                          "rotina" => "contacorrentebtc" ),
        "contasbtc"                     => Array("url" => "contas/btc/index" ,                              "modulo" => "btc",                          "rotina" => "contasbtc" ),
        "transferenciabtc"              => Array("url" => "contas/btc/transferencia" ,                      "modulo" => "btc",                          "rotina" => "transferenciabtc" ),
        //"ccempresabtc"                  => Array("url" => "contas/btcEmpresa/index" ,                       "modulo" => "btc",                          "rotina" => "ccempresabtc" ),
        "painelbtc"                     => Array("url" => "contas/painelBtc/index" ,                        "modulo" => "btc",                          "rotina" => "painelbtc" ),
        //"contabtcempresa"               => Array("url" => "contas/btcEmpresa/index" ,                       "modulo" => "btc",                          "rotina" => "contabtcempresa" ),
        //"transacoespendentes"           => Array("url" => "contas/painelBtc/index" ,                        "modulo" => "monitoramento",                "rotina" => "transacoespendentes" ),
        
        // CLUB
        //"chat"                          => Array("url" => "comunidade/chat/index" ,                         "modulo" => "comunidade",                   "rotina" => "chat" ),
        
        // REAIS
        "contacorrentereais"            => Array("url" => "contas/reais/lancamentos" ,                      "modulo" => "reais",                        "rotina" => "contacorrentereais" ),
        "contasreais"                   => Array("url" => "contas/reais/index" ,                            "modulo" => "reais",                        "rotina" => "contasreais" ),
        "transferenciareais"            => Array("url" => "contas/reais/transferencia" ,                    "modulo" => "reais",                        "rotina" => "transferenciareais" ),
        "depositos"                     => Array("url" => "contas/depositos/index" ,                        "modulo" => "depositos",                    "rotina" => "depositos"),
        "withdraw"                        => Array("url" => "contas/saques/index" ,                           "modulo" => "saques",                       "rotina" => "saques" ),
        "contareaisempresa"             => Array("url" => "contas/reaisEmpresa/index" ,                     "modulo" => "reais",                        "rotina" => "contareaisempresa" ),
 
        //Atar
        "atar/token"                    => Array("url" => "contas/atar/token" ,                           "modulo" => "saques",                       "rotina" => "saques" ),
        "atar/salvar"                   => Array("url" => "contas/atar/salvar" ,                          "modulo" => "saques",                       "rotina" => "saques" ),
        "atar/listar"                   => Array("url" => "contas/atar/listar" ,                          "modulo" => "saques",                       "rotina" => "saques" ),
        "atar/saldo"                    => Array("url" => "contas/atar/atarSaldo" ,                        "modulo" => "saques",                       "rotina" => "saques" ),
        
        // Financeiro
        "operacoes"                     => Array("url" => "financeiro/operacoes/index" ,                    "modulo" => "financeiro" ,                  "rotina" => "operacoes"),
        "processos"                     => Array("url" => "financeiro/processos/index" ,                    "modulo" => "financeiro" ,                  "rotina" => "processos"),
        "moedas"                        => Array("url" => "financeiro/moedas/index" ,                       "modulo" => "financeiro",                   "rotina" => "moedas" ),
        
        
        // Configuracoes
        //"painelcontrole"                => Array("url" => "configuracoes/painelControle/index" ,            "modulo" => "configuracoes",                "rotina" => "painelcontrole" ),
        //"painelsite"                => Array("url" => "configuracoes/painelSite/index" ,            "modulo" => "configuracoes",                "rotina" => "painelsite" ),
        //"painelsite/salvar"                => Array("url" => "configuracoes/painelSite/salvar" ,            "modulo" => "configuracoes",                "rotina" => "painelsite" ),
        //"contasempresa"                 => Array("url" => "configuracoes/contas/index" ,                    "modulo" => "configuracoes",                "rotina" => "contasempresa" ),
        //"contasempresa/status/alterar"  => Array("url" => "configuracoes/contas/alterarStatusAtivo" ,       "modulo" => "configuracoes",                "rotina" => "contasempresa" ),
       // "criptocurrencies/fees"         => Array("url" => "configuracoes/taxasMoedas/index" ,               "modulo" => "configuracoes",                "rotina" => "criptocurrencies/fees"  ),
       // "criptocurrencies/fees/update"  => Array("url" => "configuracoes/taxasMoedas/salvar" ,              "modulo" => "configuracoes",                "rotina" => "criptocurrencies/fees"  ),
       // "notasfiscais"                  => Array("url" => "configuracoes/painelNotasFiscais/index" ,        "modulo" => "configuracoes",                "rotina" => "notasfiscais"  ),
       // "notasfiscais/filtrar"          => Array("url" => "configuracoes/painelNotasFiscais/listar" ,       "modulo" => "configuracoes",                "rotina" => "notasfiscais"  ),
        //"notasfiscais/atualizar"        => Array("url" => "configuracoes/painelNotasFiscais/atualizar" ,    "modulo" => "configuracoes",                "rotina" => "notasfiscais"  ),
        //"notasfiscais/cancelar"         => Array("url" => "configuracoes/painelNotasFiscais/cancelar" ,     "modulo" => "configuracoes",                "rotina" => "notasfiscais"  ),
       // "notasfiscais/emitir"           => Array("url" => "configuracoes/painelNotasFiscais/emitir" ,       "modulo" => "configuracoes",                "rotina" => "notasfiscais"  ),
        
        
        // Cadastros
        "atar/deposito/listar"          => Array("url" => "cadastros/atar/listar" ,                       "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        "atar/deposito/salvar"          => Array("url" => "cadastros/atar/salvar" ,                         "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        "atar/deposito/token"          => Array("url" => "cadastros/atar/token" ,                         "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        
        //"usuarios"                      => Array("url" => "cadastros/usuarios/index" ,                      "modulo" => "cadastros" ,                   "rotina" => "usuarios"),
        "clientes"                      => Array("url" => "cadastros/clientes/index" ,                      "modulo" => "cadastros",                    "rotina" => "clientes" ),
        "carteiras/listar"              => Array("url" => "cadastros/carteiras/listar" ,                    "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        "carteiras/nova"                => Array("url" => "cadastros/carteiras/criarCarteira" ,             "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        "carteiras/principal"           => Array("url" => "cadastros/carteiras/marcarComoPrincipal" ,       "modulo" => "cadastros",                    "rotina" => "carteiras" ),
        //"clientes/franquia/status"      => Array("url" => "cadastros/clientes/alterarStatusFranquia" ,      "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/franquia/cancelar"    => Array("url" => "cadastros/clientes/cancelarFranquia" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/taxas/listar"         => Array("url" => "cadastros/taxasClientes/carregarTaxasClientes" , "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/taxas/salvar"         => Array("url" => "cadastros/taxasClientes/salvar" ,                "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/comissoes/cadastro"   => Array("url" => "cadastros/comissoesClientes/carregar" ,          "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/comissoes/salvar"     => Array("url" => "cadastros/comissoesClientes/salvar" ,            "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/creditos/listar"      => Array("url" => "cadastros/creditosClientes/carregar" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"clientes/creditos/salvar"      => Array("url" => "cadastros/creditosClientes/salvar" ,             "modulo" => "cadastros",                    "rotina" => "clientes" ),
        # RANKING - follow -  created André 19/07/2019
        //"follow"                        => Array("url" => "cadastros/clientes/listRankingFollow" ,          "modulo" => "cadastros",                    "rotina" => "clientes" ),
        //"followfilter"                  => Array("url" => "cadastros/clientes/listFollowFilter" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),
        

/*
        // Cartoes
        "meuscartoes"                           => Array("url" => "cartoes/meusCartoes/index" ,                     "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/listar"                    => Array("url" => "cartoes/meusCartoes/listarInvoicesCliente" ,     "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/dados"                     => Array("url" => "cartoes/meusCartoes/getDadosCartao" ,            "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/novo"                      => Array("url" => "cartoes/meusCartoes/novoCartao" ,                "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/ativar"                    => Array("url" => "cartoes/meusCartoes/ativarCartao",               "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/pin/validar"               => Array("url" => "cartoes/meusCartoes/validarPin",                 "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        "meuscartoes/senha/mostrar"             => Array("url" => "cartoes/meusCartoes/mostrarSenha",               "modulo" => "cartoes",                      "rotina" => "meuscartoes" ),
        
        "mensalidades"                          => Array("url" => "cartoes/mensalidades/index" ,                           "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidade/clientes/cartoes"          => Array("url" => "cartoes/mensalidades/getCartoesByCliente" ,             "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/listar"                   => Array("url" => "cartoes/mensalidades/listar" ,                          "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/listar/pagaveis"          => Array("url" => "cartoes/mensalidades/getMensalidadesPagaveis" ,         "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/pagar"                    => Array("url" => "cartoes/mensalidades/pagar" ,                           "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/invoice/gerar"            => Array("url" => "cartoes/mensalidades/gerarInvoice" ,                    "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/invoice/status"           => Array("url" => "cartoes/mensalidades/getStatusInvoice" ,                "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        "mensalidades/invoice/comprovante"      => Array("url" => "cartoes/mensalidades/comprovante" ,                     "modulo" => "cartoes",                 "rotina" => "mensalidades" ),
        */
        
        /*
        "invoices/filtrar"                      => Array("url" => "invoices/invoices/filtrar" ,                             "modulo" => "cartoes",                 "rotina" => "invoices" ),
        "invoices/get"                          => Array("url" => "invoices/invoices/getDadosInvoice" ,                     "modulo" => "cartoes",                 "rotina" => "invoices" ),
        "invoices/cartao/salvar"                => Array("url" => "invoices/invoices/salvarDadosCartao" ,                   "modulo" => "cartoes",                 "rotina" => "invoices" ),
        "invoices/cartao/status"                => Array("url" => "invoices/invoices/alterarStatusCartao" ,                 "modulo" => "cartoes",                 "rotina" => "invoices" ),
        "invoices/cancelar"                     => Array("url" => "invoices/invoices/cancelar" ,                            "modulo" => "cartoes",                 "rotina" => "invoices" ),
        
        "invoices/pagamento/confirmar"          => Array("url" => "invoices/invoices/confirmarPagamentoPedidoCartao" ,      "modulo" => "cartoes",                 "rotina" => "invoices" ),
        "invoices/cartao/extrato"               => Array("url" => "invoices/cards/consultaExtrato",                         "modulo" => "cartoes",                 "rotina" => "invoices" ),
        
        "administrativo/cartoes"                => Array("url" => "cartoes/administrativo/index" ,                 "modulo" => "cartoes",                      "rotina" => "administrativo/cartoes" ),
        */
        
        //"cards"                         => Array("url" => "invoices/invoices/cards" ,                       "modulo" => "cartao",                      "rotina" => "cards" ),
        //"recharges"                     => Array("url" => "invoices/recharges/index" ,                      "modulo" => "cartao",                      "rotina" => "recharges" ),
       // "invoices"                      => Array("url" => "invoices/invoices/" ,                            "modulo" => "cartao",                      "rotina" => "invoices" ),
        //"cartoes"                       => Array("url" => "invoices/cards/index" ,                          "modulo" => "cartao",                      "rotina" => "cartoes" ),
       // "recargas"                      => Array("url" => "invoices/recharges/index" ,                      "modulo" => "cartao",                      "rotina" => "recargas" ),
       // "extrato"                       => Array("url" => "invoices/cards/extratos" ,                       "modulo" => "cartao",                      "rotina" => "extrato" ),

        
        //"estabelecimentos"              => Array("url" => "pdvs/estabelecimentos/index" ,                   "modulo" => "servicos",                         "rotina" => "estabelecimentos",        "second" => "recebimentospdv"),
        //"pontospdvs"                    => Array("url" => "pdvs/pontos/index" ,                             "modulo" => "servicos",                         "rotina" => "pontospdvs",              "second" => "recebimentospdv" ),
        
        
        // Monitoramento
        //"monitoramento"                 => Array("url" => "monitoramento/painel/index" ,                    "modulo" => "monitoramento",                "rotina" => "monitoramento" ),
        
        
        // SITE
        //"cadastro"                      => Array("url" => "site/cadastro/index" ,                                       "modulo" => "site",                "rotina" => "cadastro" ),
        "cadastro/cidades/listar"       => Array("url" => "site/cadastro/getCidades" ,                                  "modulo" => "site",                "rotina" => "cadastro" ),
        "cadastro/cliente/aderir"       => Array("url" => "acesso/cartoes/aderirSimplificado" ,                         "modulo" => "site",                "rotina" => "cadastro" ),
        
        // Documentaçao - API
        //"doc/api/transferencia"            => Array("url" => "doc/transferencia/index" ,                                "modulo" => "docs",                 "rotina" => "doc/api/transferencia" ),
        //"doc/api/pdv"                      => Array("url" => "doc/pdv/index" ,                                          "modulo" => "docs",                 "rotina" => "doc/api/pdv" ),
        //"doc/api/carteira"                 => Array("url" => "doc/carteira/index" ,                                     "modulo" => "docs",                 "rotina" => "doc/api/carteira" ),
        //"doc/api/trade"                    => Array("url" => "doc/trade/index" ,                                        "modulo" => "docs",                 "rotina" => "doc/api/trade" ),
        //"doc/api/calc"                     => Array("url" => "doc/calc/index" ,                                         "modulo" => "docs",                 "rotina" => "doc/api/calc" ),
        //"doc/api/tabelas"                  => Array("url" => "doc/tabelas/index" ,                                      "modulo" => "docs",                 "rotina" => "doc/api/tabelas" ),
        //"doc/api/payment"                  => Array("url" => "doc/payment/index" ,                                      "modulo" => "docs",                 "rotina" => "doc/api/payment" ),
        //"doc/api/newsletter"               => Array("url" => "doc/newsletter/index" ,                                   "modulo" => "docs",                 "rotina" => "doc/api/newsletter" ),
        //"doc/api/contato"                  => Array("url" => "doc/contato/index" ,                                      "modulo" => "docs",                 "rotina" => "doc/api/contato" ),
        
        // OUTROS
        "error"                         => Array("url" => "error/error/index" ,                   "modulo" => "erro",                         "rotina" => "error" ),
        
        // **********************************************         Métodos internos       **********************************************
        
        "depositos/wallets/show"            => Array("url" => "cadastros/carteiras/showDados" ,                     "modulo" => "cadastros",                    "rotina" => "depositos" ),
        "depositos/wallets/listaDepositos"  => Array("url" => "cadastros/carteiras/listaDepositos" ,                 "modulo" => "cadastros",                    "rotina" => "depositos" ),
        
        // Clientes
//        "postlistarclientes"            => Array("url" => "cadastros/clientes/listar" ,                     "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postcadastroclientes"          => Array("url" => "cadastros/clientes/cadastro" ,                   "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postsalvarcliente"             => Array("url" => "cadastros/clientes/salvar" ,                     "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postexcluircliente"            => Array("url" => "cadastros/clientes/excluir" ,                    "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postrejeitarcliente"           => Array("url" => "cadastros/clientes/rejeitarCliente" ,            "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postaceitarcliente"            => Array("url" => "cadastros/clientes/aprovarCliente" ,             "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postliberarcliente"            => Array("url" => "cadastros/clientes/liberarCliente" ,             "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "clientes/consultas/cnpj"       => Array("url" => "cadastros/clientes/consultaCnpj" ,               "modulo" => "cadastros",                    "rotina" => "clientes"),
//        "clientes/consultas/cpf"       => Array("url" => "cadastros/clientes/consultaCpf" ,               "modulo" => "cadastros",                    "rotina" => "clientes"),
//        "postgetdadosvalormensalcliente"        => Array("url" => "cadastros/clientes/getDadosValorMensal" ,            "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postsalvardadosvalormensalcliente"     => Array("url" => "cadastros/clientes/salvarMovimentacaoMensal" ,       "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postgetcidadescliente"         => Array("url" => "cadastros/clientes/getCidades" ,                 "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postzerarpincliente"           => Array("url" => "cadastros/clientes/zerarPin" ,                   "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postaceitarrejeitarfotocliente"        => Array("url" => "cadastros/clientes/aceitarRejeitarFoto" ,            "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postimportarexceldadosmensalcliente"   => Array("url" => "cadastros/clientes/importFromExcel" ,                "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postgetclientebyemail"         => Array("url" => "cadastros/clientes/getByEmail" ,                  "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "postpedidosclientes"           => Array("url" => "cadastros/clientes/pedidos" ,                     "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "perfil/upgrade/analisar"       => Array("url" => "cadastros/analisePerfis/index" ,                             "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "perfil/upgrade/aprovar"        => Array("url" => "cadastros/analisePerfis/aprovarSolicitacaoUpgrade" ,         "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "perfil/upgrade/negar"          => Array("url" => "cadastros/analisePerfis/negarSolicitacaoUpgrade" ,           "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "perfil/upgrade/listar"         => Array("url" => "cadastros/analisePerfis/listar" ,                            "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "cliente/email/reenviarconfirmacao"         => Array("url" => "cadastros/clientes/reenviarEmailConfirmacao" ,   "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "cliente/status/colocarEmEspera"            => Array("url" => "cadastros/clientes/colocarEmEspera" ,            "modulo" => "cadastros",                    "rotina" => "cliente/status/colocarEmEspera" ),
//        "cliente/mercado/status"                    => Array("url" => "cadastros/clientes/alterarStatusMercado" ,       "modulo" => "cadastros",                    "rotina" => "cliente/mercado/status" ),
//        "cliente/email/reenviarboasvindas"          => Array("url" => "cadastros/clientes/reenviarEmailBoasVindas" ,    "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        "cliente/email/analisar"          => Array("url" => "cadastros/clientes/analisarEmail" ,    "modulo" => "cadastros",                    "rotina" => "perfil/upgrade/analisar" ),
//        
//        "cliente/analise/notificar"                 => Array("url" => "cadastros/clientes/notificarAnaliseDocumentos" , "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "cliente/analise/iniciar"                   => Array("url" => "cadastros/clientes/iniciarAnalise" ,             "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "cliente/analise/finalizar"                 => Array("url" => "cadastros/clientes/finalizarAnalise" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        
//        
//        "clientes/comentarios/salvar"                   => Array("url" => "cadastros/clientes/salvarComentario" ,             "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        "clientes/comentarios/listar"                 => Array("url" => "cadastros/clientes/listarComentarios" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),
//        
//        "clientes/dados/listarMoedas"                 => Array("url" => "cadastros/clientes/htmlItemMinhasMoedas" ,           "modulo" => "cadastros",                    "rotina" => "clientes" ),

        // Cadastro Contas Bancarias
        "cadastrolistarcontasbancarias"         => Array("url" => "cadastros/contas/listar" ,                  "modulo" => "cadastros",                    "rotina" => "contas" ),
        "cadastronovacontabancaria"             => Array("url" => "cadastros/contas/cadastro" ,                "modulo" => "cadastros",                    "rotina" => "contas" ),
        "cadastrosalvarcontabancaria"           => Array("url" => "cadastros/contas/salvar" ,                  "modulo" => "cadastros",                    "rotina" => "contas" ),
        "cadastroexcluircontabancaria"          => Array("url" => "cadastros/contas/excluir" ,                 "modulo" => "cadastros",                    "rotina" => "contas" ),
        "contas/bancos/listar"                  => Array("url" => "cadastros/contas/getBancos" ,                 "modulo" => "cadastros",                    "rotina" => "contas" ),
        
        // Usuarios
//        "cadastro/usuarios/listar"              => Array("url" => "cadastros/usuarios/listar" ,                 "modulo" => "cadastros",                    "rotina" => "usuarios" ),
//        "cadastro/usuarios/cadastro"            => Array("url" => "cadastros/usuarios/cadastro" ,               "modulo" => "cadastros",                    "rotina" => "usuarios" ),
//        "cadastro/usuarios/ativo"               => Array("url" => "cadastros/usuarios/alterarStatusAtivo" ,     "modulo" => "cadastros",                    "rotina" => "usuarios" ),
//        "cadastro/usuarios/excluir"             => Array("url" => "cadastros/usuarios/excluir" ,                "modulo" => "cadastros",                    "rotina" => "usuarios" ),
//        "cadastro/usuarios/salvar"              => Array("url" => "cadastros/usuarios/salvar" ,                 "modulo" => "cadastros",                    "rotina" => "usuarios" ),
//        
        // Contas Bancárias da Empresa
//        "configuracoes/contas/listar"           => Array("url" => "configuracoes/contas/listar" ,               "modulo" => "configuracoes",                "rotina" => "contas" ),
//        "configuracoes/contas/cadastro"         => Array("url" => "configuracoes/contas/cadastro" ,             "modulo" => "configuracoes",                "rotina" => "contas" ),
//        "configuracoes/contas/salvar"           => Array("url" => "configuracoes/contas/salvar" ,               "modulo" => "configuracoes",                "rotina" => "contas" ),
//        "configuracoes/contas/excluir"          => Array("url" => "configuracoes/contas/excluir" ,              "modulo" => "configuracoes",                "rotina" => "contas" ),
//        
//        "configuracoes/comissoes"   => Array("url" => "configuracoes/comissoesClientes/index" ,          "modulo" => "configuracoes",                    "rotina" => "comissoes" ),
//        "configuracoes/comissoes/cadastro"   => Array("url" => "configuracoes/comissoesClientes/carregar" ,          "modulo" => "configuracoes",                    "rotina" => "comissoes" ),
//        "configuracoes/comissoes/salvar"     => Array("url" => "configuracoes/comissoesClientes/salvar" ,            "modulo" => "configuracoes",                    "rotina" => "comissoes" ),
//        
        // Painel de controle
//        "configuracoes/painel/salvar"           => Array("url" => "configuracoes/painelControle/salvar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        
//        
//        "telegram/bots/listar"           => Array("url" => "configuracoes/TelegramBots/listar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/bots/cadastro"           => Array("url" => "configuracoes/TelegramBots/cadastro" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/bots/salvar"           => Array("url" => "configuracoes/TelegramBots/salvar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/grupos/listar"           => Array("url" => "configuracoes/TelegramGrupos/listar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/grupos/cadastro"           => Array("url" => "configuracoes/TelegramGrupos/cadastro" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/grupos/salvar"           => Array("url" => "configuracoes/TelegramGrupos/salvar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        
//        "telegram/grupos/alterarStatus"    => Array("url" => "configuracoes/TelegramGrupos/alterarStatusAtivo" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/bots/alterarStatus"      => Array("url" => "configuracoes/TelegramBots/alterarStatusAtivo" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/mensagem/alterarStatus"      => Array("url" => "configuracoes/TelegramMensagens/alterarStatusAtivo" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        "telegram/mensagem/salvar"      => Array("url" => "configuracoes/TelegramMensagens/salvar" ,       "modulo" => "configuracoes",        "rotina" => "painelcontrole" ),
//        
        // Menu BTC
        "btc/contas/resumo"                     => Array("url" => "contas/btc/resumo" ,                     "modulo" => "btc",                    "rotina" => "contasbtc" ),
        "btc/conta/lancamentos"                 => Array("url" => "contas/btc/filtrar" ,                    "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/conta/filtrarsaldo"                => Array("url" => "contas/btc/filtrarsaldo" ,               "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/lancamento/cadastro"               => Array("url" => "contas/btc/cadastro" ,                   "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/lancamento/salvar"                 => Array("url" => "contas/btc/salvar" ,                     "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/lancamento/excluir"                => Array("url" => "contas/btc/excluir" ,                    "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/lancamentos/imprimir"              => Array("url" => "contas/btc/imprimir" ,                   "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        "btc/transferir"                        => Array("url" => "contas/btc/transferir" ,                 "modulo" => "btc",                    "rotina" => "transferenciabtc" ),
        "btc/transferencias/filtrar"            => Array("url" => "contas/btc/filtrarTransferencias" ,      "modulo" => "btc",                    "rotina" => "transferenciabtc" ),
        "btc/transferencias/imprimir"           => Array("url" => "contas/btc/imprimirtransferencias" ,     "modulo" => "btc",                    "rotina" => "transferenciabtc" ),
        "btc/autorizacao/token"                 => Array("url" => "contas/btc/token" ,                      "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
        
//        "btc/empresa/imprimir"                  => Array("url" => "contas/btcEmpresa/imprimir" ,            "modulo" => "btc",                    "rotina" => "ccempresabtc" ),
//        "btc/log"                               => Array("url" => "contas/logBtc/index" ,                   "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
//        "btc/log/filtrar"                       => Array("url" => "contas/logBtc/filtrar" ,                 "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
//        "btc/log/imprimir"                      => Array("url" => "contas/logBtc/imprimir" ,                "modulo" => "btc",                    "rotina" => "contacorrentebtc" ),
//        "btc/logempresa"                        => Array("url" => "contas/logBtcEmpresa/index" ,            "modulo" => "btc",                    "rotina" => "ccempresabtc" ),
//        "btc/logempresa/filtrar"                => Array("url" => "contas/logBtcEmpresa/filtrar" ,          "modulo" => "btc",                    "rotina" => "ccempresabtc" ),
//        "btc/logempresa/imprimir"               => Array("url" => "contas/logBtcEmpresa/imprimir" ,         "modulo" => "btc",                    "rotina" => "ccempresabtc" ),
//        "btc/painel/filtrar"                    => Array("url" => "contas/painelBtc/filtrar" ,              "modulo" => "btc",                    "rotina" => "painelbtc" ),
//        "btc/painel/confirmacao"                => Array("url" => "contas/painelBtc/confirmacao" ,          "modulo" => "btc",                    "rotina" => "painelbtc" ),
//        "btc/painel/confirmar"                  => Array("url" => "contas/painelBtc/confirmar" ,            "modulo" => "btc",                    "rotina" => "painelbtc" ),
//        "btc/painel/excluir"                    => Array("url" => "contas/painelBtc/excluir" ,              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "btc/painel/filtrarnaoautorizadas"      => Array("url" => "contas/painelBtc/filtrarTransacoesNaoAutorizadas" ,              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "btc/painel/autorizartransacao"         => Array("url" => "contas/painelBtc/autorizarTranzacao" ,                           "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "btc/painel/negartransacao"             => Array("url" => "contas/painelBtc/negarTranzacao" ,                               "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "painel/ico/saques"                     => Array("url" => "contas/painelBtc/listarSaquesIco" ,                              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "painel/ico/carregarSaque"              => Array("url" => "contas/painelBtc/carregarSaquesIco" ,                              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "painel/ico/autorizarSaque"             => Array("url" => "contas/painelBtc/autorizarSaquesIco" ,                              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        "painel/ico/negarSaque"                 => Array("url" => "contas/painelBtc/negarSaquesIco" ,                              "modulo" => "excluir",                "rotina" => "painelbtc" ),
//        
//        "contas/empresa"                        => Array("url" => "contasempresa/contaCorrente/index" ,                                     "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/filtrarccbrl"           => Array("url" => "contasempresa/contaCorrente/filtrarBrl" ,                                "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/filtrarcccurrency"      => Array("url" => "contasempresa/contaCorrente/filtrarCurrency" ,                           "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/transferencia"          => Array("url" => "contasempresa/transferenciaEmpresa/index" ,                              "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/saldo"                  => Array("url" => "contasempresa/transferenciaEmpresa/calcularSaldoEmpresa" ,               "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/transferirbrl"          => Array("url" => "contasempresa/transferenciaEmpresa/transferirBrl" ,                      "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/transferircurrency"     => Array("url" => "contasempresa/transferenciaEmpresa/transferirCurrency" ,                 "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/filtrarbrl"             => Array("url" => "contasempresa/transferenciaEmpresa/filtrarTransferenciasBrl" ,           "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/filtrarcurrency"        => Array("url" => "contasempresa/transferenciaEmpresa/filtrarTransferenciasCurrency" ,      "modulo" => "contasempresa",          "rotina" => "contas/empresa/transferencia" ),
//        "contas/empresa/cadastrocurrency"       => Array("url" => "contasempresa/contaCorrente/cadastroCurrency" ,                          "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/salvarcurrency"         => Array("url" => "contasempresa/contaCorrente/salvarCurrency" ,                            "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/cadastrobrl"            => Array("url" => "contasempresa/contaCorrente/cadastroBrl" ,                               "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/salvarbrl"              => Array("url" => "contasempresa/contaCorrente/salvarBrl" ,                                 "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/excluirbrl"             => Array("url" => "contasempresa/contaCorrente/excluirBrl" ,                                "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        "contas/empresa/excluircurrency"        => Array("url" => "contasempresa/contaCorrente/excluirCurrency" ,                           "modulo" => "contasempresa",          "rotina" => "contas/empresa" ),
//        
//        "contas/resumo"                         => Array("url" => "contasempresa/resumoContas/index" ,                                      "modulo" => "contasempresa",          "rotina" => "contas/resumo" ),
//        "contas/resumo/listarContas"            => Array("url" => "contasempresa/resumoContas/listarContas" ,                               "modulo" => "contasempresa",          "rotina" => "contas/resumo" ),
//        "contas/resumo/cobrar"                  => Array("url" => "contasempresa/resumoContas/cobrar" ,                                     "modulo" => "contasempresa",          "rotina" => "contas/resumo" ),
//        "clientes/resumo"                       => Array("url" => "contasempresa/resumoClientes/index" ,                                      "modulo" => "contasempresa",          "rotina" => "clientes/resumo" ),
//        "clientes/resumo/listarContas"          => Array("url" => "contasempresa/resumoClientes/listarContas" ,                               "modulo" => "contasempresa",          "rotina" => "clientes/resumo" ),
//        
//        
        
        "clientes/states/list"                  => Array("url" => "cadastros/clientes/getEstadosByPais",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        
        // Menu Reais
        "reais/contas/resumo"                   => Array("url" => "contas/reais/resumo" ,                   "modulo" => "reais",                    "rotina" => "contasreais" ),
        "reais/conta/lancamentos"               => Array("url" => "contas/reais/filtrar" ,                  "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/conta/filtrarsaldo"              => Array("url" => "contas/reais/filtrarSaldo" ,             "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/lancamento/cadastro"             => Array("url" => "contas/reais/cadastro" ,                 "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/lancamento/salvar"               => Array("url" => "contas/reais/salvar" ,                   "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/lancamento/excluir"              => Array("url" => "contas/reais/excluir" ,                  "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/lancamentos/imprimir"            => Array("url" => "contas/reais/imprimir" ,                 "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
        "reais/transferir"                      => Array("url" => "contas/reais/transferir" ,               "modulo" => "reais",                    "rotina" => "transferenciareais" ),
        "empresa/transferir"                    => Array("url" => "contas/transferencias/transferirParaEmpresa" ,"modulo" => "reais",                    "rotina" => "transferenciareais" ),
        "reais/transferencias/filtrar"          => Array("url" => "contas/reais/filtrarTransferencias" ,    "modulo" => "reais",                    "rotina" => "transferenciareais" ),
        "reais/transferencias/imprimir"         => Array("url" => "contas/reais/imprimirtransferencias" ,   "modulo" => "reais",                    "rotina" => "transferenciareais" ),
        "reais/autorizacao/token"               => Array("url" => "contas/reais/token" ,                    "modulo" => "reais",                    "rotina" => "transferenciareais" ),
//        
//        "empresa/autorizacao/token"             => Array("url" => "contas/transferencias/tokenEmpresa" ,     "modulo" => "reais",                    "rotina" => "transferenciareais" ),
//        "reais/log"                             => Array("url" => "contas/logReais/index" ,                 "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
//        "reais/log/filtrar"                     => Array("url" => "contas/logReais/filtrar" ,               "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
//        "reais/log/imprimir"                    => Array("url" => "contas/logReais/imprimir" ,              "modulo" => "reais",                    "rotina" => "contacorrentereais" ),
//        "reais/logempresa"                      => Array("url" => "contas/logReaisEmpresa/index" ,          "modulo" => "reais",                    "rotina" => "ccempresareais" ),
//        "reais/logempresa/filtrar"              => Array("url" => "contas/logReaisEmpresa/filtrar" ,        "modulo" => "reais",                    "rotina" => "ccempresareais" ),
//        "reais/logempresa/imprimir"             => Array("url" => "contas/logReaisEmpresa/imprimir" ,       "modulo" => "reais",                    "rotina" => "ccempresareais" ),
//        
        
        //"reais/empresa/imprimir"                => Array("url" => "contas/reaisEmpresa/imprimir" ,          "modulo" => "reais",                    "rotina" => "ccempresareais" ),
        "reais/depositos/solicitar"             => Array("url" => "contas/depositos/solicitar" ,            "modulo" => "reais",                    "rotina" => "depositos" ),
        "reais/depositos/listar"                => Array("url" => "contas/depositos/listar" ,               "modulo" => "reais",                    "rotina" => "depositos" ),
        "reais/depositos/dadosconta"            => Array("url" => "contas/depositos/getDadosContaBancaria", "modulo" => "reais",                    "rotina" => "depositos" ),
        "reais/depositos/salvar"                => Array("url" => "contas/depositos/salvar" ,               "modulo" => "reais",                    "rotina" => "depositos" ),
        "reais/depositos/analisar"              => Array("url" => "contas/depositos/aprovar" ,              "modulo" => "reais",                    "rotina" => "depositos" ),
        //"reais/depositos/aprovar"               => Array("url" => "contas/depositos/aprovarDeposito" ,      "modulo" => "reais",                    "rotina" => "depositos" ),
        //"reais/depositos/cancelar"              => Array("url" => "contas/depositos/cancelar" ,             "modulo" => "reais",                    "rotina" => "depositos" ),
        //"reais/depositos/imprimir"              => Array("url" => "contas/depositos/imprimir" ,             "modulo" => "reais",                    "rotina" => "depositos" ),
        "reais/saques/listar"                   => Array("url" => "contas/saques/listar" ,                  "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/listarReais"              => Array("url" => "contas/saques/listarReais" ,             "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/saldos"                   => Array("url" => "contas/saques/saldoCliente" ,             "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/dadosconta"               => Array("url" => "contas/saques/getDadosContaBancaria",    "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/extratoCliente"           => Array("url" => "contas/saques/extratoCliente",           "modulo" => "reais",                    "rotina" => "saques" ),
        "deposito/boleto/gerar"                 => Array("url" => "contas/gerenciaNet/gerarBoleto" ,        "modulo" => "reais",                    "rotina" => "depositos" ),
        "deposito/boletolara/gerar"             => Array("url" => "contas/laraBoleto/gerarBoleto" ,        "modulo" => "reais",                    "rotina" => "depositos" ),
        "deposito/boleto/token"                 => Array("url" => "contas/gerenciaNet/token" ,              "modulo" => "reais",                    "rotina" => "depositos" ),

        "reais/saques/salvar"                   => Array("url" => "contas/saques/salvar" ,                  "modulo" => "reais",                    "rotina" => "saques" ),
        //"reais/saques/aprovar"                  => Array("url" => "contas/saques/aprovarSaque" ,            "modulo" => "reais",                    "rotina" => "saques" ),
        //"reais/saques/analisar"                 => Array("url" => "contas/saques/aprovar" ,                 "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/cancelar"                 => Array("url" => "contas/saques/cancelar" ,                "modulo" => "reais",                    "rotina" => "saques" ),
        //"reais/saques/imprimir"                 => Array("url" => "contas/saques/imprimir" ,                "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/solicitar"                => Array("url" => "contas/saques/solicitar" ,               "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/showDados"                => Array("url" => "contas/saques/showDados" ,               "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/saques/ultimosSaques"            => Array("url" => "contas/saques/ultimosSaques" ,           "modulo" => "reais",                    "rotina" => "saques" ),
        "reais/clientes/findbywallet"           => Array("url" => "contas/reais/findByWallet" ,             "modulo" => "reais",                    "rotina" => "transferencias" ),
        "reais/clientes/findbyemail"           => Array("url" => "contas/saques/findByEmail" ,              "modulo" => "reais",                    "rotina" => "transferencias" ),
        "reais/saques/getTaxas"                => Array("url" => "contas/saques/getTaxaTokens" ,            "modulo" => "reais",                    "rotina" => "saques" ),

        // Cartoes
//        "administrativo/cartoes/filtrar"        => Array("url" => "cartoes/administrativo/filtrar" ,                       "modulo" => "cartoes",                 "rotina" => "administrativo/cartoes" ),
//        "administrativo/cartoes/cadastro"       => Array("url" => "cartoes/administrativo/cadastro" ,                      "modulo" => "cartoes",                 "rotina" => "administrativo/cartoes" ),
//        "administrativo/cartoes/salvar"         => Array("url" => "cartoes/administrativo/salvar" ,                        "modulo" => "cartoes",                 "rotina" => "administrativo/cartoes" ),
//        "administrativo/cartoes/excluir"        => Array("url" => "cartoes/administrativo/excluir" ,                       "modulo" => "cartoes",                 "rotina" => "administrativo/cartoes" ),
//        "administrativo/cartoes/ativar"         => Array("url" => "cartoes/administrativo/ativar" ,                        "modulo" => "cartoes",                 "rotina" => "administrativo/cartoes" ),
//        
//        "cartoes/listar"                        => Array("url" => "invoices/cards/listar" ,                                 "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        "cartoes/alterarStatus"                 => Array("url" => "invoices/cards/alterarStatusCartao" ,                    "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        "cartoes/cancelar"                      => Array("url" => "invoices/cards/cancelar" ,                               "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        "cartoes/saldos"                        => Array("url" => "invoices/cards/saldos" ,                                 "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        "cartoes/extratos"                      => Array("url" => "invoices/cards/extratos" ,                               "modulo" => "cartoes",                 "rotina" => "extrato" ),
//        "cartoes/consultar/saldo"                => Array("url" => "invoices/cards/consultaSaldo" ,                          "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        "cartoes/visa/recarregar"               => Array("url" => "invoices/cards/executaRecargaVisa" ,                     "modulo" => "cartoes",                 "rotina" => "cartoes" ),
//        
//        "recharges/filtrar"                     => Array("url" => "invoices/recharges/filtrar" ,                            "modulo" => "cartoes",                 "rotina" => "recharges" ),
//        "recharges/finalizar"                   => Array("url" => "invoices/recharges/finalizar" ,                          "modulo" => "cartoes",                 "rotina" => "recharges" ),
//        "recharges/pagamento/dados"             => Array("url" => "invoices/recharges/getInvoicePaymentData" ,              "modulo" => "cartoes",                 "rotina" => "recharges" ),
//        "recharges/cartao/dados"                => Array("url" => "recharge/cards/getCartao" ,                              "modulo" => "cartoes",                 "rotina" => "recharges" ),
//        "recharges/cartao/validar"              => Array("url" => "recharge/cards/validate" ,                               "modulo" => "cartoes",                 "rotina" => "recharges" ),
//        "remret/listar"                         => Array("url" => "invoices/remessa/listar" ,                               "modulo" => "cartoes",                 "rotina" => "remret" ),
//        "remret/download"                       => Array("url" => "invoices/remessa/download" ,                             "modulo" => "cartoes",                 "rotina" => "remret" ),
//        "remret/retorno/upload"                 => Array("url" => "invoices/retorno/upload" ,                               "modulo" => "cartoes",                 "rotina" => "remret" ),
//        
//        
//        // Monitoramento
//        "monitoramento/refresh"                 => Array("url" => "monitoramento/painel/getDados" ,                         "modulo" => "monitoramento",           "rotina" => "monitoramento" ),
//        
//        // Comprovantes de Invoice
//        "comprovantes/invoice"                 => Array("url" => "pdfs/PDFComprovanteInvoice/gerar" ,                       "modulo" => "pdfs",                    "rotina" => "comprovantes" ),
//             
//
//        "servicos/categorias/listar"                        => Array("url" => "servicos/categoriasServicos/listar" ,                       "modulo" => "servicos",                    "rotina" => "remessas" ),
//        "servicos/categorias/listarOptions"                 => Array("url" => "servicos/categoriasServicos/getHtmlOptions" ,               "modulo" => "servicos",                    "rotina" => "remessas" ),
//        "servicos/categorias/cadastro"                      => Array("url" => "servicos/categoriasServicos/cadastro" ,                     "modulo" => "servicos",                    "rotina" => "remessas" ),
//        "servicos/categorias/salvar"                        => Array("url" => "servicos/categoriasServicos/salvar" ,                       "modulo" => "servicos",                    "rotina" => "remessas" ),
//        "servicos/categorias/excluir"                       => Array("url" => "servicos/categoriasServicos/excluir" ,                      "modulo" => "servicos",                    "rotina" => "remessas" ),
//        "servicos/categorias/alterarStatus"                 => Array("url" => "servicos/categoriasServicos/alterarStatusAtivo" ,           "modulo" => "servicos",                    "rotina" => "remessas" ),
//        
        
        // PERFIL
        "meusdados/chaves/gerar"                           => Array("url" => "perfil/meusDados/gerarKey" ,                                         "modulo" => "servicos",                    "rotina" => "pontospdvs" ),
        "meusdados/chaves/preparar"                        => Array("url" => "perfil/meusDados/prepararGerarKey" ,                                 "modulo" => "servicos",                    "rotina" => "pontospdvs" ),
        "meusdados/conta/status"                            => Array("url" => "perfil/conta/status" ,                                       "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/salvar"                                  => Array("url" => "perfil/meusDados/salvar" ,                                   "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/senha/atualizar"                         => Array("url" => "perfil/meusDados/alterarSenha" ,                             "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/foto/atualizar"                          => Array("url" => "perfil/meusDados/atualizarImagem" ,                          "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/senha/forca"                             => Array("url" => "perfil/meusDados/forcaSenha" ,                               "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/alertas/atualizar"                       => Array("url" => "perfil/meusDados/alterarAlertas" ,                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/perfil/upgrade"                          => Array("url" => "perfil/meusDados/solicitarAlteracaoPerfil" ,                 "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/upload/documento"                        => Array("url" => "perfil/meusDados/uploadDocumentos" ,                         "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/seguranca/atualizar"                     => Array("url" => "perfil/meusDados/updateSeguranca" ,                          "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/autorizacao/token"                       => Array("url" => "perfil/meusDados/token" ,                                    "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/googleauth/getsecret"                   => Array("url" => "perfil/meusDados/getGoogleAuthSecret",                      "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/country/brand"                             => Array("url" => "perfil/meusDados/getCountryBrand",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/states/list"                             => Array("url" => "perfil/meusDados/getEstadosByPais",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        //"meusdados/notificacao/get"                         => Array("url" => "perfil/meusDados/getNotificacao",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        //"meusdados/notificacao/titulo"                         => Array("url" => "perfil/meusDados/getTitulo",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        //"meusdados/notificacao/salvarNotificacao"            => Array("url" => "perfil/meusDados/salvarNotificacao",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        //"meusdados/alterarModo"                             => Array("url" => "perfil/meusDados/alterarModo",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/notafiscal/notafiscalcliente"            => Array("url" => "perfil/meusDados/notaFiscalCliente",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/navegador/listaNavegadores"              => Array("url" => "perfil/meusDados/listaNavegadores",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/documentos/listaDocumentos"               => Array("url" => "perfil/meusDados/listaDocumentos" ,                           "modulo" => "perfil",                      "rotina" => "perfil" ),
        "meusdados/navegador/statusNavegador"                => Array("url" => "perfil/meusDados/statusNavegador",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/notafiscal/listarCliente"                => Array("url" => "perfil/meusDados/getNotaFiscalOperacao",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/notafiscal/salvar"                       => Array("url" => "perfil/meusDados/notaFiscalOperacao",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/menu"                                      => Array("url" => "perfil/meusDados/menuDinamico",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meusdados/limites"                                   => Array("url" => "perfil/meusDados/meusLimites",                           "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meudados/cnpj/salvar"                              => Array("url" => "perfil/meusDados/salvarCnpj",                               "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meudados/cnpj/mostrar"                              => Array("url" => "perfil/meusDados/mostrarCnpj",                               "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meudados/kyc/start"                              => Array("url" => "perfil/meusDados/kycStart",                               "modulo" => "perfil",                       "rotina" => "meusdados" ),
        "meudados/kyc/smsresend"                            => Array("url" => "perfil/meusDados/kycSmsResend",                               "modulo" => "perfil",                       "rotina" => "meusdados" ),

        // DASHBOARD
        "dashboard/referencias/vendedor"                    => Array("url" => "principal/dashboard/carregarValorMensalPorVendedor" ,        "modulo" => "principal",                       "rotina" => "dashboard" ),
        "dashboard/referencias/adm"                         => Array("url" => "principal/dashboard/carregarValorMensalVendedores" ,         "modulo" => "principal",                       "rotina" => "meusdados" ),
        "dashboard/referencias/extrato"                     => Array("url" => "principal/dashboard/extrato" ,                               "modulo" => "principal",                       "rotina" => "meusdados" ),
        "dashboard/extrato/carregar"                        => Array("url" => "principal/dashboard/carregarExtrato" ,                       "modulo" => "principal",                       "rotina" => "meusdados" ),
        "dashboard/referencias/pagar"                       => Array("url" => "principal/dashboard/pagarComissao" ,                         "modulo" => "principal",                       "rotina" => "meusdados" ),
        "dashboard/referencias/calculartotal"               => Array("url" => "principal/dashboard/calcularValorTotalPago" ,                "modulo" => "principal",                       "rotina" => "meusdados" ),
        "indicacoes/calcularresgate"                        => Array("url" => "principal/indicacoes/getValorResgate" ,                      "modulo" => "principal",                       "rotina" => "indicacoes" ),
//        
//        "indicacoes/resgate"                                => Array("url" => "principal/indicacoes/resgatar" ,                             "modulo" => "principal",                       "rotina" => "indicacoes" ),
//        "indicacoes/transferir"                             => Array("url" => "principal/indicacoes/transferir" ,                           "modulo" => "principal",                       "rotina" => "indicacoes" ),
//        "indicacoes/getclientebyemail"                      => Array("url" => "principal/indicacoes/getClienteByEmail" ,                    "modulo" => "principal",                       "rotina" => "indicacoes" ),
//        "indicacoes/resgatar"                               => Array("url" => "principal/indicacoes/resgatarSaldo" ,                        "modulo" => "principal",                       "rotina" => "indicacoes" ),

     
        // Notificacoes
//        "notificacoes/cliente/buscar"                       => Array("url" => "notificacoes/notificacoes/buscarCliente" ,                   "modulo" => "notificacoes",                     "rotina" => "notificacoes/cliente/buscar" ),
//        "notificacoes/cadastro"                             => Array("url" => "notificacoes/notificacoes/cadastro" ,                        "modulo" => "notificacoes",                     "rotina" => "notificacoes/cadastro" ),
//        "notificacoes/salvar"                               => Array("url" => "notificacoes/notificacoes/salvar" ,                          "modulo" => "notificacoes",                     "rotina" => "notificacoes/cadastro" ),
//        "notificacoes/listar/naoexibidas"                   => Array("url" => "notificacoes/notificacoes/getNotificacoesNaoExibidas" ,      "modulo" => "notificacoes",                     "rotina" => "notificacoes/cadastro" ),
//        "notificacoes/shown"                                => Array("url" => "notificacoes/notificacoes/shown" ,                           "modulo" => "notificacoes",                     "rotina" => "notificacoes/cadastro" ),
//        "notificacoes/alertas"                              => Array("url" => "notificacoes/notificacoes/alertas" ,                         "modulo" => "notificacoes",                     "rotina" => "notificacoes/alertas" ),
//        "notificacoes/all"                                  => Array("url" => "notificacoes/notificacoes/all" ,                             "modulo" => "notificacoes",                     "rotina" => "notificacoes/all" ),
//        "notificacoes/all/filtrar"                          => Array("url" => "notificacoes/notificacoes/filtrar" ,                         "modulo" => "notificacoes",                     "rotina" => "notificacoes/all/filtrar" ),
//        "notificacoes/read"                                 => Array("url" => "notificacoes/notificacoes/marcarComoLida" ,                  "modulo" => "notificacoes",                     "rotina" => "notificacoes/read" ),
//     
        //Notificação Moeda
        "notificacaomoeda"                                  => Array("url" => "configuracoes/notificacaoMoeda/index" ,                               "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoedaOnlyView"                          => Array("url" => "configuracoes/notificacaoMoedaOnlyView/index" ,                       "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoeda/salvar"                           => Array("url" => "configuracoes/notificacaoMoeda/salvar" ,                              "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoeda/listar"                           => Array("url" => "configuracoes/notificacaoMoeda/listar" ,                              "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoedaOnlyView/listar"                   => Array("url" => "configuracoes/notificacaoMoedaOnlyView/listar" ,                      "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoeda/status/publicado"                 => Array("url" => "configuracoes/notificacaoMoeda/alterarStatus" ,                       "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),
        "notificacaomoeda/editar"                           => Array("url" => "configuracoes/notificacaoMoeda/editarNotificacao" ,                   "modulo" => "configuracoes",                     "rotina" => "notificacaomoeda" ),

        "extract"                                           => Array("url" => "extrato/extrato/index" ,                                              "modulo" => "extrato",                            "rotina" => "extrato" ),
        "extrato/listar"                                    => Array("url" => "extrato/extrato/listarExtrato" ,                                      "modulo" => "extrato",                            "rotina" => "extrato" ),
        
        "referal"                                        => Array("url" => "referencia/referencia/index" ,                                        "modulo" => "referencia",                            "rotina" => "extrato" ),
        "referencia/listar"                                 => Array("url" => "referencia/referencia/listarReferencia" ,                             "modulo" => "referencia",                            "rotina" => "extrato" ),

        
        // CONtas
        "contas/empresa/btc"                                => Array("url" => "contas/btcEmpresa/index" ,                                   "modulo" => "contasempresa",                     "rotina" => "contas/empresa/btc" ),
        "contas/empresa/reais"                              => Array("url" => "contas/reaisEmpresa/index" ,                                 "modulo" => "contasempresa",                     "rotina" => "contas/empresa/reais" ),

        /// TRADE 
        "book/layout/salvar"                                => Array("url" => "trade/BookNew/salvarLayout" ,                                 "modulo" => "trade",                    "rotina" => "book" ),
        "book/paridade"                                     => Array("url" => "trade/BookNew/dadosParidade" ,                                "modulo" => "trade",                    "rotina" => "book" ),
        "book/saldos"                                       => Array("url" => "trade/BookNew/dadosSaldo" ,                                   "modulo" => "trade",                    "rotina" => "book" ),
        "bookCointrade"                                     => Array("url" => "trade/BookNew/index" ,                                        "modulo" => "trade",                    "rotina" => "book" ),
        "book"                                              => Array("url" => "trade/book/index" ,                                           "modulo" => "trade",                    "rotina" => "book" ),
        "book/balance"                                      => Array("url" => "trade/book/getBalance" ,                                      "modulo" => "trade",                    "rotina" => "book" ),
        "book/listarbook"                                   => Array("url" => "trade/book/getBook" ,                                         "modulo" => "trade",                    "rotina" => "book" ),
        "book/trades/list"                                  => Array("url" => "trade/book/getListaTrade" ,                                   "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordem/comprar"                                => Array("url" => "trade/book/salvarOrdemCompra" ,                               "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordem/comprarnew"                             => Array("url" => "trade/bookNew/salvarOrdemCompra" ,                               "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordem/vender"                                 => Array("url" => "trade/book/salvarOrdemVenda" ,                                "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordem/vendernew"                              => Array("url" => "trade/bookNew/salvarOrdemVenda" ,                                "modulo" => "trade",                    "rotina" => "book" ),
        "book/compra/listar"                                => Array("url" => "trade/book/listarOrdensCompra" ,                              "modulo" => "trade",                    "rotina" => "book" ),
        "book/venda/listar"                                 => Array("url" => "trade/book/listarOrdensVenda" ,                               "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/listar"                                => Array("url" => "trade/book/listarMinhasOrdens" ,                              "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/listarnew"                             => Array("url" => "trade/bookNew/listarMinhasOrdens" ,                              "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/historico"                             => Array("url" => "trade/book/listarHistoricoOrdens" ,                           "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/cancelar"                              => Array("url" => "trade/book/cancelar" ,                                        "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/cancelarnew"                           => Array("url" => "trade/bookNew/cancelar" ,                                        "modulo" => "trade",                    "rotina" => "book" ),
        "book/ordens/executadas"                            => Array("url" => "trade/bookNew/listarOrdensExecutadas" ,                        "modulo" => "trade",                    "rotina" => "book" ),
        "exchange"                                          => Array("url" => "trade/compraVendaDireta/index" ,                              "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/balance"                                   => Array("url" => "trade/compraVendaDireta/getBalances" ,                         "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/paridades"                                 => Array("url" => "trade/compraVendaDireta/getParidades" ,                         "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/preco"                                     => Array("url" => "trade/compraVendaDireta/consultarPreco" ,                     "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/comprar"                                   => Array("url" => "trade/compraVendaDireta/salvarCompra" ,                       "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/vender"                                    => Array("url" => "trade/compraVendaDireta/salvarVenda" ,                        "modulo" => "trade",                    "rotina" => "mercado" ),
        "book/extrato"                                      => Array("url" => "trade/extrato/index" ,                                        "modulo" => "trade",                    "rotina" => "book/extrato" ),
        "mercado/extrato/filtrar"                           => Array("url" => "trade/compraVendaDireta/listarMinhasOrdens" ,                 "modulo" => "trade",                    "rotina" => "mercado" ),
        "mercado/listarBook"                                => Array("url" => "trade/compraVendaDireta/listarBook" ,                         "modulo" => "trade",                    "rotina" => "mercado" ),
        "book/extrato/filtrar"                              => Array("url" => "trade/extrato/listarMinhasOrdens" ,                           "modulo" => "trade",                    "rotina" => "book/extrato" ),

        "book/negociacoes"                                  => Array("url" => "trade/negociacoes/index" ,                                    "modulo" => "trade",                    "rotina" => "book/negociacoes" ),
        "book/negociacoes/filtrarcompras"                   => Array("url" => "trade/negociacoes/listarOrdensCompra" ,                       "modulo" => "trade",                    "rotina" => "book/negociacoes" ),
        "book/negociacoes/filtrarvendas"                    => Array("url" => "trade/negociacoes/listarOrdensVenda" ,                        "modulo" => "trade",                    "rotina" => "book/negociacoes" ),
        "book/negociacoes/filtrartrade"                     => Array("url" => "trade/negociacoes/listarTrade" ,                              "modulo" => "trade",                    "rotina" => "book/negociacoes" ),
        //"book/negociacoes/dados"                            => Array("url" => "trade/negociacoes/getDadosNegociacoes" ,                      "modulo" => "trade",                    "rotina" => "book/negociacoes" ),
        
        "book/paridades/listar"                             => Array("url" => "trade/book/getParidadesByMoeda" ,                      "modulo" => "trade",                    "rotina" => "book" ),
        "book/paridades/listarnew"                          => Array("url" => "trade/bookNew/getParidadesByMoeda" ,                      "modulo" => "trade",                    "rotina" => "book" ),
        "book/moedas/saldos"                                => Array("url" => "trade/book/getTableBalances" ,                         "modulo" => "trade",                    "rotina" => "book" ),
        
        "bookweb"                                              => Array("url" => "trade/bookWeb/index" ,                                           "modulo" => "trade",                    "rotina" => "book" ),

//        "book/ico"                                          => Array("url" => "trade/CompraIco/index" ,                                      "modulo" => "trade",                    "rotina" => "book/ico" ),
//        "book/ico/comprar"                                  => Array("url" => "trade/CompraIco/comprar" ,                                    "modulo" => "trade",                    "rotina" => "book/ico" ),
//        //"book/ordens/listar"                                => Array("url" => "trade/book/getParidadesByMoeda" ,                             "modulo" => "trade",                    "rotina" => "book" ),
//        "book/moedas/saldo"                                => Array("url" => "trade/book/getTableBalances" ,                                 "modulo" => "trade",                    "rotina" => "book" ),
//        "book/favorito/add"                                => Array("url" => "trade/book/adicionarFavorito" ,                                 "modulo" => "trade",                    "rotina" => "book" ),
//        "book/favorito/remove"                                => Array("url" => "trade/book/removerFavorito" ,                                 "modulo" => "trade",                    "rotina" => "book" ),

//        // Estorno
//        "estornos"                                           => Array("url" => "contas/estorno/index" ,                                       "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/analisar"                                  => Array("url" => "contas/estorno/analisar" ,                                    "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/listar"                                    => Array("url" => "contas/estorno/listar" ,                                      "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/rejeitar"                                  => Array("url" => "contas/estorno/rejeitar" ,                                    "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/cancelar"                                  => Array("url" => "contas/estorno/cancelar" ,                                    "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/aprovar"                                   => Array("url" => "contas/estorno/aprovar" ,                                     "modulo" => "estorno",                "rotina" => "estorno" ),
//        "estornos/finalizar"                                 => Array("url" => "contas/estorno/finalizar" ,                                   "modulo" => "estorno",                "rotina" => "estorno" ),
//        "deposito/estorno/iniciar"                           => Array("url" => "contas/estorno/iniciar" ,                                     "modulo" => "estorno",                "rotina" => "estorno" ),
//        "deposito/estorno/contas"                            => Array("url" => "contas/estorno/getListaContas" ,                              "modulo" => "estorno",                "rotina" => "estorno" ),
//        "deposito/estorno/salvarDadosBancarios"              => Array("url" => "contas/estorno/salvarDadosBancarios" ,                        "modulo" => "estorno",                "rotina" => "estorno" ),
//        
//        
//        "convites"                                           => Array("url" => "convidados/convidados/index" ,                                  "modulo" => "convites",               "rotina" => "convites" ),
//        "convites/enviar"                                    => Array("url" => "convidados/convidados/enviar" ,                                 "modulo" => "convites",               "rotina" => "convites" ),
//        "convites/convidados/listar"                         => Array("url" => "convidados/convidados/listar" ,                                 "modulo" => "convites",               "rotina" => "convites" ),
//        
        /*
        "p2p"                                                => Array("url" => "contasempresa/p2p/index" ,                                      "modulo" => "contasempresa",               "rotina" => "p2p" ),
        "p2p/listar"                                         => Array("url" => "contasempresa/p2p/listar" ,                                     "modulo" => "contasempresa",               "rotina" => "p2p" ),
        "p2p/cadastro"                                       => Array("url" => "contasempresa/p2p/cadastro" ,                                   "modulo" => "contasempresa",               "rotina" => "p2p" ),
        "p2p/salvar"                                         => Array("url" => "contasempresa/p2p/salvar" ,                                     "modulo" => "contasempresa",               "rotina" => "p2p" ),
        "p2p/excluir"                                        => Array("url" => "contasempresa/p2p/excluir" ,                                    "modulo" => "contasempresa",               "rotina" => "p2p" ),
        "p2p/status"                                         => Array("url" => "contasempresa/p2p/atualizarStatus" ,                            "modulo" => "contasempresa",               "rotina" => "p2p" ),
*/
//        "dashboard/historico/remotewallets"                  => Array("url" => "principal/dashboard/listarUltimasTransacoesCarteirasRemotas" ,  "modulo" => "contasempresa",               "rotina" => "p2p" ),
//        "dashboard/historico/remoteinvoices"                 => Array("url" => "principal/dashboard/listarUltimasTransacoesInvoices" ,          "modulo" => "contasempresa",               "rotina" => "p2p" ),
//        
        "dispositivos/listar"                                => Array("url" => "perfil/dispositivosMobile/listar" ,                             "modulo" => "perfil",                      "rotina" => "perfil" ),
        "dispositivos/alterarStatus"                         => Array("url" => "perfil/dispositivosMobile/alterarStatusAtivo" ,                 "modulo" => "perfil",                      "rotina" => "perfil" ),
        "dispositivos/parear"                                => Array("url" => "perfil/dispositivosMobile/parear" ,                             "modulo" => "perfil",                      "rotina" => "perfil" ),
        "dispositivos/pareamento"                            => Array("url" => "perfil/dispositivosMobile/ativarPareamento" ,                   "modulo" => "perfil",                      "rotina" => "perfil" ),

        
        
        "afiliados/listar"                                   => Array("url" => "perfil/afiliados/listar" ,                                      "modulo" => "perfil",                      "rotina" => "perfil" ),
        
        
//        // Pré-venda
//        "prevendas"                                          => Array("url" => "trade/preVendas/index" ,                                        "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/cadastro"                                 => Array("url" => "trade/preVendas/cadastro" ,                                     "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/salvar"                                   => Array("url" => "trade/preVendas/salvar" ,                                       "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/listar"                                   => Array("url" => "trade/preVendas/listar" ,                                       "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/alterarstatus"                            => Array("url" => "trade/preVendas/alterarStatusAtivo" ,                           "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/fase/cadastro"                            => Array("url" => "trade/preVendas/cadastroFaseIco" ,                              "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/fase/salvar"                              => Array("url" => "trade/preVendas/salvarFaseIco" ,                                "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/comprar"                                  => Array("url" => "trade/preVendas/comprar" ,                                "modulo" => "trade",                       "rotina" => "prevenda" ),
//        "prevendas/getSaldoDisponivel"                       => Array("url" => "trade/preVendas/getSaldoDisponivel" ,                                "modulo" => "trade",                       "rotina" => "prevenda" ),
//        
        // API Signup
        "api/signup"                                        => Array("url" => "acesso/cadastro/cadastroCliente" ,                                  "modulo" => "acesso",                    "rotina" => "signup" ),
        //"api/signup"                                        => Array("url" => "api/signup/index" ,                                          "modulo" => "api",                    "rotina" => "signup" ),
        
        
        
        
        
        // WS 
        //"ws/nf/notification"                                => Array("url" => "ws/notaFiscal/notification" ,                             "modulo" => "principal",                 "rotina" => "dashboard" ),
        "ws/gerencianet/notification"                       => Array("url" => "ws/gerenciaNet/notification" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
        "ws/gerencianet/cancelar"                           => Array("url" => "ws/gerenciaNet/cancelarBoletos" ,                         "modulo" => "principal",                 "rotina" => "dashboard" ),
        "ws/gerencianet/consultar"                          => Array("url" => "ws/gerenciaNet/gerenciaNetConsultar" ,                    "modulo" => "principal",                 "rotina" => "dashboard" ),
        //"ws/convites"                                       => Array("url" => "ws/convites/index" ,                                      "modulo" => "principal",                 "rotina" => "dashboard" ),
        
        /*
        // Teste/*
        "testEmailContaAceita"                      => Array("url" => "testes/testeEmailContaAceita/testEmailContaAceita" ,         "modulo" => "testes",                 "rotina" => "email" ),
        "testEmailUpgradePerfilNegado"              => Array("url" => "testes/TesteNf/upgradePerfilNegado" ,                        "modulo" => "testes",                 "rotina" => "email" ),
        "testEmailContaRejeitada"                   => Array("url" => "testes/testeEmailContaAceita/testEmailContaRejeitada" ,      "modulo" => "testes",                 "rotina" => "email" ),
        "currency/dados"                            => Array("url" => "principal/dashboard/dadosMoeda" ,                            "modulo" => "testes",                 "rotina" => "dashboard" ),
        "currency/dados/newc"                       => Array("url" => "principal/dashboard/criarCarteiraNewc" ,                     "modulo" => "testes",                 "rotina" => "dashboard" ),
        "emitirnfs"                                 => Array("url" => "testes/TesteNf/emitir" ,                                     "modulo" => "testes",                 "rotina" => "dashboard" ),
        "consultarnfs"                              => Array("url" => "testes/TesteNf/consultar" ,                                  "modulo" => "testes",                 "rotina" => "dashboard" ),
        "pdfnfs"                                    => Array("url" => "testes/TesteNf/pdf" ,                                        "modulo" => "testes",                 "rotina" => "dashboard" ),
        "cancelarnfs"                               => Array("url" => "testes/TesteNf/cancelar" ,                                   "modulo" => "testes",                 "rotina" => "dashboard" ),
        "listarnfs"                                 => Array("url" => "testes/TesteNf/listar" ,                                     "modulo" => "testes",                 "rotina" => "dashboard" ),
        "testecallback"                             => Array("url" => "testes/TesteRetornoCallback/index" ,                         "modulo" => "testes",                 "rotina" => "dashboard" ),
        "callback/producao/depositowalletremota"    => Array("url" => "testes/TesteApiWallets/prodCallback" ,                       "modulo" => "testes",                 "rotina" => "dashboard" ),
        "callback/homolog/depositowalletremota"     => Array("url" => "testes/TesteApiWallets/homologCallback" ,                    "modulo" => "testes",                 "rotina" => "dashboard" ),
        "teste/comissao/arvore"                     => Array("url" => "testes/TesteArvoreComissao/index" ,                    "modulo" => "testes",                 "rotina" => "dashboard" ),
        "teste/emails"                              => Array("url" => "testes/TesteEmails/index" ,                    "modulo" => "testes",                 "rotina" => "dashboard" ),
        */
       
        
        //  APIs Rest
        "api/auth/login"                            => Array("url" => "api/auth/login" ,                                            "modulo" => "api",                    "rotina" => "auth" ),
        
        
        //"book/teste"                      => Array("url" => "trade/orderBookTest/testar" ,                  "modulo" => "trade",                 "rotina" => "book" ),
        
//        "simulacaoCallback"                             => Array("url" => "testes/TesteCallbacksInvoicePdv/teste" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
//        "simulacaoCallback/invoice"                             => Array("url" => "testes/TesteCallbacksInvoicePdv/callbackInvoice" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
//        "simulacaoCallback/producao"                             => Array("url" => "testes/TesteCallbacksInvoicePdv/callbackProducao" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
//        "simulacaoCallback/homologacao"                             => Array("url" => "testes/TesteCallbacksInvoicePdv/callbackHomologacao" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
//        
//        
//        "callback/4h"                             => Array("url" => "pdvs/estabelecimentos/fourH" ,                            "modulo" => "principal",                 "rotina" => "dashboard" ),
//        "telegram"                                => Array("url" => "ws/telegram/index" ,                               "modulo" => "principal",                 "rotina" => "dashboard" )
//        
//        
//        
        
        
    );
    
    const R_CLIENTES_PROMOCAO_PAGAR = "clientes/promocao/pagar";
    
    const M_ACESSO = "acesso";
    const M_PINCIPAL = "principal";
    const M_PERFIL = "perfil";
    const M_CADASTROS = "cadastros";
    const M_BTC = "btc";
    const M_REAIS = "reais";
    const M_COMUNIDADE = "comunidade";
    const M_FINANCEIRO = "financeiro";
    const M_CONFIGURACOES = "configuracoes";
    const M_CARTOES = "cartoes";
    const M_SERVICOS = "servicos";
    const M_PDVS = "pdvs";
    const M_MONITORAMENTO = "monitoramento";
    const M_ERRO = "erro";
    const M_DOC = "docs";
    const M_FRANQUIADO = "vendedor";
    const M_RECEBIMENTOSPVD  = "recebimentospdv";
    const M_TRADE  = "trade";
    const M_COMERCIOS  = "trade";
    
    
    const R_TESTE_EMAIL  = "testeEmail";
    
    const M_SAQUES  = "saques";
    const M_DEPOSITOS  = "depositos";
    const M_TRANSFERENCIAS  = "transferencias";
    
    const R_DEPOSITOS_WALLETS_SHOW = "depositos/wallets/show";
    const R_DEPOSITOS_ULTIMOS_DEPOSITOS = "depositos/wallets/listaDepositos";
    
    const R_FILESMANAGER = "filesmanager";
    
    const R_VOTACAO = "votacao";
    const R_VOTACAO_LISTAR = "votacao/listar";
    const R_VOTACAO_SALVAR = "votacao/salvar";
    const R_VOTACAO_VOTAR =  "votacao/votar";
    const R_VOTACAO_APROVAR =  "votacao/aprovar";
    const R_VOTACAO_NEGAR =  "votacao/negar";
    
    
    
    
    
    const R_PAINEL_ICO = "painel/ico";
    const R_PAINEL_ICO_ULTIMASCOMPRAS = "painel/ico/ultimascompras";
    const R_PAINEL_ICO_ULTIMASBONIFICACOES = "painel/ico/ultimasbonificacoes";
    const R_PAINEL_ICO_DADOSFASES = "painel/ico/dadosfases";
    
    const R_IDIOMA_CHANGE = "idioma/change";
    const R_INIT = "initpage";
    const R_LOGAR_API = "logarapi";
    const R_LOGIN = "login";
    const R_LOGAR = "logar";
    const R_REVOGAR = "revogar";
    const R_ATIVACAO = "activation";
    const R_REGISTER = "register";
    const R_LOGOUT = "logout";
    const R_RECUPERACAO = "recovery";
    const R_AUTHRECOVER = "authrecover";
    const R_RECOVERVALIDATION = "recovervalidation";
    const R_CONFIRMACAO = "confirmacao";
    const R_CADASTRO = "cadastro";
    const R_QRCODEINVOICE = "qrcodeinvoice";
    const R_VALIDAR2F = "validar2f";
    const R_TWOFACTORAUTH = "twofactorauth";
    const R_DOCS_ACEITACAO = "docs/aceitacao";
    const R_DOCS_ACEITACAO_SALVAR = "docs/aceitacao/salvar";
    const R_NOTIFICACAO_MOEDA_CLIENTE = "notificacaoMoeda/Cliente";
    
    const R_RECOVER = "recover";
    const R_MAIL_CONFIRMATION = "mail/confirmation";

    const DOC_API_TRANSFERENCIA = "doc/api/transferencia";
    const DOC_API_PDV = "doc/api/pdv"; 
    const DOC_API_CARTEIRA = "doc/api/carteira"; 
    const DOC_API_TRADE = "doc/api/trade";
    const DOC_API_CALC = "doc/api/calc";
    const DOC_API_TABELAS = "doc/api/tabelas";
    const DOC_API_PAYMENT = "doc/api/payment";
    
    
    const R_DASHBOARD = "book";
    const R_CLIENTES_ONLINE = "online";
    const R_DASHBOARD_SALDOS_CLIENTES = "dashboard/saldos/clientes";
    const R_DASHBOARD_SALDOS_EMPRESA = "dashboard/saldos/empresa";
    const R_MEUSDADOS = "myprofile";
    const R_CARTEIRAS = "deposit";
    const R_INVESTIMENTO = "investimento";
    const R_INDICACOES = "indicacoes";
    const R_CONTACORRENTEBTC = "contacorrentebtc";
    const R_CONTACORRENTEREAIS = "contacorrentereais";
    const R_CONTASBTC = "contasbtc";
    const R_CONTASREAIS = "contasreais";
    const R_TRANSFERENCIABTC = "transferenciabtc";
    const R_TRANSFERENCIAREAIS = "transferenciareais";
    const R_PAINELBTC = "painelbtc";
    const R_CHAT = "chat";
    const R_DEPOSITOS = "deposit";
    const R_SAQUES = "withdraw";
    const R_OPERACOES = "operacoes";
    const R_PROCESSOS = "processos";
    const R_MOEDAS = "moedas";
    const R_CLIENTES = "clientes";
    const R_PAINELCONTROLE = "painelcontrole";
    const R_PAINELSITE = "painelsite";
    const R_PAINELSITE_SALVAR = "painelsite/salvar";
    const R_LISTAUSUARIOS = "usuarios";
    const R_LISTACLIENTES = "clientes";
    const R_ADMINISTRATIVO = "administrativo/cartoes";
    const R_CARDS = "cards";
    const R_RECHARGES = "recharges";
    const R_INVOICES = "invoices";
    const R_CARTOES = "cartoes";
    const R_RECARGAS = "recargas";
    const R_EXTRATO = "extrato";
    const R_MENSALIDADES = "mensalidades";
    const R_REMRET = "remret";
    const R_BOLETOS = "boletos";
    const R_REMESSAS = "remessas";
    const R_ESTABELECIMENTOS = "estabelecimentos";
    const R_PONTOSPDVS = "pontospdvs";
    const R_MONITORAMENTO = "monitoramento";
    const R_CONTASBANCARIASEMPRESA = "contasempresa";
    const R_CCEMPRESABTC = "ccempresabtc";
    const R_ERROR = "error";
    const R_CONTABTCEMPRESA = "contabtcempresa";
    const R_CONTAREAISEMPRESA = "contareaisempresa";
    const R_COTACOES_GET = "cotacoes/get";
    const R_BOOK_ICO = "book/ico";
    const R_BOOK_ICO_COMPRAR = "book/ico/comprar";
    const R_BOOK_PARIDADES_LISTAR = "book/paridades/listar";
    const R_BOOK_PARIDADES_LISTAR_NEW = "book/paridades/listarnew";
    const R_BOOK_MOEDAS_SALDO = "book/moedas/saldos";
    
    // Métodos internos
    const R_POSTLISTARCLIENTES = "postlistarclientes";
    const R_POSTCADASTROCLIENTES = "postcadastroclientes";
    const R_POSTSALVARCLIENTES = "postsalvarcliente";
    const R_POSTEXCLUIRCLIENTES = "postexcluircliente";
    const R_POSTREJEITARCLIENTES = "postrejeitarcliente";
    const R_POSTACEITARCLIENTES = "postaceitarcliente";
    const R_POSTLIBERARCLIENTES = "postliberarcliente";
    const R_POSTGETDADOSVALORMENSALCLIENTE = "postgetdadosvalormensalcliente";
    const R_POSTSALVARDADOSVALORMENSALCLIENTE = "postsalvardadosvalormensalcliente";
    const R_POSTGETCIDADESCLIENTE = "postgetcidadescliente";
    const R_POSTGETCIDADESPERFIL = "postgetcidadesperfil";
    const R_POSTREMOVERDOCUMENTOPERFIL = "postremoverdocumentoperfil";
    const R_MERKETUPDATE = "market/salvar";
    const R_POSTZERARPINCLIENTE = "postzerarpincliente";
    const R_POSTACEITARREHEITARFOTOCLIENTE = "postaceitarrejeitarfotocliente";
    const R_POSTIMPORTAREXCELDADOSMENSALCLIENTE = "postimportarexceldadosmensalcliente";
    const R_POSTGETCLIENTEBYEMAIL = "postgetclientebyemail";
    
    const R_CADASTROLISTARCONTASBANCARIAS = "cadastrolistarcontasbancarias";
    const R_CADASTRONOVACONTABANCARIA = "cadastronovacontabancaria";
    const R_CADASTROSALVARCONTABANCARIA = "cadastrosalvarcontabancaria";
    const R_CADASTROEXCLUIRCONTABANCARIA = "cadastroexcluircontabancaria";
    
    const R_CADASTRO_USUARIO_LISTAR = "cadastro/usuarios/listar";
    const R_CADASTRO_USUARIO_CADASTRO = "cadastro/usuarios/cadastro";
    const R_CADASTRO_USUARIO_ATIVO = "cadastro/usuarios/ativo";
    const R_CADASTRO_USUARIO_EXCLUIR = "cadastro/usuarios/excluir";
    const R_CADASTRO_USUARIO_SALVAR = "cadastro/usuarios/salvar";
    const R_CONTAS_BANCOS_LISTAR = "contas/bancos/listar";
    const R_CLIENTES_FRANQUIA_STATUS = "clientes/franquia/status";
    const R_CLIENTES_FRANQUIA_CANCELAR = "clientes/franquia/cancelar";
    const R_CLIENTES_TAXAS_LISTAR = "clientes/taxas/listar";
    const R_CLIENTES_TAXAS_SALVAR =  "clientes/taxas/salvar";
    const R_CLIENTES_COMISSOES_CADASTRO = "clientes/comissoes/cadastro";
    const R_CLIENTES_COMISSOES_SALVAR =  "clientes/comissoes/salvar";
    const R_CLIENTES_CREDITOS_LISTAR = "clientes/creditos/listar";
    const R_CLIENTES_CREDITOS_SALVAR =  "clientes/creditos/salvar";
    const R_CLIENTES_FOLLOW_FILTER =  "followfilter";

    const R_EXTRATO_LISTAR = "extrato/listar";
    
    const R_REFERENCIA_LISTAR = "referencia/listar";
    
    const R_CONFIGURACOES_CONTAS_LISTAR = "configuracoes/contas/listar";
    const R_CONFIGURACOES_CONTAS_CADASTRO = "configuracoes/contas/cadastro";
    const R_CONFIGURACOES_CONTAS_SALVAR = "configuracoes/contas/salvar";
    const R_CONFIGURACOES_CONTAS_EXCLUIR = "configuracoes/contas/excluir";
    const R_CONTASEMPRESA_STATUS_ALTERAR = "contasempresa/status/alterar";
    
    const R_CONFIGURACOES_COMISSOES_CADASTRO = "configuracoes/comissoes/cadastro";
    const R_CONFIGURACOES_COMISSOES_SALVAR = "configuracoes/comissoes/salvar";
    
    const R_CONFIGURACOES_PAINEL_SALVAR = "configuracoes/painel/salvar";
    
    const R_BTC_CONTAS_RESUMO = "btc/contas/resumo";
    const R_BTC_CONTA_LANCAMENTOS = "btc/conta/lancamentos";
    const R_BTC_CONTA_FILTRARSALDO = "btc/conta/filtrarsaldo";
    const R_BTC_LANCAMENTO_CADASTRO = "btc/lancamento/cadastro";
    const R_BTC_LANCAMENTO_SALVAR = "btc/lancamento/salvar";
    const R_BTC_LANCAMENTO_EXCLUIR = "btc/lancamento/excluir";
    const R_BTC_LANCAMENTO_IMPRIMIR = "btc/lancamentos/imprimir";
    const R_BTC_TRANSFERIR = "btc/transferir";
    const R_BTC_TRANSFERENCIAS_FILTRAR = "btc/transferencias/filtrar";
    const R_BTC_TRANSFERENCIAS_IMPRIMIR = "btc/transferencias/imprimir";
    const R_BTC_AUTORIZACAO_TOKEN = "btc/autorizacao/token";
    const R_BTC_EMPRESA_SALVAR = "btc/empresa/salvar";
    const R_BTC_EMPRESA_IMPRIMIR = "btc/empresa/imprimir";
    const R_BTC_LOG = "btc/log";
    const R_BTC_LOG_FILTRAR = "btc/log/filtrar";
    const R_BTC_LOG_IMPRIMIR = "btc/log/imprimir";
    const R_BTC_LOGEMPRESA =  "btc/logempresa";
    const R_BTC_LOGEMPRESA_FILTRAR = "btc/logempresa/filtrar";
    const R_BTC_LOGEMPRESA_IMPRIMIR = "btc/logempresa/imprimir";
    const R_BTC_PAINEL_FILTRAR = "btc/painel/filtrar";
    const R_BTC_PAINEL_CONFIRMACAO = "btc/painel/confirmacao";
    const R_BTC_PAINEL_CONFIRMAR = "btc/painel/confirmar";
    const R_BTC_PAINEL_EXCLUIR = "btc/painel/excluir";
    const R_BTC_PAINEL_FILTRARNAOAUTORIZADAS = "btc/painel/filtrarnaoautorizadas";
    const R_BTC_PAINEL_AUTORIZARTRANSACAO = "btc/painel/autorizartransacao";
    const R_BTC_PAINEL_NEGARTRANSACAO = "btc/painel/negartransacao";
    const R_PAINEL_ICO_SAQUES = "painel/ico/saques";
    const R_PAINEL_ICO_CARREGARSAQUE = "painel/ico/carregarSaque";
    const R_PAINEL_ICO_AUTORIZARSAQUE = "painel/ico/autorizarSaque";
    const R_PAINEL_ICO_NEGARSAQUE = "painel/ico/negarSaque";
        
    
    
    const R_POSTPEDIDOSCLIENTES = "postpedidosclientes";
    const R_REAIS_CONTAS_RESUMO = "reais/contas/resumo";
    const R_REAIS_CONTA_LANCAMENTOS =     "reais/conta/lancamentos";
    const R_REAIS_CONTAS_FILTRAR_SALDO =     "reais/conta/filtrarsaldo";
    const R_REAIS_LANCAMENTO_CADASTRO =     "reais/lancamento/cadastro";
    const R_REAIS_LANCAMENTO_SALVAR =     "reais/lancamento/salvar";
    const R_REAIS_LANCAMENTO_EXCLUIR =     "reais/lancamento/excluir";
    const R_REAIS_LANCAMENTOS_IMPRIMIR =     "reais/lancamentos/imprimir";
    const R_REAIS_TRANSFERIR =     "reais/transferir";
    const R_EMPRESA_TRANSFERIR =     "empresa/transferir";
    const R_REAIS_TRANSFERENCIAS_FILTRAR =     "reais/transferencias/filtrar";
    const R_REAIS_TRANSFERENCIAS_IMPRIMIR = "reais/transferencias/imprimir";
    
    const R_EMPRESA_AUTORIZACAO_TOKEN =     "empresa/autorizacao/token";
    const R_REAIS_AUTORIZACAO_TOKEN =     "reais/autorizacao/token";
    const R_REAIS_LOG =     "reais/log";
    const R_REAIS_LOG_FILTRAR =     "reais/log/filtrar";
    const R_REAIS_LOG_IMPRIMIR =     "reais/log/imprimir";
    const R_REAIS_LOGEMPRESA =     "reais/logempresa";
    const R_REAIS_LOGEMPRESA_FILTRAR =    "reais/logempresa/filtrar";
    const R_REAIS_LOGEMPRESA_IMPRIMIR =     "reais/logempresa/imprimir";
    const R_REAIS_EMPRESA_IMPRIMIR =     "reais/empresa/imprimir";
    const R_REAIS_DEPOSITOS_SOLICITAR = "reais/depositos/solicitar";
    const R_REAIS_DEPOSITOS_LISTAR =     "reais/depositos/listar";
    const R_REAIS_DEPOSITOS_ANALISAR =     "reais/depositos/analisar";
    const R_REAIS_DEPOSITOS_DADOSCONTA =     "reais/depositos/dadosconta";
    const R_REAIS_DEPOSITOS_SALVAR =     "reais/depositos/salvar";
    const R_REAIS_DEPOSITOS_APROVAR =     "reais/depositos/aprovar";
    const R_REAIS_DEPOSITOS_CANCELAR =     "reais/depositos/cancelar";
    const R_REAIS_DEPOSITOS_IMPRIMIR =     "reais/depositos/imprimir";
    
    const R_DEPOSITO_BOLETO_GERAR = "deposito/boleto/gerar";
    const R_DEPOSITO_BOLETO_LARA = "deposito/boletolara/gerar";
    const R_DEPOSITO_BOLETO_TOKEN = "deposito/boleto/token";
    
    
    const R_ATAR_TOKEN = "atar/token";
    const R_ATAR_SALVAR = "atar/salvar";
    const R_ATAR_LISTAR = "atar/listar";
    const R_ATAR_SALDO = "atar/saldo";
    
    const R_REAIS_SAQUES_LISTAR =     "reais/saques/listar";
    const R_REAIS_SAQUES_FILTRAR =     "reais/saques/listarReais";
    const R_REAIS_SAQUES_SALDOS =     "reais/saques/saldos";
    const R_REAIS_SAQUES_DADOSCONTA =     "reais/saques/dadosconta";
    const R_REAIS_SAQUES_EXTRATO_CLIENTE =     "reais/saques/extratoCliente";
    const R_REAIS_SAQUES_SALVAR =     "reais/saques/salvar";
    const R_REAIS_SAQUES_APROVAR =     "reais/saques/aprovar";    
    const R_REAIS_SAQUES_ANALISAR =     "reais/saques/analisar";
    const R_REAIS_SAQUES_CANCELAR =     "reais/saques/cancelar";
    const R_REAIS_SAQUES_IMPRIMIR =    "reais/saques/imprimir";
    const R_REAIS_SAQUES_SOLICITAR = "reais/saques/solicitar";
    const R_REAIS_SAQUES_ULTIMOS_SAQUES = "reais/saques/ultimosSaques";
    const R_REAIS_SAQUES_SHOW_DADOS = "reais/saques/showDados";   
    const R_REAIS_SAQUES_GET_TAXAS_ERC20 = "reais/saques/getTaxas";
    const R_REAIS_CLIENTES_FINDBYWALLET = "reais/clientes/findbywallet";
    const R_REAIS_CLIENTES_FINDBYEMAIL = "reais/clientes/findbyemail";
    const R_ADMINISTRATIVO_CARTOES_FILTRAR = "administrativo/cartoes/filtrar";
    const R_ADMINISTRATIVO_CARTOES_CADASTRO = "administrativo/cartoes/cadastro";
    const R_ADMINISTRATIVO_CARTOES_SALVAR = "administrativo/cartoes/salvar";
    const R_ADMINISTRATIVO_CARTOES_EXCLUIR = "administrativo/cartoes/excluir";
    const R_ADMINISTRATIVO_CARTOES_ATIVAR = "administrativo/cartoes/ativar";
    const R_CARTOES_LISTAR = "cartoes/listar";
    const R_CARTOES_ALTERARSTATUS = "cartoes/alterarStatus";
    const R_CARTOES_CANCELAR = "cartoes/cancelar";
    const R_CARTOES_SALDOS = "cartoes/saldos";
    const R_CARTOES_EXTRATOS = "cartoes/extratos";
    const R_CARTOES_CONSULTARSALDO = "cartoes/consultar/saldo";
    const R_CARTOES_VISA_RECARREGAR = "cartoes/visa/recarregar";
    
    
    const R_INVOICES_FILTRAR = "invoices/filtrar";
    const R_INVOICES_GET = "invoices/get";
    const R_MEUSCARTOES_DADOS = "meuscartoes/dados";
    const R_INVOICES_CARTAO_SALVAR =  "invoices/cartao/salvar";
    const R_INVOICES_CARTAO_STATUS = "invoices/cartao/status";
    const R_INVOICES_CANCELAR = "invoices/cancelar";
    const R_INVOICES_PAGAMENTO_CONFIRMAR =  "invoices/pagamento/confirmar";
    const R_MEUSCARTOES_NOVO = "meuscartoes/novo";
    const R_MEUSCARTOES_ATIVAR = "meuscartoes/ativar"; 
    const R_MEUSCARTOES_PIN_VALIDAR = "meuscartoes/pin/validar";
    const R_MEUSCARTOES_SENHA_MOSTRAR = "meuscartoes/senha/mostrar";
    const R_INVOICES_CARTAO_EXTRATO = "invoices/cartao/extrato";
    const R_MEUSCARTOES_LISTAR =  "meuscartoes/listar";
    
    
    
    const R_MENSALIDADES_LISTAR = "mensalidades/listar";
    const R_MENSALIDADES_CLIENTES_CARTOES = "mensalidade/clientes/cartoes";
    
    
    
    const R_MENSALIDADES_LISTAR_PAGAVEIS = "mensalidades/listar/pagaveis";
    const R_MENSALIDADES_PAGAR = "mensalidades/pagar";
    const R_MENSALIDADES_INVOICE_GERAR = "mensalidades/invoice/gerar";
    const R_MENSALIDADES_INVOICE_STATUS = "mensalidades/invoice/status";
    const R_MENSALIDADES_INVOICE_COMPROVANTE = "mensalidades/invoice/comprovante";
    const R_RECHARGES_FILTRAR =  "recharges/filtrar";
    const R_RECHARGES_FINALIZAR = "recharges/finalizar";
    const R_RECHARGES_PAGAMENTO_DADOS = "recharges/pagamento/dados";
    const R_RECHARGES_CARTAO_DADOS = "recharges/cartao/dados";
    const R_RECHARGES_CARTAO_VALIDAR = "recharges/cartao/validar";
    const R_REMRET_LISTAR = "remret/listar";
    const R_REMRET_DOWNLOAD = "remret/download";
    const R_REMRET_UPLOAD = "remret/retorno/upload";
    const R_MONITORAMENTO_REFRESH = "monitoramento/refresh";
    const R_COMPROVANTES_INVOICE = "comprovantes/invoice";
    
    const R_PONTOSPDVS_CHAVES_GERAR = "pontospdvs/chaves/gerar";
    const R_PONTOSPDVS_CHAVES_PREPARAR = "pontospdvs/chaves/preparar";
    const R_PONTOSPDVS_CHAVES_MOSTRAR = "pontospdvs/chaves/mostrar";
    const R_PONTOSPDVS_CHAVES_DESATIVAR = "pontospdvs/chaves/desativar";
    const R_ESTABELECIMENTOS_LISTAR = "estabelecimentos/listar";
    const R_ESTABELECIMENTOS_CADASTRO = "estabelecimentos/cadastro";
    const R_ESTABELECIMENTOS_SALVAR = "estabelecimentos/salvar";
    const R_ESTABELECIMENTOS_EXCLUIR = "estabelecimentos/excluir";
    const R_ESTABELECIMENTOS_ALTERARSTATUS = "estabelecimentos/alterarStatus";
    const R_ESTABELECIMENTOS_CIDADES_LISTAR = "estabelecimentos/cidades/listar";
    const R_ESTABELECIMENTOS_HTML_ESTABELECIMENTOSPORCLIENTE = "estabelecimentos/html/estalecimenosporcliente";
    const R_ESTABELECIMENTOS_WALLETS_LISTAR = "estabelecimentos/wallets/listar";
    const R_PONTOSPDVS_LISTAR = "pontospdvs/listar";
    const R_PONTOSPDVS_CADASTRO = "pontospdvs/cadastro";
    const R_PONTOSPDVS_SALVAR = "pontospdvs/salvar";
    const R_PONTOSPDVS_EXCLUIR = "pontospdvs/excluir";
    const R_PONTOSPDVS_ALTERARSTATUS = "pontospdvs/alterarStatus";
        
    const R_MEUSDADOS_CONTA_STATUS = "meusdados/conta/status";
    const R_MEUSDADOS_SALVAR = "meusdados/salvar";
    const R_MEUSDADOS_SENHA_ATUALIZAR = "meusdados/senha/atualizar";
    const R_MEUSDADOS_FOTO_ATUALIZAR = "meusdados/foto/atualizar";
    const R_MEUSDADOS_SENHA_FORCA = "meusdados/senha/forca";
    const R_MEUSDADOS_COUNTRY_BRAND = "meusdados/country/brand";
    const R_MEUSDADOS_STATES_LIST = "meusdados/states/list";
    const R_MEUSDADOS_NOTIFICACAO_GET = "meusdados/notificacao/get";
    const R_MEUSDADOS_NOTIFICACAO_TITULO = "meusdados/notificacao/titulo";
    const R_MEUSDADOS_NOTIFICACAO_SALVAR = "meusdados/notificacao/salvarNotificacao";
    const R_MEUSDADOS_ALTERAR_MODO = "meusdados/alterarModo";
    const R_CLIENTES_STATES_LIST = "clientes/states/list";
    const R_MEUSDADOS_NOTA_FISCAL = "meusdados/notafiscal/notafiscalcliente";
    const R_MEUSDADOS_NAVEGADOR_LIST = "meusdados/navegador/listaNavegadores";
    const R_MEUSDADOS_NAVEGADOR_STATUS = "meusdados/navegador/statusNavegador";
    const R_MEUSDADOS_NOTA_FISCAL_OPE_CLIENTE_LIST = "meusdados/notafiscal/listarCliente";
    const R_MEUSDADOS_NOTA_FISCAL_OPE_CLIENTE_SALVAR = "meusdados/notafiscal/salvar";
    const R_MEUSDADOS_MENU = "meusdados/menu";
    const R_MEUSDADOS_LIMITES = "meusdados/limites";
    const R_MEUSDADOS_TAXAS = "api/trade/fees";
    const R_KYC_START = "meudados/kyc/start";
    const R_KYC_SMS_RESEND = "meudados/kyc/smsresend";
    
    
    const R_DASHBOARD_REFERENCIAS_VENDEDOR = "dashboard/referencias/vendedor";
    const R_DASHBOARD_REFERENCIAS_ADM = "dashboard/referencias/adm";
    const R_DASHBOARD_REFERENCIAS_EXTRATO = "dashboard/referencias/extrato";
    const R_DASHBOARD_REFERENCIAS_CARREGAR = "dashboard/extrato/carregar";
    const R_DASHBOARD_REFERENCIAS_PAGAR = "dashboard/referencias/pagar";
    const R_DASHBOARD_REFERENCIAS_CALCULADORTOTAL = "dashboard/referencias/calculartotal";
    const R_INDICACOES_CALCULARRESGATE = "indicacoes/calcularresgate";
    const R_INDICACOES_RESGATE = "indicacoes/resgate";
    const R_INDICACOES_TRANSFERIR = "indicacoes/transferir";
    const R_INDICACOES_GETCLIENTEBYEMAIL = "indicacoes/getclientebyemail";
    const R_INDICACOES_RESGATAR = "indicacoes/resgatar";
        
    const R_RECARGAS_CARTAO_GET = "recargas/cartao/get";
    const R_RECARGAS_VALIDATE = "recargas/validate";
    const R_RECARGAS_INVOICE = "recargas/invoice";
    const R_RECARGAS_INVOICE_GET = "recargas/invoice/get";
    const R_RECARGAS_STATUS_GET = "recargas/status/get";
    const R_RECARGAS_INVOICE_COMPROVANTE = "recargas/invoice/comprovante";
    const R_RECARGAS_CARTAO = "recargas/cartao";
        
    
    const R_BOLETOS_SALVAR = "boletos/salvar";
    const R_BOLETOS_FILTRAR = "boletos/filtrar";
    const R_BOLETOS_DADOS = "boletos/dadosPagamento";
    const R_BOLETOS_FINALIZAR = "boletos/finalizar";
    const R_BOLETOS_CANCELAR = "boletos/cancelar";
    const R_BOLETOS_CADASTRO = "boletos/cadastro";
    const R_BOLETOS_COMPROVANTE = "boletos/comprovante";
    const R_BOLETOS_BARRAS_DADOS = "boletos/barras/dados";
    const R_BOLETOS_PAGAR = "boletos/pagar";
    const R_REMESSAS_SALVAR = "remessas/salvar";
    const R_REMESSAS_DADOS = "remessas/dadosPagamento";
    const R_REMESSAS_CONTAS = "remessas/contasCadastradas";
    const R_REMESSAS_FILTRAR = "remessas/filtrar";
    const R_REMESSAS_CANCELAR = "remessas/cancelar";
    const R_REMESSAS_FINALIZAR = "remessas/finalizar";
    const R_REMESSAS_CADASTRO = "remessas/cadastro";
    const R_REMESSAS_COMPROVANTE = "remessas/comprovante";
    const R_REMESSAS_PAGAR = "remessas/pagar";
    
    
    const R_ATAR_DEP_LISTAR = "atar/deposito/listar";
    const R_ATAR_DEP_DEPOSITAR = "atar/deposito/salvar";
    const R_ATAR_DEP_TOKEN = "atar/deposito/token";
    const R_ATAR_DEP_SALVAR = "atar/deposito/salvar";
    
    const R_CARTEIRAS_LISTAR = "carteiras/listar";
    const R_CARTEIRAS_NOVA = "carteiras/nova";
    const R_CARTEIRAS_PRINCIPAL = "carteiras/principal";
    
    
    const R_CADASTRO_CIDADES_LISTAR = "cadastro/cidades/listar";
    const R_CADASTRO_CLIENTE_ADERIR = "cadastro/cliente/aderir";
    const R_MEUSDADOS_ALERTAS_ATUALIZAR = "meusdados/alertas/atualizar";
    
    const R_COMERCIOS = "comercios";
    const R_COMERCIOS_CADASTRO = "comercios/cadastro";
    const R_COMERCIOS_SALVAR = "comercios/salvar";
    const R_COMERCIOS_EXCLUIR = "comercios/excluir";
    const R_COMERCIOS_CIDADES_LISTAR = "comercios/cidades/listar";
    const R_COMERCIOS_LISTAR = "comercios/listar";
    
    const R_COMERCIOS_ADMINISTRATIVO = "comercios/administrativo";
    const R_COMERCIOS_ADMINISTRATIVO_LISTAR = "comercios/administrativo/listar";
    const R_COMERCIOS_ADMINISTRATIVO_STATUS = "comercios/administrativo/status";
        
    const R_COMERCIOS_SEGMENTOS = "comercios/segmentos";
    const R_COMERCIOS_SEGMENTOS_LISTAR = "comercios/segmentos/listar";
    const R_COMERCIOS_SEGMENTOS_CADASTRO = "comercios/segmentos/cadastro";
    const R_COMERCIOS_SEGMENTOS_EXCLUIR = "comercios/segmentos/excluir";
    const R_COMERCIOS_SEGMENTOS_SALVAR = "comercios/segmentos/salvar";
    const R_COMERCIOS_SEGMENTOS_STATUS = "comercios/segmentos/status";
    
    
    const R_SERVICE_CORREIO_BUSCACEP = "service/correio/buscacep";
    const R_SERVICE_CORREIO_BUSCACOORDENADAS = "service/correio/buscacoordenadas";
    const R_CONSULTA_CPF = "consultacpf";
    const R_CURRENCY = "currency";
    
    const R_CRIPTOCURRENCIES_FEES = "criptocurrencies/fees";
    const R_CRIPTOCURRENCIES_FEES_UPDATE = "criptocurrencies/fees/update";
    const R_MEUSDADOS_PERFIL_UPGRADE = "meusdados/perfil/upgrade";
    
    
    const R_PERFIL_UPGRADE_ANALISAR = "perfil/upgrade/analisar";
    const R_PERFIL_UPGRADE_APROVAR = "perfil/upgrade/aprovar";
    const R_PERFIL_UPGRADE_NEGAR = "perfil/upgrade/negar";
    const R_PERFIL_UPGRADE_LISTAR = "perfil/upgrade/listar";
    const R_MEUSDADOS_SEGURANCA_ATUALIZAR = "meusdados/seguranca/atualizar";
    const R_MEUSDADOS_AUTORIZACAO_TOKEN = "meusdados/autorizacao/token";
     const R_MEUSDADOS_GOOGLEAUTH_GETSECRET ="meusdados/googleauth/getsecret";
    
    const R_FRANQUIADO_CLIENTES = "vendedor/clientes";
    const R_FRANQUIADO_CLIENTES_LISTAR = "vendedor/clientes/listar";
    const R_FRANQUIADO_CLIENTES_COMISSAO = "vendedor/financas/comissaoCliente";
    const R_FRANQUIADO_FINANCAS = "vendedor/financas";
    const R_FRANQUIADO_PORTAL = "vendedor/portal";
    const R_FRANQUIADO_ADMINISTRATIVO = "vendedor/administrativo";
    const R_FRANQUIADO_ADMINISTRATIVO_FILTRAR = "vendedor/administrativo/filtrar";
    const R_FRANQUIADO_ADMINISTRATIVO_CONVIDADOS = "vendedor/administrativo/filtrarClienteConvite";
    const R_FRANQUIADO_ADMINISTRATIVO_FILTRAR_REF = "vendedor/administrativo/filtrarReferencias";
    const R_FRANQUIADO_ADMINISTRATIVO_SALVAR_REF = "vendedor/administrativo/salvarReferencias";
    const R_FRANQUIADO_ADMINISTRATIVO_REMOVER_REF = "vendedor/administrativo/removerReferencias";
    const R_FRANQUIADO_ADMINISTRATIVO_CONVITES = "vendedor/administrativo/convites";
    const R_FRANQUIADO_ADMINISTRATIVO_REFERENCIAS = "vendedor/administrativo/referencias";
    const R_FRANQUIADO_ADMINISTRATIVO_REENVIARCONVITE = "vendedor/administrativo/reenviarconvite";
    const R_FRANQUIADO_ADMINISTRATIVO_FILTRARCONVITES = "vendedor/administrativo/filtrarconvites";
    const R_FRANQUIADO_CLIENTE_ANALISAR = "vendedor/cliente/analisar";
    const R_FRANQUIADO_CLIENTE_APROVARREJEITARDOCUMENTO = "vendedor/cliente/aprovarrejeitardocumento";
    const R_VENDEDOR_FINANCAS_DADOS = "vendedor/financas/dados";
    const R_VENDEDOR_FINANCAS_FILTRARCLIENTES = "vendedor/financas/filtrarClientes";
    const R_VENDEDOR_FINANCAS_GRAFICO = "vendedor/financas/grafico";
    
    const R_CRIAR_CARTEIRA_NEWC = "currency/dados/newc";
    const R_CURRENCY_DADOS = "currency/dados" ;
    const R_CLIENTES_CONSULTAS_CNPJ = "clientes/consultas/cnpj";
    const R_CLIENTES_CONSULTAS_CPF = "clientes/consultas/cpf";
    const R_NOTIFICACOES_CLIENTE_BUSCAR = "notificacoes/cliente/buscar";
    const R_NOTIFICACOES_CADASTRO = "notificacoes/cadastro";
    const R_NOTIFICACOES_SALVAR = "notificacoes/salvar";
    const R_NOTIFICACOES_LISTAR_NAOEXIBIDAS = "notificacoes/listar/naoexibidas";
    const R_NOTIFICACOES_SHOWN = "notificacoes/shown";
    const R_NOTIFICACOES_ALERTAS = "notificacoes/alertas";
    const R_NOTIFICACOES_ALL = "notificacoes/all";
    const R_NOTIFICACOES_ALL_FILTRAR = "notificacoes/all/filtrar";
    const R_NOTIFICACOES_READ = "notificacoes/read";
    const R_CLIENTE_EMAIL_REENVIARCONFIRMACAO = "cliente/email/reenviarconfirmacao";
    const R_MEUSDADOS_UPLOAD_DOCUMENTO = "meusdados/upload/documento";
    const R_CADASTRO_SENHA_FORCA = "cadastro/senha/forca";
    
    const R_NOTASFISCAIS = "notasfiscais";
    const R_NOTASFISCAIS_FILTRAR = "notasfiscais/filtrar";
    const R_NOTASFISCAIS_ATUALIZAR = "notasfiscais/atualizar";
    const R_NOTASFISCAIS_CANCELAR = "notasfiscais/cancelar";
    const R_NOTASFISCAIS_EMITIR = "notasfiscais/emitir";
    
    const R_ESTABELECIMENTOS_WALLETS_CALLBACK = "estabelecimentos/wallets/callback";
    const R_ESTABELECIMENTO_DADOS_RESUMO = "estabelecimentos/dados/resumo";
    const R_ESTABELECIMENTO_SALDO_RESGATAR = "estabelecimentos/saldo/resgatar";
    const R_TWOFACTORAUTH_TOKEN_SEND = "twofactorauth/token/send";
    const R_CLIENTE_STATUS_COLOCAREMESPERA = "cliente/status/colocarEmEspera";
    const R_ESTABELECIMENTOS_RESGATE_CALCULARTAXA = "estabelecimentos/resgate/calculartaxa";
    
    const R_MEUSDADOS_CHAVES_GERAR = "meusdados/chaves/gerar";
    const R_MEUSDADOS_CHAVES_PREPARAR = "meusdados/chaves/preparar";
    const R_MEUSDADOS_CHAVES_MOSTRAR = "meusdados/chaves/mostrar";
    const R_MEUSDADOS_CHAVES_DESATIVAR = "meusdados/chaves/desativar";
    
    const R_BOOK = "book";
    const R_BOOK_LAYOUT_SALVAR = "book/layout/salvar";
    const R_BOOK_PARIDADE = "book/paridade";
    const R_BOOK_SALDOS = "book/saldos";
    const R_BOOK_LISTAR = "book/listarbook";
    const R_BOOK_TRADES_LIST = "book/trades/list";
    const R_BOOK_BALANCE = "book/balance";
    const R_BOOK_ALTERAR_PARIDADE = "book/ordens/paridade";
    const R_BOOK_ORDEM_COMPRAR = "book/ordem/comprar";
    const R_BOOK_ORDEM_COMPRAR_NEW = "book/ordem/comprarnew";
    const R_BOOK_ORDEM_VENDER = "book/ordem/vender";
    const R_BOOK_ORDEM_VENDER_NEW = "book/ordem/vendernew";
    const R_BOOK_COMPRA_LISTAR = "book/compra/listar";
    const R_BOOK_VENDA_LISTAR = "book/venda/listar";
    const R_BOOK_ORDENS_LISTAR = "book/ordens/listar";
    const R_BOOK_ORDENS_LISTAR_NEW = "book/ordens/listarnew";
    const R_BOOK_ORDENS_HISTORICO = "book/ordens/historico";
    const R_BOOK_HISTORY = "book-history";
    const R_BOOK_SOCKET_V2 = "socket-book-v2";
    const R_BOOK_FAVORITO_ADD = "book/favorito/add";
    const R_BOOK_FAVORITO_REMOVE = "book/favorito/remove";    
    const R_BOOK_ORDENS_CANCELAR = "book/ordens/cancelar";
    const R_BOOK_ORDENS_CANCELAR_NEW = "book/ordens/cancelarnew";
    const R_BOOK_ORDENS_EXECUTADAS_NEW = "book/ordens/executadas";
    const R_MERCADO = "mercado";
    const R_MERCADO_PRECO = "mercado/preco";
    const R_MERCADO_BALANCE = "mercado/balance";
    const R_MERCADO_PARIDADES = "mercado/paridades";
    const R_MERCADO_COMPRAR = "mercado/comprar";
    const R_MERCADO_VENDER = "mercado/vender";
    const R_BOOK_EXTRATO = "book/extrato";
    const R_MERCADO_EXTRATO_FILTRAR = "mercado/extrato/filtrar";
    const R_MERCADO_LISTAR_BOOK = "mercado/listarBook";
    const R_BOOK_EXTRATO_FILTRAR = "book/extrato/filtrar";
    const R_BOOK_NEGOCIACOES = "book/negociacoes";
    const R_BOOK_NEGOCIACOES_FILTRARCOMPRAS = "book/negociacoes/filtrarcompras";
    const R_BOOK_NEGOCIACOES_FILTRARVENDAS = "book/negociacoes/filtrarvendas";
    const R_BOOK_NEGOCIACOES_FILTRARTRADE = "book/negociacoes/filtrartrade";
    const R_BOOK_NEGOCIACOES_DADOS = "book/negociacoes/dados";
    
    const R_APIPAGAMENTOS = "apipagamentos";
    const R_APIPAGAMENTOS_BUSCAR = "apipagamentos/buscar";
    const R_APIPAGAMENTOS_CALLBACK = "apipagamentos/callback";
    const R_CLIENTE_MERCADO_STATUS = "cliente/mercado/status";
    
    const R_DASHBOARD_ORDENS_EXECUTADAS =  "dashboard/ordens/executadas";
    const R_DASHBOARD_MINHASMOEDAS =  "dashboard/minhasmoedas";
    const R_DASHBOARD_MINHASMOEDAS_NOVO =  "dashboard/minhasmoedas/novo";
    const R_DASHBOARD_MOEDA_FAVORITA =  "dashboard/setMoedaFavorita"; 
    const R_DASHBOARD_MOEDA_SALDO =  "dashboard/calculaSaldo"; 
    const R_DASHBOARD_REDIRECT =  "dashboard/redirect";
    const R_DASHBOARD_TASKS_PENDENTES = "dashboard/tasks/pendentes";
    
    const R_CONTAS_EMPRESA_BTC = "contas/empresa/btc";
    const R_CONTAS_EMPRESA_REAIS = "contas/empresa/reais";
    const R_TRANSACOES_PENDENTES = "transacoespendentes";
    const R_TRANSFERENCIAS = "transferencias";
    
    
    
    const R_CONTAS_EMPRESA = "contas/empresa";
    const R_CONTAS_EMPRESA_EXCLUIRCURRENCY = "contas/empresa/excluircurrency";
    const R_CONTAS_EMPRESA_EXCLUIRBRL = "contas/empresa/excluirbrl";
    const R_CONTAS_EMPRESA_SALVARBRL = "contas/empresa/salvarbrl";
    const R_CONTAS_EMPRESA_CADASTROBRL =     "contas/empresa/cadastrobrl";
    const R_CONTAS_EMPRESA_SALVARCURRENCY = "contas/empresa/salvarcurrency";
    const R_CONTAS_EMPRESA_CADASTROCURRENCY = "contas/empresa/cadastrocurrency";
    const R_CONTAS_EMPRESA_FILTRARCCCURRENCY = "contas/empresa/filtrarcccurrency";
    const R_CONTAS_EMPRESA_FILTRARCCBRL =     "contas/empresa/filtrarccbrl";
    const R_CONTAS_EMPRESA_TRANSFERENCIA = "contas/empresa/transferencia";
    const R_CONTAS_EMPRESA_SALDO = "contas/empresa/saldo";
    const R_CONTAS_EMPRESA_TRANSFERIRBRL = "contas/empresa/transferirbrl";
    const R_CONTAS_EMPRESA_TRANSFERIRCURRENCY = "contas/empresa/transferircurrency";
    const R_CONTAS_EMPRESA_FILTRARBRL = "contas/empresa/filtrarbrl";
    const R_CONTAS_EMPRESA_FILTRARCURRENCY = "contas/empresa/filtrarcurrency";
    
    
    const  R_LICENCAS = "licencas";
    const  R_LICENCAS_LISTAR = "licencas/listar";
    const  R_LICENCAS_CADASTRAR = "licencas/cadastrar";
    const  R_LICENCAS_SALVAR = "licencas/salvar";
    const  R_LICENCAS_EXCLUIR = "licencas/excluir";
    const  R_LICENCAS_STATUS = "licencas/status";
    const  R_LICENCAS_RECURSOS_LISTAR = "licencas/recursos/listar";
    const  R_LICENCAS_RECURSOS_CADASTRAR = "licencas/recursos/cadastrar";
    const  R_LICENCAS_RECURSOS_SALVAR = "licencas/recursos/salvar";
    const  R_LICENCAS_RECURSOS_EXCLUIR = "licencas/recursos/excluir";
    const  R_LICENCAS_RECURSOS_ATRIBUIR = "licencas/recursos/atribuir";
    const  R_LICENCAS_RECURSOS_REMOVER = "licencas/recursos/remover";
    
    const  R_ROADMAP = "roadmap";
    const  R_ROADMAP_LISTAR = "roadmap/listar";
    const  R_ROADMAP_CADASTRAR = "roadmap/cadastrar";
    const  R_ROADMAP_SALVAR = "roadmap/salvar";
    const  R_ROADMAP_EXCLUIR = "roadmap/excluir";
    const  R_ROADMAP_STATUS_CONCLUIDO = "roadmap/status/concluido";
    const  R_ROADMAP_STATUS_PUBLICADO = "roadmap/status/publicado";
    
    const  R_NOTIFICACAO_MOEDA = "notificacaomoeda";
    const  R_NOTIFICACAO_MOEDA_LISTAR = "notificacaomoeda/listar";
    const  R_NOTIFICACAO_MOEDA_LISTAR_CLIENTE = "notificacaomoeda/listarCliente";
    const  R_NOTIFICACAO_MOEDA_EDITAR = "notificacaomoeda/editar";
    const  R_NOTIFICACAO_MOEDA_SALVAR = "notificacaomoeda/salvar";
    const  R_NOTIFICACAO_MOEDA_EXCLUIR = "notificacaomoeda/excluir";
    const  R_NOTIFICACAO_MOEDA_STATUS_CONCLUIDO = "notificacaomoeda/status/concluido";
    const  R_NOTIFICACAO_MOEDA_STATUS_PUBLICADO = "notificacaomoeda/status/publicado";
    
    const R_MARKETING_IMAGEM_HAS_LIDO = "marketingImagem/marcarComoLido";
    const R_MARKETING_IMAGEM_HAS_LIDO_LISTAR = "marketingImagem/listar";
    const R_MARKETING_IMAGEM_ONLY_VIEW = "marketingImagem/onlyView";
    
    const R_NOTIFICACAO_HAS_MOEDA = "notificacaomoeda/marcarComoLido";
    const  R_NOTIFICACAO_MOEDA_ONLY_VIEW = "notificacaomoedaOnlyView";
    const  R_NOTIFICACAO_MOEDA_ONLY_VIEW_LISTAR = "notificacaomoedaOnlyView/listar";
    
    const R_CLIENTE_ANALISE_INICIAR = "cliente/analise/iniciar";
    const R_CLIENTE_ANALISE_FINALIZAR = "cliente/analise/finalizar";
    const R_CONTASBANCARIAS_LISTAR = "contasbancarias/listar";
    const R_CONTASBANCARIAS_STATUS_ALTERAR = "contasbancarias/status/alterar";
    
    
    const R_DEPOSITO_EXTORNO_INICIAR = "deposito/estorno/iniciar";
    const R_DEPOSITO_EXTORNO_CONTAS = "deposito/estorno/contas";
    const R_DEPOSITO_EXTORNO_SALVAR_DADOS_BANCARIOS = "deposito/estorno/salvarDadosBancarios";
    const R_ESTORNO = "estornos";
    const R_ESTORNO_ANALISAR = "estornos/analisar";
    const R_ESTORNO_LISTAR = "estornos/listar";
    const R_ESTORNO_REJEITAR = "estornos/rejeitar";
    const R_ESTORNO_CANCELAR = "estornos/cancelar";
    const R_ESTORNO_APROVAR = "estornos/aprovar";
    const R_ESTORNO_FINALIZAR = "estornos/finalizar";
    
    const R_CLIENTE_ANALISE_NOTIFICAR = "cliente/analise/notificar";
    
    const R_CONTAS_RESUMO = "contas/resumo";
    const R_CONTAS_RESUMO_LISTARCONTAS = "contas/resumo/listarContas";
    const R_CONTAS_RESUMO_COBRAR = "contas/resumo/cobrar";
    
    const R_CLIENTES_RESUMO = "clientes/resumo";
    const R_CLIENTES_RESUMO_LISTARCONTAS = "clientes/resumo/listarContas";
    
    const R_CONVITES = "convites";
    const R_CONVITES_ENVIAR = "convites/enviar";
    const R_CONVITES_CONVIDADOS_LISTAR = "convites/convidados/listar";
    const R_CLIENTE_EMAIL_REENVIARBOASVINDAS = "cliente/email/reenviarboasvindas";
    const R_CLIENTE_EMAIL_ANALISAR = "cliente/email/analisar";
    const R_CLIENTE_DASHBOARD = "clientes/dados/listarMoedas";
    
    const R_COFRE = "investimento";
    const R_COFRE_TOKEN = "investimento/token";
    const R_COFRE_DEPOSITAR = "investimento/depositar";
    const R_COFRE_RETIRADA_SOLICITAR = "investimento/retirada/solicitar";
    const R_COFRE_RETIRADA_SACAR = "investimento/retirada/sacar";
    const R_COFRE_CONTRATOS_LISTAR = "investimento/contratos/listar";
    
    const R_COFRE_SALDO = "investimento/saldo";
    const R_COFRE_LISTAR = "investimento/filtrarInvestimentos";
    const R_COFRE_RENDIMENTOS_FILTRAR = "investimento/rendimentos/filtrar";
    const R_COFRE_DADOS_EMPRESA = "investimento/dadosEmpresa";
    const R_COFRE_DADOS_GRAFICO = "investimento/dadosGrafico";
    const R_COFRE_ADM_INVESTIMENTOS = "investimento/adm/listar";
    const R_COFRE_RETIRADA_LISTARSOLICITACOES = "investimento/retirada/listarSolicitacoes";
    const R_COFRE_RETIRADA_LISTARSOLICITACOES_MOEDAS = "investimento/retirada/listarSolicitacoesByMoedas";
    
    const R_CLIENTES_COMENTARIOS_SALVAR = "clientes/comentarios/salvar";
    const R_CLIENTES_COMENTARIOS_LISTAR = "clientes/comentarios/listar";
    
    const R_SERVICOS_CATEGORIAS_LISTAR = "servicos/categorias/listar";
    const R_SERVICOS_CATEGORIAS_LISTAROPTIONS = "servicos/categorias/listarOptions";
    const R_SERVICOS_CATEGORIAS_CADASTRO = "servicos/categorias/cadastro";
    const R_SERVICOS_CATEGORIAS_SALVAR = "servicos/categorias/salvar";
    const R_SERVICOS_CATEGORIAS_EXCLUIR = "servicos/categorias/excluir";
    const R_SERVICOS_CATEGORIAS_ALTERARSTATUS = "servicos/categorias/alterarStatus";
    
    const R_BOLETOS_RELATORIOS_CONSUMOPORCATEGORIA = "boletos/relatorios/consumoPorCategoria";
    const R_BOLETOS_RELATORIOS_CONSUMOPORMES = "boletos/relatorios/consumoPorMes";
    const R_BOLETOS_RELATORIOS_CONSUMOPORMESPORCATEGORIA = "boletos/relatorios/consumoPorMesPorCategoria";
    const R_BOLETOS_RELATORIOS_CARREGAREVENTOS = "boletos/relatorios/carregarEventos";
        
    const R_REMESSAS_RELATORIOS_CONSUMOPORCATEGORIA = "remessas/relatorios/consumoPorCategoria";
    const R_REMESSAS_RELATORIOS_CONSUMOPORMES = "remessas/relatorios/consumoPorMes";
    const R_REMESSAS_RELATORIOS_CONSUMOPORMESPORCATEGORIA = "remessas/relatorios/consumoPorMesPorCategoria";
    const R_REMESSAS_RELATORIOS_CARREGAREVENTOS = "remessas/relatorios/carregarEventos";
        
    
    const R_P2P = "p2p";
    const R_P2P_LISTAR = "p2p/listar";
    const R_P2P_CADASTRO = "p2p/cadastro";
    const R_P2P_SALVAR = "p2p/salvar";
    const R_P2P_EXCLUIR = "p2p/excluir";
    const R_P2P_STATUS = "p2p/status";
    
    const R_DISPOSITIVOS_PAREAMENTO = "dispositivos/pareamento";
    const R_DISPOSITIVOS_PAREAR = "dispositivos/parear";
    const R_DISPOSITIVOS_LISTAR = "dispositivos/listar";
    const R_DOCUMENTOS_LISTAR = "meusdados/documentos/listaDocumentos";
    const R_DISPOSITIVOS_ALTERARSTATUS = "dispositivos/alterarStatus";
    
    const R_CNPJ_SALVAR = "meudados/cnpj/salvar";
    const R_CNPJ_MOSTRAR = "meudados/cnpj/mostrar";
    
    const R_AFILIADOS_LISTAR = "afiliados/listar";
    
    
    const R_DASHBOARD_HISTORICO_REMOTEWALLETS = "dashboard/historico/remotewallets";
    const R_DASHBOARD_HISTORICO_REMOTEINVOICES = "dashboard/historico/remoteinvoices";
    
    const R_TELEGRAM_BOTS_LISTAR = "telegram/bots/listar";
    const R_TELEGRAM_BOTS_CADASTRO =  "telegram/bots/cadastro";
    const R_TELEGRAM_BOTS_SALVAR = "telegram/bots/salvar";
    const R_TELEGRAM_GRUPOS_LISTAR = "telegram/grupos/listar";
    const R_TELEGRAM_GRUPOS_CADASTRO = "telegram/grupos/cadastro";
    const R_TELEGRAM_GRUPOS_SALVAR = "telegram/grupos/salvar";
    const R_TELEGRAM_GRUPOS_ALTERARSTATUS = "telegram/grupos/alterarStatus";
    const R_TELEGRAM_BOTS_ALTERARSTATUS = "telegram/bots/alterarStatus";
    const R_TELEGRAM_MENSAGEM_ALTERARSTATUS = "telegram/mensagem/alterarStatus";
    const R_TELEGRAM_MENSAGEM_SALVAR = "telegram/mensagem/salvar";
    
    const R_PREVENDA_CADASTRO = "prevendas/cadastro";
    const R_PREVENDA_SALVAR = "prevendas/salvar";
    const R_PREVENDA_LISTAR = "prevendas/listar";
    const R_PREVENDA_ALTERARSTATUS = "prevendas/alterarstatus";
    const R_PREVENDA_FASE_CADASTRO = "prevendas/fase/cadastro";
    const R_PREVENDA_FASE_SALVAR = "prevendas/fase/salvar";
    const R_PREVENDA_COMPRAR = "prevendas/comprar";
    const R_PREVENDA_SALDODISPONIVEL = "prevendas/getSaldoDisponivel";

    public static function getRota($param) {
        if (!empty($param) && isset(self::$rotas[$param])) {
            return self::$rotas[$param]["url"];
        }
        return "";
    }
    
    public static function getModulo($rota) {
        if (!empty($rota) && isset(self::$rotas[$rota])) {
            return self::$rotas[$rota]["modulo"];
        }
        return "";
    }
    
    public static function getSecondLevel($rota) {
        if (!empty($rota) && isset(self::$rotas[$rota]) && isset(self::$rotas[$rota]["second"])) {
            return self::$rotas[$rota]["second"];
        }
        return "";
    }
    
    
    public static function getRotina($rota) {
        if (!empty($rota) && isset(self::$rotas[$rota])) {
            return self::$rotas[$rota]["rotina"];
        }
        return '';
    }
}
