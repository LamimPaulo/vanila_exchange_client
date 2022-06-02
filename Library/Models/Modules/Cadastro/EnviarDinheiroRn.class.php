<?php
namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class EnviarDinheiroRn {

  public $conexao = null;

  public function __construct($adapter = null) {
      if ($adapter == null) {
          $this->conexao = new GenericModel(\Dduo::conexao(), new EnviarDinheiro());
      } else {
          $this->conexao = new GenericModel($adapter, new EnviarDinheiro());
      }
  }

  public function salvar(EnviarDinheiro &$enviar) {
    try {
      $this->conexao->insert(Array(
        "titular" => $enviar->titular,
        "cpf" => $enviar->cpf,
        "banco" => $enviar->banco,
        "tipo_conta" => $enviar->tipo_conta,
        "agencia" => $enviar->agencia,
        "conta" => $enviar->conta,
        "valor_reais" => $enviar->valor_reais,
        "valor_btc" => $enviar->valor_btc,
        "email" => $enviar->email,
        "create_at" => $enviar->create_at,
        "ip" => $enviar->ip
      ));
    } catch(\Exception $ex) {
      echo 'Erro: '. $ex.message;
    }
  }
}
?>
