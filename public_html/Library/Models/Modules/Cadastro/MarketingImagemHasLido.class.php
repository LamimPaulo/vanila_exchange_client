<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class MarketingImagemHasLido {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer
     */
    public $idNotificacao;
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
     /**
     *
     *@var \Utils\Data 
     */
    public $dataLeitura;
        
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
        $this->id = ((isset($dados ['id'])) ? ($dados ['id']) : (null));
        $this->idNotificacao = ((isset($dados ['id_notificacao'])) ? ($dados ['id_notificacao']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->dataLeitura = ((isset($dados['data_leitura'])) ? ($dados['data_leitura'] instanceof \Utils\Data ? $dados['data_leitura'] : new \Utils\Data(substr($dados['data_leitura'], 0, 19))) : (null));

    }
    
    public function getTable() {
        return "marketing_imagem_has_lido";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new NotificacaoMoedaHasLido();
    }


}

?>