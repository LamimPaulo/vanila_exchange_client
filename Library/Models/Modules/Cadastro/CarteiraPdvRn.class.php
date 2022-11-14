<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade CarteiraPdv
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class CarteiraPdvRn {
    
    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new CarteiraPdv());
        } else {
            $this->conexao = new GenericModel($adapter, new CarteiraPdv());
        }
    }
    
    
    public function salvar(CarteiraPdv &$carteiraPdv) {
        
        $carteiraPdv->dataAtualizacao = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if ($carteiraPdv->id > 0) {
            $aux = new CarteiraPdv(Array("id" => $carteiraPdv->id));
            $this->conexao->carregar($aux);
            
            $carteiraPdv->dataCriacao = $aux->dataCriacao;
            $carteiraPdv->idReferenciaCliente = $aux->idReferenciaCliente;
            $carteiraPdv->sacado = $aux->sacado;
            $carteiraPdv->transferido = $aux->transferido;
            
        } else {
            $carteiraPdv->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $carteiraPdv->sacado = 0;
            $carteiraPdv->transferido = 0;
        }
        
        if (!$carteiraPdv->idReferenciaCliente > 0) {
            throw new \Exception("É necessário informar a identificação do cliente");
        }
        
        if (!$carteiraPdv->idMoeda > 0) {
            throw new \Exception("É necessário informar a moeda");
        }
        
        if (!$carteiraPdv->saldoBtc > 0) {
            $carteiraPdv->saldoBtc = 0;
        }
        
        if (!$carteiraPdv->saldoGastoBtc > 0) {
            $carteiraPdv->saldoGastoBtc = 0;
        }
        
        unset($carteiraPdv->referenciaCliente);
        unset($carteiraPdv->moeda);
        $this->conexao->salvar($carteiraPdv);
    }
    
    public function carregar(CarteiraPdv &$carteiraPdv, $carregar = true, $carregarReferenciaCliente = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($carteiraPdv);
        }
        
        if ($carregarReferenciaCliente && $carteiraPdv->idReferenciaCliente > 0) {
            $carteiraPdv->referenciaCliente = new ReferenciaCliente(Array("id" => $carteiraPdv->idReferenciaCliente));
            $referenciaClienteRn = new ReferenciaClienteRn();
            $referenciaClienteRn->conexao->carregar($carteiraPdv->referenciaCliente);
        }
        
        if ($carregarMoeda && $carteiraPdv->idMoeda > 0) {
            $carteiraPdv->moeda = new Moeda(Array("id" => $carteiraPdv->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($carteiraPdv->moeda);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarReferenciaCliente = true, $carregarMoeda = true) {
        
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $carteiraPdv) {
            $this->carregar($carteiraPdv, false, $carregarReferenciaCliente, $carregarMoeda);
            $lista[] = $carteiraPdv;
        }
        return $lista;
    }
    
    
    public function getByEnderecoCarteira($endereco) {
        $result = $this->conexao->select(Array("endereco_carteira" => $endereco));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    public function atualizarSaldo(CarteiraPdv $carteiraPdv, $valor) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $this->conexao->carregar($carteiraPdv);
            } catch (\Exception $ex) {
                throw new \Exception("Carteira não localizada no sistema");
            }

            $this->conexao->update(
                    Array(
                        "saldo_btc" => $valor,
                        "saldo_disponivel_btc" => $valor
                    ), 
                    Array(
                       "id" => $carteiraPdv->id 
                    )
                );
            
            
            
            // salva o registro
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            $historicoTransacaoReferencia = new HistoricoTransacaoReferencia();
            $historicoTransacaoReferencia->idReferenciaCliente = $carteiraPdv->idReferenciaCliente;
            $historicoTransacaoReferencia->idMoeda = $carteiraPdv->idMoeda;
            $historicoTransacaoReferencia->tipo = \Utils\Constantes::ATUALIZACAO;
            $historicoTransacaoReferencia->valor = $valor;
            $historicoTransacaoReferenciaRn->salvar($historicoTransacaoReferencia);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    
    public function sacar(ReferenciaCliente $referenciaCliente, Moeda $moeda, $valor, $token = null) {
        
        try {
            $this->conexao->adapter->iniciar();
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida");
            }
            
            if (!$referenciaCliente->id > 0) {
                throw new \Exception("A identificação do cliente deve ser informada");
            }
            
            $referenciaClienteRn = new ReferenciaClienteRn($this->conexao->adapter);
            $referenciaClienteRn->carregar($referenciaCliente, true, true);
            
            $cliente = new Cliente(Array("id" => $referenciaCliente->estabelecimento->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            if ($cliente->statusResgatePdv < 1) {
                throw new \Exception("O resgate de carteiras remotas está temporariamente suspenso para sua conta");
            }
            
            $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            
            // seleciona os registros que ainda nao foram consumidos
            $query = " SELECT (saldo_btc - saldo_gasto_btc) AS saldo, id FROM  carteiras_pdv WHERE id_referencia_cliente = {$referenciaCliente->id} AND id_moeda = {$moeda->id} ";
            if (AMBIENTE == "producao") { 
                $query .= "AND endereco_carteira NOT LIKE '%SANDBOX%' ";
            }
            $query .= "having saldo > 0; ";
            
            $result = $this->conexao->adapter->query($query)->execute();
            
            $saldo = 0;
            
            $valorRestante = $valor;
            
            $listaIds = Array(); // array que vai armazenar os ids para conferencia 
            $listaCarteiras = Array(); // lista de carteiras com o saldo atualizado de cada uma 
            
            ///$taxaCobrada = 0;
            foreach ($result as $carteiraBtc) {
                $saldo += $carteiraBtc["saldo"]; // é feita a soma dos saldos
                $listaIds[] = $carteiraBtc["id"]; // guardo o id
                
                if ($carteiraBtc["saldo"] < $valorRestante) { // se o valor da carteira é inferior ao valor restante 
                    $valorRestante = $valorRestante - $carteiraBtc["saldo"];
                    $v = 0;
                    $saldoSacado = $carteiraBtc["saldo"];
                    
                    //$taxaCobrada += $taxa;
                } else { // o valor contido na carteira é superior ao valor restante de saque
                    $v = $carteiraBtc["saldo"] - $valorRestante;
                    $saldoSacado = $valorRestante;
                    $valorRestante = 0;
                    
                }
                
                // armazeno a carteira de volta atualizada
                $listaCarteiras[] = Array(
                    "id" => $carteiraBtc["id"],
                    "saldo" => number_format($v, $moeda->casasDecimais, ".", ""),
                    "saldoSacado" => number_format($saldoSacado, $moeda->casasDecimais, ".", "")
                );
            }
            
            if ($valorRestante > 0) {
                throw new \Exception("Saldo insuficiente");
            }
            
            // primeira verificação de disponibilidade de saldo
            if ($saldo < $valor) {
                throw new \Exception("Saldo insuficiente");
            }
            
            // segunda verificação para se ter certeza que os registros do array ainda estão consistentes no banco de dados
            $queryVerificacaoSaldo = " SELECT SUM((saldo_btc - saldo_gasto_btc)) AS saldo FROM carteiras_pdv WHERE id IN (". implode(",", $listaIds).") AND id_moeda = {$moeda->id}; ";
            $resultVerificacaoSaldo = $this->conexao->adapter->query($queryVerificacaoSaldo)->execute();
            
            $saldoVerificacao = 0;
            if (sizeof($resultVerificacaoSaldo) > 0) {
                $dadosVerificacaoSaldo = $resultVerificacaoSaldo->current();
                $saldoVerificacao = (isset($dadosVerificacaoSaldo["saldo"]) ? $dadosVerificacaoSaldo["saldo"] : 0);
            }
            
            if ($valor > $saldoVerificacao) {
                throw new \Exception("Saldo insuficiente");
            }
            
            // atualização das carteiras
            
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            
            foreach ($listaCarteiras as $carteira) {
                
                $vSacado = number_format($carteira["saldoSacado"], $moeda->casasDecimais, ".", "");
                $isSacado = ($carteira["saldo"] > 0 ? "0" : "1");
                $queryUpdate = " UPDATE carteiras_pdv SET saldo_gasto_btc = saldo_gasto_btc + {$vSacado}, sacado = {$isSacado} WHERE id = {$carteira["id"]}; ";
                $this->conexao->adapter->query($queryUpdate)->execute();
                
                $historicoTransacaoReferencia = new HistoricoTransacaoReferencia();
                $historicoTransacaoReferencia->idReferenciaCliente = $referenciaCliente->id;
                $historicoTransacaoReferencia->idMoeda = $moeda->id;
                $historicoTransacaoReferencia->idCarteiraPdv = $carteira["id"];
                $historicoTransacaoReferencia->tipo = \Utils\Constantes::SAQUE;
                $historicoTransacaoReferencia->valor = $carteira["saldoSacado"];
                $historicoTransacaoReferenciaRn->salvar($historicoTransacaoReferencia);
            }
            
            
            if ($valor > 0) {
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                $contaCorrenteBtc = new ContaCorrenteBtc();
                $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->descricao = "Resgate de carteiras da referencia {$referenciaCliente->referencia}";
                $contaCorrenteBtc->direcao = "I";
                $contaCorrenteBtc->enderecoBitcoin = null;
                $contaCorrenteBtc->hash = null;
                $contaCorrenteBtc->executada = 1;
                $contaCorrenteBtc->idMoeda = $moeda->id;
                $contaCorrenteBtc->idCliente = $cliente->id;
                $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteBtc->transferencia = 0;
                $contaCorrenteBtc->valor = number_format($valor, $moeda->casasDecimais, ".", "");
                $contaCorrenteBtc->valorTaxa = 0;

                $contaCorrenteBtcRn->salvar($contaCorrenteBtc, $token);
            
            
            
                if ($referenciaCliente->estabelecimento->habilitarSaqueAutomatico && !(empty($referenciaCliente->estabelecimento->walletSaqueAutomatico))) {

                    $taxaTransferenciaRemota = $cliente->taxaTransferenciaRemota;
                    $taxaTransferenciaEstabelecimento = $referenciaCliente->estabelecimento->comissaoEstabelecimento;

                    if ($valor > ($taxaTransferenciaRemota + $taxaTransferenciaEstabelecimento)) { 
                        $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
                        $contaCorrenteBtcSaque = new ContaCorrenteBtc();
                        $contaCorrenteBtcSaque->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtcSaque->descricao = "Saque automatico estabelecimento {$referenciaCliente->referencia}";
                        $contaCorrenteBtcSaque->direcao = "I";
                        $contaCorrenteBtcSaque->enderecoBitcoin = $referenciaCliente->estabelecimento->walletSaqueAutomatico;
                        $contaCorrenteBtcSaque->hash = null;
                        $contaCorrenteBtcSaque->executada = 0;
                        $contaCorrenteBtcSaque->autorizada = 0;
                        $contaCorrenteBtcSaque->idMoeda = $moeda->id;
                        $contaCorrenteBtcSaque->idCliente = $cliente->id;
                        $contaCorrenteBtcSaque->tipo = \Utils\Constantes::SAIDA;
                        $contaCorrenteBtcSaque->transferencia = 1;
                        $contaCorrenteBtcSaque->valor = $valor;
                        $contaCorrenteBtcSaque->valorTaxa = number_format(($taxaTransferenciaRemota + $taxaTransferenciaEstabelecimento), 8, ".", "");

                        $contaCorrenteBtcRn->salvar($contaCorrenteBtcSaque, $token);

                        if ($taxaTransferenciaEstabelecimento > 0) {
                            $contaCorrenteBtcTaxaEstabelecimento = new ContaCorrenteBtc();
                            $contaCorrenteBtcTaxaEstabelecimento->data = new \Utils\Data(date("d/m/Y H:i:s"));
                            $contaCorrenteBtcTaxaEstabelecimento->descricao = "Comissão de saque automatico estabelecimento {$referenciaCliente->referencia}";
                            $contaCorrenteBtcTaxaEstabelecimento->direcao = "I";
                            $contaCorrenteBtcTaxaEstabelecimento->enderecoBitcoin = "";
                            $contaCorrenteBtcTaxaEstabelecimento->hash = null;
                            $contaCorrenteBtcTaxaEstabelecimento->executada = 1;
                            $contaCorrenteBtcTaxaEstabelecimento->autorizada = 1;
                            $contaCorrenteBtcTaxaEstabelecimento->idMoeda = $moeda->id;
                            $contaCorrenteBtcTaxaEstabelecimento->idCliente = $cliente->id;
                            $contaCorrenteBtcTaxaEstabelecimento->tipo = \Utils\Constantes::ENTRADA;
                            $contaCorrenteBtcTaxaEstabelecimento->transferencia = 0;
                            $contaCorrenteBtcTaxaEstabelecimento->valor = $taxaTransferenciaEstabelecimento;
                            $contaCorrenteBtcTaxaEstabelecimento->valorTaxa = 0;

                            $contaCorrenteBtcRn->salvar($contaCorrenteBtcTaxaEstabelecimento, $token);
                        }

                        if ($taxaTransferenciaRemota > 0) {

                            $contaCorrenteBtcEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
                            $contaCorrenteBtcEmpresa->id = 0;
                            $contaCorrenteBtcEmpresa->descricao = "Taxa de saque automatico do estabelecimento: REF {$referenciaCliente->referencia}";
                            $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
                            $contaCorrenteBtcEmpresa->transferencia = 0;
                            $contaCorrenteBtcEmpresa->idMoeda = $moeda->id;
                            $contaCorrenteBtcEmpresa->valor= number_format($taxaTransferenciaRemota, $moeda->casasDecimais, ".", "");
                            $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                            $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa, $token);

                        }

                    }
                }
            }
            $this->conexao->adapter->finalizar();
            return $contaCorrenteBtc;
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    
    public function criarCarteira(ReferenciaCliente $referenciaCliente, \Models\Modules\Cadastro\Estabelecimento $estabelecimento, Moeda $moeda, $parametroUm = null, $parametrosDois = null, $parametrosTres = null, $sandbox = false) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            $referenciaClienteRn = new ReferenciaClienteRn($this->conexao->adapter);
            $referenciaCliente = $referenciaClienteRn->getOrCreate($referenciaCliente->referencia, $estabelecimento->chave);
            
            $carteiraPdv = new CarteiraPdv();
            $carteiraPdv->id = null;
            $carteiraPdv->enderecoCarteira = null;
            $carteiraPdv->idReferenciaCliente = $referenciaCliente->id;
            $carteiraPdv->idMoeda = $moeda->id;
            $carteiraPdv->sacado = 0;
            $carteiraPdv->confirmado = 0;
            $carteiraPdv->saldoBtc = 0;
            $carteiraPdv->saldoGastoBtc = 0;
            $carteiraPdv->parametroUm = $parametroUm;
            $carteiraPdv->parametroDois = $parametrosDois;
            $carteiraPdv->parametroTres = $parametrosTres;
            
            $this->salvar($carteiraPdv);
            
            $walletName = "Wallet {$carteiraPdv->id} Ref {$referenciaCliente->referencia}";
            
            if ($sandbox) {
                $endereco = substr(sha1(time()."SANDBOX-ADDRESS-NEWCASH".time()), 0, 32) . "SANDBOX";
            } else {
                $carteiraGeradaRn = new CarteiraGeradaRn();
                $carteiraGerada = $carteiraGeradaRn->getWallet($moeda->id);
                $endereco = $carteiraGerada->address;
                // $endereco = \CoreBtc\Wallet::create($walletName);
            }
            
            $carteiraPdv->enderecoCarteira = $endereco;
            
            if (empty($carteiraPdv->enderecoCarteira)) {
                throw new \Exception("Não foi possível criar a carteira neste momento. Por favor, tente novamente mais tarde.");
            }
            
            $this->conexao->update(
                    Array(
                        "endereco_carteira" => $endereco
                    ), 
                    Array(
                        "id" => $carteiraPdv->id
                    )
                );
            
            $this->conexao->adapter->finalizar();
            
            return $carteiraPdv;
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        return null;
    }
    
    
    public function validarAcessoCliente(CarteiraPdv $carteiraPdv, $referencia) {
        if (!$carteiraPdv->id > 0) {
            throw new \Exception("É necessário informar o id da carteira");
        }
        if (empty($referencia)) {
            throw new \Exception("É necessário informar a referência");
        }
        $query = " SELECT COUNT(*) AS qtd FROM carteiras_pdv cp INNER JOIN referencias_clientes rc ON (cp.id_referencia_cliente = rc.id) WHERE rc.referencia = '{$referencia}' AND cp.id = {$carteiraPdv->id}; ";
        $result = $this->conexao->adapter->query($query)->execute();
        if (sizeof($result) > 0) {
            $d = $result->current();
            if (!$d["qtd"] > 0) {
                throw new \Exception("Você não tem permissão para acessar essa carteira");
            }
        } else {
            throw new \Exception("Você não tem permissão para acessar essa carteira");
        }
    }
    
    
    public function validarAcessoEstabelecimento(CarteiraPdv $carteiraPdv, $referencia, $chaveEstabelecimento) {
        if (!$carteiraPdv->id > 0) {
            throw new \Exception("É necessário informar o id da carteira");
        }
        if (empty($referencia)) {
            throw new \Exception("É necessário informar a referência");
        }
        if (empty($chaveEstabelecimento)) {
            throw new \Exception("É necessário informar a chave do estabelecimento");
        }
        $query = " SELECT COUNT(*) AS qtd "
                . " FROM carteiras_pdv cp "
                . " INNER JOIN referencias_clientes rc ON (cp.id_referencia_cliente = rc.id) "
                . " INNER JOIN estabelecimentos e ON (rc.id_estabelecimento = e.id) "
                . " WHERE rc.referencia = '{$referencia}' AND cp.id = {$carteiraPdv->id} AND (e.chave = '{$chaveEstabelecimento}' OR e.chave_sandbox = '{$chaveEstabelecimento}'); ";
        $result = $this->conexao->adapter->query($query)->execute();
        if (sizeof($result) > 0) {
            $d = $result->current();
            if (!$d["qtd"] > 0) {
                throw new \Exception("Você não tem permissão para acessar essa carteira");
            }
        } else {
            throw new \Exception("Você não tem permissão para acessar essa carteira");
        }
    }
    
    /*
    public function atualizarCarteira(CarteiraPdv &$carteiraPdv) {
        try {
            $this->conexao->adapter->iniciar();
            $moeda = MoedaRn::get(2);
            try {
                $this->conexao->carregar($carteiraPdv);
            } catch (\Exception $ex) {
                throw new \Exception("Carteira não localizada no sistema");
            }
            
            if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                $result = Array();
            } else {
                $result = \CoreBtc\Wallet::get($carteiraPdv->enderecoCarteira);
            }
            
            
            if (sizeof($result) > 0) {
                $saldo = 0;
                foreach ($result as $transacao) {

                    $transacaoCarteiraPdvRn = new TransacaoCarteiraPdvRn($this->conexao->adapter);
                    
                    
                    if ($transacaoCarteiraPdvRn->getByTxId($transacao->txid) == null) {
                        $transacaoCarteiraPdv = new TransacaoCarteiraPdv();
                        $transacaoCarteiraPdv->txid = $transacao->txid;
                        $transacaoCarteiraPdv->address = $transacao->address;
                        $transacaoCarteiraPdv->amount = $transacao->amount;
                        $transacaoCarteiraPdv->confirmacoes = $transacao->confirmations;
                        $transacaoCarteiraPdv->id = 0;
                        $transacaoCarteiraPdv->idCarteiraPdv = $carteiraPdv->id;
                        $transacaoCarteiraPdv->safe = ($transacao->safe ? 1 : 0);
                        $transacaoCarteiraPdv->scriptPubKey = $transacao->scriptPubKey;
                        $transacaoCarteiraPdv->solvable = ($transacao->solvable ? 1 : 0);
                        $transacaoCarteiraPdv->spendable = ($transacao->spendable ? 1 : 0);
                        $transacaoCarteiraPdv->vout = $transacao->vout;
                        
                        $transacaoCarteiraPdvRn->salvar($transacaoCarteiraPdv);
                        
                        $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
                        $historicoTransacaoReferencia = new HistoricoTransacaoReferencia();
                        $historicoTransacaoReferencia->id = 0;
                        $historicoTransacaoReferencia->idReferenciaCliente = $carteiraPdv->idReferenciaCliente;
                        $historicoTransacaoReferencia->idCarteiraPdv = $carteiraPdv->id;
                        $historicoTransacaoReferencia->tipo = \Utils\Constantes::DEPOSITO;
                        $historicoTransacaoReferencia->valor = number_format($transacao->amount, $moeda->casasDecimais, ".", "");
                        $historicoTransacaoReferenciaRn->salvar($historicoTransacaoReferencia);
                        
                        
                        $saldo += number_format($moeda->casasDecimais, $moeda->casasDecimais, ".", "");
                    }


                }

                $carteiraPdv->confirmado = ($saldo > 0 ? "1" : "0");
                $saldo = number_format($saldo, $moeda->casasDecimais, ".", "");
                
                if ($saldo > 0) {
                    $queryUpdate = " UPDATE carteiras_pdv SET saldo_btc = saldo_btc + {$saldo}, confirmado = {$carteiraPdv->confirmado}";
                    $this->conexao->adapter->query($queryUpdate)->execute();
                    
                    try {
                        $referenciaCliente = new ReferenciaCliente(Array("id" => $carteiraPdv->idReferenciaCliente));
                        $referenciaClienteRn = new ReferenciaClienteRn();
                        $referenciaClienteRn->conexao->carregar($referenciaCliente);
                        
                        $estabelecimento = new Estabelecimento(Array("id" => $referenciaCliente->idEstabelecimento));
                        $estabelecimentoRn = new EstabelecimentoRn();
                        $estabelecimentoRn->conexao->carregar($estabelecimento);
                        
                        if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                            if (!empty($estabelecimento->callbackHomologacao)) {
                                $this->callback($estabelecimento->callbackHomologacao, $carteiraPdv->id);
                            }
                        } else {
                            if (!empty($estabelecimento->callbackProducao)) {
                                $this->callback($estabelecimento->callbackProducao, $carteiraPdv->id);
                            }
                        }
                        
                    } catch (\Exception $ex) {

                    }
                }
                
            }
                
            $this->conexao->carregar($carteiraPdv);    
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    */
    
    public function callback($callback, $walletID, $sigla, $manual = false) {
        //$callback = "http://bitshek-test.herokuapp.com/callback";
        $curl = curl_init();
        
        if (substr($callback, strlen($callback) - 1, 1) == "/") {
            $callback = substr($callback, 0, strlen($callback) - 1);
        }
        
        $url = "{$callback}?codigo={$walletID}&coin={$sigla}";
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache"
            ),
        ));
        
        $result = curl_exec($curl);
        $httpResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $bodyResponse = $result;
        LogCallbackCarteiraPdvRn::registrarLog($httpResponse, $bodyResponse, $walletID, $url, ($manual ? 1 : 0));
        
        //exit($result);
        curl_close($curl);
        
        return $result;
    }
    
    
    public function filtrarApi($referencia, $chaveEstabelecimento, $idMoeda = 0) {
        if (empty($chaveEstabelecimento)) {
            throw new \Exception("É necessário informar a chave do estabelecimento");
        }
        
        $where = Array();
        
        $where[] = " (e.chave = '{$chaveEstabelecimento}' OR e.chave_sandbox = '{$chaveEstabelecimento}') ";
        
        if (!empty($referencia)) {
            $where[] = " c.referencia = '{$referencia}' ";
        }
        
        if ($idMoeda > 0) {
            $where[] = " cp.id_moeda = {$idMoeda} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT cp.id AS codigo, "
                . " c.referencia, "
                . " cp.endereco_carteira AS endereco, "
                . " cp.saldo_btc AS saldo, "
                . " cp.confirmado, "
                . " cp.parametro_um AS parametroUm, "
                . " cp.parametro_dois AS parametroDois, "
                . " cp.parametro_tres AS parametroTres,"
                . " m.simbolo AS moeda "
                . " FROM estabelecimentos e "
                . " INNER JOIN referencias_clientes c ON (e.id = c.id_estabelecimento) "
                . " INNER JOIN carteiras_pdv cp ON (cp.id_referencia_cliente = c.id) "
                . " INNER JOIN moedas m ON (cp.id_moeda = m.id) "
                . " {$where} "
                . " ORDER BY c.referencia, cp.endereco_carteira ";
              
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $dados["confirmado"] = $dados["confirmado"] > 0;
            
            $lista[] = $dados;
        }
        
        return $lista;
    }
    
    public function filtrar($idEstabelecimento, $filtro = null, $somenteComSaldo = true, $idMoeda = 0) {
        
        if (!$idEstabelecimento > 0) {
            throw new \Exception("Estabelecimento inválido");
        }
        
        
        $where = Array();
        $where[] = " c.id_estabelecimento = {$idEstabelecimento} ";
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(c.referencia) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(cc.endereco_carteira) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        if ($somenteComSaldo) {
            $where[] = " ((cc.saldo_btc - cc.saldo_gasto_btc) > 0) ";
        }
        
        if ($idMoeda > 0) {
            $where[] = " cc.id_moeda = {$idMoeda} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT cc.*, m.simbolo "
                . " FROM referencias_clientes c "
                . " INNER JOIN carteiras_pdv cc ON (cc.id_referencia_cliente = c.id) "
                . " INNER JOIN moedas m ON (cc.id_moeda = m.id) "
                . " {$where} "
                . " ORDER BY cc.endereco_carteira ";
              
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $carteiraPdv = new CarteiraPdv($dados);
            $moeda = new Moeda();
            $moeda->simbolo = $dados["simbolo"];
            
            $carteiraPdv->moeda = $moeda;
            
            $lista[] = $carteiraPdv;
        }
        
        return $lista;
    }
    
    
    public function contarCarteiras($idEstabelecimento, $filtro = null, $idMoeda = 0) {
        
        if (!$idEstabelecimento > 0) {
            throw new \Exception("Estabelecimento inválido");
        }
        
        
        $where = Array();
        $where[] = " c.id_estabelecimento = {$idEstabelecimento} ";
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(c.referencia) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(cc.endereco_carteira) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        if ($idMoeda> 0) {
            $where[] = " cc.id_moeda = {$idMoeda} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT COUNT(*) AS QTD "
                . " FROM referencias_clientes c "
                . " INNER JOIN carteiras_pdv cc ON (cc.id_referencia_cliente = c.id) "
                . " {$where} ";
              
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        
        foreach ($result as $dados) {
            $qtd = $dados["QTD"];
        }
        
        return $qtd;
    }
    
    
    public function getBalance(Estabelecimento $estabelecimento, $tipo = 1, $somenteConfirmadas = true, $idMoeda = 0) {
        $moeda = MoedaRn::get(2);
        $where = Array();
        
        if (!$estabelecimento->id > 0) {
            throw new \Exception("O estabelecimento precisa ser informado");
        }
        
        $where[] = " e.id = {$estabelecimento->id} ";
        
        if (AMBIENTE == "producao") {
            $where[] = " LOWER(cp.endereco_carteira)  NOT LIKE LOWER('%SANDBOX%') ";
        }
        
        if ($somenteConfirmadas) {
            $where[] = " cp.confirmado = 1 ";
        }
        
        if ($idMoeda> 0) {
            $where[] = " cc.id_moeda = {$idMoeda} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        
        if ($tipo < 1 || $tipo > 2) {
            throw new \Exception("Tipo de retorno inválido");
        }
        
        $query = " SELECT SUM(cp.saldo_btc - cp.saldo_gasto_btc) AS saldo, m.simbolo "
                    . " FROM estabelecimentos e "
                    . " INNER JOIN referencias_clientes c ON (e.id = c.id_estabelecimento) "
                    . " INNER JOIN carteiras_pdv cp ON (c.id = cp.id_referencia_cliente) "
                    . " INNER JOIN moedas m ON (m.id = cp.id_moeda) "
                    . " {$where} "
                    . " GROUP BY m.simbolo ; ";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $saldos = Array();
        
        foreach($result as $dados) {
            $saldos[$dados["simbolo"]] = number_format((isset($dados["saldo"]) ? $dados["saldo"] : 0), $moeda->casasDecimais, ".", "");
        }
              
        
        $extrato = Array();
        
        $extratoTemp = Array();
        if ($tipo == 2) {
            
            $queryExtrato = " SELECT "
                . " c.id, c.referencia,  SUM(cp.saldo_btc) AS saldo, m.simbolo "
                . " FROM estabelecimentos e "
                . " INNER JOIN referencias_clientes c ON (e.id = c.id_estabelecimento) "
                . " INNER JOIN carteiras_pdv cp ON (c.id = cp.id_referencia_cliente) "
                . " INNER JOIN moedas m ON (m.id = cp.id_moeda) "
                . " {$where} "
                . " GROUP BY c.id, c.referencia, m.simbolo "
                . " ORDER BY c.referencia, m.simbolo; ";
                    
            $resultExtrato = $this->conexao->adapter->query($queryExtrato)->execute();
            foreach ($resultExtrato as $dados) {
                
                if (!isset($extratoTemp[$dados["id"]])) {
                    $extratoTemp[$dados["id"]] = Array("referencia" => $dados["referencia"]);
                }
                
                $extratoTemp[$dados["id"]][$dados["simbolo"]] = number_format($dados["saldo"], $moeda->casasDecimais, ".", "");
                
            }
            
            foreach ($extratoTemp as $ext) {
                $extrato[] = $ext;
            }
            
            return Array("saldo" => $saldos, "extrato" => $extrato, "registros" => sizeof($extrato));
        } else {
            return Array("saldo" => $saldos);
        }
    }
    
    /*
    public function calcularTaxaResgate(Estabelecimento $estabelecimento, $valor, Moeda $moeda) {
       
        $estabelecimentoRn = new EstabelecimentoRn();
        try {
            $estabelecimentoRn->conexao->carregar($estabelecimento);
        } catch (\Exception $ex) {
            throw new \Exception("Estabelecimento inválido ou não cadastrado");
        }
        
        if ($valor <= 0) {
            throw new \Exception("Valor para saque inválido");
        }
        
        $configuracaoRn = new ConfiguracaoRn($this->conexao->adapter);
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn->conexao->carregar($configuracao);
        
        $cliente = new Cliente(Array("id" => $estabelecimento->idCliente));
        $clienteRn = new ClienteRn();
        $clienteRn->conexao->carregar($cliente);
        $taxa = ($cliente->taxaTransferenciaRemota > 0 ? $cliente->taxaTransferenciaRemota : $configuracao->taxaTransferenciaInternaBtc);
        
        if ($valor <= $taxa) {
            throw new \Exception("O valor sacado é inferior á taxa do serviço");
        }
        
            
        $querySaldo = "SELECT SUM(cp.saldo_btc - cp.saldo_gasto_btc) AS saldo, cp.id,  cp.id_referencia_cliente "
                . " FROM referencias_clientes rc "
                . " INNER JOIN carteiras_pdv cp ON (rc.id = cp.id_referencia_cliente) "
                . " WHERE "
                . " rc.id_estabelecimento = {$estabelecimento->id} ";
        if (AMBIENTE == "producao") {
            $querySaldo .= " AND endereco_carteira NOT LIKE '%SANDBOX%' "; 
        }


        $querySaldo .= " GROUP BY cp.id "
                . " having saldo > 0; ";

        $resultSaldo = $this->conexao->adapter->query($querySaldo)->execute();

        $wallets = Array();
        $ids = Array();
        $saldo = 0;
        $valorRestante = $valor;

        $taxaCobrada = 0;
        foreach ($resultSaldo as $dadosSaldo) {
        
            $saldo += $dadosSaldo["saldo"]; // é feita a soma dos saldos
            $ids[] = $dadosSaldo["id"]; // guardo o id

            if ($dadosSaldo["saldo"] < $valorRestante) { // se o valor da carteira é inferior ao valor restante 
                $valorRestante = $valorRestante - $dadosSaldo["saldo"];
                $v = 0;
                $saldoSacado = $dadosSaldo["saldo"];

                $taxaCobrada += number_format($taxa, $moeda->casasDecimais, ".", "");
                
            } else { // o valor contido na carteira é superior ao valor restante de saque
                $v = $dadosSaldo["saldo"] - $valorRestante;
                $saldoSacado = $valorRestante;
                $valorRestante = 0;
                
                if ($saldoSacado > $taxa) {
                    $taxaCobrada += number_format($taxa, $moeda->casasDecimais, ".", "");
                }
                
            }

            // armazeno a carteira de volta atualizada
            $wallets[] = Array(
                "id" => $dadosSaldo["id"],
                "id_referencia_cliente" => $dadosSaldo["id_referencia_cliente"],
                "saldo" => number_format($v, $moeda->casasDecimais, ".", ""),
                "saldoSacado" => number_format($saldoSacado, $moeda->casasDecimais, ".", "")
            );
        }

        if ($valorRestante > 0) {
            throw new \Exception("Saldo insuficiente");
        }

        // primeira verificação de disponibilidade de saldo
        if ($saldo < $valor) {
            throw new \Exception("Saldo insuficiente");
        }
        
        return $taxaCobrada;
        
    }
    */
    
    
    public function adicionarFundosSandbox($volume, $carteira) {
        try {
            $this->conexao->adapter->iniciar();
            
            if (!strpos($carteira, "SANDBOX")) {
                throw new \Exception("Só é permitido alterar o saldo de carteiras SANDBOX");
            }

            if (!$volume > 0) {
                throw new \Exception("O volume precisa ser maior que zero");
            }
            
            $carteiraPdv = $this->getByEnderecoCarteira($carteira);

            $carteiraPdv->saldoBtc += $volume;
            $carteiraPdv->dataAtualizacao = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $moeda = MoedaRn::get($carteiraPdv->idMoeda);
            
            $this->conexao->update(
                    Array(
                        "saldo_btc" => number_format($carteiraPdv->saldoBtc, $moeda->casasDecimais, ".", ""), 
                        "data_atualizacao" => $carteiraPdv->dataAtualizacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "confirmado" => 1
                    ), 
                    Array(
                        "id" => $carteiraPdv->id
                    ));
            
            
            
            try {
                $referenciaCliente = new ReferenciaCliente(Array("id" => $carteiraPdv->idReferenciaCliente));
                $referenciaClienteRn = new ReferenciaClienteRn();
                $referenciaClienteRn->conexao->carregar($referenciaCliente);

                $estabelecimento = new Estabelecimento(Array("id" => $referenciaCliente->idEstabelecimento));
                $estabelecimentoRn = new EstabelecimentoRn();
                $estabelecimentoRn->conexao->carregar($estabelecimento);

                if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                    if (!empty($estabelecimento->callbackHomologacao)) {
                        $this->callback($estabelecimento->callbackHomologacao, $carteiraPdv->id, $moeda->simbolo);
                    }
                } else {
                    if (!empty($estabelecimento->callbackProducao)) {
                        $this->callback($estabelecimento->callbackProducao, $carteiraPdv->id, $moeda->simbolo);
                    }
                }

            } catch (\Exception $ex) {
                //print \Utils\Excecao::mensagem($ex);
            }
            
            $transacaoCarteiraPdvRn = new TransacaoCarteiraPdvRn($this->conexao->adapter);
            $transacaoCarteiraPdv = new TransacaoCarteiraPdv();
            $transacaoCarteiraPdv->id = 0;
            $transacaoCarteiraPdv->address= $carteiraPdv->enderecoCarteira;
            $transacaoCarteiraPdv->amount = number_format($volume, $moeda->casasDecimais, ".", "");
            $transacaoCarteiraPdv->confirmacoes = 999999;
            $transacaoCarteiraPdv->idCarteiraPdv = $carteiraPdv->id;
            $transacaoCarteiraPdv->safe= 1;
            $transacaoCarteiraPdv->idMoeda = $carteiraPdv->idMoeda;
            $transacaoCarteiraPdv->scriptPubKey = "";
            $transacaoCarteiraPdv->solvable = 1;
            $transacaoCarteiraPdv->spendable = 1;
            $transacaoCarteiraPdv->txid = sha1($carteiraPdv->enderecoCarteira . time()) . "SANDBOX";
            $transacaoCarteiraPdv->vout = 0;
            $transacaoCarteiraPdvRn->salvar($transacaoCarteiraPdv);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    
    public function atualizarByTransacaoPendente(CarteiraPdv &$carteiraPdv, TransacaoPendenteBtc $transacaoPendenteBtc, $jsonTransacaoCore) {
        try {
            
            try {
                $this->conexao->carregar($carteiraPdv);
            } catch (\Exception $ex) {
                throw new \Exception("Carteira não localizada no sistema");
            }
            
            /*
            if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                $result = Array();
            }
            */
            
            $moeda = MoedaRn::get($carteiraPdv->idMoeda);
            
            $transacaoCarteiraPdvRn = new TransacaoCarteiraPdvRn($this->conexao->adapter);
            $transacaoCarteiraPdv = new TransacaoCarteiraPdv();
            $transacaoCarteiraPdv->txid = $transacaoPendenteBtc->hash;
            $transacaoCarteiraPdv->address = $transacaoPendenteBtc->enderecoBitcoin;
            $transacaoCarteiraPdv->amount = $transacaoPendenteBtc->valor;
            $transacaoCarteiraPdv->confirmacoes = (isset($transacaoPendenteBtc->confirmations) ? $transacaoPendenteBtc->confirmations : 0);
            $transacaoCarteiraPdv->id = 0;
            $transacaoCarteiraPdv->idMoeda = $carteiraPdv->idMoeda;
            $transacaoCarteiraPdv->idCarteiraPdv = $carteiraPdv->id;
            $transacaoCarteiraPdv->safe = (isset($transacaoPendenteBtc->safe) ? ($jsonTransacaoCore->safe ? 1 : 0) : 0); 
            $transacaoCarteiraPdv->scriptPubKey = (isset($transacaoPendenteBtc->scriptPubKey) ? $transacaoPendenteBtc->scriptPubKey : 0);
            $transacaoCarteiraPdv->solvable = (isset($transacaoPendenteBtc->solvable) ? ($jsonTransacaoCore->solvable ? 1 : 0) : 0);
            $transacaoCarteiraPdv->spendable = (isset($transacaoPendenteBtc->spendable) ? ($jsonTransacaoCore->spendable ? 1 : 0) : 0); 
            $transacaoCarteiraPdv->vout = (isset($transacaoPendenteBtc->vout) ? $transacaoPendenteBtc->vout : 0);

            $transacaoCarteiraPdvRn->salvar($transacaoCarteiraPdv);
            
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            $historicoTransacaoReferencia = new HistoricoTransacaoReferencia();
            $historicoTransacaoReferencia->id = 0;
            $historicoTransacaoReferencia->idMoeda = $carteiraPdv->idMoeda;
            $historicoTransacaoReferencia->idReferenciaCliente = $carteiraPdv->idReferenciaCliente;
            $historicoTransacaoReferencia->idCarteiraPdv = $carteiraPdv->id;
            $historicoTransacaoReferencia->tipo = \Utils\Constantes::DEPOSITO;
            $historicoTransacaoReferencia->valor = number_format($transacaoPendenteBtc->valor, $moeda->casasDecimais, ".", "");
            $historicoTransacaoReferenciaRn->salvar($historicoTransacaoReferencia);
                        
            $carteiraPdv->confirmado = ($transacaoPendenteBtc->valor > 0 ? "1" : "0");
            
            
            $queryUpdate = " UPDATE carteiras_pdv SET saldo_btc = saldo_btc + {$transacaoPendenteBtc->valor}, confirmado = {$carteiraPdv->confirmado} WHERE id = {$carteiraPdv->id};";
            $this->conexao->adapter->query($queryUpdate)->execute();

            try {
                $referenciaCliente = new ReferenciaCliente(Array("id" => $carteiraPdv->idReferenciaCliente));
                $referenciaClienteRn = new ReferenciaClienteRn();
                $referenciaClienteRn->conexao->carregar($referenciaCliente);

                $estabelecimento = new Estabelecimento(Array("id" => $referenciaCliente->idEstabelecimento));
                $estabelecimentoRn = new EstabelecimentoRn();
                $estabelecimentoRn->conexao->carregar($estabelecimento);

                if (strpos($carteiraPdv->enderecoCarteira, "SANDBOX")) {
                    if (!empty($estabelecimento->callbackHomologacao)) {
                        $this->callback($estabelecimento->callbackHomologacao, $carteiraPdv->id, $moeda->simbolo);
                    }
                } else {
                    if (!empty($estabelecimento->callbackProducao)) {
                        $this->callback($estabelecimento->callbackProducao, $carteiraPdv->id, $moeda->simbolo);
                    }
                }

            } catch (\Exception $ex) {

            }
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    public function getQuantidadeCarteiras(Estabelecimento $estabelecimento, $idMoeda = 0) {
        
        if (!$estabelecimento->id > 0) {
            throw new \Exception("Indentificação do estabelecimento inválido");
        }
        
        $sWhereMoedas = "";
        if ($idMoeda > 0) {
            $sWhereMoedas = " AND cp.id_moeda = {$idMoeda} ";
        }
        
        $query = " SELECT COUNT(DISTINCT(cp.id)) AS qtd "
                ." FROM referencias_clientes rc "
                ." INNER JOIN carteiras_pdv cp ON (rc.id = cp.id_referencia_cliente) "
                ." WHERE rc.id_estabelecimento = {$estabelecimento->id} {$sWhereMoedas};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $d) {
            $qtd = $d["qtd"];
        }
        return $qtd;
    }
    
}

?>