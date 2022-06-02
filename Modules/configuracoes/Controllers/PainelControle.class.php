<?php

namespace Modules\configuracoes\Controllers;

class PainelControle {
    
    private  $codigoModulo = "configuracoes";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            try {
                $configuracaoRn->conexao->carregar($configuracao);
            } catch (\Exception $ex) {

            }
            
            $moduloRn = new \Models\Modules\Acesso\ModuloRn();
            $moduloDeposito = new \Models\Modules\Acesso\Modulo(Array("id" => 14));
            $moduloSaque = new \Models\Modules\Acesso\Modulo(Array("id" => 15));
            $moduloTransferencia = new \Models\Modules\Acesso\Modulo(Array("id" => 16));
            $moduloNegociacoes = new \Models\Modules\Acesso\Modulo(Array("id" => 13));
            $moduloServicos = new \Models\Modules\Acesso\Modulo(Array("id" => 5));
            $moduloCartoes = new \Models\Modules\Acesso\Modulo(Array("id" => 21));
            $moduloCofre = new \Models\Modules\Acesso\Modulo(Array("id" => 22));
            
            $moduloRn->conexao->carregar($moduloDeposito);
            $moduloRn->conexao->carregar($moduloSaque);
            $moduloRn->conexao->carregar($moduloTransferencia);
            $moduloRn->conexao->carregar($moduloNegociacoes);
            $moduloRn->conexao->carregar($moduloServicos);
            $moduloRn->conexao->carregar($moduloCartoes);
            $moduloRn->conexao->carregar($moduloCofre);
            
            $telegramMensagemAutomaticaRn = new \Models\Modules\Cadastro\TelegramMensagemAutomaticaRn();
            $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
            $telegramGrupoRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
            
            $resultMensagens = $telegramMensagemAutomaticaRn->conexao->listar(null, "id");
            $mensagens = Array();
            foreach ($resultMensagens as $telegramMensagemAutomatica) {
                $mensagens[$telegramMensagemAutomatica->id] = $telegramMensagemAutomatica;
            }
            
            $resultGrupos = $telegramGrupoRn->conexao->listar("ativo > 0", "nome");
            $resultBots = $telegramBotRn->conexao->listar("ativo > 0", "nome");
            
            $grupos = Array();
            foreach ($resultGrupos as $telegramGrupo) {
                $grupos[] = $telegramGrupo;
            }
            $bots = Array();
            foreach ($resultBots as $telegramBot) {
                $bots[] = $telegramBot;
            }
            
            $params["mensagensTelegram"] = $mensagens;
            $params["botsTelegram"] = $bots;
            $params["gruposTelegram"] = $grupos;
            $params["configuracao"] = $configuracao;
            
