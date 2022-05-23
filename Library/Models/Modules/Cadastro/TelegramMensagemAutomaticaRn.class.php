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
class TelegramMensagemAutomaticaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TelegramMensagemAutomatica());
        } else {
            $this->conexao = new GenericModel($adapter, new TelegramMensagemAutomatica());
        }
    }
    
    public function salvar(TelegramMensagemAutomatica &$telegramMensagemAutomatica) {
        
        if ($telegramMensagemAutomatica->id > 0) {
            $aux = new TelegramMensagemAutomatica(Array("id" => $telegramMensagemAutomatica->id));
            $this->conexao->carregar($aux);
            
            $telegramMensagemAutomatica->ativo = $aux->ativo;
            $telegramMensagemAutomatica->dataAtualizacao = $aux->dataAtualizacao;
        } else {
            $telegramMensagemAutomatica->ativo = 1;
            $telegramMensagemAutomatica->dataAtualizacao = null;
        }
        
        if (empty($telegramMensagemAutomatica->conteudo)) {
            throw new \Exception("É necessário informar o conteúdo");
        }
        if (empty($telegramMensagemAutomatica->periodicidade)) {
            throw new \Exception("É necessário informar a periodicidade");
        }
        
        if (!$telegramMensagemAutomatica->idTelegramBot > 0) {
            throw new \Exception("É necessário informar o id do bot");
        }
        if (!$telegramMensagemAutomatica->idTelegramGrupo > 0) {
            throw new \Exception("É necessário informar o id do grupo");
        }
        
        $this->conexao->salvar($telegramMensagemAutomatica);
        
    }
    
    
    
    public function alterarStatusAtivo(TelegramMensagemAutomatica &$telegramMensagemAutomatica) {
        try {
            $this->conexao->carregar($telegramMensagemAutomatica);
        } catch (\Exception $ex) {
            throw new \Exception("Mensagem não localizada");
        }
        
        $telegramMensagemAutomatica->ativo = ($telegramMensagemAutomatica->ativo > 0 ? 0 : 1);
        
        $this->conexao->update( Array("ativo" => $telegramMensagemAutomatica->ativo), Array("id" => $telegramMensagemAutomatica->id));
    }
    
    
}

?>