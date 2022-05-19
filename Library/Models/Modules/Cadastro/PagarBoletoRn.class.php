<?php
namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class PagarBoletoRn {

  public $conexao = null;

  public function __construct(\Io\BancoDados $adapter = null) {
      if ($adapter == null) {
        $this->conexao = new GenericModel(\Dduo::conexao(), new PagarBoleto());
      } else {
          $this->conexao = new GenericModel($adapter, new PagarBoleto());
      }
  }

  public function salvar(PagarBoleto &$boleto) {
    try {
      $this->conexao->insert(Array(
        "linha_digitavel" => $boleto->linha_digitavel,
        "banco" => $boleto->banco,
        "valor" => $boleto->valor,
        "vencimento" => $boleto->vencimento,
        "email" => $boleto->email,
        "aceite" => $boleto->aceite,
        "create_at" => $boleto->create_at,
        "ip" => $boleto->ip
        )
      );
    } catch(\Exception $ex) {
      echo 'Erro: '. $ex.message;
    }
  }
}
?>
