<?php

namespace Modules\pdvs\Controllers;

class Estabelecimentos {
    
    private  $codigoModulo = "recebimentospdv";
    
    function __construct(&$params) {
        
        if ($params["_rota"] != "callback/4h") { 
            \Utils\Validacao::acesso($this->codigoModulo);
            //\Modules\principal\Controllers\Principal::validarAcessoCliente($params, false);
        }
    }
    
    public function index($params) {
        try {
            
            $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
            
            if ($adm) {
                $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
                $clientes = $estabelecimentoRn->getClientesComEstabelecimento();
                
                $params["clientes"] = $clientes;
            }
            
            if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
                $estabelecimentos = $estabelecimentoRn->conexao->listar("id_cliente = {$cliente->id}");
                $params["estabelecimentos"] = $estabelecimentos;
            } 
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = true;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        
        \Utils\Layout::view("index_estabelecimentos", $params);
    }
    
    
    public function listar($params) {
        try {
            
            $usuario = \Utils\Geral::getLogado();
            if (\Utils\Geral::isUsuario() && $usuario->tipo == \Utils\Constantes::ADMINISTRADOR) {
                $idCliente = \Utils\Post::getEncrypted($params, "idCliente", NULL);
            } else {
                if (\Utils\Geral::isCliente()) {
                    $cliente = \Utils\Geral::getCliente();
                    $idCliente = $cliente->id;
                } else {
                    throw new \Exception("Vocë precisa registrar-se como cliente ter a acesso a esse recurso");
                }
                
            }
            
            $filtro = \Utils\Post::get($params, "filtro", NULL);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $lista = $estabelecimentoRn->filtrar($idCliente, $filtro);
            
            $html = $this->htmlLista($lista);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlLista($lista) {
        ob_start();
        if (sizeof($lista) > 0) {
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            foreach ($lista as $estabelecimento) {
                
                $saldo = $carteiraPdvRn->getBalance($estabelecimento, 1, false);
                $wallets = $carteiraPdvRn->contarCarteiras($estabelecimento->id, NULL);
                
                $this->HtmlEstabelecimento($estabelecimento, $saldo["saldo"], $wallets);
            }
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Não existe estabelecimentos cadastrados.
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    
    private function HtmlEstabelecimento(\Models\Modules\Cadastro\Estabelecimento $estabelecimento, $saldo = 0, $wallets = 0) {
        ?>
            
            
            <li class="dd-item" >
                <div class="dd-handle" id="estabelecimento-<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id)?>">
                    <div class="row">
                        <div class="col col-lg-6" style="font-size: 14px;">
                            <?php echo $estabelecimento->nome ?> <br>
                            <div class="row">
                                <div class="col col-lg-6 text-center">
                                    <span class="pull-left"> Wallets: <?php echo $wallets ?></span>
                                </div>
                                <div class="col col-lg-6 text-center">
                                    <span class="pull-left"> Saldo: <?php echo number_format($saldo, 8, ".", "")?> BTC </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col col-lg-2 text-center">
                            <button class="btn btn-info btn-sm" id="btn-show-<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>" onclick="mostrarCarteiras('<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>');">
                                <i class="fa fa-plus"></i> Mostrar Wallets
                            </button>
                            <button class="btn btn-warning btn-sm" style="display: none;" id="btn-hide-<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>" onclick="esconderCarteiras('<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>');">
                                <i class="fa fa-minus"></i> Esconder Wallets
                            </button>
                        </div>
                        
                        <div class="col col-lg-2 text-center">
                            
                            <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ESTABELECIMENTOS, \Utils\Constantes::ALTERAR_STATUS)) { ?>
                            <button class="btn btn-<?php echo ($estabelecimento->ativo > 0 ? "success" : "danger")?> btn-sm" onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>');">
                                <i class="fa fa-<?php echo ($estabelecimento->ativo > 0 ? "check" : "square")?>"></i> <?php echo ($estabelecimento->ativo > 0 ? "Desativar" : " Ativar  ")?>
                            </button>
                            <?php } ?>
                        </div>
                        
                        <div class="col col-lg-2 text-center">
                            <a class="btn btn-primary btn-sm" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_ESTABELECIMENTOS_CADASTRO ?>/<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>">
                                <i class="fa fa-edit"></i> Ver / Editar
                            </a>
                        </div>
                    </div>
                    <br>
                </div>
                
                <ol class="dd-list" style="margin-left: 30px; display: none;" id="wallets-loader-<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id)?>">
                    <li class="dd-item" >
                        <div class="dd-handle text-center">
                            <img src="<?php echo IMAGES ?>loading.gif" style="width: 50px; height: 50px;" /> Carregando wallets...
                        </div>
                    </li>
                </ol>
            </li>
        <?php
    }
    
    
    public function listarWallets($params) {
        try {
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "estabelecimento", 0);
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            try {
                $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
                $estabelecimentoRn->conexao->carregar($estabelecimento);
                
            } catch (\Exception $ex) {
                throw new \Exception("Estabelecimento não localizado");
            }
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            $carteiras = $carteiraPdvRn->filtrar($estabelecimento->id, $filtro, empty($filtro));
            
            $json["html"] = $this->htmlListaWallets($carteiras, $estabelecimento->id);
            
            $json["sucesso"] = true;
        } catch (Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaWallets($lista, $idEstabelecimento) {
        ob_start();
        ?>
        <ol class="todo-list" style="margin-left: 30px;" id="wallets-<?php echo \Utils\Criptografia::encriptyPostId($idEstabelecimento)?>">
            <li > 
                
                <div class="form-group">
                    <label>Por padrão a lista mostra apenas as carteiras com saldo superior a zero. Para pesquisar por qualquer carteira utilize o filtro abaixo:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="input-pesquisa-<?php echo \Utils\Criptografia::encriptyPostId($idEstabelecimento)?>" > 
                        <span class="input-group-btn"> 
                            <button type="button" onclick="mostrarCarteiras('<?php echo \Utils\Criptografia::encriptyPostId($idEstabelecimento) ?>');" class="btn btn-primary"><i class="fa fa-search"></i></button> 
                        </span>
                    </div>
                </div>
                
            </li>
        <?php
        if (sizeof($lista) > 0) {
            
            foreach ($lista as $carteiraPdv) {
                $this->htmlCarteiraPdv($carteiraPdv);
            }
            
        } else {
            ?>
            <li class="dd-item" >
                <div class="dd-handle">
                    Não existem carteiras cadastradas neste estabelecimento.
                </div>
            </li>
            <?php
        }
        ?>
        </ol>
        <?php
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    private function htmlCarteiraPdv(\Models\Modules\Cadastro\CarteiraPdv $carteiraPdv) {
        $saldo = ($carteiraPdv->saldoBtc - $carteiraPdv->saldoGastoBtc);
        ?>
            <li >
                <button class="btn btn-primary btn-sm pull-right" style="margin-left: 15px; margin-top: 5px; margin-bottom: 5px;"  onclick="enviarCallback('<?php echo \Utils\Criptografia::encriptyPostId($carteiraPdv->id)?>');">
                        Enviar Callback
                </button>
                <div class="dd-handle">
                    
                    <span class="pull-right"> <?php echo number_format($saldo, 8, ".", "")?> BTC </span>
                    <span class="label label-primary">
                        <i class="fa fa-bitcoin"></i>
                    </span> &nbsp;&nbsp;&nbsp; 
                    <?php echo $carteiraPdv->enderecoCarteira ?>
                    <br>
                </div>
                <div style="clear: both;"></div>
            </li>
        <?php
    }
    
    public function cadastro($params) {
        try {
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Get::getEncrypted($params, 0, 0);
            
            if ($estabelecimento->id > 0) {
                try {
                    $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
                    $estabelecimentoRn->carregar($estabelecimento, true, true, false);
                } catch (\Exception $ex) {
                    throw new \Exception("Estabelecimento não localizado no sistema");
                }
            }
            $cliente = \Utils\Geral::getCliente();
            
            if (!\Utils\Geral::isCliente()) {
                $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $estabelecimento->idCliente));
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            }
            
            $params["estabelecimento"] = $estabelecimento;
            
            $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            $estados = $estadoRn->conexao->listar(null, "nome");
            
            
            $params["estados"] = $estados;
            $params["cliente"] = $cliente;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("cadastro_estabelecimento", $params);
    }
    
    public function salvar($params) {
        try {
            
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($estabelecimento->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ESTABELECIMENTOS, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para editar o estabelecimento");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ESTABELECIMENTOS, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para cadastrar estabelecimentos");
                }
            }
            
            $estabelecimento->bairro = \Utils\Post::get($params, "bairro", null);
            $estabelecimento->cep = \Utils\Post::get($params, "cep", null);
            $estabelecimento->cnpj = \Utils\Post::get($params, "cnpj", null);
            $estabelecimento->complemento = \Utils\Post::get($params, "complemento", null);
            $estabelecimento->cpf = \Utils\Post::get($params, "cpf", null);
            $estabelecimento->email = \Utils\Post::get($params, "email", null);
            $estabelecimento->endereco = \Utils\Post::get($params, "endereco", null);
            $estabelecimento->codigoCidade = \Utils\Post::get($params, "codigoCidade", null);
            $estabelecimento->nome = \Utils\Post::get($params, "nome", null);
            $estabelecimento->numero = \Utils\Post::get($params, "numero", null);
            $estabelecimento->telefone = \Utils\Post::get($params, "telefone", null);
            $estabelecimento->telefone2 = \Utils\Post::get($params, "telefone2", null);
            $estabelecimento->callbackHomologacao = \Utils\Post::get($params, "callbackHomologacao", null);
            $estabelecimento->callbackProducao = \Utils\Post::get($params, "callbackProducao", null);
            $estabelecimento->comissaoEstabelecimento = \Utils\Post::getNumeric($params, "comissaoEstabelecimento", null);
            $estabelecimento->tipoComissaoEstabelecimento = \Utils\Post::get($params, "tipoComissaoEstabelecimento", null);
            
            $estabelecimento->walletSaqueAutomatico = \Utils\Post::get($params, "walletSaqueAutomatico", null);
            $estabelecimento->habilitarSaqueAutomatico = \Utils\Post::getBooleanAsInt($params, "habilitarSaqueAutomatico", 0);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentoRn->salvar($estabelecimento);
            
            $json["id"] = \Utils\Criptografia::encriptyPostId($estabelecimento->id);
            $json["chave"] = $estabelecimento->chave;
            $json["chaveSandbox"] = $estabelecimento->chaveSandbox;
            $json["sucesso"] = true;
            $json["mensagem"] = "Registro salvo com sucesso";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ESTABELECIMENTOS, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir estabelecimentos");
            }
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentoRn->excluir($estabelecimento);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Registro excluído com sucesso";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function atualizarStatusAtivo($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ESTABELECIMENTOS, \Utils\Constantes::ALTERAR_STATUS)) {
                throw new \Exception("Você não tem permissão para atualizar status de estabelecimentos");
            }
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentoRn->alterarStatusAtivo($estabelecimento);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getCidades($params) {
        try {
            $estado = new \Models\Modules\Cadastro\Estado(Array("id" => \Utils\Post::getEncrypted($params, "idEstado", 0)));
            
            try {
                $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
                $estadoRn->conexao->carregar($estado);
            } catch (\Exception $ex) {
                throw new \Exception("Estado não encontrado");
            }
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidades = $cidadeRn->conexao->listar("id_estado = {$estado->id}", "nome");
            
            ob_start();
            foreach ($cidades as $cidade) {
                ?>
                <option value="<?php echo $cidade->codigo?>"><?php echo $cidade->nome ?></option>    
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
    
    
    public function getOptionsEstabelecimentosByCliente($params) {
        try {
            $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
            $cliente = new \Models\Modules\Cadastro\Cliente();
            
            if ($adm) {
                $cliente->id = \Utils\Post::getEncrypted($params, "id", 0);
            } else if (\Utils\Geral::isCliente())  {
                $cliente->id = \Utils\Geral::getCliente()->id;
            }
            
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                
            }
            
            $where = null;
            if ($cliente->id > 0) {
                $where = new \Zend\Db\Sql\Where();
                $where->equalTo("id_cliente", $cliente->id);
            }
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentos = $estabelecimentoRn->listar($where, "nome", null, null, false, true);
            
            ob_start();
            ?>
            <option value="<?php echo \Utils\Criptografia::encriptyPostId(0) ?>">De todos os estabelecimentos</option>    
            <?php
            foreach ($estabelecimentos as $estabelecimento) {
                ?>
                <option value="<?php echo \Utils\Criptografia::encriptyPostId($estabelecimento->id) ?>"><?php echo ($adm ? $estabelecimento->cliente->nome . " - " : "") . $estabelecimento->nome ?></option>
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
    
    
    public function enviarCallback($params) {
        try {
            $carteiraPdv = new \Models\Modules\Cadastro\CarteiraPdv();
            $carteiraPdv->id = \Utils\Post::getEncrypted($params, "wallet", 0);
            
            try {
                $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
                $carteiraPdvRn->carregar($carteiraPdv, true, true);
                
                
            } catch (\Exception $ex) {
                throw new \Exception("Carteira inválida");
            }
            
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = $carteiraPdv->referenciaCliente->idEstabelecimento;
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentoRn->conexao->carregar($estabelecimento);
                   
            $url = "";
            if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                if (empty($estabelecimento->callbackHomologacao)) {
                    throw new \Exception("Carteira de Homologação. Você precisa cadastrar uma URL de homologação antes de enviar um callback");
                }
                $url = "{$estabelecimento->callbackHomologacao}?codigo={$carteiraPdv->id}";
                $resultado = $carteiraPdvRn->callback($estabelecimento->callbackHomologacao, $carteiraPdv->id, true);
            } else {
                if (empty($estabelecimento->callbackProducao)) {
                    throw new \Exception("Carteira de Produção. Você precisa cadastrar uma URL de produção antes de enviar um callback");
                }
                $url = "{$estabelecimento->callbackProducao}?codigo={$carteiraPdv->id}";
                $resultado = $carteiraPdvRn->callback($estabelecimento->callbackProducao, $carteiraPdv->id, true);
            }
            
            
            $json["url"] = $url;
            $json["resultado"] = $resultado;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function getResumoDados($params) {
        try {
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "estabelecimento", 0);
            
            try {
                $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
                $estabelecimentoRn->conexao->carregar($estabelecimento);
            } catch (\Exception $ex) {
                throw new \Exception("Estabelecimento não localizado");
            }
            
            $referenciaClienteRn = new \Models\Modules\Cadastro\ReferenciaClienteRn();
            $qtdClientes = $referenciaClienteRn->getQuantidadeReferencias($estabelecimento);
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            $qtdCarteiras = $carteiraPdvRn->getQuantidadeCarteiras($estabelecimento);
            
            $saldo = $carteiraPdvRn->getBalance($estabelecimento, 1, true);
            
            
            
            $json["clientes"] = $qtdClientes;
            $json["wallets"] = $qtdCarteiras;
            $json["saldo"] = number_format($saldo["saldo"], 8, ".", "");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function resgatarsaldo($params) {
        try {
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "estabelecimento", 0);
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentoRn->sacar($estabelecimento, $valor);
            
            
            $json["mensagem"] = "Saque realizado com sucesso!";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function calcularTaxa($params) {
        try {
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->id = \Utils\Post::getEncrypted($params, "estabelecimento", 0);
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            
            $taxaCobrada = $carteiraPdvRn->calcularTaxaResgate($estabelecimento, $valor);
            
            
            $json["taxa"] = number_format($taxaCobrada, 8, '.', "");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function fourH($params) {
        
        try {
            $idCliente = 15093064538401; // Rodrigo
            //$idCliente = 15093064536678; // Vagner
            
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente));
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimentos = $estabelecimentoRn->listar("id_cliente = {$cliente->id} AND ativo > 0", null, null, null, false, false);
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            
            foreach ($estabelecimentos as $estabelecimento) {
                $balance = $carteiraPdvRn->getBalance($estabelecimento, 1, false);
                //$estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
                
                $saldoMinimo = number_format(($configuracao->taxaTransferenciaInternaBtc * 2), 8, ".", "");
                
                //echo "Balance do estabelecimento {$estabelecimento->id} = {$balance["saldo"]} <br>";
                if ($balance["saldo"] >= $saldoMinimo) {
                    $estabelecimentoRn->sacar($estabelecimento, $balance["saldo"]);
                    // echo "Pediu saque <br><br>";
                }
                
            }
            
            echo "ok";
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
        
        try {
            
            $btc = \Exchanges\Bitfinex::ticker('BTCUSD');
            $ltc = \Exchanges\Bitfinex::ticker('LTCUSD');
            $dash = \Exchanges\Bitfinex::ticker('DSHUSD');
            $eth = \Exchanges\Bitfinex::ticker('ETHUSD');
            
            $tabelaConversaoRn = new \Models\Modules\ICO\TabelaConversaoIcoRn();
            $result = $tabelaConversaoRn->conexao->listar();
            
            foreach ($result as $tabelaConversao) {
                //$tabelaConversao =  new \Models\Modules\ICO\TabelaConversaoIco();
                switch ($tabelaConversao->idMoeda) {
                    case 2:    
                        if ($tabelaConversao->precoBaseDollar > 0) {
                            $preco = number_format(($tabelaConversao->precoDollar > 0 ? ($tabelaConversao->precoDollar / $btc["sell"]) : 0), 8, ".", "");
                        } else {
                            $preco = number_format(($btc["sell"] * $tabelaConversao->volumeMoeda), 4, ".", "");
                        }
                        $cotacao = number_format(($btc["sell"]), 4, ".", "");
                        break;
                    case 3:    
                        if ($tabelaConversao->precoBaseDollar > 0) {
                            $preco = number_format(($tabelaConversao->precoDollar > 0 ? ($tabelaConversao->precoDollar / $eth["sell"]) : 0), 8, ".", "");
                        } else {
                            $preco = number_format(($eth["sell"] * $tabelaConversao->volumeMoeda), 4, ".", "");
                        }
                        $cotacao = number_format(($eth["sell"]), 4, ".", "");
                        break;
                    case 4:   
                        if ($tabelaConversao->precoBaseDollar > 0) {
                            $preco = number_format(($tabelaConversao->precoDollar > 0 ? ($tabelaConversao->precoDollar / $ltc["sell"]) : 0), 8, ".", "");
                        } else {
                            $preco = number_format(($ltc["sell"] * $tabelaConversao->volumeMoeda), 4, ".", "");
                        }
                        $cotacao = number_format(($ltc["sell"]), 4, ".", "");
                        break;
                    case 7:    
                        if ($tabelaConversao->precoBaseDollar > 0) {
                            $preco = number_format(($tabelaConversao->precoDollar > 0 ? ($tabelaConversao->precoDollar / $dash["sell"]) : 0), 8, ".", "");
                        } else {
                            $preco = number_format(($dash["sell"] * $tabelaConversao->volumeMoeda), 4, ".", "");
                        }
                        $cotacao = number_format(($dash["sell"]), 4, ".", "");
                        break;
                    default:
                        $preco = 0;
                        $cotacao = 0;
                }
                
                $update = Array();
                if($preco > 0) {
                    if ($tabelaConversao->precoBaseDollar > 0) {
                        $update["volume_moeda"] = $preco;
                    } else {
                        $update["preco_dollar"] = $preco;
                    }
                    
                }
                
                
                if($cotacao > 0) {
                    $update["cotacao_dollar"] = $cotacao;
                }
                
                if (sizeof($update) > 0) {
                    $tabelaConversaoRn->conexao->update($update, Array("id" => $tabelaConversao->id));
                }
                
            }
            
        } catch (\Exception $ex) {
            //print_r($ex);
        }
        
    }
}