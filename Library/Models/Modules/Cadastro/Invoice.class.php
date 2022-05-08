<?php

namespace Models\Modules\Cadastro;

class Invoice {

  // Lista de propriedades
  public $InvoiceId;
  public $Status;
  public $Currency;
  public $CurrencyTotal;
  public $DigitalCurrency;
  public $DigitalCurrencyAddress;
  public $DigitalCurrencyAmount;
  public $DigitalCurrencyFee;
  public $DateCreate;
  public $Param1;
  public $Param2;
  public $Param3;

  public function __construct($dados=null) {
    if (!is_null($dados)) {
        $this->exchangeArray($dados);
    }
  }

  public function exchangeArray($dados) {
    //Só atribuo os dados do array somente se eles existem
    // CREATE TABLE `pdv` (
    //   `InvoiceId` bigint(20) NOT NULL,
    //   `Status` varchar(0) DEFAULT NULL,
    //   `Currency` varchar(10) DEFAULT NULL,
    //   `CurrencyTotal` decimal(16,8) DEFAULT NULL,
    //   `DigitalCurrency` varchar(20) DEFAULT NULL,
    //   `DigitalCurrencyAddress` varchar(100) DEFAULT NULL,
    //   `DigitalCurrencyAmount` decimal(16,8) DEFAULT NULL,
    //   `DigitalCurrencyFee` decimal(16,8) DEFAULT NULL,
    //   `DateCreate` datetime DEFAULT NULL,
    //   `DigitalCurrencyAmountPaid` decimal(16,8) DEFAULT NULL,
    //   `DateDeposit` datetime DEFAULT NULL,
    //   `Param1` varchar(100) DEFAULT NULL,
    //   `Param2` varchar(100) DEFAULT NULL,
    //   `Param3` varchar(100) DEFAULT NULL,
    //   PRIMARY KEY (`InvoiceId`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    $this->InvoiceId = isset($dados['InvoiceId']) ? $dados['InvoiceId'] : null;
    $this->Status = isset($dados['Status']) ? $dados['Status'] : null;
    $this->Currency = isset($dados['Currency']) ? $dados['Currency'] : null;
    $this->CurrencyTotal = isset($dados['CurrencyTotal']) ? $dados['CurrencyTotal'] : null;
    $this->DigitalCurrency = isset($dados['DigitalCurrency']) ? $dados['DigitalCurrency'] : null;
    $this->DigitalCurrencyAddress = isset($dados['DigitalCurrencyAddress']) ? $dados['DigitalCurrencyAddress'] : null;
    $this->DigitalCurrencyAmount = isset($dados['']) ? $dados[''] : null;
    $this->DigitalCurrencyFee = isset($dados['DigitalCurrencyFee']) ? $dados['DigitalCurrencyFee'] : null;
    $this->DateCreate = isset($dados['DateCreate']) ? $dados['DateCreate'] : null;
    $this->DigitalCurrencyAmountPaid = isset($dados['DigitalCurrencyAmountPaid']) ? $dados['DigitalCurrencyAmountPaid'] : null;
    $this->DateDeposit = isset($dados['DateDeposit']) ? $dados['DateDeposit'] : null;
    $this->Param1 = isset($dados['Param1']) ? $dados['Param1'] : null;
    $this->Param2 = isset($dados['Param2']) ? $dados['Param2'] : null;
    $this->Param3 = isset($dados['Param3']) ? $dados['Param3'] : null;
    $this->confirmacaoenvio = isset($dados['confirmacaoenvio']) ? $dados['confirmacaoenvio'] : null;
    $this->email = isset($dados['email']) ? $dados['email'] : null;
    $this->cel = isset($dados['cel']) ? $dados['cel'] : null;


    //$this->id = isset($dados['id']) ? $dados['id'] : null;
    // Campo de busca:
    /*
    $this->id_cliente = isset($dados['id_cliente']) ? $dados['id_cliente'] : null;
    $this->total = isset($dados['total']) ? $dados['total'] : null;
    $this->redir = isset($dados['redir']) ? $dados['redir'] : null;
    $this->nemail = isset($dados['nemail']) ? $dados['nemail'] : null;
    $this->dc = isset($dados['dc']) ? $dados['dc'] : null;
    $this->customid = isset($dados['customid']) ? $dados['customid'] : null;
    */
    return;
  }

  public function getTable() {
      return "pdv";
  }

  public function getSequence() {
      return null;
  }

  public function getInstance() {
      return new Invoice();
  }
}
