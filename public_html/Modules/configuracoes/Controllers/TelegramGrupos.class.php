<?php

namespace Modules\configuracoes\Controllers;

class TelegramGrupos {
    
    public function listar($params) {
        try {
            
            $telegramGrupoRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
            $result = $telegramGrupoRn->conexao->listar(null, "nome", null, null);
            
            
            
            $json["html"] = $this->listaHtml($result);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listaHtml($lista) {
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $telegramGrupo) {
                ?>
                <tr>
                    <td>
                        <?php echo $telegramGrupo->nome ?>
                    </td>
                    <td>
                        <?php echo $telegramGrupo->codigo?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-success" onclick="cadastroGrupoTelegram('<?php echo \Utils\Criptografia::encriptyPostId($telegramGrupo->id)?>')" >
                            Editar
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-<?php echo ($telegramGrupo->ativo > 0 ? "danger" : "primary") ?>" onclick="alterarStatusGrupoTelegram('<?php echo \Utils\Criptografia::encriptyPostId($telegramGrupo->id)?>')" >
                            <?php echo ($telegramGrupo->ativo > 0 ? "Desativar" : "Ativar") ?>
                        </button>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="4"  class="text-center" >
                    Nenhum Grupo cadastrado.
                </td>
            </tr>
            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function cadastro($params) {
        try {
            
            $telegramGrupo = new \Models\Modules\Cadastro\TelegramGrupo();
            $telegramGrupo->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($telegramGrupo->id > 0) {
                $telegramBotRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
                $telegramBotRn->conexao->carregar($telegramGrupo);
            } 
            
            
            $telegramGrupo->id = \Utils\Criptografia::encriptyPostId($telegramGrupo->id);
            
            $json["grupo"] = $telegramGrupo;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            
            $telegramGrupoRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
            
            $telegramGrupo = new \Models\Modules\Cadastro\TelegramGrupo();
            $telegramGrupo->id = \Utils\Post::getEncrypted($params, "id", 0);
            $telegramGrupo->nome = \Utils\Post::get($params, "nome", NULL);
            $telegramGrupo->codigo = \Utils\Post::get($params, "codigo", NULL);
            
            
            $telegramGrupoRn->salvar($telegramGrupo);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Grupo salvo com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusAtivo($params) {
        try {
            $telegramGrupo = new \Models\Modules\Cadastro\TelegramGrupo();
            $telegramGrupo->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $telegramGrupoRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
            $telegramGrupoRn->alterarStatusAtivo($telegramGrupo);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}