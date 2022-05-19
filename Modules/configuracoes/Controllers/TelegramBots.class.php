<?php

namespace Modules\configuracoes\Controllers;

class TelegramBots {
    
    public function listar($params) {
        try {
            
            $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
            $result = $telegramBotRn->conexao->listar(null, "nome", null, null);
            
            
            
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
            foreach ($lista as $telegramBot) {
                ?>
                <tr>
                    <td>
                        <?php echo $telegramBot->nome ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-success" onclick="cadastroBotTelegram('<?php echo \Utils\Criptografia::encriptyPostId($telegramBot->id)?>')" >
                            Editar
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-<?php echo ($telegramBot->ativo > 0 ? "danger" : "primary") ?>" onclick="alterarStatusBotTelegram('<?php echo \Utils\Criptografia::encriptyPostId($telegramBot->id)?>')" >
                            <?php echo ($telegramBot->ativo > 0 ? "Desativar" : "Ativar") ?>
                        </button>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="3" class="text-center">
                    Nenhum bot cadastrado.
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
            
            $telegramBot = new \Models\Modules\Cadastro\TelegramBot();
            $telegramBot->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($telegramBot->id > 0) {
                $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
                $telegramBotRn->conexao->carregar($telegramBot);
            } 
            
            
            $telegramBot->id = \Utils\Criptografia::encriptyPostId($telegramBot->id);
            
            $json["bot"] = $telegramBot;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            
            $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
            
            $telegramBot = new \Models\Modules\Cadastro\TelegramBot();
            $telegramBot->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $telegramBot->nome = \Utils\Post::get($params, "nome", NULL);
            $telegramBot->chave = \Utils\Post::get($params, "chave", NULL);
            
            
            $telegramBotRn->salvar($telegramBot);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Bot salvo com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusAtivo($params) {
        try {
            $telegramBot = new \Models\Modules\Cadastro\TelegramBot();
            $telegramBot->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
            $telegramBotRn->alterarStatusAtivo($telegramBot);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Status alterado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
}