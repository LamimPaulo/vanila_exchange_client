<?php

namespace Modules\cadastros\Controllers;

use Utils\Layout;


class Clientes {
    
    private  $codigoModulo = "cadastros";

    /**
     * Faço a validação das permissões de acesso no construtor
     * @param type $params
     */
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    /**
     * 
     * Função responsável pela exibição da view
     * @param array $params Array com os dados do controller passado automaticamente pelo route
     */
    
    
    
    public function getCidades($params) {
        try {
            $idEstado = \Utils\Post::get($params, "estado", null);
            $codigo =  \Utils\Post::get($params, "cidade", null);
            
            if (!$idEstado > 0) {
                $idEstado = "0";
            }
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            if ($idEstado > 0) { 
                $cidades  = $cidadeRn->listar("id_estado = {$idEstado}", "nome", null, null, false);
            }  else {
                $cidades = Array();
            }
            ob_start();
            ?>
            <option value="">Selecione uma cidade</option>
            <?php
            foreach ($cidades as $cidade) {
                ?>
                <option value="<?php echo $cidade->codigo?>" <?php echo ($cidade->codigo == $codigo ? "selected='true'"  : "")?>><?php echo $cidade->nome?></option>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();

            $json["html"]  = $html;
            
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function aceitarRejeitarFoto($params) {
        try {
            $doc = \Utils\Post::get($params, "doc", NULL);
            $aceitar = \Utils\Post::get($params, "aceitar", NULL);
            $motivo = \Utils\Post::get($params, "motivo", NULL);
            $observacoes = \Utils\Post::get($params, "observacoes", NULL);
            
            if ($aceitar < 1 && empty($motivo)) {
                throw new \Exception("Por favor, selecione um motivo na lista");
            }
            
            if (!empty($observacoes)) {
                $motivo .= "<br>{$observacoes}";
            }
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::get($params, "idCliente", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $c = new \Models\Modules\Cadastro\Cliente(Array("id" => $cliente->id));
            $clienteRn->conexao->carregar($c);
            
            if ($aceitar <= 0) {
                $notificacao = new \Models\Modules\Cadastro\Notificacao();
                $notificacao->clientes = 1;
                $notificacao->usuarios = 0;
                $notificacao->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $notificacao->id = 0;
                $notificacao->tipo = "e";
            }
            
            $msgDocComFoto = "Não é possível aprovar outro documento antes de aprovar o Documento de Identificação com Foto";
            switch ($doc) {
                case "d":
                    if ($aceitar > 0) {
                        $clienteRn->conexao->update(Array("foto_documento_verificada" => 1, "motivo_recusa_foto_documento" => ""), Array("id" => $cliente->id));
                    } else {
                        $clienteRn->conexao->update(
                                Array(
                                    "foto_documento_verificada" => 3, 
                                    "foto_residencia_verificada" => 0,
                                    "foto_cliente_verificada" => 0,
                                    "foto_documento" => null, 
                                    "foto_documento_verso" => null, 
                                    "motivo_recusa_foto_documento" => $motivo
                                ), 
                                Array("id" => $cliente->id));
                        
                        $notificacao->html = "Documento rejeitado: {$motivo}";
                        
                    }
                    break;
                case "r":
                    if ($aceitar > 0) {
                        
                        if ($c->fotoDocumentoVerificada < 1) {
                            throw new \Exception($msgDocComFoto);
                        }
                        
                        $clienteRn->conexao->update(Array("foto_residencia_verificada" => 1, "motivo_recusa_foto_residencia" => ""), Array("id" => $cliente->id));
                    } else {
                        $clienteRn->conexao->update(Array("foto_residencia_verificada" => 3, "foto_residencia" => null, "motivo_recusa_foto_residencia" => $motivo), Array("id" => $cliente->id));
                        $notificacao->html = "Comprovante de residência rejeitado: {$motivo}";
                    }
                    break;
                case "c":
                    if ($aceitar > 0) {
                        if ($c->fotoDocumentoVerificada < 1) {
                            throw new \Exception($msgDocComFoto);
                        }
                        
                        $clienteRn->conexao->update(Array("foto_cliente_verificada" => 1, "motivo_recusa_foto_cliente" => ""), Array("id" => $cliente->id));
                    } else {
                        $clienteRn->conexao->update(Array("foto_cliente_verificada" => 3, "foto_cliente" => null, "motivo_recusa_foto_cliente" => $motivo), Array("id" => $cliente->id));
                        $notificacao->html = "Selfie rejeitada: {$motivo}";
                    }
                    break;
                case "o":
                    if ($aceitar > 0) {
                        if ($c->fotoDocumentoVerificada < 1) {
                            throw new \Exception($msgDocComFoto);
                        }
                        
                        $clienteRn->conexao->update(Array("foto_outro_documento_verificada" => 1, "motivo_recusa_foto_outro_documento" => ""), Array("id" => $cliente->id));
                    } else {
                        $clienteRn->conexao->update(Array("foto_outro_documento_verificada" => 3, "foto_outro_documento" => null, "motivo_recusa_foto_outro_documento" => $motivo), Array("id" => $cliente->id));
                        $notificacao->html = "Documento Pj rejeitado: {$motivo}";
                    }
                    break;
            }
            
            $clienteRn->conexao->carregar($c);
            if ($c->fotoClienteVerificada > 0 && $c->fotoDocumentoVerificada > 0 && $c->fotoResidenciaVerificada > 0) {
                \Lahar\Cadastro::estagioLead($c, 2);
                $clienteRn->pagarAirDropPromocaoICONEWC($cliente,  'Bônus de Ativação de Cadastro', false);
                $clienteRn->pagarAirDropCadastroValidacao($cliente, "Bonus Validação de cadastro AIR DROP");
            }
            
            if ($aceitar <= 0) {
                $notificacaoRn = new \Models\Modules\Cadastro\NotificacaoRn();
                $notificacaoRn->salvarNotificacao($notificacao, Array(\Utils\Criptografia::encriptyPostId($cliente->id)), Array(), false, false);
            }
            
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function zerarPin($params) {
        try {
            $idCliente = \Utils\Post::get($params, "idCliente", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->update(Array("pin" => null), Array("id" => $idCliente));
            
            $json["sucesso"]  = true;
        } catch (\Exception $ex) {
            $json["sucesso"]  = false;
            $json["mensagem"]  = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getByEmail($params) {
        try {
            $email = \Utils\Post::get($params, "email", null);
            if (empty($email)) {
                throw new \Exception("É necessário informar o email do cliente");
            }
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = $clienteRn->getByEmail($email);
            if ($cliente == null) {
                $cliente = new \Models\Modules\Cadastro\Cliente();
            }
            
            $json["cliente"] = $cliente;
            $json["id"] = \Utils\Criptografia::encriptyPostId($cliente->id);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusMercado($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTACLIENTES, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar status do cliente");
            }
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->alterarStatusMercado($cliente);
            
            $json["status"] = $cliente->statusMercado;
            $json["id"] = $cliente->id;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function consultaCnpj($params) {
        
        try {
            $cnpj = \Utils\Post::get($params, "cnpj", "");
            $idCliente = \Utils\Post::get($params, "idCliente", "");
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = $idCliente;
            
            try {
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema");
            }
            
            
            $consultaCnpj = \Modules\services\Controllers\Consulta::cnpj($cnpj);
            
            $result = $clienteRn->conexao->listar( "cnpj = '{$cnpj}' AND id != {$cliente->id}");
            if (sizeof($result) > 0) {
                $c = $result->current();
                throw new \Exception("O CNPJ já está cadastrado para o cliente {$c->nome}");
            }
            
            $consultaCnpj->datahora = $consultaCnpj->datahora->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO);
            
            $cliente->cnpj = $cnpj;
            $clienteRn->conexao->update(Array("cnpj" => $cnpj), Array("id" => $cliente->id));
            
            $json["cliente"] = $cliente;
            $json["consulta"] = $consultaCnpj;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function consultaCpf($params) {
        
        try {
            $idCliente = \Utils\Post::get($params, "idCliente", "");
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = $idCliente;
            
            try {
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema");
            }
            
            if (!\Utils\Validacao::cpf($cliente->documento)) {
                throw new \Exception("CPF inválido");
            }
            
            $consultaCpf = \Modules\services\Controllers\Consulta::cpf($cliente->documento);
            
            $json["cliente"] = $cliente;
            $json["consultaCpf"] = $consultaCpf;
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Consulta concluída con sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function reenviarEmailConfirmacao($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTACLIENTES, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar status do cliente");
            }
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::get($params, "idCliente", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            try {
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema.");
            }
            \Email\ConfirmacaoEmail::send($cliente);
            $clienteRn->conexao->update(Array("qtd_envios_emails_recuperacao" => ($cliente->qtdEnviosEmailsRecuperacao + 1)), Array("id" => $cliente->id));
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function reenviarEmailBoasVindas($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_LISTACLIENTES, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para alterar status do cliente");
            }
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::get($params, "idCliente", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            try {
                $clienteRn->conexao->carregar($cliente);
                
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema.");
            }
            
            $senhaTemp = substr(sha1($cliente->email . range(00, 99) . \Utils\Constantes::SEED_SENHA), 0, 10);
                
            $senha = sha1($senhaTemp.\Utils\Constantes::SEED_SENHA);
            
            /*if ($senha != $cliente->senha) {
                throw new \Exception("O cliente já realizou a troca de senha.");
            }*/
            
            $cliente->senha = $senhaTemp;
            
            //$cliente->email = "vagnercarvalho.vfc@gmail.com";
            \Email\BoasVindas::send($cliente);
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function iniciarAnalise($params) {
        try {
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->iniciarAnalise($cliente);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "A análise foi marcada como iniciada!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function finalizarAnalise($params) {
        try {
            
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->finalizarAnalise($cliente);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "A análise foi marcada como finalizada!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function notificarAnaliseDocumentos($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "codigo", 0);
           
            \Email\StatusAnalisePerfil::send($cliente);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Email enviado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function salvarComentario($params) {
        try {
            $observacaoCliente = new \Models\Modules\Cadastro\ObservacaoCliente();
            $observacaoCliente->idCliente  = \Utils\Post::getEncrypted($params, "cliente", 0);
            $observacaoCliente->observacoes = \Utils\Post::get($params, "anotacoes", null);
            
            $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();
            $observacaoClienteRn->salvar($observacaoCliente);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Comentário gravado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function listarComentarios($params) {
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
             
            $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();
            $result = $observacaoClienteRn->listar("id_cliente  = {$cliente->id} ", "data DESC", null, null, false, true);
            
            ob_start();
            if (sizeof($result)) {
                foreach ($result as $observacaoCliente) {
                    
                ?>        
                <tr>
                    <td colspan="1">
                        <strong>Administrador: <?php echo $observacaoCliente->usuario->nome ?> - Data/Hora: <?php echo $observacaoCliente->data->formatar("d/m/Y H:i:s") ?></strong>
                        <p> 
                            <?php echo $observacaoCliente->observacoes ?>
                        </p>
                    </td>
                </tr>
                <?php
                }
            } else {
                ?>
                <tr >
                    <td colspan="1" class="text-center">
                        Nenhum log para o cliente
                    </td>
                </tr>
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
    
    
    public function analisarEmail($params) {
        
        try {
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, 'cliente', 0);
            
            $clienteRn->conexao->carregar($cliente);
            $analiseEmail = \IPorto\IPorto::isValidMail($cliente->email);
            $cliente->retornoAnaliseEmail = $analiseEmail["json"];
            $clienteRn->conexao->update(Array("retorno_analise_email" => $cliente->retornoAnaliseEmail), Array("id" => $cliente->id));

            $res = json_decode($$cliente->retornoAnaliseEmail);

            

        




            
            //$json["json"] = $cliente->retornoAnaliseEmail;
            $json["sucesso"] = true;
            $json["mensagem"] = "Análise conclúida com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function enviarCreditosCampanha($params) {
        
        try {
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::get($params, "cliente", 0);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $clienteRn->conexao->carregar($cliente);
            
            if ($cliente->idPromocao > 0) {
                throw new \Exception("Cliente já beneficiado com a promoção");
            }
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReais = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->clienteDestino = null;
            $contaCorrenteReais->comissaoConvidado = 0;
            $contaCorrenteReais->comissaoLicenciado = 0;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Crédito Promoção: Quero meus 10 Reais!";
            $contaCorrenteReais->idCliente = $cliente->id;
            $contaCorrenteReais->idClienteDestino = null;
            $contaCorrenteReais->idReferenciado = null;
            $contaCorrenteReais->orderBook = 0;
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->transferencia = 0;
            $contaCorrenteReais->valor = 10;
            $contaCorrenteReais->valorTaxa = 0;
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->bloqueado = 0;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = "Pagamento Promoção: Quero meus 10 reais - {$cliente->nome}";
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReaisEmpresa->transferencia = 0;
            $contaCorrenteReaisEmpresa->valor = 10;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            $clienteRn->conexao->update(Array("id_promocao" => 1), Array("id" => $cliente->id));
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Créditos enviados com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function htmlItemMinhasMoedas($params) {
        
        try {
            $rankingGeral = array();
            $posicaoRanking = null;
            $esconderZerados = true;
            $result = null;
            $investido = null;

            $cliente = new \Models\Modules\Cadastro\Cliente();
            $cliente->id = \Utils\Post::getEncrypted($params, "cliente", 0);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            //exit(print_r($cliente->id));
            $clienteRn->conexao->carregar($cliente);

            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar("ativo > 0 AND id > 1", "principal DESC, id asc, nome");
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            
            //$orderBookRn->getValorTotalOrdensReais($cliente);
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            ob_start();
            $saldoReais = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            if (!$esconderZerados  || $saldoReais["bloqueado"] > 0 || $saldoReais["saldo"] > 0) {
                ?>
        
                <tr style="font-size: 12px;">         
                         


                    <td style="vertical-align: middle; text-align: left;">
                        <img src="<?php echo IMAGES ?>currencies/BRL.png" style="max-width: 25px; max-height: 25px;" />
                        &nbsp;&nbsp;BRL - Real
                    </td>
                    <td style="vertical-align: middle;" class="text-center">-</td>
                    
                    <td style="vertical-align: middle;" class="text-center">R$ <?php echo number_format($saldoReais["saldo"], $configuracao->qtdCasasDecimais,",",".") ?></td>
                  
                    <td style="vertical-align: middle;" class="text-center">R$ <?php echo number_format($saldoReais["bloqueado"], $configuracao->qtdCasasDecimais, ",",".") ?></td>
                    
                    <td style="vertical-align: middle;" class="text-center">-</td> 

                    <td style="vertical-align: middle;" class="text-center">-</td> 

                    
                </tr>
                                
                     <?php
            }
            //$p = Principal::getParity();
            
            //$paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            foreach ($moedas as $moeda) {
                
                /*$paridade = $paridadeRn->find($moeda->id);
                if ($paridade != null) { 
                    $rankingGeral = $orderBookRn->rankingMensal($paridade);                   
                }                
                
                $posicaoRanking = null;
                if (sizeof($rankingGeral) > 0) {
                    foreach ($rankingGeral as $index => $value) {                        
                        if ($cliente->id === $value["id"] && $moeda->id === $value["id_moeda_book"]) {
                            $posicaoRanking = $index + 1;
                            break;
                        } 
                    }
                }*/
                
                if($moeda->id == 2 || $moeda->id == 4 || $moeda->id == 7){
                    $result = $cofreRn->conexao->listar("id_cliente = {$cliente->id} AND sacado = 0 AND id_moeda = {$moeda->id}", "data_entrada", null, null);   
                    if(sizeof($result) > 0){
                        foreach ($result as $invest){
                            $investido += $invest->volumeDepositado;
                        }
                    }
                }
                
                $saldoConta = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true); 
                $saldoTotalSomado = ($saldoConta["saldo"]+$saldoConta["bloqueado"]);


                ?>
               <tr style="font-size: 12px;">   
                    

                    <td style="vertical-align: middle;" style="text-align: left;">
                        <a class="count-info" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD_REDIRECT ?>/<?php echo $moeda->simbolo ?>/a" class="pull-left">
                            <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="max-width: 25px; max-height: 25px;" />
                            &nbsp;&nbsp;<?php echo $moeda->simbolo ?> - 
                            <?php echo $moeda->nome ?>
                        </a>

                    </td>
                    <td style="vertical-align: middle;" class="text-center">

                     </td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($saldoConta["saldo"], $moeda->casasDecimais, ".", "") ?></td>                    
                                      
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($saldoConta["bloqueado"], $moeda->casasDecimais, ".", "") ?></td>

                    <td style="vertical-align: middle;" class="text-center"><?php echo $investido == null ? "-" : number_format($investido, 8, ",", ".") ?></td> 
                    
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($investido == null ? $saldoTotalSomado : $saldoTotalSomado + $investido, 8, ",", ".") ?></td> 

                     
                </tr>
                    
                <?php
                $investido = null;
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
    
    
    public function getEstadosByPais($params) {
        
        try {
            $pais = new \Models\Modules\Cadastro\Pais();
            $pais->id = \Utils\Post::get($params, "pais", 0);
            $idEstado = \Utils\Post::get($params, "estado", 0);
            
            if (!$pais->id > 0) {
                $pais = "0";
            }
            
            $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            $estados = $estadoRn->conexao->listar("id_pais = {$pais->id}", "nome");
            
            if (!(sizeof($estados) > 0)) {
                $estados = $estadoRn->conexao->listar("id_pais IS NULL", "nome");
            }
            
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
    
}