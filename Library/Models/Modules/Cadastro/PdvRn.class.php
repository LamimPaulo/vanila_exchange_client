<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class PdvRn {
  public $conexao = null;

  public function __construct(\Io\BancoDados $adapter = null) {
      if ($adapter == null) {
          $this->conexao = new GenericModel(\Dduo::conexao(), new Pdv());
      } else {
          $this->conexao = new GenericModel($adapter, new Pdv());
      }
  }

  public function salvar(Pdv &$pdv) {
    try {
        $this->conexao->adapter->iniciar();
        // RDN
        $this->conexao->insert(Array(
            "id" => $pdv->id
        ));
        $this->conexao->adapter->finalizar();
    } catch(\Exception $e) {
        $this->conexao->adapter->cancelar();
        throw new \Exception($e);
    }
  }

}
