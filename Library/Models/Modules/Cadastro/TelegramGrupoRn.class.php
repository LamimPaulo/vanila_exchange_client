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
class TelegramGrupoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TelegramGrupo());
        } else {
            $this->conexao = new GenericModel($adapter, new TelegramGrupo());
        }
    }
    
    public function salvar(TelegramGrupo &$telegramGrupo) {
        
        if ($telegramGrupo->id > 0) {
            $aux = new TelegramGrupo(Array("id" => $telegramGrupo->id));
            $this->conexao->carregar($aux);
            
            $telegramGrupo->ativo = $aux->ativo;
        } else {
            $telegramGrupo->ativo = 1;
        }
        
        if (empty($telegramGrupo->nome)) {
            throw new \Exception("O nome do Grupo deve ser informado");
        }
        
        if (empty($telegramGrupo->codigo)) {
            throw new \Exception("O código do Grupo deve ser informado");
        }
        
        $this->conexao->salvar($telegramGrupo);
        
    }
    
    public function alterarStatusAtivo(TelegramGrupo $telegramGrupo) {
        try {
            $this->conexao->carregar($telegramGrupo);
        } catch (\Exception $ex) {
            throw new \Exception("Grupo não localizado");
        }
        
        $telegramGrupo->ativo = ($telegramGrupo->ativo > 0 ? 0 : 1);
        
        $this->conexao->update( Array("ativo" => $telegramGrupo->ativo), Array("id" => $telegramGrupo->id));
    }
    
}

?>