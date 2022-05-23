<?php

namespace Modules\configuracoes\Controllers;

class Roadmap {
    
    private $codigoModulo = "configuracoes";
    
    public function __construct($params) {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        \Utils\Layout::view("roadmap", $params);
    }
    
    public function listar($params) {
        
        try {
            
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $roadmaps = $roadmapRn->conexao->listar(NULL, "posicao", NULL, NULL);
            
            ob_start();
            if (sizeof($roadmaps) > 0) {
                foreach ($roadmaps as $roadmap) {
                    $this->tableRoadmap($roadmap);
                }
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="7">
                        Nenhum roadmap cadastrado
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
    
    
    
    private function tableRoadmap(\Models\Modules\Cadastro\Roadmap $roadmap) {
        ?>
        <tr>
            <td class="text-center"><?php echo $roadmap->posicao ?></td>
            <td><?php echo $roadmap->titulo ?></td>
            <td class="text-center"><?php echo $roadmap->data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
            <td class="text-center">
                <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ROADMAP, \Utils\Constantes::EDITAR)) { ?>
                <button class="btn btn-success" onclick="cadastro('<?php echo \Utils\Criptografia::encriptyPostId($roadmap->id)?>');">
                    <i class="fa fa-edit"></i>
                </button>
                <?php } ?>
            </td>
            <td class="text-center" >
                <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ROADMAP, \Utils\Constantes::EXCLUIR)) { ?>
                <button class="btn btn-danger"  onclick="modalExcluir('<?php echo \Utils\Criptografia::encriptyPostId($roadmap->id)?>');">
                    <i class="fa fa-trash"></i>
                </button>
                <?php } ?>
            </td>
            
            <td class="text-center" >
                
                <button class="btn btn-<?php echo ($roadmap->concluido > 0 ? "primary" : "danger") ?>"  onclick="alterarStatusConcluido('<?php echo \Utils\Criptografia::encriptyPostId($roadmap->id)?>');">
                    <i class="fa fa-<?php echo ($roadmap->concluido > 0 ? "check" : "remove") ?>"></i>
                </button>
                
            </td>
            
            <td class="text-center" >
                
                <button class="btn btn-<?php echo ($roadmap->publicado > 0 ? "primary" : "danger") ?>"  onclick="alterarStatusPublicado('<?php echo \Utils\Criptografia::encriptyPostId($roadmap->id)?>');">
                    <i class="fa fa-<?php echo ($roadmap->publicado > 0 ? "check" : "remove") ?>"></i>
                </button>
                
            </td>
        </tr>
        <?php
    }
    
    public function cadastrar($params) {
        try {
            
            $roadmap = new \Models\Modules\Cadastro\Roadmap();
            $roadmap->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($roadmap->id > 0) {
                $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
                $roadmapRn->conexao->carregar($roadmap);
            }
            
            $roadmap->id = \Utils\Criptografia::encriptyPostId($roadmap->id);
            $roadmap->data = ($roadmap->data != null ? $roadmap->data->formatar(\Utils\Data::FORMATO_PT_BR) : "");
            
            $json["roadmap"] = $roadmap;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            
            $roadmap = new \Models\Modules\Cadastro\Roadmap();
            $roadmap->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($roadmap->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ROADMAP, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para editar registros");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ROADMAP, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para criar registros");
                }
            }
            
            $roadmap->imagem = \Utils\File::get($params, "imagem", null, Array("IMG"), null, "roadmap");
            $roadmap->posicao = \Utils\Post::get($params, "posicao", 0);
            $roadmap->texto = \Utils\Post::get($params, "texto", null);
            $roadmap->titulo = \Utils\Post::get($params, "titulo", null);
            $roadmap->data = \Utils\Post::getData($params, "data", null, "00:00:00");
            
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $roadmapRn->salvar($roadmap);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Roadmap salvo com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function excluir($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_ROADMAP, \Utils\Constantes::EXCLUIR)) {
                throw new \Exception("Você não tem permissão para excluir registros");
            }
            $roadmap = new \Models\Modules\Cadastro\Roadmap();
            $roadmap->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $roadmapRn->excluir($roadmap);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Roadmap excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusConcluido($params) {
        try {
            $roadmap = new \Models\Modules\Cadastro\Roadmap();
            $roadmap->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $roadmapRn->alterarStatusConcluido($roadmap);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function alterarStatusPublicado($params) {
        try {
            $roadmap = new \Models\Modules\Cadastro\Roadmap();
            $roadmap->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $roadmapRn->alterarStatusPublicado($roadmap);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}