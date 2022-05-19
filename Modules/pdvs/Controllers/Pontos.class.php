<?php

namespace Modules\pdvs\Controllers;

class Pontos {
    
    private  $codigoModulo = "recebimentospdv";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        //\Modules\principal\Controllers\Principal::validarAcessoCliente($params, false);
    }
    
    public function index($params) {
        
        try {
            $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
            if ($adm) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clientes = $clienteRn->conexao->listar(null, "nome", null);
                $params["clientes"] = $clientes;
            }
            
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = true;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("index_pontos_pdv", $params);
    }
    
    
    public function listar($params) {
        try {
            if (!\Utils\Geral::isCliente()) {
                throw new \Exception("Vocë precisa registrar-se como cliente ter a acesso a esse recurso");
            }
            
            $idEstabelecimento = \Utils\Post::getEncrypted($params, "idEstabelecimento", NULL);
            $filtro = \Utils\Post::get($params, "filtro", NULL);
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            $lista = $pontoPdvRn->filtrar($idEstabelecimento, $filtro);
            
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
        $this->cabecalhoHtmlPontoPdv();
        if (sizeof($lista) > 0) {
            foreach ($lista as $pontoPdv) {
                $this->HtmlPontoPdv($pontoPdv);
            }
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Não existem pontos cadastrados.
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    private function cabecalhoHtmlPontoPdv() {
        ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-5">
                        <strong>Descrição</strong>
                    </div>
                    <div class="col col-lg-4">
                        <strong>Estabelecimento</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Ativo</strong>
                    </div>
                    <div class="col-lg-1  text-center">
                        <strong>Excluir</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Editar</strong>
                    </div>
                </div>
            </li>   
        <?php
    }
    
    private function HtmlPontoPdv(\Models\Modules\Cadastro\PontoPdv $pontoPdv) {
        ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-5">
                        <?php echo $pontoPdv->descricao ?>
                    </div>
                    <div class="col col-lg-4">
                        <?php echo $pontoPdv->estabelecimento->nome ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::ALTERAR_STATUS)) { ?>
                        <button class="btn btn-<?php echo ($pontoPdv->ativo > 0 ? "success" : "danger")?>" onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($pontoPdv->id) ?>');">
                            <i class="fa fa-<?php echo ($pontoPdv->ativo > 0 ? "check" : "square")?>"></i>
                        </button>
                        <?php } ?>
                    </div>
                    <div class="col-lg-1  text-center">
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::EXCLUIR)) { ?>
                        <button class="btn btn-danger" onclick="dialogExcluir('<?php echo \Utils\Criptografia::encriptyPostId($pontoPdv->id) ?>');">
                            <i class="fa fa-remove"></i>
                        </button>
                        <?php } ?>
                    </div>
                    
                    <div class="col-lg-1 text-center">
                        <a class="btn btn-primary" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_PONTOSPDVS_CADASTRO ?>/<?php echo \Utils\Criptografia::encriptyPostId($pontoPdv->id) ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                    </div>
                </div>
            </li>   
        <?php
    }
    
    
    public function cadastro($params) {
        try {
            $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Get::getEncrypted($params, 0, 0);
            
            if ($pontoPdv->id > 0) {
                try {
                    $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
                    $pontoPdvRn->carregar($pontoPdv, true, false);
                } catch (\Exception $ex) {
                    throw new \Exception("Ponto de PDV não localizado no sistema");
                }
                
                $chavePdvRn = new \Models\Modules\Cadastro\ChavePdvRn();
                $chavePdv = $chavePdvRn->getByPontoPdv($pontoPdv);
                $params["chave"] = $chavePdv;
            }
            
            $params["ponto"] = $pontoPdv;
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn($pontoPdvRn->conexao->adapter);
            
            $where = null;
            if (!$adm) {
                $cliente = \Utils\Geral::getCliente();
                if ($cliente != null) {
                    $where = new \Zend\Db\Sql\Where();
                    $where->equalTo("id_cliente", $cliente->id);
                }
            }
            
            $estabelecimentos = $estabelecimentoRn->listar($where, "nome", null, null, false, true);
            
            
            $params["estabelecimentos"] = $estabelecimentos;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("cadastro_ponto_pdv", $params);
    }
    
    public function salvar($params) {
        try {
            
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($pontoPdv->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para editar o PDV");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para cadastrar PDVs");
                }
            }
            
            $pontoPdv->descricao = \Utils\Post::get($params, "descricao", null);
            $pontoPdv->callbackHomologacao = \Utils\Post::get($params, "callbackHomologacao", null);
            $pontoPdv->callbackProducao = \Utils\Post::get($params, "callbackProducao", null);
            $pontoPdv->idEstabelecimento = \Utils\Post::getEncrypted($params, "idEstabelecimento", null);
            
            $pontoPdv->comissaoPdv = \Utils\Post::getNumeric($params, "comissaoPdv", null);
            $pontoPdv->tipoComissaoPdv = \Utils\Post::get($params, "tipoComissaoPdv", null);
            $pontoPdv->walletSaqueAutomatico = \Utils\Post::get($params, "walletSaqueAutomatico", null);
            $pontoPdv->habilitarSaqueAutomatico = \Utils\Post::getBooleanAsInt($params, "habilitarSaqueAutomatico", 0);
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            $pontoPdvRn->salvar($pontoPdv);
            
            $json["id"] = \Utils\Criptografia::encriptyPostId($pontoPdv->id);
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
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir PDVs");
            }
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            $pontoPdvRn->excluir($pontoPdv);
            
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
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PONTOSPDVS, \Utils\Constantes::ALTERAR_STATUS)) {
                throw new \Exception("Você não tem permissão para alterar o status de PDVs");
            }
            $pontoPdv = new \Models\Modules\Cadastro\PontoPdv();
            $pontoPdv->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
            $pontoPdvRn->alterarStatusAtivo($pontoPdv);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
}