<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasDocumentoSistema {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $idCliente;
    
    
    /**
     * 
     * @var String
     */
    public $idDocumentoSistema;

    
    /**
     * 
     * @var \Utils\Data 
     */
    public $dataAceitacao;
    
    
    
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
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idDocumentoSistema = ((isset($dados ['id_documento_sistema'])) ? ($dados ['id_documento_sistema']) : (null));
        $this->dataAceitacao = ((isset($dados['data_aceitacao'])) ? ($dados['data_aceitacao'] instanceof \Utils\Data ? $dados['data_aceitacao'] : 
            new \Utils\Data(substr($dados['data_aceitacao'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "clientes_has_documentos_sistemas";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ClienteHasDocumentoSistema();
    }


}

?>