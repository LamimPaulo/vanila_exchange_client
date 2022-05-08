<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LogAcesso {


    
    /**
     * Código do banco
     * @var String
     */
    public $email;

    
    /**
     * 
     * @var Datatime 
     */
    public $dataHora;
    
    /**
     * 
     * @var String 
     */
    public $ip;

    /**
     * 
     * @var String 
     */
    public $sistemaoperacional;

    /**
     * 
     * @var String 
     */
    public $navegador;

    /**
     * 
     * @var String 
     */
    public $versaonavegador;

    /**
     * 
     * @var String 
     */
    public $agente;

    /**
     *
     * @var String 
     */
    public $idRegistro;
    
    /**
     *
     * @var String 
     */
    public $tabela;
    
    /**
     *
     * @var String 
     */
    public $acao;
    
    /**
     *
     * @var String 
     */
    public $jsonDados;
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    /**
     *
     * @var Integer 
     */
    public $idUsuario;
    
    
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
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->datahora = ((isset($dados ['data_hora'])) ? ($dados ['data_hora']) : (null));
        $this->ip = ((isset($dados ['ip'])) ? ($dados ['ip']) : (null));
        $this->sistemaoperacional = ((isset($dados ['sistemaoperacional'])) ? ($dados ['sistemaoperacional']) : (null));
        $this->navegador = ((isset($dados ['navegador'])) ? ($dados ['navegador']) : (null));
        $this->versaonavegador = ((isset($dados ['versaonavegador'])) ? ($dados ['versaonavegador']) : (null));
        $this->agente = ((isset($dados ['agente'])) ? ($dados ['agente']) : (null));
        
        $this->idRegistro = ((isset($dados ['id_registro'])) ? ($dados ['id_registro']) : (null));
        $this->tabela = ((isset($dados ['tabela'])) ? ($dados ['tabela']) : (null));
        $this->acao = ((isset($dados ['acao'])) ? ($dados ['acao']) : (null));
        $this->jsonDados = ((isset($dados ['json_dados'])) ? ($dados ['json_dados']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idUsuario = ((isset($dados ['id_usuario'])) ? ($dados ['id_usuario']) : (null));
    }
    
    public function getTable() {
        return "logacesso";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new LogAcesso();
    }


}

?>