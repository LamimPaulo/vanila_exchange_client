<?php

namespace Modules\perfil\Controllers;

//require_once "../../../Library/Models/Modules/Cadastro/CidadeRn.class.php";
//require_once "../../../Library/Models/Modules/Cadastro/EstadoRn.class.php";

/*require_once getcwd() . '/Library/Models/Modules/Cadastro/EstadoRn.class.php';
require_once getcwd() . '/Library/Models/Modules/Cadastro/CidadeRn.class.php';*/

require_once getcwd() . '/Library/Models/Modules/Cadastro/ConsultaCnpj.class.php';
require_once getcwd() . '/Library/Models/Modules/Cadastro/ConsultaCnpjRn.class.php';

require_once getcwd() . '/Library/Documento/IWebService.class.php';


use Utils\Data;

class MeusDados
{
    private $codigoModulo = "perfil";
    private $idioma = null;

    function __construct()
    {
        $this->idioma = new \Utils\PropertiesUtils("perfil", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
    }

    public function index($params)
    {
        try {

            $getCliente = (\Utils\Geral::isCliente() ? \Utils\Geral::getCliente() : null);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente->id = $getCliente->id;
            $clienteRn->conexao->carregar($cliente);
            $clienteVerificado = $clienteRn->clienteVerificado($cliente);

            //$estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            //$cidadeRn = new \Models\Modules\Cadastro\CidadeRn();

            //$codigoCidade = (($cliente != null && !empty($cliente->cidade)) ? $cliente->cidade : ($cliente != null && !empty($cliente->cidade) ? $cliente->cidade : ""));
            //$resultCidades = $cidadeRn->conexao->listar("codigo = '{$codigoCidade}' ", "nome");


            /*if (sizeof($resultCidades) > 0) {
                $cidade = $resultCidades->current();

                $estado = new \Models\Modules\Cadastro\Estado(array("id" => $cidade->idEstado));
                $estadoRn->conexao->carregar($estado);

            } else {
                $cidade = "";
                $estado = new \Models\Modules\Cadastro\Estado(array("id" => 1));
                $estadoRn->conexao->carregar($estado);
            }*/

            \Utils\Geral::setCliente($cliente);
            \Utils\Geral::setLogado(null, $cliente);

            //$estados = $estadoRn->conexao->listar(null, "sigla");

            $paisRn = new \Models\Modules\Cadastro\PaisRn();
            $paises = $paisRn->listar(" ativo = 1", "nome");

            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $taxasMoedas = $taxaMoedaRn->taxasMoedasAtivas();

            $clienteTaxaRn = new \Models\Modules\Cadastro\ClienteHasTaxaRn();

            foreach ($taxasMoedas as $taxaMoeda) {
                $result = $clienteTaxaRn->getTaxaCliente($cliente, $taxaMoeda["moedaId"], true);
                $resultFinal[] = $taxaMoeda + $result;
            }

            $comissao = \Models\Modules\Cadastro\ClienteHasComissaoRn::get($cliente->id);

            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $todosOsBancos = $bancoRn->conexao->listar(" codigo <> 1000 AND codigo <> 000", "nome");

            $params["clienteVerificado"] = $clienteVerificado;
            $params["todosOsBancos"] = $todosOsBancos;
            $params["taxas"] = $resultFinal;
            //$params["cidade"] = $cidade;
            $params["configuracao"] = $configuracao;
            $params["paises"] = $paises;
            //$params["estado"] = $estado;
            //$params["estados"] = $estados;
            $params["cliente"] = $cliente;
            $params["comissao"] = $comissao;

        } catch (\Exception $ex) {

        }

        \Utils\Layout::view("perfil", $params);
    }

    public function getCidades($params)
    {
        try {

            $idEstado = \Utils\Post::get($params, "estado", null);
            $codigoCidade = \Utils\Post::get($params, "cidade", null);
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();

            if ($idEstado > 0) {
                $cidades = $cidadeRn->listar("id_estado = {$idEstado}", "nome", null, null, false);
            } else {
                $cidades = array();
            }

            ob_start();
            ?>
            <option value="">Selecione uma cidade</option>
            <?php
            foreach ($cidades as $cidade) {
                $checked = ($cidade->codigo == $codigoCidade ? "selected='selected'" : "");
                ?>
                <option value="<?php echo $cidade->codigo ?>" <?php echo $checked ?>><?php echo $cidade->nome ?></option>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function salvar($params)
    {
        try {

            $cliente = (\Utils\Geral::isCliente() ? \Utils\Geral::getCliente() : null);

            $confirmacaoSenha = \Utils\Post::get($params, "confirmacao", "");

            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            if ($cliente->tipoAutenticacao != \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                throw new \Exception("Por favor, altere a segurança da sua conta para 2FA Authenticator.");
            }

            $cliente->celular = \Utils\Post::get($params, "celular", "");
            $cliente->ddi = \Utils\Post::get($params, "ddi", "");
            $cliente->sexo = \Utils\Post::get($params, "sexo", "");

            if ($cliente->documentoVerificado == 0) {
                if ($cliente->fotoDocumentoVerificada != \Utils\Constantes::DOCUMENTO_VERIFICADO || $configuracao->kyc == 0) {
                    $cliente->nome = \Utils\Post::get($params, "nome", null);
                    $cliente->dataNascimento = \Utils\Post::getData($params, "dataNascimento", null, "00:00:00");
                    $cliente->documento = \Utils\Post::getDoc($params, "cpf", "");
                    $cliente->documentoTipo = \Utils\Post::get($params, "documentoTipo", 0);
                    $cliente->idPaisNaturalidade = \Utils\Post::getEncrypted($params, "idPaisNaturalidade", 0);

                    if (empty($cliente->documento)) {
                        throw new \Exception($this->idioma->getText("validacao2"));
                    }

                    if (empty($cliente->dataNascimento)) {
                        throw new \Exception($this->idioma->getText("validacao4"));
                    }

                    if (empty($cliente->idPaisNaturalidade)) {
                        throw new \Exception($this->idioma->getText("validacao5"));
                    }

                    if (empty($cliente->sexo)) {
                        throw new \Exception($this->idioma->getText("validacao10"));
                    }

                    if ($cliente->idPaisNaturalidade < 1) {
                        $cliente->idPaisNaturalidade = null;
                    }

                    if ($cliente->idPaisNaturalidade == 30) {
                        $cliente->utilizaSaqueDepositoBrl = 1;
                        $cliente->documentoTipo = \Utils\Constantes::DOCUMENTO_CPF;
                    } else {
                        if ($cliente->utilizaSaqueDepositoBrl > 0) {
                            $cliente->documentoTipo = \Utils\Constantes::DOCUMENTO_CPF;
                        }
                    }
                }

                if (\Utils\Validacao::cpf($cliente->documento)) {
                    $dataNascimento = $cliente->dataNascimento->formatar(\Utils\Data::FORMATO_PT_BR);
                    if (strlen($dataNascimento) >= 8) {
                    //    $iWebService = new \Documento\IWebService();
                    //    $dados = $iWebService->consultar($cliente->documento, $dataNascimento);

                    //    if (!empty($dados)) {
                    //        if ($dados["status"] == "regular") {
                    //            $cliente->nome = $dados["nome"];
                    //            $cliente->documento = \Utils\Validacao::limparString($cliente->documento);
                    //            $cliente->nomeMae = $dados["mae"];
                    //            $cliente->endereco = $dados["logradouro"];
                    //            $cliente->numero = $dados["numero"];
                    //            $cliente->complemento = $dados["complemento"];
                    //            $cliente->bairro = $dados["bairro"];
                    //            $cliente->cep = $dados["cep"];
                    //            $cliente->sexo = $dados["genero"] == "MASCULINO" ? "M" : "F";
                    //            $cliente->dataNascimento = new \Utils\Data(date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $dados["dataNascimento"]))));
                    //            $cliente->documentoVerificado = 0;
                    //        } else {
                    //            $observacaoCliente = new \Models\Modules\Cadastro\ObservacaoCliente();
                    //            $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();

                    //            $observacaoCliente->idCliente = $cliente->id;
                    //            $observacaoCliente->observacoes = $dados["response"];
                    //            $observacaoClienteRn->salvar($observacaoCliente);

                    //            $cliente->anotacoes = "Cliente em espera por consulta de CPF";
                    //            $cliente->status = 0;
                    //            $clienteRn->alterarStatusCliente($cliente);
                    //        }
                    //    } else {
                    //        throw new \Exception("Por favor, tente novamente mais tarde.");
                    //    }
                    } else {
                        throw new \Exception($this->idioma->getText("validacao4"));
                    }
                } else {
                    throw new \Exception("Por favor, digite um CPF valído.");
                }
            }
            // exit(print_r($cliente));
            $cliente->senha = null;
            $clienteRn->salvar($cliente, $confirmacaoSenha, null, null, false);

            \Utils\Geral::setCliente($cliente);
            $json["mensagem"] = $this->idioma->getText("dadosSalvoSucesso");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function updateSeguranca($params)
    {
        try {

            $codigo = \Utils\Post::get($params, "tipoAutenticacaoToken", null);
            $token = \Utils\Post::get($params, "authCodigo2faAtivar", "");
            $pin = \Utils\Post::get($params, "pin", null);
            $fraseSeguranca = \Utils\Post::get($params, "fraseSeguranca", null);
            $tipoAutenticacao = \Utils\Post::get($params, "tipoAutenticacao", "email");
            $secretHidden = \Utils\Post::get($params, "authSecretHidden", "");

            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $cliente = (\Utils\Geral::isCliente() ? \Utils\Geral::getCliente() : null);

            $updateFraseSegurança = false;
            $updatePin = false;
            $update2fa = false;

            if ($cliente != null) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);

                if (!empty($pin)) {
                    if($cliente->pin != $pin){
                        $cliente->pin = $pin;
                        $updatePin = true;
                    }
                }

                if (!empty($fraseSeguranca)) {
                    if($cliente->fraseSeguranca != $fraseSeguranca){
                        $cliente->fraseSeguranca = $fraseSeguranca;
                        $updateFraseSegurança = true;
                    }
                }

                if (empty($cliente->pin) || empty($cliente->fraseSeguranca)) {
                    throw new \Exception($this->idioma->getText("cadastrePin"));
                }

                $cliente->twoFactorAuth = 1;

                $autenticacaoAnterior = $cliente->tipoAutenticacao;

                if ($cliente->tipoAutenticacao != $tipoAutenticacao) {

                    if (($cliente->twoFactorAuth == 1) && ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE)) {
                        $authRn->validar($codigo);
                    }

                    $cliente->tipoAutenticacao = $tipoAutenticacao;

                    if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {

                        $cliente->googleAuthSecret = $secretHidden;

                        if ($cliente->googleAuthAtivado < 1) {
                            if (empty($token)) {
                                throw new \Exception($this->idioma->getText("necessarioGoogle"));
                            }

                            $ga = new \GoogleAuth\GoogleAuthenticator();
                            if ($ga->verifyCode($cliente->googleAuthSecret, $token, 1320)) {
                                $cliente->googleAuthAtivado = 1;
                            } else {
                                throw new \Exception($this->idioma->getText("googleInvalido"));
                            }
                        }
                    }
                }

                if ($autenticacaoAnterior != $cliente->tipoAutenticacao) {
                    $update2fa = true;
                }


                $clienteRn->updateDadosSeguranca($cliente, $updatePin, $updateFraseSegurança, $update2fa);
            }

            \Utils\Geral::setCliente($cliente);

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    function uploadDocumentos($params)
    {

        try {
            $clienteSessao = (\Utils\Geral::isCliente() ? \Utils\Geral::getCliente() : null);
            if ($clienteSessao != null) {
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $cliente->id = $clienteSessao->id;
                $clienteRn->conexao->carregar($cliente);
                $documento = new \Models\Modules\Cadastro\Documentos();
                $documentoRn = new \Models\Modules\Cadastro\DocumentosRn();
                $dados = array();
                $docs = \Utils\Post::get($params, "parDocs", null);

                switch ($docs) {
                    case 0: // Documento Frente  
                        $cliente->fotoDocumento = \Utils\File::get($params, "documento", "", array(), $cliente, \Utils\Constantes::DOCUMENTOS_FOTO);
                        if (!empty($cliente->fotoDocumento)) {
                            $cliente->motivoRecusaFotoDocumento = "";
                            $documento->idCliente = $cliente->id;
                            $documento->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
                            $documento->tipoDocumento = \Utils\Constantes::DOCUMENTO_FRENTE;
                            $documento->status = \Utils\Constantes::DOCUMENTO_PENDENTE; // EM ANALISE
                            $documento->nomeArquivo = $cliente->fotoDocumento;

                            $documentoRn->salvar($documento);

                            $dados["foto_documento"] = $documento->id;
                            $dados["foto_documento_verificada"] = \Utils\Constantes::DOCUMENTO_PENDENTE;
                            $dados["motivo_recusa_foto_documento"] = $cliente->motivoRecusaFotoDocumento;
                        }
                        break;

                    case 1: // Documento Verso                        
                        $cliente->fotoDocumentoVerso = \Utils\File::get($params, "documento", "", array(), $cliente, \Utils\Constantes::DOCUMENTOS_FOTO);
                        if (!empty($cliente->fotoDocumentoVerso)) {
                            $documento->idCliente = $cliente->id;
                            $documento->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
                            $documento->tipoDocumento = \Utils\Constantes::DOCUMENTO_VERSO;
                            $documento->status = \Utils\Constantes::DOCUMENTO_PENDENTE; // EM ANALISE
                            $documento->nomeArquivo = $cliente->fotoDocumentoVerso;

                            $documentoRn->salvar($documento);

                            $dados["foto_documento_verso"] = $documento->id;
                        }
                        break;

                    case 2: // Residencia
                        $cliente->fotoResidencia = \Utils\File::get($params, "documento", "", array(), $cliente, \Utils\Constantes::COMPROVANTE_RESIDENCIA_FOTO);
                        if (!empty($cliente->fotoResidencia)) {
                            $documento->idCliente = $cliente->id;
                            $documento->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
                            $documento->tipoDocumento = \Utils\Constantes::DOCUMENTO_COMP_RESIDENCIA;
                            $documento->status = \Utils\Constantes::DOCUMENTO_PENDENTE; // EM ANALISE
                            $documento->nomeArquivo = $cliente->fotoResidencia;
                            $cliente->motivoRecusaFotoResidencia = "";

                            $documentoRn->salvar($documento);

                            $dados["foto_residencia"] = $documento->id;
                            $dados["foto_residencia_verificada"] = \Utils\Constantes::DOCUMENTO_PENDENTE;
                            $dados["motivo_recusa_foto_residencia"] = $cliente->motivoRecusaFotoResidencia;
                        }
                        break;

                    case 3: // Selfie
                        $cliente->fotoCliente = \Utils\File::get($params, "documento", "", array(), $cliente, \Utils\Constantes::SELFIE_FOTO);
                        if (!empty($cliente->fotoCliente)) {
                            $documento->idCliente = $cliente->id;
                            $documento->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
                            $documento->tipoDocumento = \Utils\Constantes::DOCUMENTO_SELFIE;
                            $documento->status = \Utils\Constantes::DOCUMENTO_PENDENTE; // EM ANALISE
                            $documento->nomeArquivo = $cliente->fotoCliente;
                            $cliente->motivoRecusaFotoCliente = "";

                            $documentoRn->salvar($documento);

                            $dados["foto_cliente"] = $documento->id;
                            $dados["foto_cliente_verificada"] = \Utils\Constantes::DOCUMENTO_PENDENTE;
                            $dados["motivo_recusa_foto_cliente"] = $cliente->motivoRecusaFotoCliente;
                        }
                        break;

                    case 4: // PJ - Outros documentos
                        $cliente->fotoOutroDocumento = \Utils\File::get($params, "documento", "", array(), $cliente, \Utils\Constantes::OUTROS_FOTO);
                        if (!empty($cliente->fotoOutroDocumento)) {
                            $documento->idCliente = $cliente->id;
                            $documento->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
                            $documento->tipoDocumento = \Utils\Constantes::DOCUMENTO_PJ;
                            $documento->status = \Utils\Constantes::DOCUMENTO_PENDENTE; // EM ANALISE
                            $documento->nomeArquivo = $cliente->fotoOutroDocumento;
                            $cliente->motivoRecusaFotoOutroDocumento = "";

                            $documentoRn->salvar($documento);

                            $dados["foto_outro_documento"] = $documento->id;
                            $dados["foto_outro_documento_verificada"] = \Utils\Constantes::DOCUMENTO_PENDENTE;
                            $dados["motivo_recusa_foto_outro_documento"] = $cliente->motivoRecusaFotoCliente;
                        }
                        break;
                }

                if (sizeof($dados) > 0) {
                    $clienteRn->conexao->update(
                        $dados,
                        array(
                            "id" => $cliente->id
                        ));

                } else {
                    throw new \Exception($this->idioma->getText("necessarioInformarDocumentos"));
                }
            }
            $json["mensagem"] = $this->idioma->getText("arquivoEnviadoSucesso");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function listaDocumentos($params)
    {
        try {
            $idioma = new \Utils\PropertiesUtils("perfil", IDIOMA);
            $clienteSessao = \Utils\Geral::getCliente();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = $clienteSessao->id;
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            $documentosRn = new \Models\Modules\Cadastro\DocumentosRn();
            $documentoFrente = new \Models\Modules\Cadastro\Documentos();
            $documentoVerso = new \Models\Modules\Cadastro\Documentos();
            $documentoResidencia = new \Models\Modules\Cadastro\Documentos();
            $documentoSelfie = new \Models\Modules\Cadastro\Documentos();
            $documentoPj = new \Models\Modules\Cadastro\Documentos();

            if ($cliente->fotoDocumentoVerificada > 0) {
                $documentoFrente->id = $cliente->fotoDocumento;
                $documentosRn->conexao->carregar($documentoFrente);
            }

            if (!empty($cliente->fotoDocumentoVerso)) {
                $documentoVerso->id = $cliente->fotoDocumentoVerso;
                $documentosRn->conexao->carregar($documentoVerso);
            }

            if ($cliente->fotoResidenciaVerificada > 0) {
                $documentoResidencia->id = $cliente->fotoResidencia;
                $documentosRn->conexao->carregar($documentoResidencia);
            }

            if ($cliente->fotoClienteVerificada > 0) {
                $documentoSelfie->id = $cliente->fotoCliente;
                $documentosRn->conexao->carregar($documentoSelfie);
            }

            if ($cliente->fotoOutroDocumentoVerificada > 0) {
                $documentoPj->id = $cliente->fotoOutroDocumento;
                $documentosRn->conexao->carregar($documentoPj);
            }

            ob_start();


            ?>
            <tr> <!--DOCUMENTO FRENTE-->

                <td class="text-center"><?php echo $cliente->fotoDocumentoVerificada != \Utils\Constantes::DOCUMENTO_VERIFICADO ? $idioma->getText("documentofrente") : $idioma->getText("tipodocumentoenvio"); ?></td>
                <td class="text-center"><?php
                    switch ($cliente->fotoDocumentoVerificada) {
                        case 1: // Verificado
                            echo empty($documentoFrente->dataEnvio) ? " - " : $documentoFrente->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 2: // Pendente
                            echo empty($documentoFrente->dataEnvio) ? " - " : $documentoFrente->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3: // Recusado
                            echo empty($documentoFrente->dataEnvio) ? " - " : $documentoFrente->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoDocumentoVerificada) {
                        case 1:
                            echo empty($documentoFrente->dataAnalise) ? " - " : $documentoFrente->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3:
                            echo empty($documentoFrente->dataAnalise) ? " - " : $documentoFrente->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoDocumentoVerificada) {
                        case 0:
                            echo '-';
                            break;
                        case 1:
                            echo $idioma->getText("documentoaprovado");
                            break;
                        case 2:
                            echo $idioma->getText("documentoanalise");
                            break;
                        case 3:
                            echo $idioma->getText("documentorejeitado") ?>
                            <a tabindex="0" class="motivo-recusa" role="button"
                               data-controle='<?php echo $idioma->getText("documentorejeitado") ?>'
                               data-motivo='<?php echo $cliente->motivoRecusaFotoDocumento ?>' data-toggle="popover"
                               data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                                <i style="font-size: 15px;" class="fa fa-info-circle"></i>
                            </a>
                            <?php break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoDocumentoVerificada) {
                        case 0:
                            $botaoHabilitado = 'disabled';
                            if (!empty($cliente->nome) && !empty($cliente->email) && !empty($cliente->documento) && !empty($cliente->dataNascimento) && !empty($cliente->nomeMae) && !empty($cliente->ddi) && !empty($cliente->celular)) {
                                $botaoHabilitado = 'onclick="$(\'#inputDocs0\').click();"';
                            }
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" ' . $botaoHabilitado . ' name="0">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                            break;
                        case 1:
                            echo '<i class="fa fa-check-circle" style="color: #00ae4d;font-size: 14px !important;"></i>';
                            break;
                        case 2:
                            echo '<i class="fa fa-check-circle" style="color: #f8ac59;font-size: 14px !important;"></i>';
                            break;
                        case 3:
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-warning" onclick="$(\'#inputDocs0\').click();" name="0">
                                          <small>' . $idioma->getText("reenviarBtn") . '</small>
                                          </button>';
                            break;
                    }
                    ?>
                </td>
            </tr>

            <!--DOCUMENTO VERSO -->
            <?php if ($cliente->fotoDocumentoVerificada != 1) { ?>
                <tr>

                    <td class="text-center"><?php echo $idioma->getText("documentoverso"); ?></td>
                    <td class="text-center"><?php
                        switch ($cliente->fotoDocumentoVerificada) {
                            case 1:
                                echo empty($documentoVerso->dataEnvio) ? " - " : $documentoVerso->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 2:
                                echo empty($documentoVerso->dataEnvio) ? " - " : $documentoVerso->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 3:
                                echo empty($documentoVerso->dataEnvio) ? " - " : $documentoVerso->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoDocumentoVerificada) {
                            case 1:
                                echo empty($documentoVerso->dataAnalise) ? " - " : $documentoVerso->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 3:
                                echo empty($documentoVerso->dataAnalise) ? " - " : $documentoVerso->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoDocumentoVerificada) {
                            case 0:
                                echo '-';
                                break;
                            case 1:
                                echo $idioma->getText("documentoaprovado");
                                break;
                            case 2:
                                if (empty($cliente->fotoDocumentoVerso)) {
                                    echo '-';
                                } else {
                                    echo $idioma->getText("documentoanalise");
                                }

                                break;
                            case 3:
                                echo "-";
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoDocumentoVerificada) {
                            case 0:
                                $botaoHabilitado = 'disabled';
                                if (!empty($cliente->nome) && !empty($cliente->email) && !empty($cliente->documento) && !empty($cliente->dataNascimento) && !empty($cliente->nomeMae) && !empty($cliente->ddi) && !empty($cliente->celular)) {
                                    $botaoHabilitado = 'onclick="$(\'#inputDocs1\').click();"';
                                }
                                echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success"  ' . $botaoHabilitado . '  name="0">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                                break;
                            case 1:
                                echo '<i class="fa fa-check-circle" style="color: #00ae4d;font-size: 14px !important;"></i>';
                                break;
                            case 2:
                                if (empty($cliente->fotoDocumentoVerso)) {
                                    echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" onclick="$(\'#inputDocs1\').click();" name="0">
                                             <small>' . $idioma->getText("enviarBtn") . '</small>
                                             </button>';
                                } else {
                                    echo '<i class="fa fa-check-circle" style="color: #f8ac59;font-size: 14px !important;"></i>';
                                }

                                break;
                            case 3:
                                echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" onclick="$(\'#inputDocs1\').click();" name="0">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                                break;
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>

            <!--DOCUMENTO RESIDENCIA-->
            <tr>
                <td class="text-center"><?php echo $idioma->getText("residencia"); ?></td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoResidenciaVerificada) {
                        case 1:
                            echo empty($documentoResidencia->dataEnvio) ? " - " : $documentoResidencia->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 2:
                            echo empty($documentoResidencia->dataEnvio) ? " - " : $documentoResidencia->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3:
                            echo empty($documentoResidencia->dataEnvio) ? " - " : $documentoResidencia->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoResidenciaVerificada) {
                        case 1:
                            echo empty($documentoResidencia->dataAnalise) ? " - " : $documentoResidencia->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3:
                            echo empty($documentoResidencia->dataAnalise) ? " - " : $documentoResidencia->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoResidenciaVerificada) {
                        case 0:
                            echo '-';
                            break;
                        case 1:
                            echo $idioma->getText("documentoaprovado");
                            break;
                        case 2:
                            echo $idioma->getText("documentoanalise");
                            break;
                        case 3:
                            echo $idioma->getText("documentorejeitado") ?>
                            <a tabindex="0" class="motivo-recusa" role="button"
                               data-controle='<?php echo $idioma->getText("documentorejeitado") ?>'
                               data-motivo='<?php echo $cliente->motivoRecusaFotoResidencia ?>' data-toggle="popover"
                               data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                                <i style="font-size: 15px;" class="fa fa-info-circle"></i>
                            </a>
                            <?php break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoResidenciaVerificada) {
                        case 0:
                            $botaoHabilitado = 'disabled';
                            if (!empty($cliente->endereco) && !empty($cliente->numero) && !empty($cliente->cep) && !empty($cliente->cidade) && !empty($cliente->estado) && !empty($cliente->idPais)) {
                                $botaoHabilitado = 'onclick="$(\'#inputDocs2\').click();"';
                            }
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" ' . $botaoHabilitado . ' name="2">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                            break;
                        case 1:
                            echo '<i class="fa fa-check-circle" style="color: #00ae4d;font-size: 14px !important;"></i>';
                            break;
                        case 2:
                            echo '<i class="fa fa-check-circle" style="color: #f8ac59;font-size: 14px !important;"></i>';
                            break;
                        case 3:
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-warning" onclick="$(\'#inputDocs2\').click();" name="2">
                                          <small>' . $idioma->getText("reenviarBtn") . '</small>
                                          </button>';
                            break;
                    }
                    ?>
                </td>
            </tr>

            <!--DOCUMENTO FOTO-->
            <tr>
                <td class="text-center"><?php echo $idioma->getText("selfie"); ?></td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoClienteVerificada) {
                        case 1:
                            echo empty($documentoSelfie->dataEnvio) ? " - " : $documentoSelfie->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 2:
                            echo empty($documentoSelfie->dataEnvio) ? " - " : $documentoSelfie->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3:
                            echo empty($documentoSelfie->dataEnvio) ? " - " : $documentoSelfie->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoClienteVerificada) {
                        case 1:
                            echo empty($documentoSelfie->dataAnalise) ? " - " : $documentoSelfie->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                        case 3:
                            echo empty($documentoSelfie->dataAnalise) ? " - " : $documentoSelfie->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                            break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoClienteVerificada) {
                        case 0:
                            echo '-';
                            break;
                        case 1:
                            echo $idioma->getText("documentoaprovado");
                            break;
                        case 2:
                            echo $idioma->getText("documentoanalise");
                            break;
                        case 3:
                            echo $idioma->getText("documentorejeitado") ?>
                            <a tabindex="0" class="motivo-recusa" role="button"
                               data-controle='<?php echo $idioma->getText("documentorejeitado") ?>'
                               data-motivo='<?php echo $cliente->motivoRecusaFotoCliente ?>' data-toggle="popover"
                               data-trigger="focus" style="margin-left: 5px; font-size: 9px">
                                <i style="font-size: 15px;" class="fa fa-info-circle"></i>
                            </a>
                            <?php break;
                    }
                    ?>
                </td>
                <td class="text-center">
                    <?php
                    switch ($cliente->fotoClienteVerificada) {
                        case 0:

                            $botaoHabilitado = 'disabled';
                            if (!empty($cliente->nome) && !empty($cliente->email) && !empty($cliente->documento) && !empty($cliente->dataNascimento) && !empty($cliente->nomeMae) && !empty($cliente->ddi) && !empty($cliente->celular)) {
                                $botaoHabilitado = 'onclick="$(\'#inputDocs3\').click();"';
                            }
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" ' . $botaoHabilitado . ' name="2">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                            break;
                        case 1:
                            echo '<i class="fa fa-check-circle" style="color: #00ae4d;font-size: 14px !important;"></i>';
                            break;
                        case 2:
                            echo '<i class="fa fa-check-circle" style="color: #f8ac59;font-size: 14px !important;"></i>';
                            break;
                        case 3:
                            echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-warning" onclick="$(\'#inputDocs3\').click();" name="2">
                                          <small>' . $idioma->getText("reenviarBtn") . '</small>
                                          </button>';
                            break;
                    }
                    ?>
                </td>
            </tr>

            <!--DOCUMENTO EMPRESA-->
            <?php if (($cliente->fotoDocumentoVerificada == 1) && ($cliente->fotoResidenciaVerificada == 1) && ($cliente->fotoClienteVerificada == 1)) {
                ?>

                <tr>
                    <td class="text-center"><?php echo $idioma->getText("empresa"); ?></td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoOutroDocumentoVerificada) {
                            case 1:
                                echo empty($documentoPj->dataEnvio) ? " - " : $documentoPj->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 2:
                                echo empty($documentoPj->dataEnvio) ? " - " : $documentoPj->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 3:
                                echo empty($documentoPj->dataEnvio) ? " - " : $documentoPj->dataEnvio->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoOutroDocumentoVerificada) {
                            case 1:
                                echo empty($documentoPj->dataAnalise) ? " - " : $documentoPj->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                            case 2:
                                echo "-";
                                break;
                            case 3:
                                echo empty($documentoPj->dataAnalise) ? " - " : $documentoPj->dataAnalise->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP);
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoOutroDocumentoVerificada) {
                            case 0:
                                echo '-';
                                break;
                            case 1:
                                echo $idioma->getText("documentoaprovado");
                                break;
                            case 2:
                                echo $idioma->getText("documentoanalise");
                                break;
                            case 3:
                                echo $idioma->getText("documentorejeitado");
                                break;
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <?php
                        switch ($cliente->fotoOutroDocumentoVerificada) {
                            case 0:
                                echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-success" onclick="$(\'#inputDocs4\').click();" name="3">
                                          <small>' . $idioma->getText("enviarBtn") . '</small>
                                          </button>';
                                break;
                            case 1:
                                echo '<i class="fa fa-check-circle" style="color: #00ae4d;font-size: 14px !important;"></i>';
                                break;
                            case 2:
                                echo '<i class="fa fa-check-circle" style="color: #f8ac59;font-size: 14px !important;"></i>';
                                break;
                            case 3:
                                echo '<button style="width: 63px !important;" type="button" class="btn btn-xs btn-warning" onclick="$(\'#inputDocs4\').click();"  name="3">
                                          <small>' . $idioma->getText("reenviarBtn") . '</small>
                                          </button>';
                                break;
                        }
                        ?>
                    </td>
                </tr>
            <?php }


            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function removerDocumento($params)
    {
        try {


            $cliente = \Utils\Geral::getCliente();

            $tipoDoc = \Utils\Post::get($params, "tipoDoc", null);

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            switch ($tipoDoc) {
                case "D":
                    $clienteRn->conexao->update(array("foto_documento" => null, "foto_documento_verso" => null, "foto_documento_verificada" => 0), array("id" => $cliente->id));
                    break;
                case "R":
                    $clienteRn->conexao->update(array("foto_residencia" => null, "foto_residencia_verificada" => 0), array("id" => $cliente->id));
                    break;
                case "C":
                    $clienteRn->conexao->update(array("foto_cliente" => null, "foto_cliente_verificada" => 0), array("id" => $cliente->id));
                    break;
                case "O":
                    $clienteRn->conexao->update(array("foto_outro_documento" => null, "foto_outro_documento_verificada" => 0), array("id" => $cliente->id));
                    break;
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function alterarSenha($params)
    {
        try {
            $senha = \Utils\Post::get($params, "senha", null);
            $confirmacao = \Utils\Post::get($params, "confirmacao", null);
            $senhaAtual = \Utils\Post::get($params, "senhaAtual", null);

            $forca = \Utils\Senha::forca($senha);
            if ($forca < 4) {
                throw new \Exception($this->idioma->getText("senhaMuitoFraca"));
            }

            if (\Utils\Geral::isCliente()) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $cliente = \Utils\Geral::getCliente();

                $clienteRn->conexao->carregar($cliente);

                $cliente->senha = $senha;
                $clienteRn->alterarSenha($cliente, $confirmacao, $senhaAtual);
            }

            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("senhaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }

        print json_encode($json);
    }

    public function alterarAlertas($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();

            $cliente->recebimentoAlertaMovimentacaoConta = \Utils\Post::get($params, "recebimentoAlertaMovimentacaoConta", "N");

            if (!in_array($cliente->recebimentoAlertaMovimentacaoConta, array("S", "E", "N"))) {
                throw new \Exception($this->idioma->getText("configRecebimentoInvalida"));
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->update(
                array(
                    "recebimento_alerta_movimentacao_conta" => $cliente->recebimentoAlertaMovimentacaoConta
                ),
                array(
                    "id" => $cliente->id
                )
            );

            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("configAlertaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }

        print json_encode($json);
    }

    public function atualizarImagem($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $base64Data = \Utils\Post::getBase64($params, "imagemBase64", null);


            if (empty($base64Data)) {
                throw new \Exception($this->idioma->getText("voceNaoSelecionouImagem"));
            }
            $tipo = str_replace("data:image/", "", substr($base64Data, 0, strpos($base64Data, ";")));

            $base64Data = substr($base64Data, strpos($base64Data, "base64,") + 7);

            $nomeFoto = time() . ".{$tipo}";

            if (!file_exists(UPLOADS . $cliente->id)) {
                mkdir(UPLOADS . $cliente->id);
            }

            if (!file_exists(UPLOADS . $cliente->id . "/profile")) {
                mkdir(UPLOADS . $cliente->id . "/profile");
            }
            $url = UPLOADS . $cliente->id . "/profile/" . $nomeFoto;
            $binary = base64_decode($base64Data); // decoficiação da imagem base64 em binary
            $file = fopen($url, 'wb'); // crio um arquivo com o nome que eu defino para a imagem 
            // Importante, o arquivo deve ser aberto com o parâmetro wb pois a escrita será de dados binários
            fwrite($file, $binary); // escrevo a imagem no arquivo
            fclose($file); // fecho o arquivo


            if (\Utils\Geral::isCliente()) {
                $cliente->foto = $cliente->id . "/profile/" . $nomeFoto;
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

                $clienteRn->conexao->update(array("foto" => $cliente->foto), array("id" => $cliente->id));
            }


            $clienteRn->conexao->carregar($cliente);
            \Utils\Geral::setLogado(null, $cliente);

            $json["url"] = URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/" . \Utils\Criptografia::encriptyPostId($cliente->foto);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function forcaSenha($params)
    {
        try {
            $senha = \Utils\Post::get($params, "senha", "");

            $forca = \Utils\Senha::forca($senha);

            $json["forca"] = $forca;
            $json["percentual"] = ($forca = 0 ? 0 : ($forca < 4 ? 3 : ($forca < 6 ? 6 : 10)));
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function solicitarAlteracaoPerfil($params)
    {
        try {

            $idLicencaSoftware = \Utils\Post::getEncrypted($params, "tipoPerfil", "");

            $cliente = \Utils\Geral::getCliente();

            $clienteHasLicencaRn = new \Models\Modules\Cadastro\ClienteHasLicencaRn();
            $clienteHasLicenca = new \Models\Modules\Cadastro\ClienteHasLicenca();
            $clienteHasLicenca->id = 0;
            $clienteHasLicenca->idCliente = $cliente->id;
            $clienteHasLicenca->idLicencaSoftware = $idLicencaSoftware;

            $clienteHasLicencaRn->salvar($clienteHasLicenca);

            try {
                \Email\UpgradePerfilSolicitado::send($cliente);
            } catch (\Exception $ex) {

            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print \Zend\Json\Json::encode($json);
    }

    public function prepararGerarKey($params)
    {

        try {
            $cliente = \Utils\Geral::getCliente();

            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("vocePrecisaRegistrarCliente"));
            }

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            if (empty($cliente->pin)) {
                throw new \Exception($this->idioma->getText("vocePrecisaCadastrarPinAcesseNovamente"));
            }

            if ($cliente->documentoVerificado != 1) {
                throw new \Exception($this->idioma->getText("contaNaoVerificada"));
            }

            if ($cliente->tipoAutenticacao != \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                throw new \Exception("Você deve utilizar 2FA Authenticator para gerar suas  credenciais.");
            }

            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $auth = new \Models\Modules\Cadastro\Auth();
            $auth->idCliente = $cliente->id;

            $authRn->salvar($auth, \Utils\Constantes::TIPO_AUTH_GOOGLE);

            $json["mensagem"] = $this->idioma->getText("useGoogle");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function gerarKey($params)
    {
        try {

            $token = \Utils\Post::get($params, "token", null);

            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);

            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("vocePrecisaRegistrarCliente"));
            }

            if ($cliente->tipoAutenticacao != \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                throw new \Exception("Você deve utilizar 2FA Authenticator para gerar suas  credenciais.");
            }

            if ($cliente->documentoVerificado != 1) {
                throw new \Exception($this->idioma->getText("contaNaoVerificada"));
            }

            if(empty($token) || !is_numeric($token)){
                throw new \Exception("Token inválido.");
            }

            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token, $cliente);

            $clienteRn->gerarApiKeys($cliente);

            $json["sucesso"] = true;
            $json["mensagem"] = "Credenciais geradas com sucesso e enviadas para seu e-mail. ";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function token($params)
    {
        try {

            $tipo = \Utils\Post::get($params, "tipo", null);

            $auth = new \Models\Modules\Cadastro\Auth();
            $cliente = \Utils\Geral::getCliente();

            $email = $cliente->email;
            $telefone = $cliente->celular;
            $auth->idCliente = $cliente->id;

            if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $tipo = $cliente->tipoAutenticacao;
            }

            $authRn = new \Models\Modules\Cadastro\AuthRn();

            $authRn->salvar($auth, $tipo);

            if ($tipo == \Utils\Constantes::TIPO_AUTH_EMAIL) {
                $json["meio"] = $this->idioma->getText("") . $email . $this->idioma->getText("porFavorInsiraToken");
            }

            if ($tipo == \Utils\Constantes::TIPO_AUTH_SMS) {
                $json["meio"] = $this->idioma->getText("foiEnviadoSMS") . $telefone . $this->idioma->getText("porFavorInsiraToken");
            }

            if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $json["meio"] = $this->idioma->getText("useGoogle");
            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public static function htmlGradeLicencas(\Models\Modules\Cadastro\ClienteHasLicenca $licenca = null, \Models\Modules\Cadastro\ClienteHasLicenca $solicitacaoLicenca = null)
    {

        ob_start();
        try {

            $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
            $licencas = $licencaSoftwareRn->conexao->listar("ativo > 0", "ordem", NULL, NULL);


            if ($licenca != null) {
                ?>
                <div class="col col-xs-12">
                    <br>
                    <div class="alert alert-info text-center">
                        <?php echo /*$this->idioma->getText("parabens") .*/ $licenca->licencaSoftware->nome ?>.
                    </div>
                    <br>
                </div>
                <?php
            }

            if (sizeof($licencas) > 0) {
                $temLicenca = false;
                foreach ($licencas as $licencaSoftware) {
                    if ($licenca == null || $licenca->licencaSoftware->ordem < $licencaSoftware->ordem) {
                        self::tableLicencaSoftware($licencaSoftware, $solicitacaoLicenca);
                        $temLicenca = true;
                    }
                }

                if ($temLicenca) {
                    ?>
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-4 col-xs-12 col-md-offset-4 col-md-offset-0">
                            <div class="form-buttons text-center">
                                <br><br>
                                <?php if ($solicitacaoLicenca == false) { ?>
                                    <button class="btn btn-primary text-center full-width" type="button"
                                            id="btnAlterarPerfil" onclick="solicitarAlteracaoPerfil();">
                                        <?php echo /*$this->idioma->getText("solicitarLicenca")*/ "" ?>
                                    </button>

                                <?php } ?>


                                <br>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="col col-xs-12 text-center">
                    <?php /*echo $this->idioma->getText("nenhumaLicenca")*/ ?>
                </div>
                <?php
            }


        } catch (\Exception $ex) {
            ?>
            <div class="col col-xs-12 text-center">
                <?php echo \Utils\Excecao::mensagem($ex) ?>
            </div>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function getGoogleAuthSecret($params)
    {
        try {
            $ga = new \GoogleAuth\GoogleAuthenticator();
            $time = time();
            $secret = $ga->createSecret();

            $qrCode = $ga->getQRCodeGoogleUrl(TITULO, $secret, TITULO);

            $json["qrCode"] = $qrCode;
            $json["secret"] = $secret;

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = true;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function getCountryBrand($params)
    {
        try {
            $ddi = \Utils\Post::get($params, "ddi", null);

            if (!empty($ddi)) {
                $paisRn = new \Models\Modules\Cadastro\PaisRn();
                $result = $paisRn->conexao->listar("ddi = '{$ddi}'", "nome");

                if (sizeof($result) > 0) {
                    $pais = $result->current();

                    $json["src"] = IMAGES;

                }

            }

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function getEstadosByPais($params)
    {

        try {
            $pais = new \Models\Modules\Cadastro\Pais();
            $pais->id = \Utils\Post::getEncrypted($params, "pais", 0);
            $idEstado = \Utils\Post::getEncrypted($params, "estado", 0);

            $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            $estados = $estadoRn->conexao->listar("id_pais = {$pais->id}", "nome");

            ob_start();
            ?>
            <option value="0">Selecione um estado</option>
            <?php
            if (sizeof($estados) > 0) {
                foreach ($estados as $estado) {

                    $selected = ($idEstado == $estado->id ? "selected='true'" : "");
                    ?>
                    <option value="<?php echo $estado->id ?>" <?php echo $selected ?>><?php echo $estado->nome ?></option>
                    <?php
                }
            }
            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function getNotificacao($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $notificacaoClienteOperacaoRn = new \Models\Modules\Cadastro\NotificacaoClienteOperacaoRn();
            $comunicacaoRn = new \Models\Modules\Cadastro\NotificacaoComunicacaoRn();
            $operacaoRn = new \Models\Modules\Cadastro\NotificacaoOperacaoRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $disabled = "";
            $clienteVerificado = $clienteRn->clienteVerificado($cliente);

            $result = $operacaoRn->conexao->listar(" ativo = 1 ");

            ob_start();
            foreach ($result as $operacao) { #loop menus notificações comentario André 18/07/2019

                #echo "<pre>" ,print_r($operacao->traducao);exit;

                switch ($clienteVerificado) {
                    case true: ?>
                        <tr>
                            <td><?php echo $this->idioma->getText($operacao->traducao);#coluna notificação ?></td>
                            <?php
                            $resultComunicao = $comunicacaoRn->conexao->listar(" ativo = 1 ");
                            $disabled = "";
                            foreach ($resultComunicao as $comunicacao) {
                                $resultCliente = $notificacaoClienteOperacaoRn->conexao->listar(" id_cliente = {$cliente->id} AND id_notificacao_operacao = {$operacao->id} AND id_notificacao_comunicacao = {$comunicacao->id} ");

                                if (sizeof($resultCliente) > 0) {
                                    foreach ($resultCliente as $clienteOperacao) {
                                        if ($clienteOperacao->idNotificacaoComunicacao == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR && $clienteOperacao->ativo == 1) {
                                            $disabled = "disabled";
                                        } ?>
                                        <td class="text-center"><input
                                                    type="checkbox" <?php echo $clienteOperacao->idNotificacaoComunicacao == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR ? "" : $disabled #coluna desativar
                                            ?>
                                                    id="<?php echo \Utils\Criptografia::encriptyPostId($clienteOperacao->id) ?>"
                                                    class="js-switch"
                                                    onchange="salvarNotificacao('<?php echo \Utils\Criptografia::encriptyPostId($clienteOperacao->id) ?>');" <?php echo $clienteOperacao->ativo == 0 ? "" : "checked" ?>/>
                                        </td>
                                    <?php }
                                } else { ?>

                                    <td class="text-center"><input
                                                type="checkbox" <?php echo isset($clienteOperacao->idNotificacaoComunicacao) == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR ? "" : $disabled ?>
                                                id="<?php echo \Utils\Criptografia::encriptyPostId($operacao->id . "#" . $comunicacao->id) ?>"
                                                onchange="salvarNotificacao('<?php echo \Utils\Criptografia::encriptyPostId($operacao->id . "#" . $comunicacao->id) ?>');"
                                                class="js-switch"/></td>
                                <?php }
                            } ?>
                        </tr>
                        <?php
                        break;

                    case false:
                        if ($operacao->idCategoriaMoeda != 1) { ?>
                            <tr>
                                <td><?php echo $this->idioma->getText($operacao->traducao) ?></td>
                                <?php
                                $resultComunicao = $comunicacaoRn->conexao->listar(" ativo = 1 ");
                                $disabled = "";
                                foreach ($resultComunicao as $comunicacao) {
                                    $resultCliente = $notificacaoClienteOperacaoRn->conexao->listar(" id_cliente = {$cliente->id} AND id_notificacao_operacao = {$operacao->id} AND id_notificacao_comunicacao = {$comunicacao->id} ");

                                    if (sizeof($resultCliente) > 0) {
                                        foreach ($resultCliente as $clienteOperacao) {
                                            if ($clienteOperacao->idNotificacaoComunicacao == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR && $clienteOperacao->ativo == 1) {
                                                $disabled = "disabled";
                                            } ?>
                                            <td class="text-center"><input
                                                        type="checkbox" <?php echo $clienteOperacao->idNotificacaoComunicacao == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR ? "" : $disabled ?>
                                                        id="<?php echo \Utils\Criptografia::encriptyPostId($clienteOperacao->id) ?>"
                                                        class="js-switch"
                                                        onchange="salvarNotificacao('<?php echo \Utils\Criptografia::encriptyPostId($clienteOperacao->id) ?>');" <?php echo $clienteOperacao->ativo == 0 ? "" : "checked" ?>/>
                                            </td>
                                        <?php }
                                    } else { ?>
                                        <td class="text-center"><input
                                                    type="checkbox" <?php echo $clienteOperacao->idNotificacaoComunicacao == \Utils\Constantes::NOTIFICACAO_TIPO_DESATIVAR ? "" : $disabled ?>
                                                    id="<?php echo \Utils\Criptografia::encriptyPostId($operacao->id . "#" . $comunicacao->id) ?>"
                                                    onchange="salvarNotificacao('<?php echo \Utils\Criptografia::encriptyPostId($operacao->id . "#" . $comunicacao->id) ?>');"
                                                    class="js-switch"/></td>
                                    <?php }
                                } ?>
                            </tr>
                            <?php
                        }
                        break;
                }
            }

            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function getTitulo($params)
    {
        try {
            $comunicacaoRn = new \Models\Modules\Cadastro\NotificacaoComunicacaoRn();

            $result = $comunicacaoRn->conexao->listar(" ativo = 1 ");

            ob_start(); ?>
            <th><?php echo $this->idioma->getText("perfil23") ?></th>
            <?php

            foreach ($result as $dados) {
                ?>
                <th class="text-center"><?php echo $this->idioma->getText($dados->traducao) ?></th>
                <?php
            }

            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function salvarNotificacao($params)
    {
        try {
            $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoClienteOperacaoRn();
            $notificacaoCliente = new \Models\Modules\Cadastro\NotificacaoClienteOperacao();
            $cliente = \Utils\Geral::getCliente();
            $id = \Utils\Post::getEncrypted($params, "id", null);
            $status = \Utils\Post::get($params, "status", false);

            if (strpos($id, "#")) {
                $chars = explode('#', $id);
                $notificacaoCliente->idCliente = $cliente->id;
                $notificacaoCliente->idNotificacaoOperacao = $chars[0]; // ID operação
                $notificacaoCliente->idNotificacaoComunicacao = $chars[1]; // ID comunicação
                $notificacaoCliente->ativo = $status == true ? 1 : 0;
                $notificacaoRn->salvar($notificacaoCliente);

            } else {
                $notificacaoCliente->id = $id;
                $notificacaoRn->alterarStatus($notificacaoCliente);
            }


            $json["mensagem"] = $this->idioma->getText("notificacaoSucesso");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function notaFiscalCliente($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $notaFiscalRn = new \Models\Modules\Cadastro\NotaFiscalRn();
            $data = \Utils\Post::get($params, "data", "todos");
            $status = \Utils\Post::get($params, "status", "T");
            $tipoNota = \Utils\Post::get($params, "tipoNota", "T");
            $filtro = \Utils\Post::get($params, "filtro", null);

            switch ($data) {
                case "dia":
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;
                case "todos":
                    $dataInicial = null;
                    $dataFinal = null;
                    break;
            }

            $result = $notaFiscalRn->filtrarCliente($cliente->id, $dataInicial, $dataFinal, $tipoNota, $status, $filtro);

            $json["html"] = $this->itemNotaFiscal($result);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function itemNotaFiscal($dados)
    {
        ob_start();
        if (sizeof($dados) > 0) {
            foreach ($dados as $notaFiscal) {
                $servico = "";
                $status = "";

                if ($notaFiscal->idDeposito != null) {
                    $servico = "Depósito";
                } else if ($notaFiscal->idSaque != null) {
                    $servico = "Saque";
                } else if ($notaFiscal->idBoleto != null) {
                    $servico = "Boleto";
                } else if ($notaFiscal->idRemessaDinheiro != null) {
                    $servico = "Remessa de Valores";
                }

                if ($notaFiscal->status == "Negada") {
                    $status = "Negada";
                } else if ($notaFiscal->status == "AguardandoAutorizacao") {
                    $status = "Aguardando Autorização";
                } else if ($notaFiscal->status == "SolicitandoAutorizacao") {
                    $status = "Solicitando Autorização";
                } else if ($notaFiscal->status == "AutorizacaoSolicitada") {
                    $status = "Autorização Solicitada";
                } else if ($notaFiscal->status == "EmProcessoDeAutorizacao") {
                    $status = "Em Processo De Autorização";
                } else if ($notaFiscal->status == "Autorizada") {
                    $status = "Autorizada";
                } else if ($notaFiscal->status == "AutorizadaAguardandoGeracaoPDF") {
                    $status = "Autorizada - Aguardando Geração PDF";
                } else if ($notaFiscal->status == "SolicitandoCancelamento") {
                    $status = "Solicitando Cancelamento";
                } else if ($notaFiscal->status == "CancelamentoSolicitado") {
                    $status = "Cancelamento Solicitado";
                } else if ($notaFiscal->status == "Cancelada") {
                    $status = "Cancelada";
                } else if ($notaFiscal->status == "CanceladaAguardandoAtualizacaoPDF") {
                    $status = "Cancelada - Aguardando Atualização PDF";
                } else if ($notaFiscal->status == "CancelamentoNegado") {
                    $status = "Cancelamento Negado";
                } else if ($notaFiscal->status == "EmProcessoDeCancelamento") {
                    $status = "Em Processo De Cancelamento";
                }


                ?>
                <tr>
                    <td class="text-center"><?php echo $notaFiscal->numeroNf ?></td>
                    <td class="text-center"><?php echo $notaFiscal->idnf ?></td>
                    <td class="text-center"><?php echo $notaFiscal->dataCriacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                    <td class="text-center"><?php echo($notaFiscal->dataAutorizacao != null ? $notaFiscal->dataAutorizacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : "") ?></td>
                    <td class="text-center"><?php echo $status ?></td>
                    <td class="text-center"><a href="<?php echo $notaFiscal->linkDownloadPdf ?>" target="_BLANK"><i
                                    style="color: #d00000 !important;" class="fa fa-file-pdf-o"></i></a></td>
                    <td class="text-center"><?php echo $servico ?></td>
                    <td class="text-center">R$ <?php echo number_format($notaFiscal->valorTotal, 2, ",", ".") ?></td>
                </tr>
                <?php
            }
        } else { ?>
            <tr>
                <td colspan="12" style='text-align: center'>
                    <?php echo $this->idioma->getText("notafiscal26") ?>
                </td>
            </tr>
            <?php
        }

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function listaNavegadores($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $navegadorRn = new \Models\Modules\Cadastro\NavegadorRn();

            $result = $navegadorRn->navegadorByCliente($cliente->id);

            ob_start();

            foreach ($result as $dados) {
                $nav = new \Models\Modules\Cadastro\Navegador($dados);
                if ($nav->ativo == 1) {
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $nav->navegador ?></td>
                        <td class="text-center"><?php echo $nav->sistemaOperacional ?></td>
                        <td class="text-center"><?php echo(empty(trim($nav->localizacao)) ? $this->idioma->getText("desconhecido") : $nav->localizacao) ?></td>
                        <td class="text-center"><?php echo str_replace(array("::1", "127.0.0.1"), "Localhost", $nav->ipUltimoAcesso) ?></td>
                        <td class="text-center"><?php echo $nav->dataAcesso->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                    </tr>
                <?php }
            }

            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function notaFiscalOperacao($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $depositoAtivo = \Utils\Post::getBoolean($params, "deposito", false);
            $saqueAtivo = \Utils\Post::getBoolean($params, "saque", false);

            $nfOpeCliente = new \Models\Modules\Cadastro\NotaFiscalOperacaoCliente();
            $nfOpeClienteRn = new \Models\Modules\Cadastro\NotaFiscalOperacaoClienteRn();

            $nfOpeCliente->idCliente = $cliente->id;
            $nfOpeCliente->depositoAtivo = $depositoAtivo == false ? 0 : 1;
            $nfOpeCliente->saqueAtivo = $saqueAtivo == false ? 0 : 1;

            $result = $nfOpeClienteRn->filtrarCliente($cliente->id);

            if ($result == null) {
                $nfOpeClienteRn->salvar($nfOpeCliente);
            } else {
                $nfOpeCliente->id = $result->id;
                $nfOpeClienteRn->alterarStatus($nfOpeCliente);
            }

            $json["mensagem"] = $this->idioma->getText("notafiscal35");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function getNotaFiscalOperacao($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $nfOpeClienteRn = new \Models\Modules\Cadastro\NotaFiscalOperacaoClienteRn();

            $nfOpeCliente = $nfOpeClienteRn->filtrarCliente($cliente->id);

            ob_start(); ?>
            <tr>
                <td><?php echo $this->idioma->getText("notafiscal30") ?></td>
                <td><input type="checkbox" id="depositoNf"
                           onchange="setNotaOperacao();" <?php echo $nfOpeCliente->saqueAtivo ? "checked" : ""; ?>></td>
            </tr>
            <tr>
                <td><?php echo $this->idioma->getText("notafiscal31") ?></td>
                <td><input type="checkbox" id="saqueNf"
                           onchange="setNotaOperacao();" <?php echo $nfOpeCliente->depositoAtivo ? "checked" : ""; ?>>
                </td>
            </tr>
            <?php

            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function meusLimites($params)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $clienteVerificado = $clienteRn->clienteVerificado($cliente);

            $json["verificado"] = $clienteVerificado;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function menuDinamico($param)
    {
        try {
            $cliente = \Utils\Geral::getCliente();
            $empresa = \Models\Modules\Cadastro\EmpresaRn::getEmpresa();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);

            $documentosEntregue = $clienteRn->clienteVerificado($cliente);

            $seguranca = false;
            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE && !empty($cliente->pin) && !empty($cliente->fraseSeguranca)) {
                $seguranca = true;
            }

            $todosOsDadosPreenchidos = ((!empty($cliente->nome)) && (!empty($cliente->email)) && (!empty($cliente->nomeMae)) && (!empty($cliente->nomePai)) && (!empty($cliente->celular)) && (!empty($cliente->dataNascimento)) && (!empty($cliente->rg)) && (!empty($cliente->documento)) &&
                (!empty($cliente->endereco)) && (!empty($cliente->bairro)) && (!empty($cliente->cep)) && (!empty($cliente->cidade)));

            ob_start(); ?>


            <!--menus Laterais do perfil do usuário -->

            <a data-toggle="tab" href="#tab-1" onclick="menuDinamico('liMeusDados');">
                <li class="list-group-item active" id="liMeusDados">
                    <small class="pull-right text-muted"></small>
                    <strong><?php echo $this->idioma->getText("meusDados") ?></strong>&nbsp;&nbsp;<i
                            class="fa fa-check-circle"
                            style="color: <?php echo $todosOsDadosPreenchidos ? "#00ae4d;" : "#f8ac59" ?>"></i>
                    <div class="small m-t-xs">
                        <p class="m-b-xs">
                            <?php echo $this->idioma->getText("minhasInformacoes") ?>
                            <br/>
                        </p>
                        <p class="m-b-none">
                        </p>
                    </div>
                </li>
            </a>
            <?php if ($documentosEntregue && $configuracao->kyc == 1) { ?>
                <a data-toggle="tab" href="#tab-3" onclick="listaDocumentos(); menuDinamico('liDocumentos');">
                    <li class="list-group-item" id="liDocumentos">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo $this->idioma->getText("documentos") ?></strong>&nbsp;&nbsp;<i
                                class="fa fa-check-circle"
                                style="color: <?php echo $documentosEntregue ? "#00ae4d;" : "#f8ac59" ?>"></i>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo $this->idioma->getText("enviarDocumentos") ?>
                                <br/>
                            </p>
                        </div>
                    </li>
                </a>
            <?php } ?>
            <?php if ($cliente->documentoVerificado == 1) { ?>
                <a data-toggle="tab" href="#tab-15" onclick="listaEmpresa(); menuDinamico('liEmpresa');">
                    <li class="list-group-item" id="liEmpresa">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo "Empresa" ?></strong>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo "Cadastrar CNPJ" ?>
                                <br/>
                            </p>
                        </div>
                    </li>
                </a>
            <?php } ?>
            <a data-toggle="tab" href="#tab-6" onclick="menuDinamico('liSeguranca');">
                <li class="list-group-item" id="liSeguranca">
                    <small class="pull-right text-muted"></small>
                    <strong><?php echo $this->idioma->getText("abaSeguranca") ?></strong>&nbsp;&nbsp;<i
                            class="fa fa-check-circle"
                            style="color: <?php echo $seguranca ? "#00ae4d;" : "#f8ac59" ?>"></i>
                    <div class="small m-t-xs">
                        <p class="m-b-xs">
                            <?php echo $this->idioma->getText("perfil12") ?>
                            <br/>
                        </p>
                    </div>
                </li>
            </a>
            <?php if ($documentosEntregue) { ?>
                <a data-toggle="tab" href="#tab-5" onclick="listarContasBancarias(); menuDinamico('liMinhasContas');">
                    <li class="list-group-item" id="liMinhasContas">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo $this->idioma->getText("perfil50") ?></strong>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo $this->idioma->getText("perfil51") ?>
                                <br/>
                            </p>
                        </div>
                    </li>
                </a>
            <?php } ?>

            <?php if (($documentosEntregue) && ($empresa->notaFiscal == 1)) { ?>
                <a data-toggle="tab" href="#tab-10"
                   onclick="menuDinamico('liNotaFiscal'); listaNotaFiscal(); getNotaOperacao();">
                    <li class="list-group-item" id="liNotaFiscal">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo $this->idioma->getText("notaFiscal") ?></strong>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo $this->idioma->getText("notafiscal33") ?>
                            </p>
                            <p class="m-b-none">
                            </p>
                        </div>
                    </li>
                </a>
            <?php } ?>

            <?php if ($documentosEntregue) {
                //if(\Models\Modules\Acesso\ModuloRn::validar(\Utils\Rotas::M_DOC, \Utils\Constantes::ACESSO)){ ?>
                <a data-toggle="tab" href="#tab-8" class="tab-etapas-perfil" onclick="menuDinamico('liApi');">
                    <li class="list-group-item" id="liApi">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo $this->idioma->getText("perfil20") ?></strong>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo $this->idioma->getText("perfil16") ?>
                            </p>
                            <p class="m-b-none">
                            </p>
                        </div>
                    </li>
                </a>
                <?php //} 
            } ?>
            <?php if ($documentosEntregue && $configuracao->aparelhos == 1) { ?>
                <a data-toggle="tab" href="#tab-9"
                   onclick="menuDinamico('liAparelhos'); listarDispositivosMobile(); parear();">
                    <li class="list-group-item" id="liAparelhos">
                        <small class="pull-right text-muted"></small>
                        <strong><?php echo $this->idioma->getText("perfil1") ?></strong>
                        <div class="small m-t-xs">
                            <p class="m-b-xs">
                                <?php echo $this->idioma->getText("perfil17") ?>
                            </p>
                            <p class="m-b-none">
                            </p>
                        </div>
                    </li>
                </a>
            <?php } ?>
            <a data-toggle="tab" href="#tab-11" onclick="listaNavegadores(); menuDinamico('liNavegadores');">
                <li class="list-group-item" id="liNavegadores">
                    <small class="pull-right text-muted"></small>
                    <strong><?php echo $this->idioma->getText("perfil21") ?></strong>
                    <div class="small m-t-xs">
                        <p class="m-b-xs">
                            <?php echo $this->idioma->getText("perfil22") ?>
                        </p>
                        <p class="m-b-none">
                        </p>
                    </div>
                </li>
            </a>

            <a data-toggle="tab" href="#tab-12" onclick=" meusLimites(); getTaxas(); menuDinamico('liLimite');">
                <li class="list-group-item" id="liLimite">
                    <small class="pull-right text-muted"></small>
                    <strong><?php echo $this->idioma->getText("perfil24") ?></strong>
                    <div class="small m-t-xs">
                        <p class="m-b-xs">
                            <?php echo $this->idioma->getText("perfil25") ?>
                        </p>
                        <p class="m-b-none">
                        </p>
                    </div>
                </li>
            </a>

            <?php

            $html = ob_get_contents();
            ob_end_clean();

            $json["html"] = $html;
            $json["verificado"] = \Utils\Criptografia::encriptyPostId($documentosEntregue);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);

    }

    public function alterarModo($params)
    {
        try {
            $modo = \Utils\Post::getBoolean($params, "modo", null);
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            if ($modo) {
                $modoOperacao = \Utils\Constantes::MODO_BASIC;
            } else {
                $modoOperacao = \Utils\Constantes::MODO_TRADER;
            }

            $clienteRn->alterarModo($cliente, $modoOperacao);


            $json["mensagem"] = "Modo de operação alterado com sucesso.";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function salvarCnpj($params)
    {
        try {
            $consultaCnpjRn = new \Models\Modules\Cadastro\ConsultaCnpjRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $cnpj = \Utils\Post::get($params, "cnpj", null);
            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);

            $cnpj = \Utils\Validacao::limparString($cnpj);

            if (empty($cnpj) && !\Utils\Validacao::cnpj($cnpj)) {
                throw new \Exception("CNPJ invalído.");
            }

            if (!empty($consultaCnpjRn->getByCnpj($cnpj)) || !empty($cliente->cnpj)) {
                throw new \Exception("CNPJ não pode ser cadastrado.");
            }

            $documento = new \Documento\IWebService();
            $dados = $documento->consultarCnpj($cnpj);

            if ($cliente->documento == \Utils\Validacao::limparString($dados["CpfResponsavel"])) {
                $consultaCnpjRn->salvarArray($dados);
                $clienteRn->conexao->update(array("cnpj" => $cnpj), array("id" => $cliente->id));
            } else {
                throw new \Exception("CNPJ não pertence ao CPF cadastrado.");
            }

            $json["razaoSocial"] = $dados["NomeEmpresa"];
            $json["cnpj"] = $dados["Cnpj"];
            $json["mensagem"] = "CNPJ cadastrado com sucesso.";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function mostrarCnpj()
    {
        try {
            $consultaCnpjRn = new \Models\Modules\Cadastro\ConsultaCnpjRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $cliente = \Utils\Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);

            $consultaCnpj = $consultaCnpjRn->getByCnpj($cliente->cnpj);

            $mascara = new \Utils\Mascara("", "");

            $json["razaoSocial"] = $consultaCnpj->nomeEmpresa;
            $json["cnpj"] = empty($cliente->cnpj) ? "" : $mascara->cnpj($consultaCnpj->cnpj);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function marketupdate(){
        try{
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);


            if($cliente->campanhaMarketReceber){
                $cliente->campanhaMarketReceber = 0;
            }else{
                $cliente->campanhaMarketReceber = 1;
            }

            $clienteRn->updateMarket($cliente);

            return print json_encode([
                'sucesso' => true,
                'msg' => 'Atualizado com sucesso',
            ]);


        }
        catch (\Exception $ex)
        {
            return print json_encode([
                'sucesso' => false,
                'msg' => \Utils\Excecao::mensagem($ex)
            ]);
        }
    }

    public function kycStart()
    {

        try {
            $clienteGet = \Utils\Geral::getCliente();
            $clienteGet = new \Models\Modules\Cadastro\Cliente(Array("id" => $clienteGet->id));
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($clienteGet);

            if ($clienteGet->tipoAutenticacao != \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                throw new \Exception("Por favor, altere a segurança da sua conta para 2FA Authenticator.");
            }
            
            if (!empty($clienteGet->kycUpdateData)) {
                $dataAtual = new \Utils\Data(date("Y-m-d H:i:s"));

                $diferenca = $dataAtual->diferenca($clienteGet->kycUpdateData);

                if ($diferenca->y <= 0 && $diferenca->m <= 0 && $diferenca->d < 7) {
                    
                    $diferenca->d = ($diferenca->d - 7)  * -1;

                    $stringData = " {$diferenca->d} dia(s), {$diferenca->h} hora(s) e {$diferenca->i} minuto(s)";
                    throw new \Exception("Por favor, espere o prazo de {$stringData} para tentar novamente.");
                }
            }

            $clienteDocumento = $clienteGet->documento || null;
            $clienteNome = $clienteGet->nome || null;
            $clienteEmail = $clienteGet->email || null;
            $clienteCelular = $clienteGet->celular || null;

            if ($clienteGet->documento_verificado != 0 || $clienteGet->documento_verificado != null) {
                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'já Existe um processo em andamento',
                    'msgType' => 'w'
                ]);
            }

            if ($clienteGet->celular == '' || $clienteGet->celular == null || strlen($clienteGet->celular) < 10) {
                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'Necessário ter telefone celular informado. Acesse Meu perfil, clique na aba documentos e informe.',
                    'msgType' => 'w'
                ]);
            }

            if ($clienteGet->documento == '' || $clienteGet->documento == null) {
                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'Necessário ter CPF  informado. Acesse Meu perfil, clique na aba documentos e informe.',
                    'msgType' => 'w'
                ]);
            }


            $queueName = 'kycstart_prod';


            $params = [
                'comando' => 'user.kycstage1',
                'id_cliente' => $clienteGet->id,
                'parametros' => [
                    'nome' => $clienteGet->documento,
                    'email' => $clienteGet->email,
                    'telefone' => $clienteGet->celular,
                    'cpf' => $clienteGet->documento
                ]
            ];

            $result = \LambdaAWS\QueueKYC::sendQueue($queueName, false, $params);


            if (!$result) {

                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'Falha ao processar envio de KYC',
                    'msgType' => 'e'
                ]);
            }

            $json['mensagem'] = 'Processo com sucesso.';
            $json['sucesso'] = true;
            $json['msgType'] = 's';

            return print json_encode([
                'sucesso' => true,
                'msg' => 'Enviamos um SMS/E-mail para seu telefone. Verifique.',
                'msgType' => 's',
                'queueReturnUid' => $result['queueReturnUid']
            ]);


        } catch (\Exception $ex) {

            return print json_encode([
                'sucesso' => false,
                'msg' => \Utils\Excecao::mensagem($ex),
                'msgType' => 'e'
            ]);


        }


    }

    public function kycSmsResend()
    {
        try {
            $clienteGet = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $clienteRn->conexao->carregar($clienteGet);
            $dataAtual = new Data(date('Y-m-d H:i:s'));
            $dataComparar = $dataAtual->diferenca($clienteGet->kycSmsResendData);
            if($dataComparar->d == 0)
            {
                if($dataComparar->i < 30)
                {
                    return print json_encode([
                        'sucesso' => false,
                        'msg' => 'Você ainda não pode enviar novamente o E-mail, aguarde 30 minutos ',
                        'msgType' => 'w'
                    ]);
                }
            }



            if ($clienteGet->celular == '' || $clienteGet->celular == null || strlen($clienteGet->celular) < 10) {
                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'Necessário ter telefone celular informado. Acesse Meu perfil, clique na aba documentos e informe.',
                    'msgType' => 'w'
                ]);
            }

            $queueName = 'kycstart_prod';


            $params = [
                'comando' => 'user.resendsms',
                'id_cliente' => $clienteGet->id,
                'parametros' => [
                    'nome' => $clienteGet->documento,
                    'email' => $clienteGet->email,
                    'telefone' => $clienteGet->celular,
                    'cpf' => $clienteGet->documento
                ]
            ];

            $result = \LambdaAWS\QueueKYC::sendQueue($queueName, false, $params);
            if (!$result) {

                return print json_encode([
                    'sucesso' => false,
                    'msg' => 'Falha ao processar envio de sms/e-mail. Tente novamente mais tarde.',
                    'msgType' => 'e'
                ]);
            }

            $clienteRn->setLastUpdateResendSMS($clienteGet);
            return print json_encode([
                'sucesso' => true,
                'msg' => 'SMS/E-mail enviado com sucesso',
                'msgType' => 's',
                'queueReturnUid' => $result['queueReturnUid']
            ]);
        } catch (\Exception $e) {
            return print json_encode([
                'sucesso' => false,
                'msg' => \Utils\Excecao::mensagem($e),
                'msgType' => 'e'
            ]);
        }
    }
}
