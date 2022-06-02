<?php

namespace Models\Modules\Cadastro;
set_time_limit(200);

use Io\BancoDados;
use \Models\Modules\Model\GenericModel;

/**
 * Classe que contém as regras de negócio da entidade Cliente
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteRn
{

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
    public $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null)
    {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Cliente());
        } else {
            $this->conexao = new GenericModel($adapter, new Cliente());
        }
    }

    public function salvar(Cliente &$cliente, $confirmacaoSenha = null, $permissoesRotinas = array(), $permissoesModulos = array(), $alterarPermissoes = true)
    {
        $db_ = new BancoDados();
        $configuracao = new Configuracao(array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn($db_);
        $configuracaoRn->conexao->carregar($configuracao);

        $emailBoasVindas = false;
        if (!empty($cliente->senha)) {

            if (empty($confirmacaoSenha)) {
                throw new \Exception($this->idioma->getText("necessarioConfirmarSenha"));
            }

            if ($cliente->senha != $confirmacaoSenha) {
                throw new \Exception($this->idioma->getText("aSenhaConfirmacaoDevemIguais"));
            }
        }


        if (!\Utils\Validacao::email($cliente->email)) {
            throw new \Exception($this->idioma->getText("emailInvalido"));
        }

        $result = $this->conexao->listar("email = '{$cliente->email}' AND id != {$cliente->id}", NULL, NULL, 1);

        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("oEmailJaCadastrado"));
        }

        /*$usuario = \Utils\Geral::getLogado();*/

        if ($cliente->id > 0) {

            $aux = new Cliente(array("id" => $cliente->id));
            $this->conexao->carregar($aux);

            $cliente->idPromocao = $aux->idPromocao;
            $cliente->status = $aux->status;
            $cliente->idUsuario = $aux->idUsuario;

            if ($configuracao->kyc == 1) {
                $cliente->nome = $aux->nome;
            }

            if ($aux->fotoDocumentoVerificada > 0 && $configuracao->kyc == 1) {
                $cliente->documento = $aux->documento;
                $cliente->celular = $aux->celular;
                $cliente->dataNascimento = $aux->dataNascimento;
                $cliente->email = $aux->email;
                $cliente->idPaisNaturalidade = $aux->idPaisNaturalidade;
                $cliente->documentoTipo = $aux->documentoTipo;
                $cliente->utilizaSaqueDepositoBrl = $aux->utilizaSaqueDepositoBrl;
            }

            $cliente->origemCadastro = $aux->origemCadastro;
            $cliente->quantidadeTentativasSegundoFator = $aux->quantidadeTentativasSegundoFator;
            $cliente->dataUltimaTentativaSegundoFator = $aux->dataUltimaTentativaSegundoFator;

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $cliente->googleAuthAtivado = $aux->googleAuthAtivado;
                $cliente->googleAuthSecret = $aux->googleAuthSecret;
            } else {
                $cliente->googleAuthAtivado = 0;
                $cliente->googleAuthSecret = null;
            }


            $cliente->retornoAnaliseEmail = $aux->retornoAnaliseEmail;
            $cliente->hashValidacaoEmail = $aux->hashValidacaoEmail;
            $cliente->validadeHashValidacaoEmail = $aux->validadeHashValidacaoEmail;
            $cliente->hashRecuperacaoSenha = $aux->hashRecuperacaoSenha;
            $cliente->validadeHashRecuperacaoSenha = $aux->validadeHashRecuperacaoSenha;
            $cliente->quantidadeTentativasRecuperacao = $aux->quantidadeTentativasRecuperacao;
            $cliente->quantidadeTentativasLogin = $aux->quantidadeTentativasLogin;
            $cliente->bloquearLogin = $aux->bloquearLogin;
            $cliente->bloquearRecuperacaoSenha = $aux->bloquearRecuperacaoSenha;

            if ($cliente->analiseCliente > 0) {

                if ($aux->idAnaliseClienteAdm > 0) {
                    $cliente->idAnaliseClienteAdm = $aux->idAnaliseClienteAdm;
                } else {
                    if ($usuario != null && ($usuario->id != $cliente->id)) {
                        $cliente->idAnaliseClienteAdm = $usuario->id;
                    } else {
                        $cliente->idAnaliseClienteAdm = null;
                    }
                }
            } else {
                $cliente->idAnaliseClienteAdm = null;
            }

            if ($cliente->bloquearLogin == 1 && $aux->bloquearLogin == 1) {
                $cliente->status = 1;
                $cliente->bloquearLogin = 0;
                $cliente->quantidadeTentativasLogin = 0;
            }


            if (empty($cliente->comissao)) {
                $cliente->comissao = $aux->comissao;
            }

            if (empty($cliente->pin)) {
                $cliente->pin = $aux->pin;
            }

            if (empty($cliente->senha)) {
                $cliente->senha = $aux->senha;
            } else {
                $cliente->senha = sha1($cliente->senha . \Utils\Constantes::SEED_SENHA);
            }

            if (empty($cliente->fotoCliente)) {
                $cliente->fotoCliente = $aux->fotoCliente;
            }

            if (empty($cliente->fotoDocumento)) {
                $cliente->fotoDocumento = $aux->fotoDocumento;
            }


            if (empty($cliente->fotoDocumentoVerso)) {
                $cliente->fotoDocumentoVerso = $aux->fotoDocumentoVerso;
            }

            if (empty($cliente->fotoResidencia)) {
                $cliente->fotoResidencia = $aux->fotoResidencia;
            }

            if (empty($cliente->fotoOutroDocumento)) {
                $cliente->fotoOutroDocumento = $aux->fotoOutroDocumento;
            }

            if (empty($cliente->codigoPais)) {
                $cliente->codigoPais = $aux->codigoPais;
            }

            $cliente->dataExpiracao = $aux->dataExpiracao;
            $cliente->dataCadastro = $aux->dataCadastro;


            $cliente->fotoClienteVerificada = $aux->fotoClienteVerificada;
            $cliente->fotoDocumentoVerificada = $aux->fotoDocumentoVerificada;
            $cliente->fotoResidenciaVerificada = $aux->fotoResidenciaVerificada;
            $cliente->fotoOutroDocumentoVerificada = $aux->fotoOutroDocumentoVerificada;

            if (empty($cliente->foto)) {
                $cliente->foto = $aux->foto;
            }

            $cliente->idReferencia = $aux->idReferencia;


            // variáveis que se não forem atualizadas devem permanecer conforme configuradas
            if ($cliente->taxaComissaoSaque == null) {
                $cliente->taxaComissaoSaque = $aux->taxaComissaoSaque;
            }

            if ($cliente->taxaComissaoDeposito == null) {
                $cliente->taxaComissaoDeposito = $aux->taxaComissaoDeposito;
            }


            if ($cliente->taxaComissaoTransfenciaCurrency == null) {
                $cliente->taxaComissaoTransfenciaCurrency = $aux->taxaComissaoTransfenciaCurrency;
            }

            $cliente->recebimentoAlertaMovimentacaoConta = $aux->recebimentoAlertaMovimentacaoConta;
            $cliente->emailConfirmado = $aux->emailConfirmado;

            $cliente->considerarTaxaDepositoCliente = ($cliente->considerarTaxaDepositoCliente != null ? $cliente->considerarTaxaDepositoCliente : $aux->considerarTaxaDepositoCliente);
            $cliente->considerarTaxaSaqueCliente = ($cliente->considerarTaxaSaqueCliente != null ? $cliente->considerarTaxaSaqueCliente : $aux->considerarTaxaSaqueCliente);
            $cliente->considerarTaxaTransferenciaCurrency = ($cliente->considerarTaxaTransferenciaCurrency != null ? $cliente->considerarTaxaTransferenciaCurrency : $aux->considerarTaxaTransferenciaCurrency);

            $cliente->statusFranquia = $aux->statusFranquia;
            $cliente->statusMercado = $aux->statusMercado;

            $cliente->statusSaqueCurrency = $aux->statusSaqueCurrency;
            $cliente->qtdEnviosEmailsRecuperacao = $aux->qtdEnviosEmailsRecuperacao;


            $cliente->emAnalise = $aux->emAnalise;
            $cliente->dataInicioAnalise = $aux->dataInicioAnalise;
            $cliente->dataFimAnalise = $aux->dataFimAnalise;
            $cliente->idUsuarioInicioAnalise = $aux->idUsuarioInicioAnalise;
            $cliente->idUsuarioTerminoAnalise = $aux->idUsuarioTerminoAnalise;
            $cliente->idClienteConvite = $aux->idClienteConvite;
            $cliente->comissaoConvitePago = $aux->comissaoConvitePago;

            $cliente->ultimaAtividade = $aux->ultimaAtividade;
            $cliente->dataUltimoLogin = $aux->dataUltimoLogin;
            $cliente->ipUltimoAcesso = $aux->ipUltimoAcesso;
            $cliente->webkit = $aux->webkit;
            $cliente->ipCadastro = $aux->ipCadastro;
            $cliente->dataUltimaTentativaSegundoFator = null;
            $cliente->dataUltimaTentativaLogin = null;


            if (!is_numeric($cliente->clienteP2p)) {
                $cliente->clienteP2p = $aux->clienteP2p;
            }

            $cliente->apiKey = $aux->apiKey;
            $cliente->clientid = $aux->clientid;

        } else {

            //$cliente->apiKey = \Utils\Criptografia::token($cliente->nome);
            //$cliente->clientid = \Utils\Criptografia::userid($cliente->apiKey);


            $cliente->clienteP2p = 0;
            if (empty($cliente->origemCadastro)) {
                $cliente->origemCadastro = \Utils\Constantes::ORIGEM_SITE;
            }


            if ($cliente->tipoTaxaCarteiraRemota == null) {
                $cliente->tipoTaxaCarteiraRemota = "p";
            }

            if ($cliente->tipoTaxaInvoicePdv == null) {
                $cliente->tipoTaxaInvoicePdv = "p";
            }

            $cliente->idPromocao = null;
            $cliente->hashValidacaoEmail = null;
            $cliente->validadeHashValidacaoEmail = null;
            $cliente->hashRecuperacaoSenha = null;
            $cliente->validadeHashRecuperacaoSenha = null;
            $cliente->quantidadeTentativasRecuperacao = 0;
            $cliente->quantidadeTentativasLogin = 0;
            $cliente->bloquearLogin = 0;
            $cliente->bloquearRecuperacaoSenha = 0;

            $cliente->analiseCliente = 0;
            $cliente->idAnaliseClienteAdm = null;

            $cliente->emailConfirmado = 0;
            $cliente->recebimentoAlertaMovimentacaoConta = "S";

            $cliente->ultimaAtividade = null;
            $cliente->dataUltimoLogin = null;
            /*$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];*/
            $ip = '';
            if (strpos($ip, ',') !== false) {
                $ip = substr($ip, 0, strpos($ip, ','));
            }


            $cliente->ipUltimoAcesso = $ip;
            $cliente->webkit = null;

            if ($cliente->status == null) {
                $cliente->status = 0;
            }
            if ($cliente->cardReload == null) {
                $cliente->cardReload = 0;
            }

            if (!is_numeric($cliente->comissao) || !$cliente->comissao > 0) {
                $cliente->comissao = 0;
            }

            if (empty($cliente->codigoPais)) {
                $cliente->codigoPais = "076";
            }

            //$senhaTemp = substr(sha1($cliente->email . \Utils\Constantes::SEED_SENHA), 0, 10);
            //$cliente->senha = sha1($senhaTemp.\Utils\Constantes::SEED_SENHA);
            $cliente->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $cliente->fotoClienteVerificada = 0;
            $cliente->fotoDocumentoVerificada = 0;
            $cliente->fotoResidenciaVerificada = 0;
            $cliente->fotoOutroDocumentoVerificada = 0;

            $cliente->taxaComissaoDeposito = 0;
            $cliente->taxaComissaoSaque = 0;
            $cliente->taxaComissaoTransfenciaCurrency = 0;
            $cliente->considerarTaxaDepositoCliente = 0;
            $cliente->considerarTaxaSaqueCliente = 0;
            $cliente->considerarTaxaTransferenciaCurrency = 0;

            $cliente->statusFranquia = 0;

            $cliente->statusMercado = 1;
            $cliente->qtdEnviosEmailsRecuperacao = 0;

            $cliente->statusDepositoBrl = 1;
            $cliente->statusDepositoCurrency = 1;
            $cliente->statusSaqueBrl = 1;
            $cliente->statusSaqueCurrency = 1;
            $cliente->statusResgatePdv = 1;

            $cliente->emAnalise = 0;
            $cliente->dataInicioAnalise = null;
            $cliente->dataFimAnalise = null;
            $cliente->idUsuarioInicioAnalise = null;
            $cliente->idUsuarioTerminoAnalise = null;

            $cliente->idMoedaAtual = 1;

            $cliente->googleAuthAtivado = 0;
            $cliente->googleAuthSecret = null;
            $cliente->quantidadeTentativasSegundoFator = 0;
            $cliente->dataUltimaTentativaSegundoFator = null;

            $cliente->modoOperacao = \Utils\Constantes::MODO_TRADER;

            $cliente->documentoVerificado = 0;

            $emailBoasVindas = true;
        }

        if (!is_numeric($cliente->clienteP2p)) {
            $cliente->clienteP2p = 0;
        }

        $cliente->documentoTipo = \Utils\Constantes::DOCUMENTO_CPF;

        if ($cliente->dataNascimento != null) {
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            $diff = $dataAtual->diferenca($cliente->dataNascimento);

            if ($diff->y < 18) {
                throw new \Exception($this->idioma->getText("idadeSuperior18Anos"));
            }
        }

        if (!$cliente->taxaTransferenciaRemota > 0) {
            $cliente->taxaTransferenciaRemota = 0;
        }

        if (!$cliente->taxaInvoicesPdv > 0) {
            $cliente->taxaInvoicesPdv = 0;
        }

        if (!is_numeric($cliente->forcarAnaliseSaques)) {
            $cliente->forcarAnaliseSaques = 0;
        }

        if (!empty($cliente->pin) && strlen($cliente->pin) != 4) {
            throw new \Exception($this->idioma->getText("pinDeveTer4Digitos"));
        }

        if (empty($cliente->senha)) {
            $cliente->senha = null;
        }

        if (empty($cliente->email)) {
            throw new \Exception($this->idioma->getText("informarEmailCliente"));
        }


        if (!empty($cliente->documento)) {
            if (!\Utils\Validacao::cpf($cliente->documento)) {
                throw new \Exception($this->idioma->getText("cpfInvalido"));
            }

            /* $resultCpf = $this->conexao->listar("documento = '{$cliente->documento}' AND id != {$cliente->id}", NULL, NULL, 1);
             if (sizeof($resultCpf) > 0) {
                 throw new \Exception($this->idioma->getText("cpfJaCadastrado"));
             }*/
        }

        if (empty($cliente->nome)) {
            throw new \Exception($this->idioma->getText("necessarioInformarNomeCliente"));
        }


        if ($cliente->cardReload == null) {
            $cliente->cardReload = 0;
        }


        if (empty($cliente->sexo)) {
            //$consultaCpfRn = new ConsultaCpfRn();
            $consulta = null;//$consultaCpfRn->getByCpf($cliente->documento);
            if ($consulta != null) {
                switch (strlen($consulta->genero)) {
                    case "masculino":
                        $cliente->sexo = "M";
                        break;
                    case "m":
                        $cliente->sexo = "M";
                        break;
                    case "feminino":
                        $cliente->sexo = "F";
                        break;
                    case "outros":
                        $cliente->sexo = "O";
                        break;

                    default:
                        $cliente->sexo = "M";
                        break;
                }
            } else {
                $cliente->sexo = "M";
            }

        }

        if (!empty($cliente->celular)) {

            if (empty($cliente->ddi)) {
                throw new \Exception($this->idioma->getText("cadastrarDDi"));
            }

            $cel = str_replace(array("(", ")", " ", "-"), "", $cliente->celular);
            if (strlen($cel) != 10 && strlen($cel) != 11) {
                throw new \Exception($this->idioma->getText("celularInvalido"));
            }
        }


        $tiposAuth = array(
            \Utils\Constantes::TIPO_AUTH_EMAIL,
            \Utils\Constantes::TIPO_AUTH_SMS,
            \Utils\Constantes::TIPO_AUTH_GOOGLE
        );

        if (!in_array($cliente->tipoAutenticacao, $tiposAuth)) {
            $cliente->tipoAutenticacao = \Utils\Constantes::TIPO_AUTH_EMAIL;
        }

        $cliente->twoFactorAuth = 1;

        if (!is_numeric($cliente->comissaoConvitePago) && !$cliente->comissaoConvitePago > 0) {
            $cliente->comissaoConvitePago = 0;
        }

        try {
            // Criptografo os dados de segurança 
            $cliente->pin = $cliente->getEncriptedPin();
            $cliente->fraseSeguranca = $cliente->getEncriptedFraseSeguranca();

            $cliente->nome = strtoupper($cliente->nome);
            $cliente->retornoAnaliseEmail;

            //Salvar cliente

            $this->conexao->salvar($cliente);


            /*if (\Utils\Geral::isCliente() && \Utils\Geral::getCliente()->id == $cliente->id) {
                $usuario = (\Utils\Geral::isUsuario() ? \Utils\Geral::getLogado() : null);
                \Utils\Geral::setLogado($usuario, $cliente);
            }*/


            if ($alterarPermissoes) {
                $permissaoClienteRn = new \Models\Modules\Acesso\PermissaoClienteRn($db_);
                $permissaoClienteRn->salvar($cliente, $permissoesRotinas);
                $permissaoModuloClienteRn = new \Models\Modules\Acesso\PermissaoModuloClienteRn($db_);
                $permissaoModuloClienteRn->salvar($cliente, $permissoesModulos);
            }



        } catch (\Exception $e) {

            throw new \Exception(\Utils\Excecao::mensagem($e));
        }

    }


    function pagarAirDropPromocaoICONEWC(Cliente $cliente, $descricao, $pagarReferencia = false)
    {
        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));

        $dataInicio = \Utils\Constantes::getDataInicioICO();

        if ($dataAtual->maior($dataInicio)) {
            $moedas = array(
                array("moeda" => 33, "volume" => 50)
            );

            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
            $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn($this->conexao->adapter);
            $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn($this->conexao->adapter);
            $faseAtual = $faseIcoRn->getFaseIco(\Utils\Constantes::ID_ICO);

            foreach ($moedas as $dados) {

                $moeda = new Moeda(array("id" => $dados["moeda"]));
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);

                $contaCorrenteBtc = new ContaCorrenteBtc();
                $contaCorrenteBtc->id = 0;
                $contaCorrenteBtc->autorizada = 1;
                $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->descricao = "{$descricao} {$moeda->nome}";
                $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                $contaCorrenteBtc->enderecoBitcoin = "";
                $contaCorrenteBtc->executada = 1;
                $contaCorrenteBtc->origem = 5;
                $contaCorrenteBtc->idCliente = $cliente->id;
                $contaCorrenteBtc->idMoeda = $dados["moeda"]; // NEOT
                $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteBtc->transferencia = 0;
                $contaCorrenteBtc->valor = number_format($dados["volume"], 8, ".", "");
                $contaCorrenteBtc->valorTaxa = 0;
                $contaCorrenteBtc->orderBook = 0;

                $contaCorrenteBtcRn->salvar($contaCorrenteBtc, NULL);

                $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
                $contaCorrenteEmpresaBtc->id = 0;
                $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteEmpresaBtc->descricao = "{$descricao} {$moeda->nome} Cliente {$cliente->nome}";
                $contaCorrenteEmpresaBtc->idMoeda = $dados["moeda"]; // NEOT
                $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
                $contaCorrenteEmpresaBtc->transferencia = 0;
                $contaCorrenteEmpresaBtc->airdrop = 1;
                $contaCorrenteEmpresaBtc->valor = number_format($dados["volume"], 8, ".", "");

                $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
                $faseIcoRn->incrementarTokensComprados($faseAtual, $dados["volume"]);


                $distribuicaoToken = new \Models\Modules\ICO\DistribuicaoToken();
                $distribuicaoToken->id = 0;
                $distribuicaoToken->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $distribuicaoToken->idFase = $faseAtual->id;
                $distribuicaoToken->idCliente = $cliente->id;
                $distribuicaoToken->volumeToken = number_format($contaCorrenteBtc->valor, 8, ".", "");
                $distribuicaoToken->valorTotal = number_format($contaCorrenteBtc->valor * 0.00001550, 8, ".", "");
                $distribuicaoToken->idMoeda = 2;
                $distribuicaoToken->idIco = 1;
                $distribuicaoToken->preco = number_format(0.00001550, 8, ".", "");
                $distribuicaoToken->tipo = 2;

                $distribuicaoTokenRn->salvar($distribuicaoToken);

                if ($cliente->idReferencia > 0 && $pagarReferencia) {

                    $referencia = new Cliente(array("id" => $cliente->idReferencia));
                    $this->conexao->carregar($referencia);

                    $contaCorrenteBtc = new ContaCorrenteBtc();
                    $contaCorrenteBtc->id = 0;
                    $contaCorrenteBtc->autorizada = 1;
                    $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteBtc->descricao = "Bônus de cadastro de Indicado {$moeda->nome}";
                    $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                    $contaCorrenteBtc->enderecoBitcoin = "";
                    $contaCorrenteBtc->executada = 1;
                    $contaCorrenteBtc->origem = 5;
                    $contaCorrenteBtc->idCliente = $referencia->id;
                    $contaCorrenteBtc->idMoeda = $dados["moeda"]; // NEOT
                    $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                    $contaCorrenteBtc->transferencia = 0;
                    $contaCorrenteBtc->valor = number_format($dados["volume"], 8, ".", "");
                    $contaCorrenteBtc->valorTaxa = 0;
                    $contaCorrenteBtc->orderBook = 0;

                    $contaCorrenteBtcRn->salvar($contaCorrenteBtc, NULL);

                    $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
                    $contaCorrenteEmpresaBtc->id = 0;
                    $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteEmpresaBtc->descricao = "Bônus de cadastro referência Cliente {$referencia->nome}";
                    $contaCorrenteEmpresaBtc->idMoeda = $dados["moeda"]; // NEOT
                    $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
                    $contaCorrenteEmpresaBtc->transferencia = 0;
                    $contaCorrenteEmpresaBtc->airdrop = 1;
                    $contaCorrenteEmpresaBtc->valor = number_format($dados["volume"], 8, ".", "");

                    $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
                    $faseIcoRn->incrementarTokensComprados($faseAtual, $dados["volume"]);

                    $distribuicaoToken = new \Models\Modules\ICO\DistribuicaoToken();
                    $distribuicaoToken->id = 0;
                    $distribuicaoToken->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $distribuicaoToken->idFase = $faseAtual->id;
                    $distribuicaoToken->idCliente = $referencia->id;
                    $distribuicaoToken->volumeToken = number_format($contaCorrenteBtc->valor, 8, ".", "");
                    $distribuicaoToken->valorTotal = number_format($contaCorrenteBtc->valor * 0.00001550, 8, ".", "");
                    $distribuicaoToken->idMoeda = 2;
                    $distribuicaoToken->idIco = 1;
                    $distribuicaoToken->preco = number_format(0.00001550, 8, ".", "");
                    $distribuicaoToken->tipo = 2;

                    $distribuicaoTokenRn->salvar($distribuicaoToken);
                }

            }
        }
    }


    function pagarAirDropCadastroValidacao(Cliente $cliente, $descricao)
    {
        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));

        $dataInicio = new \Utils\Data("05/11/2018 12:00:00");
        $dataLimite = new \Utils\Data("20/11/2018 23:59:59");

        if ($dataAtual->menor($dataLimite)) {
            $moedas = array(/*Array("moeda" => 30, "volume" => 50)*/
            );


            foreach ($moedas as $dados) {

                $moeda = new Moeda(array("id" => $dados["moeda"]));
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);

                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                $result = $contaCorrenteBtcRn->conexao->listar("id_cliente = {$cliente->id} AND id_moeda = {$moeda->id} AND origem = 7 AND data_cadastro BETWEEN '{$dataInicio->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataLimite->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ");


                if (sizeof($result) < 2) {
                    $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                    $saldoNeot = $contaCorrenteEmpresaBtcRn->calcularSaldoContaAirdrop($dados["moeda"]);

                    if ($dados["volume"] > $saldoNeot) {
                        $dados["volume"] = $saldoNeot;
                    }

                    if ($dados["volume"] > 0) {
                        $contaCorrenteBtc = new ContaCorrenteBtc();
                        $contaCorrenteBtc->id = 0;
                        $contaCorrenteBtc->autorizada = 1;
                        $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtc->descricao = "{$descricao} {$moeda->nome}";
                        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                        $contaCorrenteBtc->enderecoBitcoin = "";
                        $contaCorrenteBtc->executada = 1;
                        $contaCorrenteBtc->origem = 7;
                        $contaCorrenteBtc->idCliente = $cliente->id;
                        $contaCorrenteBtc->idMoeda = $dados["moeda"]; // NEOT
                        $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteBtc->transferencia = 0;
                        $contaCorrenteBtc->valor = number_format($dados["volume"], 8, ".", "");
                        $contaCorrenteBtc->valorTaxa = 0;
                        $contaCorrenteBtc->orderBook = 0;

                        $contaCorrenteBtcRn->salvar($contaCorrenteBtc, NULL);

                        $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
                        $contaCorrenteEmpresaBtc->id = 0;
                        $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteEmpresaBtc->descricao = "{$descricao} {$moeda->nome} Cliente {$cliente->nome}";
                        $contaCorrenteEmpresaBtc->idMoeda = $dados["moeda"]; // NEOT
                        $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
                        $contaCorrenteEmpresaBtc->transferencia = 0;
                        $contaCorrenteEmpresaBtc->airdrop = 1;
                        $contaCorrenteEmpresaBtc->valor = number_format($dados["volume"], 8, ".", "");

                        $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);

                        $saldoNeot = $contaCorrenteEmpresaBtcRn->calcularSaldoContaAirdrop($dados["moeda"]);
                        if ($saldoNeot < 0) {
                            $contaCorrenteBtcRn->excluir($contaCorrenteBtc);
                            $contaCorrenteEmpresaBtcRn->excluir($contaCorrenteEmpresaBtc);
                        }

                    }
                }
            }
        }
    }


    public function filtrar($status = "T", $filtro = null, $statusDocumentos = "A", $idLicenca = 0, $ordenacao = "A", \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipoData = "A", $online = "T")
    {

        $where = array();


        if ($status != "T") {
            if (in_array($status, array(0, 1, 2, 4))) {
                $where[] = " c.status = {$status} ";
            }
            if (in_array($status, array(5))) {
                $where[] = " c.analise_cliente = 1 ";
            }

        }

        if (!empty($filtro)) {
            $where[] = " (  "
                . " (LOWER(c.email) LIKE LOWER('%{$filtro}%'))  OR  "
                . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%'))   OR "
                . " (LOWER(c.celular) LIKE LOWER('%{$filtro}%'))   OR "
                . " (LOWER(c.telefone) LIKE LOWER('%{$filtro}%'))   OR "
                . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                . " (LOWER(c.cnpj) LIKE LOWER('%{$filtro}%')) "
                . ")";
        }

        if ($idLicenca > 0) {
            $dataAtual = date("Y-m-d H:i:s");
            $licencaAprovada = \Utils\Constantes::LICENCA_APROVADO;
            $where[] = " (chl.id_licenca_software = {$idLicenca} AND chl.data_vencimento >= '{$dataAtual}' AND chl.situacao = '{$licencaAprovada}') ";
        }

        switch (strtoupper($statusDocumentos)) {
            case "B":
                $where[] = " (c.foto_documento IS NOT NULL AND LENGTH(c.foto_documento) > 0) AND "
                    . "(c.foto_residencia IS NOT NULL AND LENGTH(c.foto_residencia) > 0) AND "
                    . "(c.foto_cliente IS NOT NULL AND LENGTH(c.foto_cliente) > 0) ";
                break;
            case "C":
                $where[] = " ( (c.foto_documento IS NULL OR LENGTH(c.foto_documento) <= 0) OR "
                    . "(c.foto_residencia IS NULL OR LENGTH(c.foto_residencia) <= 0) OR "
                    . "(c.foto_cliente IS NULL OR LENGTH(c.foto_cliente) <= 0) ) ";
                break;
            case "D" :
                $where[] = " c.em_analise < 1 ";
                $where[] = " ( "
                    . " ( (c.foto_documento IS NOT NULL OR LENGTH(c.foto_documento) > 0) AND c.foto_documento_verificada < 1 ) OR "
                    . " ( (c.foto_residencia IS NOT NULL OR LENGTH(c.foto_residencia) > 0) AND c.foto_residencia_verificada < 1 ) OR "
                    . " ( (c.foto_cliente IS NOT NULL OR LENGTH(c.foto_cliente) > 0) AND c.foto_cliente_verificada < 1 ) "
                    . " ) ";
                break;
            case "E" :
                $where[] = " c.em_analise > 0 ";
                break;
            case "F" :
                $where[] = " c.em_analise < 1 ";
                $where[] = " ( ( c.foto_outro_documento IS NOT NULL OR LENGTH(c.foto_outro_documento) > 0) AND c.foto_outro_documento_verificada < 1  ) ";
                break;
            case "G" :
                $where[] = " c.em_analise < 1 ";
                $where[] = " ( "
                    . " LENGTH(c.motivo_recusa_foto_documento) <= 0 AND LENGTH(c.motivo_recusa_foto_residencia) <= 0 AND LENGTH(c.motivo_recusa_foto_cliente) <= 0 ) AND "
                    . " ( "
                    . " (c.foto_documento IS NOT NULL AND  LENGTH(c.foto_documento) > 0 AND "
                    . " c.foto_residencia IS NOT NULL AND  LENGTH(c.foto_residencia) > 0 AND "
                    . " c.foto_cliente IS NOT NULL OR LENGTH(c.foto_cliente) > 0) AND "
                    . " (c.foto_documento_verificada + c.foto_residencia_verificada +  c.foto_cliente_verificada) < 3 "
                    . " ) ";
                break;
            case "H" :
                $where[] = " c.em_analise < 1 ";
                $where[] = " c.analise_cliente < 1 ";
                $where[] = " ( "
                    . " LENGTH(c.motivo_recusa_foto_documento) <= 0 AND LENGTH(c.motivo_recusa_foto_residencia) <= 0 AND LENGTH(c.motivo_recusa_foto_cliente) <= 0 ) AND "
                    . " ( "
                    . " (c.foto_documento IS NOT NULL AND  LENGTH(c.foto_documento) > 0 AND "
                    . " c.foto_residencia IS NOT NULL AND  LENGTH(c.foto_residencia) > 0 AND "
                    . " c.foto_cliente IS NOT NULL OR LENGTH(c.foto_cliente) > 0) AND "
                    . " (c.foto_documento_verificada + c.foto_residencia_verificada +  c.foto_cliente_verificada) < 3 "
                    . " ) ";
                break;
        }


        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {

            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }

            $sTipoData = "";
            switch (strtolower($tipoData)) {
                case "a":
                    $sTipoData = "c.data_cadastro";
                    break;
                case "b":
                    $sTipoData = "c.data_nascimento";
                    break;
                default:
                    $sTipoData = "c.data_cadastro";
            }
            $where[] = " {$sTipoData} BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }

        if (strtolower($online) != "t") {
            $limite = new \Utils\Data(date("d/m/Y H:i:s"));
            $limite->subtrair(0, 0, 0, 0, 3);
            switch (strtolower($online)) {
                case "s":
                    $where[] = " c.ultima_atividade >= '{$limite->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
                    break;
                case "n":
                    $where[] = " c.ultima_atividade < '{$limite->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
                    break;
            }
        }

        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");


        $orderBy = "";

        switch (strtolower($ordenacao)) {
            case "a":
                $orderBy = "ORDER BY c.nome ASC";
                break;
            case "b":
                $orderBy = "ORDER BY c.nome DESC";
                break;
            case "c":
                $orderBy = "ORDER BY c.data_cadastro ASC";
                break;
            case "d":
                $orderBy = "ORDER BY c.data_cadastro DESC ";
                break;

            default:
                $orderBy = "ORDER BY c.nome ASC";
        }

        $licencaAprovada = \Utils\Constantes::LICENCA_APROVADO;
        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));

        $query = " SELECT c.* "
            . " FROM clientes c "
            . " LEFT JOIN clientes_has_licencas chl ON (c.id = chl.id_cliente AND chl.situacao = '{$licencaAprovada}' AND chl.data_vencimento >= '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}') "
            . " {$where} "
            . " {$orderBy} ";

        //throw new \Exception($query);

        $result = $this->conexao->adapter->query($query)->execute();

        $lista = array();
        foreach ($result as $d) {
            $cliente = new Cliente($d);
            $lista[] = $cliente;
        }
        return $lista;
    }


    public function criarCarteiraPadrao(Cliente $cliente)
    {
        try {

            $carteiraRn = new CarteiraRn();
            $result = $carteiraRn->conexao->listar("id_cliente = {$cliente->id}", null, null, NULL);

            if (sizeof($result) < 1) {
                $carteira = new Carteira();
                $carteira->id = 0;
                $carteira->idCliente = $cliente->id;
                $carteira->idMoeda = 2;
                $carteira->nome = "Bitcoin Wallet";
                $carteira->principal = 1;

                $carteiraRn->salvar($carteira);
            }

        } catch (\Exception $ex) {

        }
    }

    public function alterarStatusCliente(Cliente &$cliente)
    {
        $this->conexao->update(array("status" => $cliente->status), array("id" => $cliente->id));
    }

    public function liberarLogin(Cliente &$cliente)
    {

        $this->conexao->update(array("bloquear_login" => $cliente->bloquearLogin, "bloquear_recuperacao_senha" => $cliente->bloquearRecuperacaoSenha,
            "quantidade_tentativas_login" => $cliente->quantidadeTentativasLogin, "quantidade_tentativas_recuperacao" => $cliente->quantidadeTentativasRecuperacao,
            "anotacoes" => $cliente->anotacoes), array("id" => $cliente->id));


    }

    public function getByEmail($email)
    {
        if (!empty($email)) {
            $result = $this->conexao->listar("email = '{$email}'", null, null, 1);
            if (sizeof($result) > 0) {
                return $result->current();
            }
        }
        return null;
    }

    public function getByCpf($cpf)
    {
        if (!empty($cpf)) {
            $result = $this->conexao->listar("documento = '{$cpf}'", null, null, 1);
            if (sizeof($result) > 0) {
                return $result->current();
            }
        }
        return null;
    }

    public function excluir(Cliente &$cliente)
    {
        try {

            $this->conexao->adapter->iniciar();

            $movimentacaoMesRn = new MovimentacaoMesRn();
            $movimentacaoMesRn->conexao->delete("id_cliente = {$cliente->id} ");

            $this->conexao->excluir($cliente);

            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }


    public function extratoUsuario(Usuario $usuario, $ref, $idCliente = null)
    {
        $whereCliente = ($idCliente > 0 ? " AND c.id = {$idCliente} " : "");
        $query = "SELECT "
            . " c.id AS id_cliente, "
            . " c.nome AS cliente, "
            . " c.email, "
            . " c.comissao, "
            . " c.data_expiracao, "
            . " m.periodo_ref, "
            . " m.deposito, "
            . " m.saque, "
            . " m.venda, "
            . " m.compra, "
            . " m.pago, "
            . " m.valor_pago, "
            . " m.btc_pago "
            . " FROM clientes c INNER JOIN movimentacoes_mes m ON (c.id = m.id_cliente) "
            . " WHERE "
            . " c.id_usuario = {$usuario->id } AND "
            . " c.status = 1 AND "
            . " m.periodo_ref = '{$ref}' "
            . " {$whereCliente} "
            . " ORDER BY m.pago DESC, c.nome ";

        $result = $this->conexao->adapter->query($query)->execute();
        return $result;
    }


    public function updateDadosSeguranca(Cliente $cliente, $updatePin = false, $updateFraseSegurança = false, $update2fa = false)
    {

        $aux = new Cliente(array("id" => $cliente->id));
        try {
            $this->conexao->carregar($aux);
        } catch (\Exception $ex) {
            throw new \Exception("e");
        }

        if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS) {
            if (empty($aux->celular)) {
                throw new \Exception($this->idioma->getText("ativarTokenPorSMS"));
            }
        }

        if (!empty($aux->pin)) {
            $cliente->pin = $aux->pin;
        }

        if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
            if ($aux->googleAuthAtivado < 1) {
                if (empty($cliente->googleAuthSecret)) {
                    throw new \Exception($this->idioma->getText("autenticacaoGoogleSecret"));
                }

                if ($cliente->googleAuthAtivado < 1) {
                    throw new \Exception($this->idioma->getText("chaveGoogleNaoValidade"));
                }
            } else {
                $cliente->googleAuthSecret = $aux->googleAuthSecret;
            }
        } else {
            $cliente->googleAuthSecret = null;
            $cliente->googleAuthAtivado = 0;
        }

        if (!empty($cliente->pin)) {
            if (strlen($cliente->pin) != 4) {
                throw new \Exception($this->idioma->getText("pinDeveTer4Digitos"));
            }
        }

        $cliente->pin = $cliente->getEncriptedPin();
        $cliente->fraseSeguranca = $cliente->getEncriptedFraseSeguranca();

        $this->conexao->update(
            array(
                "google_auth_ativado" => $cliente->googleAuthAtivado,
                "google_auth_secret" => $cliente->googleAuthSecret,
                "tipo_autenticacao" => $cliente->tipoAutenticacao,
                "pin" => $cliente->pin,
                "frase_seguranca" => $cliente->fraseSeguranca
            ),
            array("id" => $cliente->id));

        if ($updatePin) {
            $this->conexao->update(
                array("data_update_pin" => date("Y-m-d H:i:s")),
                array("id" => $cliente->id));
        }

        if ($updateFraseSegurança) {
            $this->conexao->update(
                array("data_update_frase_seguranca" => date("Y-m-d H:i:s")),
                array("id" => $cliente->id));
        }

        if ($update2fa) {
            $this->conexao->update(
                array("data_update_twofa" => date("Y-m-d H:i:s"), "documento_verificado" => 0),
                array("id" => $cliente->id));
        }

    }

    public function alterarSenha(Cliente $cliente, $confirmacao, $senhaAtual)
    {

        try {
            $c = new Cliente(array("id" => $cliente->id));
            $this->conexao->carregar($c);
        } catch (\Exception $e) {
            throw new \Exception($this->idioma->getText("clienteNaoLocalizado"));
        }

        if ($c->senha !== sha1($senhaAtual . \Utils\Constantes::SEED_SENHA)) {
            throw new \Exception($this->idioma->getText("senhaAtualInvalida"));
        }

        if (empty($cliente->senha)) {
            throw new \Exception($this->idioma->getText("necessarioInformarSenha"));
        }

        if (empty($confirmacao)) {
            throw new \Exception($this->idioma->getText("necessarioConfirmarSenha"));
        }

        if ($cliente->senha != $confirmacao) {
            throw new \Exception($this->idioma->getText("senhaConfirmacaoNaoConferem"));
        }

        $cliente->senha = sha1($cliente->senha . \Utils\Constantes::SEED_SENHA);

        $this->conexao->update(array("senha" => $cliente->senha, "data_update_senha" => date("Y-m-d H:i:s")), array("id" => $cliente->id));

        $clienteLogado = \Utils\Geral::getCliente();
        if ($clienteLogado != null) {
            if ($cliente->id == $clienteLogado->id) {
                if (\Utils\Geral::isUsuario()) {
                    \Utils\Geral::setLogado(\Utils\Geral::getLogado(), $cliente);
                } else {
                    \Utils\Geral::setLogado(null, $cliente);
                }
            }
        }

    }


    public function getQuantidadeClientesPorStatus()
    {

        $query = " SELECT status, COUNT(*) AS qtd FROM clientes"
            . " GROUP BY status;";

        $dados = $this->conexao->adapter->query($query)->execute();

        $ativos = 0;
        $aguardando = 0;
        $inativos = 0;

        foreach ($dados as $d) {
            switch ($d["status"]) {
                case 0:
                    $aguardando += $d["qtd"];
                    break;
                case 1:
                    $ativos += $d["qtd"];
                    break;
                case 2:
                    $inativos += $d["qtd"];
                    break;
                default:
                    break;
            }
        }

        return array("ativos" => $ativos, "aguardando" => $aguardando, "inativos" => $inativos);
    }

    public function getQuantidadeClientesOnline()
    {

        $dataRef = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataRef->subtrair(0, 0, 0, 0, 1, 0);

        $result = $this->conexao->adapter->query("SELECT * FROM clientes WHERE ultima_atividade >= '{$dataRef->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}';")->execute();
        return $result;
    }


    public function getNumeroClientesOnline()
    {

        $dataRef = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataRef->subtrair(0, 0, 0, 0, 1, 0);

        $result = $this->conexao->adapter->query("SELECT COUNT(*) AS qtd FROM clientes WHERE ultima_atividade >= '{$dataRef->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}';")->execute();
        $qtd = 0;
        if (sizeof($result) > 0) {
            $d = $result->current();
            $qtd = ($d["qtd"] > 0 ? $d["qtd"] : 0);
        }
        return $qtd;
    }


    public function alterarStatusFranquia(Cliente $cliente)
    {

        try {
            $this->conexao->carregar($cliente);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText(""));
        }

        if (!$cliente->tipoPerfil == \Utils\Constantes::PERFIL_CLIENTE) {
            throw new \Exception("O cliente não é um vendedor comissionado");
        }

        $cliente->statusFranquia = ($cliente->statusFranquia > 0 ? 0 : 1);

        $this->conexao->update(
            array(
                "status_franquia" => $cliente->statusFranquia
            ),
            array(
                "id" => $cliente->id
            )
        );
    }


    public function cancelarFranquia(Cliente $cliente)
    {
        try {
            $this->conexao->adapter->iniciar();

            try {
                $this->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema");
            }

            if (!$cliente->tipoPerfil == \Utils\Constantes::PERFIL_CLIENTE) {
                throw new \Exception("O cliente não é um vendedor comissionado");
            }

            $cliente->tipoPerfil = \Utils\Constantes::PERFIL_CLIENTE;

            $this->conexao->update(
                array(
                    "tipo_perfil" => $cliente->tipoPerfil
                ),
                array(
                    "id" => $cliente->id
                )
            );

            $this->conexao->update(array("id_referencia" => null), array("id_referencia" => $cliente->id));

            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {

            $this->conexao->adapter->cancelar();
            throw new \Exception($ex);
        }
    }


    public function alterarStatusMercado(Cliente &$cliente)
    {
        try {
            try {
                $this->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema");
            }
            $cliente->statusMercado = ($cliente->statusMercado > 0 ? 0 : 1);

            $this->conexao->update(
                array(
                    "status_mercado" => $cliente->statusMercado
                ),
                array(
                    "id" => $cliente->id
                )
            );
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    public function iniciarAnalise(Cliente &$cliente)
    {
        try {
            $this->conexao->carregar($cliente);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
        }

        if ($cliente->emAnalise > 0) {
            throw new \Exception($this->idioma->getText("clienteJaEmAnalise"));
        }

        $usuarioLogado = \Utils\Geral::getLogado();

        if (!\Utils\Geral::isUsuario() || $usuarioLogado == null) {
            throw new \Exception($this->idioma->getText("vcNaoPermissaoExecutarAcao"));
        }

        $cliente->emAnalise = 1;
        $cliente->dataInicioAnalise = new \Utils\Data(date("d/m/Y H:i:s"));
        $cliente->dataFimAnalise = null;
        $cliente->idUsuarioInicioAnalise = $usuarioLogado->id;
        $cliente->idUsuarioTerminoAnalise = null;

        $this->conexao->update(
            array(
                "em_analise" => $cliente->emAnalise,
                "data_inicio_analise" => $cliente->dataInicioAnalise->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                "data_fim_analise" => $cliente->dataFimAnalise,
                "id_usuario_inicio_analise" => $cliente->idUsuarioInicioAnalise,
                "id_usuario_termino_analise" => $cliente->idUsuarioTerminoAnalise
            ),
            array(
                "id" => $cliente->id
            )
        );

    }


    public function finalizarAnalise(Cliente &$cliente)
    {
        try {
            $this->conexao->carregar($cliente);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
        }

        if ($cliente->emAnalise < 1) {
            throw new \Exception($this->idioma->getText("clienteJaEmAnalise"));
        }

        $usuarioLogado = \Utils\Geral::getLogado();

        if (!\Utils\Geral::isUsuario() || $usuarioLogado == null) {
            throw new \Exception($this->idioma->getText("vcNaoPermissaoExecutarAcao"));
        }

        $cliente->emAnalise = 0;
        $cliente->dataFimAnalise = new \Utils\Data(date("d/m/Y H:i:s"));
        $cliente->idUsuarioTerminoAnalise = $usuarioLogado->id;

        $this->conexao->update(
            array(
                "em_analise" => $cliente->emAnalise,
                "data_fim_analise" => $cliente->dataFimAnalise->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                "id_usuario_termino_analise" => $cliente->idUsuarioTerminoAnalise
            ),
            array(
                "id" => $cliente->id
            )
        );
    }

    public function setUltimaAtividade()
    {

        $pagina = (string)$_SERVER["HTTP_REFERER"];
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        if (strpos($ip, ',') !== false) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }
        //$moeda = \Modules\principal\Controllers\Principal::getParity();
        $cliente = \Utils\Geral::getCliente();
        # 05/07/2019 - Caique t23 - Online
        $ultima_atividade = new \Utils\Data(date("d/m/Y H:i:s"));
        if ($cliente != null && $cliente instanceof Cliente) {
            try {
                $query = "UPDATE clientes SET ultima_atividade = '{$ultima_atividade->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}', pagina_atual = '{$pagina}', ip_ultimo_acesso = '$ip', webkit = '{$_SERVER["HTTP_USER_AGENT"]}'  WHERE id = '{$cliente->id}';";
                $result = $this->conexao->adapter->query($query)->execute();
                $status = "sucesso";

            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText(""));
                $status = "erro";

            }
        }
        return $status;
        # end
    }

    public function creditarComissaoReferencia(Cliente $cliente, $comissao, $descricao, $convite, $idReferencia, $token = null, $origem = 0)
    {

        $this->conexao->carregar($cliente);

        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
        $contaCorrenteReais = new ContaCorrenteReais();
        $contaCorrenteReais->id = 0;
        $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $contaCorrenteReais->descricao = $descricao;
        $contaCorrenteReais->idCliente = $cliente->id;
        $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
        $contaCorrenteReais->transferencia = 0;
        $contaCorrenteReais->valor = number_format($comissao, 2, ".", '');
        $contaCorrenteReais->valorTaxa = 0;
        $contaCorrenteReais->origem = $origem;
        $contaCorrenteReais->idReferenciado = $idReferencia;

        if ($convite) {
            $contaCorrenteReais->comissaoConvidado = 1;
            $contaCorrenteReais->comissaoLicenciado = 0;
        } else {
            $contaCorrenteReais->comissaoConvidado = 0;
            $contaCorrenteReais->comissaoLicenciado = 1;
        }

        $contaCorrenteReaisRn->salvar($contaCorrenteReais, $token);

        $contaCorrenteReaisEmpresaRn = new ContaCorrenteReaisEmpresaRn($this->conexao->adapter);
        $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
        $contaCorrenteReaisEmpresa->id = 0;
        $contaCorrenteReaisEmpresa->bloqueado = 1;
        $contaCorrenteReaisEmpresa->data = $contaCorrenteReais->data;
        $contaCorrenteReaisEmpresa->descricao = $descricao;
        $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::SAIDA;
        $contaCorrenteReaisEmpresa->transferencia = 0;
        $contaCorrenteReaisEmpresa->valor = number_format($comissao, 2, ".", '');
        $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa, $token);

    }


    public function getQuantidadeReferenciasByCliente(Cliente $cliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null)
    {
        $where = array();

        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }

            $where[] = " c.data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }

        if ($cliente != null && $cliente->id > 0) {
            $where[] = " c.id_referencia = {$cliente->id} ";
        } else {
            $where[] = " c.id_referencia IS NOT NULL ";
        }
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT COUNT(*) AS qtd FROM clientes c {$whereString};";
        $qtd = 0;
        $dados = $this->conexao->adapter->query($query)->execute();
        foreach ($dados as $d) {
            $qtd = $d["qtd"];
        }
        return $qtd;
    }

    public function getQuantidadeConvidadosByCliente(Cliente $cliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null)
    {
        $where = array();

        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }

            $where[] = " c.data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }

        if ($cliente != null && $cliente->id > 0) {
            $where[] = " c.id_cliente_convite = {$cliente->id}";
        } else {
            $where[] = " c.id_cliente_convite IS NOT NULL ";
        }
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT COUNT(*) AS qtd FROM clientes c {$whereString};";
        $qtd = 0;
        $dados = $this->conexao->adapter->query($query)->execute();
        foreach ($dados as $d) {
            $qtd = $d["qtd"];
        }
        return $qtd;
    }


    public function getClientesComLicenca($filtro)
    {

        $where = array();

        if (!empty($filtro)) {
            $where[] = " ( "
                . " ( LOWER(c.nome) LIKE LOWER('%{$filtro}%') ) OR "
                . " ( LOWER(c.email) LIKE LOWER('%{$filtro}%') ) "
                . "  ) ";
        }

        $sDataAtual = date("Y-m-d H:i:s");
        $aprovada = \Utils\Constantes::LICENCA_APROVADO;
        $where[] = " chl.data_vencimento >= '{$sDataAtual}' ";
        $where[] = " chl.situacao = '{$aprovada}' ";

        $stringWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $query = " SELECT c.*, ls.nome As licenca, chl.id_licenca_software, chl.id AS id_cliente_has_licenca "
            . " FROM clientes c "
            . " INNER JOIN clientes_has_licencas chl ON (c.id = chl.id_cliente) "
            . " INNER JOIN licencas_software ls ON (chl.id_licenca_software = ls.id) "
            . " {$stringWhere} "
            . " ORDER BY c.nome  ";
        //throw new \Exception($query);        
        $result = $this->conexao->adapter->query($query)->execute();

        $lista = array();
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            $licenca = new LicencaSoftware(array("id" => $dados["id_licenca_software"], "nome" => $dados["licenca"]));
            $clienteHasLicenca = new ClienteHasLicenca(array("id" => $dados["id_cliente_has_licenca"]));

            $lista[] = array("cliente" => $cliente, "licenca" => $licenca, "clienteHasLicenca" => $clienteHasLicenca);
        }

        return $lista;
    }

    public function getQuantidadeClientesCadastradosIco()
    {

        $query = " SELECT COUNT(*) AS qtd FROM clientes WHERE origem_cadastro = 'newctoken';";
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $dados) {
            $qtd = $dados["qtd"];
        }
        return $qtd;

    }

    public function getQuantidadeClientesCadastrados()
    {

        $query = " SELECT COUNT(*) AS qtd FROM clientes;";
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $dados) {
            $qtd = $dados["qtd"];
        }
        return $qtd;

    }

    public function getQuantidadeClientesCadastradosPorMes()
    {

        $lista = array();
        $query = "";

        for ($i = 1; $i <= 12; $i++) {
            $query = "SELECT COUNT(*) AS qtd, origem_cadastro AS oc  FROM clientes WHERE MONTH(data_cadastro) = {$i} AND YEAR(data_cadastro) = YEAR(curdate()) GROUP BY origem_cadastro DESC;";
            $result = $this->conexao->adapter->query($query)->execute();

            foreach ($result as $dados) {

                if ($dados["oc"] == "site") {
                    $qtdSite = $dados["qtd"];
                    $origemSite = $dados["oc"];
                    $lista[$i] = array("mes" => $i, "origem" => $origemSite, "qtdSite" => $qtdSite);
                } else {
                    $lista[$i] = array("mes" => $i, "origem" => $origemSite, "qtdSite" => $qtdSite, "origemIco" => $dados["oc"], "qtdIco" => $dados["qtd"]);
                }
            }
        }

        return $lista;
    }

    public function getQuantidadeClientesCadastradosPorDia()
    {

        $lista = array();
        $query = "";


        $lista = array();
        for ($i = 0; $i <= 6; $i++) {
            $query = "SELECT COUNT(*) AS qtd, origem_cadastro AS oc FROM clientes WHERE WEEKOFYEAR(data_cadastro) = WEEKOFYEAR(current_date()) AND YEAR(data_cadastro) = YEAR(curdate()) AND dayname(data_cadastro)  =  {$i} GROUP BY origem_cadastro DESC;";
            $result = $this->conexao->adapter->query($query)->execute();

            foreach ($result as $dados) {
                if ($dados["oc"] == "site") {
                    $qtdSite = $dados["qtd"];
                    $origemSite = $dados["oc"];
                    $lista[$i] = array("dia" => $i, "origem" => $origemSite, "qtdSite" => $qtdSite);
                } else {
                    if (sizeof($result) == 1) {
                        $lista[$i] = array("dia" => $i, "origem" => $origemSite, "qtdSite" => 0, "origemIco" => $dados["oc"], "qtdIco" => $dados["qtd"]);
                    } else {
                        $lista[$i] = array("dia" => $i, "origem" => $origemSite, "qtdSite" => $qtdSite, "origemIco" => $dados["oc"], "qtdIco" => $dados["qtd"]);
                    }
                }
            }
        }
        //exit(print_r($lista));
        return $lista;

    }


    public function getQuantidadeClientesVerificadosSistema()
    {
        $query = "SELECT "
            . " COUNT(*)  AS total FROM clientes c  "
            . " WHERE  "
            . " email_confirmado > 0 AND  "
            . " (foto_documento_verificada > 0 AND foto_cliente_verificada > 0 AND foto_residencia_verificada > 0)";

        $dados = $this->conexao->adapter->query($query)->execute();
        $total = 0;
        if (sizeof($dados)) {
            foreach ($dados as $d) {
                $total = $d["total"];
            }
        }
        return $total;
    }

    public function getCarteiraPrincipal(Cliente $cliente, $idMoeda)
    {

        $query = "SELECT c.* FROM carteiras_clientes c WHERE id_cliente = {$cliente->id} AND id_moeda = {$idMoeda} ORDER BY principal DESC, id ASC;";
        $result = $this->conexao->adapter->query($query)->execute();

        foreach ($result as $dados) {
            $carteira = new Carteira($dados);
            return $carteira;
        }
        return null;
    }

    public function getClientesReferencias($cliente)
    {

        $query = "SELECT * FROM clientes WHERE id_referencia = {$cliente->id} ORDER BY nome ASC;";

        $result = $this->conexao->adapter->query($query)->execute();

        $referencias = array();
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            $referencias[] = $cliente;
        }

        return $referencias;
    }

    public function getClienteConvidado()
    {

        $query = "SELECT  distinct(c1.id), c1.* FROM clientes c1 
                INNER JOIN clientes c2 ON (c1.id = c2.id_referencia OR c1.id = c2.id_cliente_convite)
                order by nome ASC;";
        $result = $this->conexao->adapter->query($query)->execute();

        $lista = array();
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            $lista[] = array("cliente" => $cliente);
        }

        return $lista;
    }


    public function getArvoreReferencias(Cliente $cliente)
    {
        $inicioIco = \Utils\Constantes::getDataInicioICO();
        $arvore = array();

        $clientes = $this->conexao->listar(" id_referencia = {$cliente->id} AND status = 1 AND data_cadastro >= '{$inicioIco->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "nome");

        $contaCorrenteRn = new ContaCorrenteBtcRn();
        $saldo = $contaCorrenteRn->calcularSaldoConta($cliente, 33, false);

        if (sizeof($clientes)) {
            foreach ($clientes as $c) {
                $arvore[] = $this->getArvoreReferencias($c);
            }
        }

        $nivel = "0";

        if ($saldo >= 20000) {
            $nivel = "4";
        } else if ($saldo >= 5000) {
            $nivel = "3";
        } else if ($saldo >= 2500) {
            $nivel = "2";
        } else if ($saldo >= 1000) {
            $nivel = "1";
        }

        return array(
            "cliente" => array("nivel" => $nivel),
            "referencias" => $arvore);
    }


    public function getQuantidadeClientesReferenciados(Cliente $cliente)
    {
        $inicioIco = \Utils\Constantes::getDataInicioICO();
        $clientes = $this->conexao->listar(" id_referencia = {$cliente->id} AND status = 1 AND data_cadastro >= '{$inicioIco->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "nome");

        $quantidade = sizeof($clientes);

        if (sizeof($clientes)) {
            foreach ($clientes as $c) {
                $quantidade += $this->getQuantidadeClientesReferenciados($c);
            }
        }
        return $quantidade;
    }


    public function getRankingMoeda(Moeda $moeda, $skip, $limit)
    {

        $sLimit = ($limit > 0 ? " LIMIT {$limit} " : "");
        $sOffset = ($skip > 0 ? " OFFSET {$skip} " : "");

        "SELECT "
        . " c.nome, c.id, "
        . " (SELECT SUM()) "
        . " FROM clientes c";

    }

    public function clienteVerificado(Cliente $cliente)
    {
        $clienteVerificado = false;
        $configuracao = ConfiguracaoRn::get();
        if ($configuracao->kyc == 0) {
            $clienteVerificado = true;
        } else {

            if ($cliente->id == null) {
                throw new \Exception($this->idioma->getText("clienteInvalidoNaoEncontrado"));
            }

            $this->conexao->carregar($cliente);

            if (($cliente->fotoDocumentoVerificada == \Utils\Constantes::DOCUMENTO_VERIFICADO) &&
                ($cliente->fotoResidenciaVerificada == \Utils\Constantes::DOCUMENTO_VERIFICADO) &&
                ($cliente->fotoClienteVerificada == \Utils\Constantes::DOCUMENTO_VERIFICADO)) {
                $clienteVerificado = true;
            }
        }

        return $clienteVerificado;
    }

    public function alterarModo(Cliente &$cliente, $modo)
    {
        $this->conexao->update(array("modo_operacao" => $modo), array("id" => $cliente->id));
    }

    public function confirmarEmail(Cliente &$cliente)
    {
        $this->conexao->update(array("email_confirmado" => 1), array("id" => $cliente->id));
    }

    public function senhaTemporaria(Cliente &$cliente)
    {
        $cliente->senha = sha1($cliente->senha . \Utils\Constantes::SEED_SENHA);
        $this->conexao->update(array("senha" => $cliente->senha), array("id" => $cliente->id));
    }

    public function setMoedaFavorita(Cliente &$cliente, $idMoeda)
    {
        $this->conexao->update(array("moeda_favorita" => $idMoeda), array("id" => $cliente->id));
    }

    public function setParidadeAtual(Cliente &$cliente, $idParidade)
    {
        $this->conexao->update(array("id_moeda_atual" => $idParidade), array("id" => $cliente->id));
    }

    public function gerarApiKeys(Cliente &$cliente)
    {

        $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));
        $dataAtual->subtrair(0, 0, 1, 0, 0, 0);

        //Verificar if modificado para teste
        if (empty($cliente->dataUpdateApiKey) || $dataAtual->maior($cliente->dataUpdateApiKey)) {
            $cliente->apiKey = \Utils\Criptografia::token($cliente->nome);
            $cliente->clientid = \Utils\Criptografia::userid($cliente->apiKey);

            $cliente->dataUpdateApiKey = date("Y-m-d H:i:s");

            $this->conexao->update(array("api_key" => $cliente->apiKey, "clientid" => $cliente->clientid, "data_update_api_key" => $cliente->dataUpdateApiKey), array("id" => $cliente->id));

            $bodyMail = [
                'nome' => $cliente->nome,
                'email' => $cliente->email,
                'params' => [
                    "cliente_nome" => $cliente->nome,
                    "cliente_email" => $cliente->email,
                    "client_id" => $cliente->clientid,
                    "api_key" => $cliente->apiKey,
                    "data_hora" => $dataAtual->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP),
                    "id_cliente" => $cliente->id,
                    "id_usuario" => ""
                ],
                'template_name' => 'system.security.newapicredentials'
            ];

            $rabbit = new \RabbitMq\Client();
            $result = $rabbit->sendQueue('notificacoes', $bodyMail);

        } else {
            throw new \Exception("Aguardar o período de 24 horas para renovar as credenciais.");
        }
    }

    public function setLastUpdateResendSMS(Cliente &$cliente)
    {
        $this->conexao->update(array("kyc_sms_resend_data" => date('Y-m-d H:i:s')), array("id" => $cliente->id));
    }

    public function updateMarket(Cliente &$cliente)
    {

        $this->conexao->update(array("campanha_market_receber" => $cliente->campanhaMarketReceber), array("id" => $cliente->id));
    }

    public function kycUpdateStatus(Cliente &$cliente, $status)
    {
        $this->conexao->update(array("documento_verificado" => $status), array("id" => $cliente->id));
    }

}

?>
