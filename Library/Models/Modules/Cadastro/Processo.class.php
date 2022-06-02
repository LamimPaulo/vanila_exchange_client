<?php

namespace Models\Modules\Cadastro;


class Processo {
    /**
     * Chave primária da tabela
     * @var Integer
     */
    public $id;

    /**
     * Símbolo da moeda
     * @var String
     */
    public $simbolo;


    /**
     * Nome da moeda
     * @var String
     */
    public $nome;

    /**
     * Ativa?
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
        $this->sigla = ((isset($dados ['simbolo'])) ? ($dados ['simbolo']) : (null));
        $this->nome = ((isset($dados ['nome'])) ? ($dados ['nome']) : (null));
        $this->ativo = ((isset($dados ['ativo'])) ? ($dados ['ativo']) : (null));
        $this->cod_usr = ((isset($dados ['cod_usr'])) ? ($dados ['cod_usr']) : (null));
        $this->dt_atualizacao = ((isset($dados ['dt_atualizacao'])) ? ($dados ['dt_atualizacao']) : (null));
    }

    public function getTable() {
        return "moedas";
    }

    public function getSequence() {
      return null;
    }
    public function getInstance() {
        return new Moeda();
    }
}
