<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TelegramMensagemAutomatica {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var \Utils\Data 
     */
    public $dataAtualizacao;
    
    /**
     *
     * @var String 
     */
    public $periodicidade;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $idTelegramBot;
    
    /**
     *
     * @var Integer 
     */
    public $idTelegramGrupo;
    
    /**
     *
     * @var String 
     */
    public $conteudo;
    
    /**
     *
     * @var String 
     */
    public $slug;
    
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->conteudo = ((isset($dados ['conteudo'])) ? ($dados ['conteudo']) : (null));
        $this->dataAtualizacao = ((isset($dados ['data_atualizacao'])) ? ($dados ['data_atualizacao'] instanceof \Utils\Data ? 
                $dados ['data_atualizacao'] : new \Utils\Data(substr($dados ['data_atualizacao'], 0, 19))) : (null));
        $this->idTelegramBot = ((isset($dados ['id_telegram_bot'])) ? ($dados ['id_telegram_bot']) : (null));
        $this->idTelegramGrupo = ((isset($dados ['id_telegram_grupo'])) ? ($dados ['id_telegram_grupo']) : (null));
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->periodicidade = ((isset($dados ['periodicidade'])) ? ($dados ['periodicidade']) : (null));
        $this->slug = ((isset($dados ['slug'])) ? ($dados ['slug']) : (null));
    }
    
    public function getTable() {
        return "telegram_mensagem_automatica";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new TelegramMensagemAutomatica();
    }


}

?>