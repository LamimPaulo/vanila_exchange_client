<?php

namespace Modules\servicos\Controllers;

class CategoriasServicos {
    
    private $idioma = null;
    
        public function __construct(&$params) {

        $this->idioma = new \Utils\PropertiesUtils("categoria_servico", 'IDIOMA');
    }
    
    public function listar($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categorias = $categoriaServicoRn->conexao->listar("id_cliente = {$cliente->id}", "descricao", NULL, NULL);
            
            ob_start();
            if (sizeof($categorias) > 0) {
                foreach ($categorias as $categoria) {
                    ?>
                    <tr>
                        <td><?php echo $categoria->descricao ?></td>
                        <td class="text-center">
                            <button class="btn btn-<?php echo ($categoria->ativo > 0 ? "primary" : "danger") ?>" onclick="alterarStatusCategoriaServico('<?php echo \Utils\Criptografia::encriptyPostId($categoria->id)?>');">
                                <i class="fa fa-<?php echo ($categoria->ativo > 0 ? "check" : "times") ?>"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-success" onclick="cadastroCatagoriasServicos('<?php echo \Utils\Criptografia::encriptyPostId($categoria->id)?>');">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-danger" onclick="modalExcluirCategoriasServicos('<?php echo \Utils\Criptografia::encriptyPostId($categoria->id)?>');">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center" ><?php echo $this->idioma->getText("nenhumaCadastrada") ?></td>
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
    
    
    public function cadastro($params) {
        try {
            $categoriaServico = new \Models\Modules\Cadastro\CategoriaServico();
            $categoriaServico->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($categoriaServico->id > 0) {
                try {
                    $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
                    $categoriaServicoRn->conexao->carregar($categoriaServico);
                } catch (\Exception $ex) {
                    throw new \Exception($this->idioma->getText("catServInvalida"));
                }
            }
            $categoriaServico->id = \Utils\Criptografia::encriptyPostId($categoriaServico->id);
            
            $json["categoria"] = $categoriaServico;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            $categoriaServico = new \Models\Modules\Cadastro\CategoriaServico();
            $categoriaServico->id = \Utils\Post::getEncrypted($params, "id", 0);
            $categoriaServico->descricao = \Utils\Post::get($params, "descricao", null);
            
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categoriaServicoRn->salvar($categoriaServico);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("catCadSuces");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function alterarStatusAtivo($params) {
        try {
            $categoriaServico = new \Models\Modules\Cadastro\CategoriaServico();
            $categoriaServico->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categoriaServicoRn->alterarStatusAtivo($categoriaServico);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("statusAltSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function excluir($params) {
        try {
            $categoriaServico = new \Models\Modules\Cadastro\CategoriaServico();
            $categoriaServico->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categoriaServicoRn->excluir($categoriaServico);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("catExcSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getHtmlOptions($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $categoriaServicoRn = new \Models\Modules\Cadastro\CategoriaServicoRn();
            $categorias = $categoriaServicoRn->conexao->listar("id_cliente = {$cliente->id} AND ativo > 0", "descricao");
            
            ob_start();
            ?>
            <option value="<?php echo \Utils\Criptografia::encriptyPostId(0)?>">Selecione uma categoria para o serviço</option>
            <?php
            if (sizeof($categorias) > 0) {
                foreach ($categorias as $categoria) {
                    ?>
                    <option value="<?php echo \Utils\Criptografia::encriptyPostId($categoria->id)?>"><?php echo $categoria->descricao ?></option>
                    <?php
                }
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("catExcSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}
