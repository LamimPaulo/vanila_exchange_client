<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class InvoicePdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new InvoicePdv());
        } else {
            $this->conexao = new GenericModel($adapter, new InvoicePdv());
        }
    }
    
    public function salvar(InvoicePdv &$invoicePdv, $sandbox = false) {
        try {
            $this->conexao->adapter->iniciar();
            
            if ($invoicePdv->id > 0) {

                $aux = new InvoicePdv(Array("id" => $invoicePdv->id));
                $this->conexao->carregar($aux);

                $invoicePdv->dataCriacao = $aux->dataCriacao;
                $invoicePdv->dataCancelamento = $aux->dataCancelamento;
                $invoicePdv->status = $aux->status;
                $invoicePdv->saldoRecebido = $aux->saldoRecebido;

            } else {

                $invoicePdv->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
                $invoicePdv->dataDeposito = null;
                $invoicePdv->dataCancelamento = null;
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_AGUARDANDO;
                $invoicePdv->saldoRecebido = 0;
            }

            if (empty($invoicePdv->email)) {
                //throw new \Exception("O email deve ser informaado");
            }

            $cel = str_replace(Array(")", "(", " ", "-"), "", $invoicePdv->celular);
            if (strlen($cel) != 10 && strlen($cel) != 11) {
                //throw new \Exception("Celular inválido");
            }

            if (!$invoicePdv->valorBrl > 0) {
                throw new \Exception("O valor em BRL deve ser maior que zero");
            }

            if (!$invoicePdv->valorBtc > 0) {
                throw new \Exception("O valor em BTC deve ser maior que zero");
            }

            if (!$invoicePdv->idMoeda > 0) {
                throw new \Exception("A moeda de pagamento deve ser informada");
            }

            if (!$invoicePdv->cotacaoMoedaBtc > 0) {
                throw new \Exception("A cotação em BRL da moeda digital deve ser informada");
            }

            $invoicePdv->enderecoCarteira = "";

            if ($invoicePdv->taxaBtc < 0) {
                throw new \Exception("A taxa de BTC deve ser informada");
            }

            if ($invoicePdv->id > 0) {
                throw new \Exception("A identificação do PDV deve ser informada");
            }

            
            if ($invoicePdv->cotacaoBtcBrl < 0) {
                $invoicePdv->cotacaoBtcBrl = null;
            }
            
            unset($invoicePdv->pontoPdv);
            unset($invoicePdv->moeda);

            $this->conexao->salvar($invoicePdv);
            
            if ($sandbox /*|| AMBIENTE === "desenvolvimento"*/) {
                $invoicePdv->enderecoCarteira  = substr(sha1(time()."SANDBOX-ADDRESS-NEWCASH".time()), 0, 32) . "SANDBOX";
            } else {
                $carteiraGeradaRn = new CarteiraGeradaRn();
                $carteiraGerada = $carteiraGeradaRn->getWallet($invoicePdv->idMoeda);
                $invoicePdv->enderecoCarteira = $carteiraGerada->address;
                //$invoicePdv->enderecoCarteira = \CoreBtc\Wallet::create("PDV INVOICE {$invoicePdv->id} ");
            }
            
            if (empty($invoicePdv->enderecoCarteira)) {
                throw new \Exception("Não foi possível cria o endereço de pagamento no momento. Por favor, tente novamente mais tarde.");
            }
            
            $this->conexao->update(
                    Array("endereco_carteira" => $invoicePdv->enderecoCarteira), 
                    Array("id" => $invoicePdv->id)
                );
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function getByEnderecoCarteira($endereco) {
        $result = $this->conexao->select(Array("endereco_carteira" => $endereco));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function novaInvoice(InvoicePdv &$invoicePdv, PontoPdv $pontoPdv, $moeda, $sandbox = false, $exchange = null) {
        
        try {
            
            if (strtolower($moeda) == "brl") {
                throw new \Exception("No momento a plataforma está aceitando somente invoices com crédito em BTC");
            }
            
            $this->conexao->adapter->iniciar();
            
            $moedaRn = new MoedaRn();
            $moeda = $moedaRn->getBySimbolo($moeda);
            
            if ($moeda == null) {
                throw new \Exception("Moeda inválida");
            }
            
            if (!$invoicePdv->valorBrl >= 10) { 
                throw new \Exception("O valor da invoice deve ser de no mínimo R$ 10,00");
            }
            
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $pontoPdvRn = new PontoPdvRn($this->conexao->adapter);
            $pontoPdvRn->carregar($pontoPdv, true, true);
            
            $cliente = new Cliente(Array("id" => $pontoPdv->estabelecimento->idCliente));
            $clienteRn = new ClienteRn($this->conexao->adapter);
            $clienteRn->conexao->carregar($cliente);
            
            $taxaTransferenciaInterna = $configuracao->taxaTransferenciaInternaBtc;
            $taxaNewcash = ($cliente->taxaInvoicesPdv);
            $taxaCliente = ($pontoPdv->comissaoPdv);
            
            
            
            
            
            /*$taxa = 0;
            $taxaBtc = 0;
            if ($invoicePdv->cotacaoBtcBrl <= 0 && $moeda->simbolo == "BRL") {
                $taxa = round($invoicePdv->valorBrl * ($configuracao->percentualVenda / 100), 2) + round($invoicePdv->valorBrl * ($configuracao->taxaSaque / 100), 2);
                $invoicePdv->valorBrl += $taxa;
            } else {
                $taxaBtc = $configuracao->taxaTransferenciaInternaBtc;
            }
            */
            
            
            $cotacaoBtc = 0;
            if ($invoicePdv->cotacaoBtcBrl > 0) {
                $cotacaoBtc = $invoicePdv->cotacaoBtcBrl;
            } else {
                if (empty($exchange)) {
                    throw new \Exception("Quando não informada a cotação é necessário informar uma Exchange para cotação do valor de venda");
                }
                $cotacaoBtc = \Utils\TickerUtils::getTickerByExchange($exchange, $moeda, "v");
            }
            
            $valorBtc = number_format(($invoicePdv->valorBrl / $cotacaoBtc), $moeda->casasDecimais, ".", "");
            
            if ($cliente->tipoTaxaInvoicePdv == "p") {
                $taxaNewcash = number_format(($taxaNewcash /100 * $valorBtc), 8, ".", "");
            }
            
            if ($pontoPdv->tipoComissaoPdv == "p") {
                $taxaCliente = number_format(($taxaCliente /100 * $valorBtc), 8, ".", "");
            }
            
            
            $taxaCobrada = number_format(($taxaTransferenciaInterna + $taxaNewcash + $taxaCliente), 8, ".", "");
            $valorBtc = number_format(($valorBtc + $taxaCobrada), 8, ".", "");
            
            $invoicePdv->cotacaoMoedaBtc = $cotacaoBtc;
            $invoicePdv->idMoeda = $moeda->id;
            $invoicePdv->idPontoPdv = $pontoPdv->id;
            $invoicePdv->taxaBtc = $taxaCobrada;
            $invoicePdv->valorBtc = $valorBtc;
            
            $this->salvar($invoicePdv, $sandbox);
            
            
            $this->carregar($invoicePdv, true, true, true);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex), $ex->getCode());
        }
        
    }
    
    
    public function carregar(InvoicePdv &$invoicePdv, $carregar = true, $carregarMoeda = true, $carregarPontoPdv = true) {
        if ($carregar) {
            $this->conexao->carregar($invoicePdv);
        }
        
        if ($carregarMoeda && $invoicePdv->idMoeda > 0) {
            $invoicePdv->moeda = new Moeda(Array("id" => $invoicePdv->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($invoicePdv->moeda);
        }
        
        if ($carregarPontoPdv && $invoicePdv->idPontoPdv > 0) {
            $invoicePdv->pontoPdv = new PontoPdv(Array("id" =>$invoicePdv->idPontoPdv));
            $pontoPdvRn = new PontoPdvRn();
            $pontoPdvRn->carregar($invoicePdv->pontoPdv, true, true);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarMoeda = true, $carregarPontoPdv = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $invoicePdv) {
            $this->carregar($invoicePdv, false, $carregarMoeda, $carregarPontoPdv);
            $lista[] = $invoicePdv;
        }
        
        return $lista;
    }
    
    public function cancelar(InvoicePdv &$invoicePdv) {
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $this->conexao->carregar($invoicePdv);
            } catch (\Exception $ex) {
                throw new \Exception("Invoice não localizada no sistema");
            }
            
            if ($invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_CANCELADO) {
                throw new \Exception("A Invoice já está cancelada no sistema");
            }
            
            if ($invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGO || $invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMAIS || $invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMENOS) {
                throw new \Exception("A Invoice consta como paga no sistema. ");
            }
            
            
            $this->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::STATUS_INVOICE_PDV_CANCELADO,
                        "data_cancelamento" => date("Y-m-d H:i:s")
                    ), 
                    Array(
                        "id" => $invoicePdv->id
                    )
                );
            
            $this->conexao->carregar($invoicePdv);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function callback($callback, $invoiceID) {
        $curl = curl_init();
        
        if (substr($callback, strlen($callback) - 1, 1) == "/") {
            $callback = substr($callback, 0, strlen($callback) - 1);
        }
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$callback}?invoice={$invoiceID}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "postman-token: c6bb8cd7-24fd-4ecf-d156-02ca98e75efe"
            ),
        ));
            
        $content = curl_exec($curl);
        curl_close($curl);
        
        return $content;
    }
    
    
    public function filtrar($chave, $email, $status, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        
        $where = Array();
        
        if (!empty($email)) {
            $where[] = " i.email = '{$email}' ";
        }
        
        if (!empty($status)) {
            $where[] = " i.status = '{$status}' ";
        }
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("A data inicial não pode ser maior que a data final");
            }
            $where[] = " i.data_criacao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO)} 00:00:00' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO)} 23:59:59' ";
            
        }
        $where[] = " (c.chave = '{$chave}' OR c.chave_homologacao = '{$chave}') ";
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT i.* FROM invoices_pdv i "
                . " INNER JOIN pontos_pdv p ON (i.id_ponto_pdv = p.id) "
                . " INNER JOIN chaves_pdv c ON (c.id_ponto_pdv = p.id) "
                . " {$where} "
                . " ORDER BY i.id ";
                //exit($query);
        $lista = Array();
        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $invoicePdv = new InvoicePdv($dados);
            $this->carregar($invoicePdv, false, true, true);
            $lista[] = $invoicePdv;
        }
        
        return $lista;
    }
    
    
    
    
    
    public function atualizarStatus(InvoicePdv &$invoicePdv, TransacaoPendenteBtc $transacaoPendenteBtc, $jsonTransacaoCore, $token = null) {
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $this->carregar($invoicePdv, TRUE, FALSE, TRUE);
            } catch (\Exception $ex) {
                throw new \Exception("Invoice não localizada no sistema");
            }
            
            $moeda = MoedaRn::get(2);
            
            $cotacaoBtc = \Utils\Constantes::COTACAO_BTC;
            
            $transacaoInvoicePdvRn = new TransacaoInvoicePdvRn($this->conexao->adapter);
            $transacaoInvoicePdv = new TransacaoInvoicePdv();
            $transacaoInvoicePdv->txid = $transacaoPendenteBtc->hash;
            $transacaoInvoicePdv->address = $transacaoPendenteBtc->enderecoBitcoin;
            $transacaoInvoicePdv->amount = $transacaoPendenteBtc->valor;
            $transacaoInvoicePdv->confirmations = (isset($jsonTransacaoCore->confirmations) ? $jsonTransacaoCore->confirmations : 0);
            $transacaoInvoicePdv->id = 0;
            $transacaoInvoicePdv->idInvoicePdv = $invoicePdv->id;
            $transacaoInvoicePdv->safe = (isset($jsonTransacaoCore->safe) ? ($jsonTransacaoCore->safe ? 1 : 0) : 0);
            $transacaoInvoicePdv->scriptPubKey = (isset($jsonTransacaoCore->scriptPubKey) ? $jsonTransacaoCore->scriptPubKey : "");
            $transacaoInvoicePdv->solvable = (isset($jsonTransacaoCore->solvable) ? ($jsonTransacaoCore->solvable ? 1 : 0) : 0);
            $transacaoInvoicePdv->spendable = (isset($jsonTransacaoCore->spendable) ? ($jsonTransacaoCore->spendable ? 1 : 0) : 0 );
            $transacaoInvoicePdv->vout = (isset($jsonTransacaoCore->vout) ? $jsonTransacaoCore->vout : 0);

            $transacaoInvoicePdvRn->salvar($transacaoInvoicePdv);


            $invoicePdv->dataDeposito = new \Utils\Data(date("d/m/Y"));
            
            $valorTotalRecebido = number_format(($transacaoPendenteBtc->valor + $invoicePdv->saldoRecebido), $moeda->casasDecimais, ".", "");
            
            if ($valorTotalRecebido == number_format($invoicePdv->valorBtc, $moeda->casasDecimais, ".", "") ) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGO;
            } else if ($valorTotalRecebido > $invoicePdv->valorBtc) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMAIS;
            } else if ($valorTotalRecebido < $invoicePdv->valorBtc) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMENOS;
            }
            
            $queryUpdate = " UPDATE invoices_pdv SET saldo_recebido = saldo_recebido + {$transacaoPendenteBtc->valor}, status = '{$invoicePdv->status}', data_deposito = '{$invoicePdv->dataDeposito->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";

            $this->conexao->adapter->query($queryUpdate)->execute();

            $this->carregar($invoicePdv, true, true, true);

            if ($invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGO || $invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMAIS) { 
                $configuracao = new Configuracao(Array("id" => 1));
                $configuracaoRn = new ConfiguracaoRn();
                $configuracaoRn->conexao->carregar($configuracao);

                $cliente = new Cliente(Array("id" => $invoicePdv->pontoPdv->estabelecimento->idCliente));
                $clienteRn = new ClienteRn($this->conexao->adapter);
                $clienteRn->conexao->carregar($cliente);

                $taxaTransferenciaInterna = $configuracao->taxaTransferenciaInternaBtc;
                $taxaNewcash = ($cliente->taxaInvoicesPdv);
                $taxaCliente = ($invoicePdv->pontoPdv->comissaoPdv);
                
                if ($cliente->tipoTaxaInvoicePdv == "p") {
                    $taxaNewcash = number_format(($taxaNewcash /100 * $invoicePdv->saldoRecebido), 8, ".", "");
                }

                if ($invoicePdv->pontoPdv->tipoComissaoPdv == "p") {
                    $taxaCliente = number_format(($taxaCliente /100 * $invoicePdv->saldoRecebido), 8, ".", "");
                }
                
                $valorTotalTaxas = number_format(($taxaNewcash+$taxaCliente+$taxaTransferenciaInterna), 8, ".", "");
                
                        
                $valor = number_format($invoicePdv->saldoRecebido-$valorTotalTaxas, 8, ".", "");

                
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                
                if (!is_numeric(strpos($invoicePdv->enderecoCarteira, "SANDBOX"))) {
                    if (/*$invoicePdv->cotacaoBtcBrl <= 0 && */ $invoicePdv->moeda->simbolo == "BRL") {
                        
                        
                        $valor = number_format(($transacaoPendenteBtc->valor * $cotacaoBtc), 2, ".", "");

                        $contaCorrenteReais = new ContaCorrenteReais();
                        $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteReais->descricao = "Recebimento Invoice No. {$invoicePdv->id} ";
                        $contaCorrenteReais->idCliente = $invoicePdv->pontoPdv->estabelecimento->idCliente;
                        $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteReais->transferencia = 0;
                        $contaCorrenteReais->valor = number_format(($valor), 2, ".", "");
                        $contaCorrenteReais->valorTaxa = 0;

                        $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
                        $contaCorrenteReaisRn->salvar($contaCorrenteReais, $token);

                    } else {
                        
                       
                        
                        if ($valor > 0) {
                            $contaCorrenteBtc = new ContaCorrenteBtc();
                            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                            $contaCorrenteBtc->descricao = "Recebimento Invoice No. {$invoicePdv->id} ";
                            $contaCorrenteBtc->idCliente = $invoicePdv->pontoPdv->estabelecimento->idCliente;
                            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                            $contaCorrenteBtc->transferencia = 0;
                            $contaCorrenteBtc->valor = number_format($valor, $moeda->casasDecimais, ".", "");
                            $contaCorrenteBtc->valorTaxa = 0;
                            $contaCorrenteBtc->idMoeda = 2;
                            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                            $contaCorrenteBtc->executada = 1;
                            $contaCorrenteBtc->autorizada = 1;
                            $contaCorrenteBtc->enderecoBitcoin = "";
                            $contaCorrenteBtc->enderecoEnvio = "";
                            $contaCorrenteBtc->hash = "";
                            $contaCorrenteBtc->orderBook = 0;
                            $contaCorrenteBtc->origem = 11;
                            $contaCorrenteBtc->seed = "";

                            $contaCorrenteBtcRn->salvar($contaCorrenteBtc, $token);
                            
                            $invoiceHasContaCorrente = new InvoiceHasContaCorrente();
                            $invoiceHasContaCorrente->idContaCorrenteBtc = $contaCorrenteBtc->id;
                            $invoiceHasContaCorrente->idInvoicePdv = $invoicePdv->id;
                            $invoiceHasContaCorrente->idCliente = $cliente->id;
                            $invoiceHasContaCorrente->tipo = \Utils\Constantes::ENTRADA;
                            $invoiceHasContaCorrenteRn = new InvoiceHasContaCorrenteRn($this->conexao->adapter);
                            $invoiceHasContaCorrenteRn->salvar($invoiceHasContaCorrente);
                            
                            if ($invoicePdv->pontoPdv->habilitarSaqueAutomatico > 0 && !empty($invoicePdv->pontoPdv->walletSaqueAutomatico)) {
                                
                                $contaCorrenteBtcSaque = new ContaCorrenteBtc();
                                $contaCorrenteBtcSaque->data = new \Utils\Data(date("d/m/Y H:i:s"));
                                $contaCorrenteBtcSaque->descricao = "Saque Automatico Invoice No. {$invoicePdv->id} ";
                                $contaCorrenteBtcSaque->idCliente = $invoicePdv->pontoPdv->estabelecimento->idCliente;
                                $contaCorrenteBtcSaque->tipo = \Utils\Constantes::SAIDA;
                                $contaCorrenteBtcSaque->transferencia = 0;
                                $contaCorrenteBtcSaque->valor = number_format($valor, $moeda->casasDecimais, ".", "");
                                $contaCorrenteBtcSaque->valorTaxa = 0;
                                $contaCorrenteBtcSaque->idMoeda = 2;
                                $contaCorrenteBtcSaque->direcao = \Utils\Constantes::TRANF_EXTERNA;
                                $contaCorrenteBtcSaque->executada = 0;
                                $contaCorrenteBtcSaque->autorizada = 0;
                                $contaCorrenteBtcSaque->enderecoBitcoin = $invoicePdv->pontoPdv->walletSaqueAutomatico;
                                $contaCorrenteBtcSaque->enderecoEnvio = "";
                                $contaCorrenteBtcSaque->hash = "";
                                $contaCorrenteBtcSaque->orderBook = 0;
                                $contaCorrenteBtcSaque->origem = 11;
                                $contaCorrenteBtcSaque->seed = "";

                                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                                $contaCorrenteBtcRn->salvar($contaCorrenteBtcSaque, $token);
                                
                                $invoiceHasContaCorrenteSaque = new InvoiceHasContaCorrente();
                                $invoiceHasContaCorrenteSaque->idContaCorrenteBtc = $contaCorrenteBtcSaque->id;
                                $invoiceHasContaCorrenteSaque->idInvoicePdv = $invoicePdv->id;
                                $invoiceHasContaCorrenteSaque->idCliente = $cliente->id;
                                $invoiceHasContaCorrenteSaque->tipo = \Utils\Constantes::SAIDA;
                                $invoiceHasContaCorrenteRn->salvar($invoiceHasContaCorrenteSaque);
                                
                            }
                            
                        }
                    }

                    
                    if ($taxaCliente > 0) {
                        $contaCorrenteBtcTaxaCliente = new ContaCorrenteBtc();
                        $contaCorrenteBtcTaxaCliente->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtcTaxaCliente->descricao = "Comissão Invoice No. {$invoicePdv->id} ";
                        $contaCorrenteBtcTaxaCliente->idCliente = $invoicePdv->pontoPdv->estabelecimento->idCliente;
                        $contaCorrenteBtcTaxaCliente->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteBtcTaxaCliente->transferencia = 0;
                        $contaCorrenteBtcTaxaCliente->valor = number_format($taxaCliente, $moeda->casasDecimais, ".", "");
                        $contaCorrenteBtcTaxaCliente->valorTaxa = 0;
                        $contaCorrenteBtcTaxaCliente->idMoeda = 2;
                        $contaCorrenteBtcTaxaCliente->direcao = \Utils\Constantes::TRANF_INTERNA;
                        $contaCorrenteBtcTaxaCliente->executada = 1;
                        $contaCorrenteBtcTaxaCliente->autorizada = 1;
                        $contaCorrenteBtcTaxaCliente->enderecoBitcoin = "";
                        $contaCorrenteBtcTaxaCliente->enderecoEnvio = "";
                        $contaCorrenteBtcTaxaCliente->hash = "";
                        $contaCorrenteBtcTaxaCliente->orderBook = 0;
                        $contaCorrenteBtcTaxaCliente->origem = 11;
                        $contaCorrenteBtcTaxaCliente->seed = "";

                        $contaCorrenteBtcRn->salvar($contaCorrenteBtcTaxaCliente, $token);
                    }
                    
                    $taxaTotalEmpresa = number_format(($taxaNewcash + $taxaTransferenciaInterna), 8, ".", "");
                    
                    if ($taxaTotalEmpresa > 0) {
                        $contaCorrenteBtcEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                        $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
                        $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtcEmpresa->descricao = "Taxa Recebimento Invoice {$invoicePdv->id}";
                        $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteBtcEmpresa->transferencia = 0;
                        $contaCorrenteBtcEmpresa->idMoeda = 2;
                        $contaCorrenteBtcEmpresa->valor = $taxaTotalEmpresa;
                        $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa, $token);
                    }
                }                           
                    
            }
            
            try {
                if (!empty($invoicePdv->callback)) {
                    $this->callback($invoicePdv->callback, $invoicePdv->id);
                } else if (strpos($invoicePdv->enderecoCarteira, "SANDBOX") >= 0) {
                    if (!empty($invoicePdv->pontoPdv->callbackHomologacao)) {
                        $this->callback($invoicePdv->pontoPdv->callbackHomologacao, $invoicePdv->id);
                    }
                } else if (!empty($invoicePdv->pontoPdv->callbackProducao)) {
                    $this->callback($invoicePdv->pontoPdv->callbackProducao, $invoicePdv->id);
                }
            } catch (\Exception $ex) {
                
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    public function adicionarFundosSandbox($volume, $carteira) {
        try {
            $this->conexao->adapter->iniciar();
            
            if (!strpos($carteira, "SANDBOX")) {
                throw new \Exception("Só é permitido inserir saldo diretamente em  invoices SANDBOX");
            }

            if (!$volume > 0) {
                throw new \Exception("O volume precisa ser maior que zero");
            }
            
            $invoicePdv = $this->getByEnderecoCarteira($carteira);

            $invoicePdv->saldoRecebido += $volume;
            $invoicePdv->dataDeposito = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $moeda = MoedaRn::get(2);
            
            if ($invoicePdv->saldoRecebido == number_format($invoicePdv->valorBtc, $moeda->casasDecimais, ".", "") ) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGO;
            } else if ($invoicePdv->saldoRecebido > $invoicePdv->valorBtc) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMAIS;
            } else if ($invoicePdv->saldoRecebido < $invoicePdv->valorBtc) {
                $invoicePdv->status = \Utils\Constantes::STATUS_INVOICE_PDV_PAGOMENOS;
            }
            
            $this->conexao->update(
                    Array(
                        "saldo_recebido" => number_format($invoicePdv->saldoRecebido, $moeda->casasDecimais, ".", ""), 
                        "data_deposito" => $invoicePdv->dataDeposito->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                    ), 
                    Array(
                        "id" => $invoicePdv->id
                    ));
            
            //$carteira = str_replace("SANDBOX", "", $carteira);
            $retornoCallback = "";
            try {
                $pontoPdv = new PontoPdv(Array("id" => $invoicePdv->idPontoPdv));
                $pontoPdvRn = new PontoPdvRn();
                $pontoPdvRn->conexao->carregar($pontoPdv);

                    if (!empty($invoicePdv->callback)) {
                        $retornoCallback = $this->callback($invoicePdv->callback, $invoicePdv->id);
                    } else if (strpos($carteira, "SANDBOX") >= 0) {
                        if (!empty($pontoPdv->callbackHomologacao)) {
                            $retornoCallback = $this->callback($pontoPdv->callbackHomologacao, $invoicePdv->id);
                        }
                    }else if (!empty($pontoPdv->callbackProducao)) {
                        $retornoCallback = $this->callback($pontoPdv->callbackProducao, $invoicePdv->id);
                    }

            } catch (\Exception $ex) {
                //print \Utils\Excecao::mensagem($ex);
            }
            
            
            
            $this->conexao->adapter->finalizar();
            return $retornoCallback;
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
}

?>