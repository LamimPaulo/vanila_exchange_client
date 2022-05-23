<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Cadastro\Moeda;
use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Moeda
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class OperacaoRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
       public $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Moeda());
        } else {
            $this->conexao = new GenericModel($adapter, new Moeda());
        }
    }

    public function carregar(Moeda &$moeda, $carregar = true) {
        if ($carregar) {
            $this->conexao->carregar($moeda);
        }
    }

    public function listar($where = null, $order = null, $offset = null, $limit = null) {
      $result = $this->conexao->listar($where, $order, $offset, $limit);
      $lista = Array();
      foreach ($result as $moeda) {
          $this->carregar($moeda, false);
          $lista[] = $moeda;
      }
      return $lista;
    }

    public function salvar(Moeda &$par) {
        if (strlen($par->nome) <= 0) {
            throw new \Exception($this->idioma->getText("necessarioInformarNomeMoeda"));
        }

        if (strlen($par->simbolo) < 2) {
            throw new \Exception($this->idioma->getText("simboloMoedaInvalido"));
        }

        $this->conexao->salvar($par);
    }
}
