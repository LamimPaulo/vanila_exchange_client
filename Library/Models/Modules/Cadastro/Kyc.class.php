<?php

namespace Models\Modules\Cadastro;


/**
 * 
 *
 */
class Kyc {

    /**
     *
     * @var Integer 
     */
    public $id;



    /**
     *
     * @var String 
     */
    public $caf_id;

    /**
     *
     * @var String
     */
    public $report_id;

    /**
     *
     * @var String
     */
    public $status;

    /**
     *
     * @var String
     */
    public $federal_document;


    /**
     *
     * @var String
     */
    public $url;


    /**
     *
     * @var \Utils\Data 
     */
    public $created_at;

    /**
     *
     * @var \Utils\Data
     */
    public $updated_at;

    
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
        $this->caf_id = ((isset($dados ['caf_id'])) ? ($dados ['caf_id']) : (null));
        $this->report_id = ((isset($dados ['report_id'])) ? ($dados ['report_id']) : (null));
        $this->status = ((isset($dados ['status'])) ? ($dados ['status']) : (null));
        $this->federal_document = ((isset($dados ['federal_document'])) ? ($dados ['federal_document']) : (null));
        $this->created_at = ((isset($dados ['created_at'])) ? ($dados ['created_at']) : (null));
        $this->updated_at = ((isset($dados ['updated_at'])) ? ($dados ['updated_at']) : (null));
        $this->url = ((isset($dados ['url'])) ? ($dados ['url']) : (null));

    }
    
    public function getTable() {
        return "kyc_verifications";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Kyc();
    }


}

?>
