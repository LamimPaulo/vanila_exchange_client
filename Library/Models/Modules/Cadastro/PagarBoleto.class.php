<?php
namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class PagarBoleto {
  public $linha_digitavel;
  public $banco;
  public $valor;
  public $vencimento;
  public $email;
  public $aceite;
  public $create_at;
  public $ip;

  public function __construct($dados = null) {
    if (!is_null($dados)) {
        $this->exchangeArray($dados);
    }    
  }

  public function exchangeArray($dados) {
    $this->linha_digitavel = $dados["linha_digitavel"];
    $this->banco = $dados["banco"];
    $this->valor = $dados["valor"];
    $this->vencimento = $dados["vencimento"];
    $this->email = $dados["email"];
    $this->aceite = $dados["aceite"];
    $this->create_at = $dados["create_at"];
    $this->ip = $dados["ip"];
    return;
  }

  public function getTable() {
      return "PagarBoleto";
  }

  public function getSequence() {
      return null;
  }

  public function getInstance() {
      return new PagarBoleto();
  }
}
?>
