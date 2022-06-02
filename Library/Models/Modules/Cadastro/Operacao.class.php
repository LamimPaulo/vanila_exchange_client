<?php

namespace Models\Modules\Cadastro;


class Operacao {
    /**
     * Chave primária da tabela
     * @var Integer
     */
    public $id;

    /**
     * Id da moeda
     * @var String
     */
    public $id_moeda;


    /**
     * Id contábil
     * @var String
     */
    public $id_contabil;

    /**
     * Tipo do movimento
     * @var String
     */
    public $movimento;

    /**
     * Descrição do movimento
     * @var String
     */
    public $nome;

    /**
     * Ativo?
     * @var String
     */
    public $ativo;

    /**
     * Último usuário que alterou o registro
     * @var String
     */
    public $cod_usr;

    /**
     * Data da última alteração
     * @var String
     */
    public $dt_atualizacao;

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
        $this->id_moeda = ((isset($dados ['id_moeda'])) ? ($dados['id_moeda']) : (null));
        $this->id_contabil = ((isset($dados ['id_contabil'])) ? ($dados ['id_contabil']) : (null));
        $this->movimento = ((isset($dados ['movimento'])) ? ($dados ['movimento']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->cod_usr = ((isset($dados ['cod_usr'])) ? ($dados ['cod_usr']) : (null));
        $this->dt_atualizacao = ((isset($dados ['dt_atualizacao'])) ? ($dados ['dt_atualizacao']) : (null));
    }

    public function getTable() {
        return "operacoes";
    }

    public function getSequence() {
      return null;
    }
    public function getInstance() {
        return new Operacao();
    }
}
