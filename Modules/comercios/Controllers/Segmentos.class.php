<?php

namespace Modules\comercios\Controllers;

class Segmentos {
    
    private $codigoModulo = "comercios";
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("index_segmentos", $params);
    }
    
    
    public function listar($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            $segmentoRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            
            $where = (empty($filtro) ? null : " LOWER(nome) LIKE LOWER('%{$filtro}%') ");
            
            $lista = $segmentoRn->conexao->listar($where, "nome", null, null);
            
            $json["html"] = $this->htmlLista($lista);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function htmlLista($lista) {
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $segmento) {
                $this->htmlItemLista($segmento);
            }
        } else {
            ?>
            <tr>
                <td class="text-center" colspan="4">Nenhum segmento comercial cadastrado</td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    public function htmlItemLista(\Models\Modules\Cadastro\SeguimentoComercial $segmento) {
        ?>
        <tr>
            <td class="text-center"><?php echo $segmento->nome ?></td>
            <td class="text-center">
                <button class="btn btn-xs btn-<?php echo ($segmento->ativo < 1 ? "danger" : "primary") ?>" type="button" 
                        onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($segmento->id); ?>');">
                    <i class="fa fa-<?php echo ($segmento->ativo < 1 ? "square-o" : "check") ?>"></i> 
                </button>
            </td>
            <td class="text-center">
                <button class="btn btn-success" onclick="cadastroSegmento('<?php echo \Utils\Criptografia::encriptyPostId($segmento->id); ?>');">
                    <i class="fa fa-edit"></i>
                </button>
            </td>
            <td class="text-center">
                <button class="btn btn-danger" onclick="dialogExcluirSegmentoComercio('<?php echo \Utils\Criptografia::encriptyPostId($segmento->id); ?>');">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php
    }
    
    
    
    public function cadastro($params) {
        try {
            $segmento = new \Models\Modules\Cadastro\SeguimentoComercial();
            $segmento->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($segmento->id > 0) {
                $segmentoRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
                $segmentoRn->conexao->carregar($segmento);
            }
            
            $json["codigo"] = \Utils\Criptografia::encriptyPostId($segmento->id);
            $json["nome"] = $segmento->nome;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            $segmento = new \Models\Modules\Cadastro\SeguimentoComercial();
            $segmento->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $segmento->nome = \Utils\Post::get($params, "nome", NULL);
            
            $segmentoRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            $segmentoRn->salvar($segmento);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Segmento comercial cadastrado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $segmento = new \Models\Modules\Cadastro\SeguimentoComercial();
            $segmento->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $segmentoRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            $segmentoRn->excluir($segmento);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Registro excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function status($params) {
        try {
            $segmento = new \Models\Modules\Cadastro\SeguimentoComercial();
            $segmento->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $segmentoRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            $segmentoRn->alterarStatusAtivo($segmento);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}