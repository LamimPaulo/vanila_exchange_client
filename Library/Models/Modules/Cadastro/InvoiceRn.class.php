<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class InvoiceRn {
  public $conexao = null;

  public function __construct(\Io\BancoDados $adapter = null) {
      if ($adapter == null) {
          $this->conexao = new GenericModel(\Dduo::conexao(), new Invoice());
      } else {
          $this->conexao = new GenericModel($adapter, new Invoice());
      }
  }

  public function salvar(Invoice &$pdv) {
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

  public function carregar(Invoice &$inv) {
    $result = $this->conexao->listar("id_cliente = " . $inv->id_cliente);
    $lista = [];
    foreach ($result as $invoice) {
        $this->carregar($invoice);
        # TODO: verificar se está correto
        $lista[] = $invoice;
    }
    # TODO: verificar se está correto
    return $invoice;
  }

  public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true) {

      foreach ($result as $contaCorrenteBtc) {
          $this->carregar($contaCorrenteBtc, false, $carregarCliente);
          $lista[] = $contaCorrenteBtc;
      }
      return $contaCorrenteBtc;
  }
  
  public function listar($id) {
    $res = $this->conexao->listar("id = {$id}");
    if (sizeof($res) > 0) {
      return $res->current();
    };
    return null;
  }

  public function salvarX() {

  }
}