            $params["moduloDeposito"] = $moduloDeposito;
            $params["moduloSaque"] = $moduloSaque;
            $params["moduloTransferencia"] = $moduloTransferencia;
            $params["moduloNegociacoes"] = $moduloNegociacoes;
            $params["moduloServicos"] = $moduloServicos;
            $params["moduloCartoes"] = $moduloCartoes;
            $params["moduloCofre"] = $moduloCofre;
            
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("configuracoes", $params);
    }
    
    public function salvar($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PAINELCONTROLE, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar as configurações");
            }
           
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao();
            
            $configuracao->percentualVenda = \Utils\Post::getNumeric($params, "percentualVenda", 0);
            $configuracao->percentualCompra = \Utils\Post::getNumeric($params, "percentualCompra", 0);
            $configuracao->percentualCompraPassiva = \Utils\Post::getNumeric($params, "percentualCompraPassiva", 0);
            $configuracao->percentualVendaPassiva = \Utils\Post::getNumeric($params, "percentualVendaPassiva", 0);
            $configuracao->percentualDepositos = \Utils\Post::getNumeric($params, "percentualDepositos", 0);
            $configuracao->depositoDoisCinco = \Utils\Post::getNumeric($params, "depositoDoisCinco", 0);
            $configuracao->depositoCincoDez = \Utils\Post::getNumeric($params, "depositoCincoDez", 0);
            $configuracao->depositoDezCinquenta = \Utils\Post::getNumeric($params, "depositoDezCinquenta", 0);
            $configuracao->depositoCinquentaAcima = \Utils\Post::getNumeric($params, "depositoCinquentaAcima", 0);
            $configuracao->emailNome = \Utils\Post::get($params, "emailNome", "");
            $configuracao->emailPorta = \Utils\Post::get($params, "emailPorta", 0);
            $configuracao->emailSenha = \Utils\Post::get($params, "emailSenha", "");
            $configuracao->emailSmtp = \Utils\Post::get($params, "emailSmtp", "");
            $configuracao->emailUsuario = \Utils\Post::get($params, "emailUsuario", "");
            $configuracao->valorMensalidadeCartao = \Utils\Post::getNumeric($params, "valorMensalidadeCartao", 0);
            $configuracao->valorCartao = \Utils\Post::getNumeric($params, "valorCartao", 0);
            $configuracao->valorCartaoEx = \Utils\Post::getNumeric($params, "valorCartaoEx", 0);
            $configuracao->emailSmtpAuth = \Utils\Post::getBooleanAsInt($params, "emailSmtpAuth", "f");
           
            $configuracao->taxaTransferenciaInternaBtc = \Utils\Post::getNumeric($params, "taxaTransferenciaInternaBtc", 0);
            $configuracao->taxaTransferenciaInternaReais = \Utils\Post::getNumeric($params, "taxaTransferenciaInternaReais", 0);
            $configuracao->taxaSaque =  \Utils\Post::getNumeric($params, "taxaSaque", 0);
            $configuracao->taxaDeposito =  \Utils\Post::getNumeric($params, "taxaDeposito", 0);
            $configuracao->tarifaTed = \Utils\Post::getNumeric($params, "tarifaTed", 0);
            $configuracao->tempoRecargaCartao =  \Utils\Post::get($params, "tempoRecargaCartao", "");
            $configuracao->taxaRecargaCartao =  \Utils\Post::getNumeric($params, "taxaRecargaCartao", 0);
            $configuracao->valorMaximoRemessa =  \Utils\Post::getNumeric($params, "valorMaximoRemessa", 0);
            $configuracao->valorMinimoRemessa =  \Utils\Post::getNumeric($params, "valorMinimoRemessa", 0);
            $configuracao->taxaRemessa =  \Utils\Post::getNumeric($params, "taxaRemessa", 0);
            $configuracao->prazoEfetuacaoRemessa =  \Utils\Post::get($params, "prazoEfetuacaoRemessa", "");
            $configuracao->valorMaximoBoleto =  \Utils\Post::getNumeric($params, "valorMaximoBoleto", 0);
            $configuracao->taxaBoleto =  \Utils\Post::getNumeric($params, "taxaBoleto", 0);
            $configuracao->valorMinimoBoleto =  \Utils\Post::getNumeric($params, "valorMinimoBoleto", 0);
            $configuracao->prazoPagamentoBoleto =  \Utils\Post::get($params, "prazoPagamentoBoleto", "");
            $configuracao->taxaAdesaoClube =  \Utils\Post::getNumeric($params, "taxaAdesaoClube", 0);
            $configuracao->mensalidadeClube =  \Utils\Post::getNumeric($params, "mensalidadeClube", 0);
            $configuracao->qtdMinConfirmacoesTransacao =  \Utils\Post::get($params, "qtdMinConfirmacoesTransacao", "");
            $configuracao->qtdMaxConfirmacoesTransacao =  \Utils\Post::get($params, "qtdMaxConfirmacoesTransacao", "");
            $configuracao->valorMaximoSaqueBtc =  \Utils\Post::getNumeric($params, "valorMaximoSaqueBtc", 0);
            
            
            $configuracao->statusNovosCadastros = \Utils\Post::getBooleanAsInt($params, "statusNovosCadastros", "0");
            $configuracao->statusLoginSistema = \Utils\Post::getBooleanAsInt($params, "statusLoginSistema", "0");
            $configuracao->statusUpgradePerfil = \Utils\Post::getBooleanAsInt($params, "statusUpgradePerfil", "0");
            $configuracao->exibirMensagemSite = \Utils\Post::getBooleanAsInt($params, "exibirMensagemSite", "0");
            $configuracao->statusCarteiras = \Utils\Post::getBooleanAsInt($params, "statusCarteiras", "0");
            $configuracao->statusTransferenciaBrl = \Utils\Post::getBooleanAsInt($params, "statusTransferenciaBrl", "0");
            $configuracao->statusTransferenciaCurrency = \Utils\Post::getBooleanAsInt($params, "statusTransferenciaCurrency", "0");
            $configuracao->statusDepositoBrl = \Utils\Post::getBooleanAsInt($params, "statusDepositoBrl", "0");
            $configuracao->statusDepositoCurrency = \Utils\Post::getBooleanAsInt($params, "statusDepositoCurrency", "0");
            
            $configuracao->alertaSite = \Utils\Post::get($params, "alertaSite", null);
            $configuracao->tipoAlertaSite = \Utils\Post::get($params, "tipoAlertaSite", null);
            $configuracao->dataFinalAlertaSite = \Utils\Post::getData($params, "dataFinalAlertaSite", null, "23:59:59");
            
            $configuracao->valorMinSaqueReais = \Utils\Post::getNumeric($params, "valorMinSaqueReais", 0);
            $configuracao->valorMinimoNegociacaoBrl = \Utils\Post::getNumeric($params, "valorMinimoNegociacaoBrl", 0);
            $configuracao->volumeMinimoNegociacaoBtc = \Utils\Post::getNumeric($params, "volumeMinimoNegociacaoBtc", 0);
            
            $configuracao->comissaoUsoCofre = \Utils\Post::getNumeric($params, "comissaoUsoCofre", 0);
            $configuracao->prazoDiasSaqueCofre = \Utils\Post::get($params, "prazoDiasSaqueCofre", 0);
            $configuracao->percentualEstornoDeposito = \Utils\Post::getNumeric($params, "percentualEstornoDeposito", 0);
            $configuracao->taxaRedeBtc = \Utils\Post::getNumeric($params, "taxaRedeBtc", 0);
            $configuracao->taxaDepositoBoleto = \Utils\Post::getNumeric($params, "taxaDepositoBoleto", 0);
            $configuracao->tarifaDepositoBoleto = \Utils\Post::getNumeric($params, "tarifaDepositoBoleto", 0);
            
            
            $configuracao->qtdCasasDecimais = \Utils\Post::get($params, "qtdCasasDecimais", 0);
            $configuracao->prazoHorasAtendimento = \Utils\Post::get($params, "prazoHorasAtendimento", 0);
            $configuracao->prazoHorasValidacaoDepositos = \Utils\Post::get($params, "prazoHorasValidacaoDepositos", 0);
            $configuracao->prazoHorasValidacaoSaques = \Utils\Post::get($params, "prazoHorasValidacaoSaques", 0);
            $configuracao->comissaoConvite =  \Utils\Post::getNumeric($params, "comissaoConvite", 0);
            $configuracao->prazoDiasContratoCofre =  \Utils\Post::get($params, "prazoDiasContratoCofre", 0);
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
           
            $configuracaoRn->salvar($configuracao);
            
            
            $moduloRn = new \Models\Modules\Acesso\ModuloRn();
            
            $moduloDeposito = new \Models\Modules\Acesso\Modulo(Array("id" => 14));
            $moduloDeposito->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-deposito", "0");
            $moduloSaque = new \Models\Modules\Acesso\Modulo(Array("id" => 15));
            $moduloSaque->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-saque", "0");
            $moduloTransferencia = new \Models\Modules\Acesso\Modulo(Array("id" => 16));
            $moduloTransferencia->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-transferencia", "0");
            $moduloNegociacoes = new \Models\Modules\Acesso\Modulo(Array("id" => 13));
            $moduloNegociacoes->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-negociacoes", "0");
            $moduloServicos = new \Models\Modules\Acesso\Modulo(Array("id" => 5));
            $moduloServicos->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-servicos", "0");
            $moduloCartoes = new \Models\Modules\Acesso\Modulo(Array("id" => 21));
            $moduloCartoes->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-cartoes", "0");
            $moduloCofre = new \Models\Modules\Acesso\Modulo(Array("id" => 22));
            $moduloCofre->visivelCliente = \Utils\Post::getBooleanAsInt($params, "modulo-cofre", "0");
            
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloNegociacoes->visivelCliente), Array("id" => $moduloNegociacoes->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloDeposito->visivelCliente), Array("id" => $moduloDeposito->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloSaque->visivelCliente), Array("id" => $moduloSaque->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloTransferencia->visivelCliente), Array("id" => $moduloTransferencia->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloServicos->visivelCliente), Array("id" => $moduloServicos->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloCartoes->visivelCliente), Array("id" => $moduloCartoes->id));
            $moduloRn->conexao->update(Array("visivel_cliente" => $moduloCofre->visivelCliente), Array("id" => $moduloCofre->id));
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
}