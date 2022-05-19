<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TelegramBotRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TelegramBot());
        } else {
            $this->conexao = new GenericModel($adapter, new TelegramBot());
        }
    }
    
    public function salvar(TelegramBot &$telegramBot) {
        
        if ($telegramBot->id > 0) {
            $aux = new TelegramBot(Array("id" => $telegramBot->id));
            $this->conexao->carregar($aux);
            
            $telegramBot->ativo = $aux->ativo;
        } else {
            $telegramBot->ativo = 1;
        }
        
        if (empty($telegramBot->nome)) {
            throw new \Exception("O nome do Bot deve ser informado");
        }
        
        if (empty($telegramBot->chave)) {
            throw new \Exception("A chave do Bot deve ser informada");
        }
        
        $this->conexao->salvar($telegramBot);
        
    }
    
    public function alterarStatusAtivo(TelegramBot $telegramBot) {
        try {
            $this->conexao->carregar($telegramBot);
        } catch (\Exception $ex) {
            throw new \Exception("Bot não localizado");
        }
        
        $telegramBot->ativo = ($telegramBot->ativo > 0 ? "0" : "1");
        
        $this->conexao->update(Array("ativo" => $telegramBot->ativo), Array("id" => $telegramBot->id));
    }
    
}

?>