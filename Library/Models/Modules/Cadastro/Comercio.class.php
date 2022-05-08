<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class Comercio {

    /**
     *
     * @var Integer 
     */
    public $id;
    
    /**
     *
     * @var String 
     */
    public $descricao;
    
    
    /**
     *
     * @var String 
     */
    public $cep;
    
    
    /**
     *
     * @var String 
     */
    public $endereco;
    
    
    /**
     *
     * @var String 
     */
    public $numero;
    
    
    /**
     *
     * @var String 
     */
    public $bairro;
    
    
    /**
     *
     * @var String 
     */
    public $codigoCidade;
    
    
    /**
     *
     * @var Integer 
     */
    public $idSegmentoComercio;
    
    
    /**
     *
     * @var Integer 
     */
    public $publico;
    
    
    /**
     *
     * @var String 
     */
    public $coordenadas;
    
    
    /**
     *
     * @var Integer 
     */
    public $idCliente;
    
    
    
    /**
     * Código do banco
     * @var Integer
     */
    public $ativo;
    
    
    /**
     *
     * @var SeguimentoComercial 
     */
    public $seguimentoComercial;
    
    /**
     *
     * @var Cliente
     */
    public $cliente;
    
    /**
     *
     * @var Cidade
     */
    public $cidade;
    
    
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
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->codigoCidade = ((isset($dados ['codigo_cidade'])) ? ($dados ['codigo_cidade']) : (null));
        $this->coordenadas = ((isset($dados ['coordenadas'])) ? ($dados ['coordenadas']) : (null));
        $this->descricao = ((isset($dados ['descricao'])) ? ($dados ['descricao']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->idSegmentoComercio = ((isset($dados ['id_segmento_comercio'])) ? ($dados ['id_segmento_comercio']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->publico = ((isset($dados ['publico'])) ? ($dados ['publico']) : (null));
        $this->endereco = ((isset($dados ['endereco'])) ? ($dados ['endereco']) : (null));
    }
    
    public function getTable() {
        return "comercios";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new Comercio();
    }


}

?>