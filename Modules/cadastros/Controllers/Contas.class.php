<?php

namespace Modules\cadastros\Controllers;

require_once getcwd() . '/Library/Models/Modules/Cadastro/ConsultaCnpjRn.class.php';
require_once getcwd() . '/Library/Models/Modules/Cadastro/ConsultaCnpj.class.php';

class Contas {
    
    private  $codigoModulo = "movimentacoes";
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("saque", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        try {
            $usuarioLogado = \Utils\Geral::getLogado();
            if (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $params["clientes"] = $clienteRn->conexao->listar(null, 'nome');
            }
            
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $params["bancos"] = $bancoRn->conexao->listar(null, 'nome');
        } catch (\Exception $ex) {
            exit(\Utils\Excecao::mensagem($ex));
        }
        \Utils\Layout::view("index_contas_bancarias", $params);
    }
    
    public function listar($params) {
        try {
            
            $usuarioLogado = \Utils\Geral::getLogado();
            
            $idCliente = "";
            if (\Utils\Geral::isUsuario() && $usuarioLogado->tipo == \Utils\Constantes::ADMINISTRADOR) {
                $idCliente = \Utils\Post::get($params, "idCliente", 0);
                
            } else {
                $cliente = \Utils\Geral::getCliente();
                $idCliente = $cliente->id;
            }
            
            $filtro = \Utils\Post::get($params, "f", null);
            
            $w = "";
            
            if (!empty($filtro)) {
                $w = "AND ( (LOWER(agencia) LIKE LOWER('%{$filtro}%')) OR "
                . " (LOWER(conta) LIKE LOWER('%{$filtro}%'))"
                . ") ";
            }
            
            $contaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $lista = $contaRn->listar("id_cliente = {$idCliente} {$w}", "id", null, null, true);
            
            $html = $this->listaHtmlCarteira($lista);
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listaHtmlCarteira($lista) {
        ob_start();
        if (sizeof($lista) > 0)  {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-lg-4">
                        <strong><?php echo $this->idioma->getText("bancoC") ?></strong>
                    </div>
                    <div class="col-lg-2 text-center">
                        <strong><?php echo $this->idioma->getText("tipoContaC") ?></strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong><?php echo $this->idioma->getText("agenciaC") ?></strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong><?php echo $this->idioma->getText("contaC") ?></strong>
                    </div>
                    <div class="col-lg-3 text-center">
                        <strong><?php echo $this->idioma->getText("obsC") ?></strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong><?php echo $this->idioma->getText("excluirC") ?></strong>
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
                        <?php echo $this->idioma->getText("nenhumaContaEncontrada") ?>
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function listeHtmlItem(\Models\Modules\Cadastro\ContaBancaria $conta) {
        $cliente = \Utils\Geral::getCliente();
        $idCliente = $cliente->id;
        ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-lg-4">
                        <?php echo $conta->banco->nome ?>
                    </div>
                    <div class="col-lg-2 text-center">
                        <?php echo $conta->getTipoConta() ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php echo $conta->agencia ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php echo $conta->conta ?>
                    </div>
                    <div class="col-lg-3 text-center">
                        <?php echo $conta->observacoes ?>
                    </div>
                    <div class="col-lg-1 text-center">
                        <?php if ($idCliente == $conta->idCliente) { ?>
                        <button class="btn btn-danger" onclick="modalExcluir(<?php echo $conta->id ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                        <?php } ?>
                    </div>
                </div>
            </li>
        <?php
    }
    
    
    public function cadastro($params) {
        try {
            
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->id = \Utils\Post::get($params, "id", null);
            
            if ($contaBancaria->id > 0) {
                $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                $contaBancariaRn->conexao->carregar($contaBancaria);
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
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->idBanco = \Utils\Post::get($params, "idBanco", null);
            $contaBancaria->conta = \Utils\Post::get($params, "conta", null);
            $contaBancaria->agencia = \Utils\Post::get($params, "agencia", null);
            $contaBancaria->tipoConta = \Utils\Post::get($params, "tipoConta", null);
            $contaBancaria->agenciaDigito = \Utils\Post::get($params, "agenciaDigito", 0);
            $contaBancaria->contaDigito = \Utils\Post::get($params, "contaDigito", 0);
            $contaBancaria->documentoCliente = \Utils\Post::getEncrypted($params, "documento", null);
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();

            $contaBancariaRn->salvar($contaBancaria);
            
            
            $json["mensagem"] = $this->idioma->getText("contaRegistrada");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function excluir($params) {
        try {
            
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->id = \Utils\Post::get($params, "id", 0);
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contaBancariaRn->conexao->excluir($contaBancaria);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getBancos($params) {
        try {
            $bancoRn = new \Models\Modules\Cadastro\BancoRn();
            $bancos = $bancoRn->conexao->listar(null, "nome", null, null);
            
            ob_start();
            ?>
            <option value="0">Selecione um banco</option>
            <?php
            foreach ($bancos as $banco) {
                ?>
                <option value="<?php echo $banco->id ?>"><?php echo "{$banco->codigo} - {$banco->nome}" ?></option>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["bancos"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
            $json["sucesso"] = false;
        }
        print json_encode($json);
    }
    
    
    
    
    
    public function getContasCliente($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $html = "";
            
            
            if ($cliente != null) { 
                $contaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                $lista = $contaRn->listar("id_cliente = {$cliente->id}", "ativo DESC", null, null, true);

                $html = $this->listaTableCarteira($lista);
            }             
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listaTableCarteira($lista) {
        ob_start();
        if (sizeof($lista) > 0)  {
            foreach ($lista as $contaBancaria) {
                $this->listeTrItem($contaBancaria);
            }
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function listeTrItem(\Models\Modules\Cadastro\ContaBancaria $conta) {
        ?>
        <tr >
            <td class="text-center">
                <?php echo $conta->nomeCliente ?>
            </td>
            <td class="text-center">
                <?php echo $conta->documentoCliente ?>
            </td>
            <td class="text-center">
                <?php echo $conta->banco->codigo ?>
            </td>
            <td class="text-center">
                <?php echo $conta->banco->nome ?>
            </td>
            <td class="text-center">
                <?php echo $conta->agencia . ($conta->agenciaDigito != null ? " - " . $conta->agenciaDigito : "") ?>
            </td>
            <td class="text-center">
                <?php echo $conta->conta . ($conta->contaDigito != null ? " - " . $conta->contaDigito : "") ?>
            </td>
            <td class="text-center">
                <?php echo $conta->getTipoConta() ?>
            </td>
            <td class="text-center">
                <?php echo ($conta->ativo > 0 ?  $this->idioma->getText("simC") : $this->idioma->getText("naoC")) ?>
            </td>
            <td class="text-center">
                <input type="checkbox" class="js-switch" onchange="alterarStatusContaBancaria('<?php echo \Utils\Criptografia::encriptyPostId($conta->id) ?>');" <?php echo $conta->ativo > 0 ?  "checked" : "" ?>/>
            </td>
        </tr>
        <?php
    }
    
    
    public function alterarStatus($params) {
        try {
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contaBancariaRn->alterarStatusAtivo($contaBancaria);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("statusAlteradoSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}