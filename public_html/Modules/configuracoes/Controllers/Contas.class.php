<?php

namespace Modules\configuracoes\Controllers;

class Contas {
    
    private  $codigoModulo = "configuracoes";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
           
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $params["bancos"] = $bancoRn->conexao->listar(null, 'nome');
        } catch (\Exception $ex) {
            exit(\Utils\Excecao::mensagem($ex));
        }
        \Utils\Layout::view("index_contas_bancarias", $params);
    }
    
    public function listar($params) {
        try {
            
            
            $filtro = \Utils\Post::get($params, "f", null);
            
            $w = null;
            
            if (!empty($filtro)) {
                $w = " ( (LOWER(agencia) LIKE LOWER('%{$filtro}%')) OR "
                . " (LOWER(conta) LIKE LOWER('%{$filtro}%'))"
                . ") ";
            }
            
            $contaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $lista = $contaRn->listar($w, "id", null, null, true);
            
            $html = $this->listaHtmlContas($lista);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listaHtmlContas($lista) {
        ob_start();
        if (sizeof($lista) > 0)  {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-lg-3">
                        <strong>Banco</strong>
                    </div>
                    <div class="col-lg-2 text-center">
                        <strong>Tipo de Conta</strong>
                    </div>
                    <div class="col-lg-2 text-center">
                        <strong>Agencia</strong>
                    </div>
                    <div class="col-lg-2 text-center">
                        <strong>Conta</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Ativo</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Editar</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Excluir</strong>
                    </div>
                </div>
            </li>
            <?php
            foreach ($lista as $contaBancaria) {
                $this->listeHtmlItem($contaBancaria);
            }
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Nenhuma conta bancária cadastrada
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function listeHtmlItem(\Models\Modules\Cadastro\ContaBancariaEmpresa $conta) {
        ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-lg-3">
                        <?php echo $conta->banco->nome ?>
                    </div>
                    <div class="col-lg-2 text-center">
                        <?php echo $conta->getTipoConta() ?>
                    </div>
                    <div class="col-lg-2 text-center">
                        <?php echo $conta->agencia ?>
                    </div>
                    <div class="col-lg-2 text-center">
                        <?php echo $conta->conta ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::ALTERAR_STATUS)) {  ?>
                        <button class="btn btn-<?php echo ($conta->ativo > 0 ? "primary" : "danger")?>" onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($conta->id) ?>')">
                            <i class="fa fa-<?php echo ($conta->ativo > 0 ? "check" : "remove")?>"></i>
                        </button>
                        <?php } ?>
                        
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::EDITAR)) {  ?>
                        <button class="btn btn-primary" onclick="cadastro(<?php echo $conta->id ?>)">
                            <i class="fa fa-edit"></i>
                        </button>
                        <?php }  ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::EXCLUIR)) {  ?>
                        <button class="btn btn-danger" onclick="modalExcluir(<?php echo $conta->id ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                        <?php }  ?>
                    </div>
                </div>
            </li>
        <?php
    }
    
    
    public function cadastro($params) {
        try {
           
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
            $contaBancaria->id = \Utils\Post::get($params, "id", null);
            
            if ($contaBancaria->id > 0) {
                $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
                $contaBancariaRn->conexao->carregar($contaBancaria);
                $json["salvar"] = (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::EDITAR));
            } else {
                $json["salvar"] = (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::CADASTRAR));
            }
            
            $json["conta"] = $contaBancaria;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
            $contaBancaria->id = \Utils\Post::get($params, "id", 0);
            
            if ($contaBancaria->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para editar os dados do usuário");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para cadastrar novos usuários");
                }
            }
            
            $contaBancaria->idBanco = \Utils\Post::get($params, "idBanco", null);
            $contaBancaria->conta = \Utils\Post::get($params, "conta", null);
            $contaBancaria->titular = \Utils\Post::get($params, "titular", null);
            $contaBancaria->cnpj = \Utils\Post::getDoc($params, "cnpj", null);
            $contaBancaria->agencia = \Utils\Post::get($params, "agencia", null);
            $contaBancaria->tipoConta = \Utils\Post::get($params, "tipoConta", null);
            $contaBancaria->observacoes = \Utils\Post::get($params, "observacoes", null);
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contaBancariaRn->salvar($contaBancaria);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Conta bancária cadastrada com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function excluir($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir usuários");
            }
            
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
            $contaBancaria->id = \Utils\Post::get($params, "id", 0);
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contaBancariaRn->conexao->excluir($contaBancaria);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusAtivo($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_CONTASBANCARIASEMPRESA, \Utils\Constantes::ALTERAR_STATUS)) {
                throw new \Exception("Você não tem permissão para alterar o status do usuário");
            }
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
            $contaBancaria->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contaBancariaRn->alterarStatusAtivo($contaBancaria);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}