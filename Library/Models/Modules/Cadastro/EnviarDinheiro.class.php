<?php
namespace Models\Modules\Cadastro;

class EnviarDinheiro {
  public $titular;
  public $cpf;
  public $banco;
  public $tipo_conta;
  public $agencia;
  public $conta;
  public $valor_reais;
  public $valor_btc;
  public $email;
  public $create_at;
  public $ip;

  public function __construct($dados = null) {
    if (!is_null($dados)) {
        $this->exchangeArray($dados);
    }
  }

  public function exchangeArray($dados) {
    $this->titular = $dados["titular"];
    $this->cpf = $dados["cpf"];
    $this->banco = $dados["banco"];
    $this->tipo_conta = $dados["tipo_conta"];
    $this->agencia = $dados["agencia"];
    $this->conta = $dados["conta"];
    $this->valor_reais = $dados["valor_reais"];
    $this->valor_btc = $dados["valor_btc"];
    $this->email = $dados["email"];
    $this->create_at = $dados["create_at"];
    $this->ip = $dados["ip"];
  }

  public function getTable() {
      return "EnviarDinheiro";
  }

  public function getSequence() {
      return null;
  }

  public function getInstance() {
      return new EnviarDinheiro();
  }
}
?>
