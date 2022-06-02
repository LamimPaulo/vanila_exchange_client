<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados da Remessa de Dinheiro
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LimiteOperacional {

    
    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var Integer 
     */
    public $ativo;
    
    /**
     *
     * @var Integer 
     */
    public $fase;
    
    /**
     *
     * @var Integer 
     */
    public $idMoeda;
    
    /**
     *
     * @var Double 
     */
    public $saqueDiario;
    
    /**
     *
     * @var Double 
     */
    public $saqueMensal;
    
    /**
     *
     * @var Double 
     */
    public $depositoDiario;
    
    /**
     *
     * @var Double 
     */
    public $depositoMensal;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Cliente 
     */
    public $cliente;
    
    /**
     *
     * @var Moeda 
     */
    public $moeda;
    
    
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
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->fase = ((isset($dados ['fase'])) ? ($dados ['fase']) : (null));
        $this->idMoeda = ((isset($dados ['id_moeda'])) ? ($dados ['id_moeda']) : (null));
        $this->saqueDiario = ((isset($dados ['saque_diario'])) ? ($dados ['saque_diario']) : (null));
        $this->saqueMensal = ((isset($dados ['saque_mensal'])) ? ($dados ['saque_mensal']) : (null));
        $this->depositoDiario = ((isset($dados ['deposito_diario'])) ? ($dados ['deposito_diario']) : (null));
        $this->depositoMensal = ((isset($dados ['deposito_mensal'])) ? ($dados ['deposito_mensal']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        
    }
    
    public function getTable() {
        return "limites_operacionais";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LimiteOperacional();
    }
}

?>
