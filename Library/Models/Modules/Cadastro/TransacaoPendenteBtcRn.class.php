<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Banco
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class TransacaoPendenteBtcRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
    private $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TransacaoPendenteBtc());
        } else {
            $this->conexao = new GenericModel($adapter, new TransacaoPendenteBtc());
        }
    }


    public function salvar(TransacaoPendenteBtc &$transacaoPendenteBtc) {

        try {

            $transacaoPendenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $transacaoPendenteBtc->executada = 0;


            if (empty($transacaoPendenteBtc->hash)) {
                throw new \Exception($this->idioma->getText("necessarioHash"), 101);
            }
            
            if (empty($transacaoPendenteBtc->enderecoBitcoin)) {
                throw new \Exception($this->idioma->getText("necessarioEndDestino"), 109);
            }
            
            if (!$transacaoPendenteBtc->valor > 0) {
                throw new Exception($this->idioma->getText("valorPrecisaMaioroZero"), 108);
            }


            $contaCorrenteBtcRn = new ContaCorrenteBtcRn();
            $contaCorrenteBtc = $contaCorrenteBtcRn->find($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor);

            if ($contaCorrenteBtc != null) {
                throw new \Exception($this->idioma->getText("jaExisteTransacao"), 102);
            } else {
                
                $transacaoCarteiraPdvRn = new TransacaoCarteiraPdvRn($this->conexao->adapter);
                $transacaoCarteiraPdv = $transacaoCarteiraPdvRn->getByTxId($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor);
                
                if ($transacaoCarteiraPdv != null) {
                    throw new \Exception($this->idioma->getText("jaExisteTransacao"), 102);
                } else {
                    $transacaoInvoicePdvRn = new TransacaoInvoicePdvRn($this->conexao->adapter);
                    $transacaoInvoicePdv = $transacaoInvoicePdvRn->getByTxId($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor);
                    
                    if ($transacaoInvoicePdv != null) {
                        throw new \Exception($this->idioma->getText("jaExisteTransacao"), 102);
                    } else {
                        
                        $depositoIcoRn = new \Models\Modules\ICO\DepositoIcoRn($this->conexao->adapter);
                        $depositoIco = $depositoIcoRn->find($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor, new Moeda(Array("id" => $transacaoPendenteBtc->idMoeda)));
                        
                        if ($depositoIco != null) {
                            throw new \Exception($this->idioma->getText("jaExisteTransacao"), 102);
                        }
                        
                    }
                }
            }

            $tpb = $this->getByHash($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor);
            if ($tpb != null) {
                throw new \Exception($this->idioma->getText("transacaoAguardaConfirmacao"), 103);
            }

            
            
            try {
                \Utils\BitcoinAddress::validate($transacaoPendenteBtc->enderecoBitcoin);
            } catch (\Exception $ex) {
                //throw new \Exception("Endereço inválido", 109);
            }
            
            // valida se a carteira informada pertence ä base do sistema
            $carteiraClienteRn = new CarteiraRn($this->conexao->adapter);
            $carteira = $carteiraClienteRn->getByEndereco($transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->idMoeda);
            
            $validarDeposito = false;
            
            if ($carteira == null) {
                $carteiraPdvRn = new CarteiraPdvRn($this->conexao->adapter);
                $carteiraPdv = $carteiraPdvRn->getByEnderecoCarteira($transacaoPendenteBtc->enderecoBitcoin);
                if ($carteiraPdv == null) {
                    $invoicePdvRn = new InvoicePdvRn($this->conexao->adapter);
                    $invoicePdv = $invoicePdvRn->getByEnderecoCarteira($transacaoPendenteBtc->enderecoBitcoin);
                    if ($invoicePdv == null) {
                        
                        $walletIcoRn = new \Models\Modules\ICO\WalletIcoRn($this->conexao->adapter);
                        $walletIco = $walletIcoRn->getByAddress($transacaoPendenteBtc->enderecoBitcoin, new Moeda(Array("id" => $transacaoPendenteBtc->idMoeda)));
                        if ($walletIco == NULL) {
                            throw new \Exception($this->idioma->getText("endInformadoNaoPertenceBase"), 125);
                        } else {
                            $transacaoPendenteBtc->idCliente = $walletIco->idCliente;
                        }
                    } else {
                        $invoicePdvRn->carregar($invoicePdv, false, false, true);
                        
                        $transacaoPendenteBtc->idCliente = $invoicePdv->pontoPdv->estabelecimento->idCliente;
                    }
                } else {
                    $carteiraPdvRn->carregar($carteiraPdv, false, true);
                    $estabelecimento = new Estabelecimento(Array("id" => $carteiraPdv->referenciaCliente->idEstabelecimento));
                    $estabelecimentoRn = new EstabelecimentoRn($this->conexao->adapter);
                    $estabelecimentoRn->conexao->carregar($estabelecimento);
                    
                    $transacaoPendenteBtc->idCliente = $estabelecimento->idCliente;
                }
            } else {
                $validarDeposito = true;
                $transacaoPendenteBtc->idCliente = $carteira->idCliente;
            }
            
            if (!$transacaoPendenteBtc->idCliente > 0) {
                throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"), 106);
            }

            $cliente = new Cliente(Array("id" => $transacaoPendenteBtc->idCliente));
            $clienteRn = new ClienteRn();
            try{
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("clienteNaoLocalizado"), 107);
            }
            
            if ($validarDeposito && $cliente->statusDepositoCurrency < 1) {
                throw new \Exception($this->idioma->getText("depositanteCriptoEstaSuspenso"), 128);
            }
            
            if (empty($transacaoPendenteBtc->descricao)) {
                throw new \Exception($this->idioma->getText("necessarioDescricaoLancamento"), 104);
            }

            if (!isset($transacaoPendenteBtc->data->data) || $transacaoPendenteBtc->data->data == null) {
                throw new \Exception($this->idioma->getText("informarDataLancamento"), 105);
            }

            if ($transacaoPendenteBtc->tipo != \Utils\Constantes::ENTRADA && $transacaoPendenteBtc->tipo != \Utils\Constantes::SAIDA) {
                throw new \Exception($this->idioma->getText("tipoMovimentoInvalido"), 110);
            }

            if (!$transacaoPendenteBtc->idMoeda > 0) {
                throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
            }
            
            $moeda = new Moeda(Array("id" => $transacaoPendenteBtc->idMoeda));
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
            }
            
            if ($moeda->statusDeposito < 1) {
                 $texto1 = $this->idioma->getText("depositoUspenso");
                 $texto1 = str_replace("{var}",$moeda->simbolo, $texto1);
                throw new \Exception($texto1, 130);
            }
            
            if ($moeda->ativo < 1) {

                throw new \Exception($this->idioma->getText("comercioMoedaSuspenso"), 130);
            }
            if ($moeda->statusMercado < 1) {
                //throw new \Exception($this->idioma->getText("comercioMoedaTempSuspenso"), 123);
            }
            
            $transacaoPendenteBtc->erro = "";

            unset($transacaoPendenteBtc->cliente);
            unset($transacaoPendenteBtc->usuario);
            
            
            $dados = Array(
                        "data" => $transacaoPendenteBtc->data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "data_cadastro" => $transacaoPendenteBtc->dataCadastro->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "data_confirmacao" => null,
                        "descricao" => $transacaoPendenteBtc->descricao,
                        "endereco_bitcoin" => $transacaoPendenteBtc->enderecoBitcoin,
                        "erro" => "",
                        "executada" => 0,
                        "hash" => $transacaoPendenteBtc->hash,
                        "id_cliente" => $transacaoPendenteBtc->idCliente,
                        "id_conta_corrente_btc" => null,
                        "id_usuario" => null,
                        "id_moeda" => $transacaoPendenteBtc->idMoeda,
                        "tipo" => $transacaoPendenteBtc->tipo,
                        "valor" => $transacaoPendenteBtc->valor
                    );
            $this->conexao->insert(
                    $dados
                );

        } catch (\Exception $e) {
            
            throw new \Exception(\Utils\Excecao::mensagem($e), $e->getCode());
        }

    }


    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $filtro = null, $status = 0, $idMoeda = 0) {

        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception($this->idioma->getText("dataInicialInformada"));
        }
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception($this->idioma->getText("dataFinalInformada"));
        }
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
        }

        $where = Array();

        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(t.descricao) LIKE LOWER('%{$filtro}%')  ) OR "
                    . " ( LOWER(t.endereco_bitcoin) LIKE LOWER('%{$filtro}%')  ) OR "
                    . " ( LOWER(t.hash) LIKE LOWER('%{$filtro}%')  ) OR "
                    . " ( LOWER(c.nome) LIKE LOWER('%{$filtro}%')  ) OR "
                    . " ( LOWER(t.erro) LIKE LOWER('%{$filtro}%')  )"
                    . " ) ";
        }
        
        if ($idMoeda > 0) {
            $where[] = " t.id_moeda = {$idMoeda} ";
        }

        $where[] = " t.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";


        if ($status >= 0) {
            $where[] = " t.executada = {$status} ";
        }

        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : " ");

        $query = "SELECT "
                . " t.*, c.nome, u.nome AS usuario "
                . " FROM transacoes_pendentes_btc t "
                . " INNER JOIN clientes c ON (t.id_cliente = c.id) "
                . " LEFT JOIN usuarios u ON (t.id_usuario = u.id) "
                . " {$where} "
                . " ORDER BY data DESC;";

        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {

            $transacaoPendenteBtc = new TransacaoPendenteBtc($dados);
            $cliente = new Cliente(Array("id" => $dados["idCliente"], "nome" => $dados["nome"]));
            $transacaoPendenteBtc->cliente = $cliente;

            $usuario = new Usuario(Array("id" => $dados["idUsuario"], "nome" => $dados["usuario"]));
            $transacaoPendenteBtc->usuario = $usuario;
            $lista[] = $transacaoPendenteBtc;
        }



        return $lista;
    }



    public function carregar(TransacaoPendenteBtc &$transacaoPendenteBtc, $carregar = true, $carregarCliente = true, $carregarUsuario = true, $carregarMoeda = true) {
        if ($carregar) {
            
            $result = $this->conexao->select(
                    Array(
                        "hash" => $transacaoPendenteBtc->hash
                    )
                );
            
            if (sizeof($result) > 0) {
                $transacaoPendenteBtc = $result->current();
            }
        }

        if ($carregarCliente && $transacaoPendenteBtc->idCliente > 0) {
            $transacaoPendenteBtc->cliente = new Cliente(Array("id" => $transacaoPendenteBtc->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($transacaoPendenteBtc->cliente);
        }

        if ($carregarUsuario && $transacaoPendenteBtc->idUsuario > 0) {
            $transacaoPendenteBtc->usuario = new Usuario(Array("id" => $transacaoPendenteBtc->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($transacaoPendenteBtc->usuario);
        }

        if ($carregarMoeda && $transacaoPendenteBtc->idMoeda > 0) {
            $transacaoPendenteBtc->moeda = new Moeda(Array("id" => $transacaoPendenteBtc->idMoeda));
            $moedaoRn = new MoedaRn();
            $moedaoRn->conexao->carregar($transacaoPendenteBtc->moeda);
        }
    }

    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarUsuario = true, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $transacaoPendenteBtc) {
            $this->carregar($transacaoPendenteBtc, false, $carregarCliente, $carregarUsuario, $carregarMoeda);
            $lista[] = $transacaoPendenteBtc;
        }
        return $lista;
    }


    public function validar(TransacaoPendenteBtc $transacaoPendenteBtc, $jsonTransacaoCore, $token) {
        try {
            $this->conexao->adapter->iniciar();


            if (empty($transacaoPendenteBtc->hash)) {
                throw new \Exception($this->idioma->getText("necessarioHash"), 101);
            }
            
            $tpb = $this->getByHash($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor);

            $depositoIcoRn = new \Models\Modules\ICO\DepositoIcoRn();
            $depositoIco = $depositoIcoRn->find($transacaoPendenteBtc->hash, $transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->valor, new Moeda(Array("id" => $tpb->idMoeda)));
            
            if ($depositoIco != null) {
                throw new \Exception("Depósito já creditado", 101);
            }
            
            $contaCorrenteBtc = null;

            if ($tpb !== null) {
                try {

                    if ($transacaoPendenteBtc->idMoeda != $tpb->idMoeda) {
                        throw new \Exception($this->idioma->getText("moedasDivergentes"), 124);
                    }

                    $moeda = new Moeda(Array("id" => $tpb->idMoeda));
                    try {
                        $moedaRn = new MoedaRn();
                        $moedaRn->conexao->carregar($moeda);
                    } catch (\Exception $ex) {
                        throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
                    }
                    
                    
                    if ($tpb->executada > 0) {
                        throw new \Exception($this->idioma->getText("transacaoConfirmadaSistema"), 102);
                    }

                    if (empty($transacaoPendenteBtc->descricao)) {
                        throw new \Exception($this->idioma->getText("necessarioDescricaoLancamento"), 104);
                    }

                    if (!isset($transacaoPendenteBtc->data->data) || $transacaoPendenteBtc->data->data == null) {
                        throw new \Exception($this->idioma->getText("informarDataLancamento"), 105);
                    }


                    if (!$transacaoPendenteBtc->valor > 0) {
                        throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"), 108);
                    }

                    if (empty($transacaoPendenteBtc->enderecoBitcoin)) {
                        throw new \Exception($this->idioma->getText("necessarioEndDestino"), 109);
                    }


                    if ($transacaoPendenteBtc->tipo != \Utils\Constantes::ENTRADA && $transacaoPendenteBtc->tipo != \Utils\Constantes::SAIDA) {
                        throw new \Exception($this->idioma->getText("tipoMovimentoInvalido"), 110);
                    }

                    if (!$transacaoPendenteBtc->data->igual($tpb->data)) {
                        throw new \Exception($this->idioma->getText("dataRegistroNaoConfere"), 111);
                    }

                    if ($transacaoPendenteBtc->descricao != $tpb->descricao) {
                        throw new \Exception($this->idioma->getText("descNaoConfere"), 112);
                    }

                    
                    if (number_format($transacaoPendenteBtc->valor, $moeda->casasDecimais, ".", "") != number_format($tpb->valor, $moeda->casasDecimais, ".", "")) {
                        throw new \Exception($this->idioma->getText("valorRistroNaoConfere") . "[{$transacaoPendenteBtc->valor} => {$tpb->valor}]", 115);
                    }

                    if ($transacaoPendenteBtc->tipo != $tpb->tipo) {
                        throw new \Exception($this->idioma->getText("tipoMovimentoNaoConfere"), 116);
                    }


                    if ($moeda->ativo < 1) {
                        throw new \Exception($this->idioma->getText("comercioMoedaSuspenso"), 130);
                    }
                    if ($moeda->statusMercado < 1) {
                        //throw new \Exception($this->idioma->getText("comercioMoedaTempSuspenso"), 123);
                    }

                    $carteiraClienteRn = new CarteiraRn($this->conexao->adapter);
                    $carteira = $carteiraClienteRn->getByEndereco($transacaoPendenteBtc->enderecoBitcoin, $transacaoPendenteBtc->idMoeda);
                    
                    if ($carteira != null) {
                        
                        $this->registrarContaCorrenteBtc($tpb, $token);
                        
                    } else {
                        
                        if ($jsonTransacaoCore == null && json_last_error()) {
                            //throw new \Exception($this->idioma->getText("dadosJsonInvalidos"), 126);
                        }
                        
                        $carteiraPdvRn = new CarteiraPdvRn($this->conexao->adapter);
                        $carteiraPdv = $carteiraPdvRn->getByEnderecoCarteira($transacaoPendenteBtc->enderecoBitcoin);
                        if ($carteiraPdv != null) {
                            
                            $jsonTransacaoCore = json_decode($jsonTransacaoCore);
                            
                            $carteiraPdvRn->atualizarByTransacaoPendente($carteiraPdv, $transacaoPendenteBtc, $jsonTransacaoCore);
                            
                        } else {
                            
                            $invoicePdvRn = new InvoicePdvRn($this->conexao->adapter);
                            $invoicePdv = $invoicePdvRn->getByEnderecoCarteira($transacaoPendenteBtc->enderecoBitcoin);
                            if ($invoicePdv != null) {
                                $jsonTransacaoCore = json_decode($jsonTransacaoCore);
                                $invoicePdvRn->atualizarStatus($invoicePdv, $transacaoPendenteBtc, $jsonTransacaoCore, $token);
                                
                            } else {
                                $walletIcoRn = new \Models\Modules\ICO\WalletIcoRn($this->conexao->adapter);
                                $walletIco = $walletIcoRn->getByAddress($transacaoPendenteBtc->enderecoBitcoin, new Moeda(Array("id" => $transacaoPendenteBtc->idMoeda)));
                                
                                if ($walletIco != NULL) {
                                    $depositoIcoRn = new \Models\Modules\ICO\DepositoIcoRn($this->conexao->adapter);
                                    $depositoIcoRn->gerarByTransacaoPendente($tpb);
                                } 
                            }
                            
                        }
                    }
                    
                    $this->conexao->update(
                            Array(
                                "data_confirmacao" => date("Y-m-d H:i:s"),
                                "executada" => 1,
                                "id_conta_corrente_btc" => ($contaCorrenteBtc != null ? $contaCorrenteBtc->id : null)
                            ),
                            Array(
                                "hash" => $tpb->hash
                            )
                        );

                    
                    

                } catch (\Exception $ex) {
                    $this->conexao->update(
                            Array(
                                "erro" => \Utils\Excecao::mensagem($ex)
                            ),
                            Array(
                                "hash" => $tpb->hash
                            )
                        );
                        throw new \Exception(\Utils\Excecao::mensagem($ex), $ex->getCode());
                }
            } else {
                throw new \Exception($this->idioma->getText("tentativaDeConfirmacaoNaoExiste"), 117);
            }


            $this->conexao->adapter->finalizar();
            return $contaCorrenteBtc;
        } catch (\Exception $ex) {
            
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex), $ex->getCode());
        }
    }

    
    private function registrarContaCorrenteBtc(TransacaoPendenteBtc $tpb, $token) {
        $contaCorrenteBtc = new ContaCorrenteBtc();
        $contaCorrenteBtc->data = new \Utils\Data($tpb->data->formatar(\Utils\Data::FORMATO_PT_BR) . " " . date("H:i:s"));
        $contaCorrenteBtc->descricao = $tpb->descricao;
        $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
        $contaCorrenteBtc->enderecoBitcoin = $tpb->enderecoBitcoin;
        $contaCorrenteBtc->executada = 1;
        $contaCorrenteBtc->hash = $tpb->hash;
        $contaCorrenteBtc->idCliente = $tpb->idCliente;
        $contaCorrenteBtc->tipo = $tpb->tipo;
        $contaCorrenteBtc->transferencia = 0;
        $contaCorrenteBtc->idMoeda = $tpb->idMoeda;
        $contaCorrenteBtc->valor = $tpb->valor;
        $contaCorrenteBtc->valorTaxa = 0;
        $contaCorrenteBtc->autorizada = 1;
        $contaCorrenteBtc->origem = 0;

        $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
        $contaCorrenteBtcRn->salvar($contaCorrenteBtc, $token);
        
        $cliente = new Cliente(Array("id" => $tpb->idCliente));
        $clienteRn = new ClienteRn();
        $clienteRn->conexao->carregar($cliente);
        
        
        if ($cliente->idReferencia <= 0) {
            
            $result = $contaCorrenteBtcRn->conexao->listar("id_cliente = {$tpb->idCliente} AND tipo = '{$tpb->tipo}' AND order_book = 0 AND id != {$contaCorrenteBtc->id}", null, null, 1);
            
            if (sizeof($result) < 1) {
                switch ($tpb->idMoeda) {
                    case 20:
                        $clienteRn->conexao->update(Array("id_referencia" => 15093064536763), Array("id" => $cliente->id)); // PEDRO PAULO INACIO RODRIGUES
                        
                        break;
                    case 25:
                        $clienteRn->conexao->update(Array("id_referencia" => 15093064537181), Array("id" => $cliente->id)); // BRUNO LOURENCO DE SOUZA SANTOS
                        
                        break;
                    case 28:
                        $clienteRn->conexao->update(Array("id_referencia" => 15093064537377), Array("id" => $cliente->id)); // TIAGO DAS NEVES RODRIGUES
                        
                        break;

                    default:
                        break;
                }
            } 
            
        }
    }

    public function getByHash($hash, $wallet, $valor) {
        $result = $this->conexao->select(Array(
            "hash" => $hash,
            "endereco_bitcoin" => $wallet,
            "valor" => number_format($valor, 8, ".", "")
        ));

        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }



    public function confirmar(TransacaoPendenteBtc $transacaoPendenteBtc) {

        try {
            $this->conexao->adapter->iniciar();

            if (empty($transacaoPendenteBtc->hash)) {
                throw new \Exception($this->idioma->getText("necessarioHash"), 101);
            }
            
            $tpb = $this->getByHash($transacaoPendenteBtc->hash);
            
            if ($tpb == null) {
                throw new \Exception($this->idioma->getText("transacaoNaoLocalizadaSistema"), 117);
            } 
            

            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
            $ccb = $contaCorrenteBtcRn->getByHash($transacaoPendenteBtc->hash);

            if ($ccb != null) {
                throw new \Exception($this->idioma->getText("jaExisteTransacaoComHash"), 102);
            }

            if (empty($transacaoPendenteBtc->descricao)) {
                throw new \Exception($this->idioma->getText("necessarioDescricaoLancamento"), 104);
            }

            if (!isset($transacaoPendenteBtc->data->data) || $transacaoPendenteBtc->data->data == null) {
                throw new \Exception($this->idioma->getText("informarDataLancamento"), 105);
            }

            if (!$transacaoPendenteBtc->valor > 0) {
                throw new \Exception($this->idioma->getText("valorPrecisaMaioroZero"), 108);
            }

            if (empty($transacaoPendenteBtc->enderecoBitcoin)) {
                throw new \Exception($this->idioma->getText("necessarioEndDestino"), 109);
            }

            if (!($transacaoPendenteBtc->idMoeda > 0)) {
                throw new \Exception($this->idioma->getText("moedaInvalida"), 122);
            }

            $usuario = \Utils\Geral::getLogado();

            if ($usuario == null) {
                throw new \Exception($this->idioma->getText("sessaoInvalidaLoginNovamente"));
            }

            if (!$usuario instanceof Usuario || $usuario->tipo != \Utils\Constantes::ADMINISTRADOR) {
                throw new \Exception($this->idioma->getText("vcNaoPermissaoExecutarAcao"));
            }

            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->data = $transacaoPendenteBtc->data;
            $contaCorrenteBtc->descricao = $transacaoPendenteBtc->descricao;
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_EXTERNA;
            $contaCorrenteBtc->enderecoBitcoin = $transacaoPendenteBtc->enderecoBitcoin;
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->hash = $transacaoPendenteBtc->hash;
            $contaCorrenteBtc->idCliente = $tpb->idCliente;
            $contaCorrenteBtc->tipo = $tpb->tipo;
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->idMoeda = $transacaoPendenteBtc->idMoeda;
            $contaCorrenteBtc->valor = $transacaoPendenteBtc->valor;
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->origem = 0;

            $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

            $this->conexao->update(
                    Array(
                        "data" => $transacaoPendenteBtc->data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "descricao" => $transacaoPendenteBtc->descricao,
                        "endereco_bitcoin" => $transacaoPendenteBtc->enderecoBitcoin,
                        "hash" => $transacaoPendenteBtc->hash,
                        "valor" => $transacaoPendenteBtc->valor,
                        "executada" => 1,
                        "id_usuario" => $usuario->id,
                        "id_moeda" => $transacaoPendenteBtc->idMoeda,
                        "data_confirmacao" => date("Y-m-d H:i:s"),
                        "id_conta_corrente_btc" => $contaCorrenteBtc->id
                    ),
                    Array(
                        "hash" => $transacaoPendenteBtc->hash
                    )
                );

            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
            $this->conexao->adapter->cancelar();
        }

    }



    public function excluir(TransacaoPendenteBtc $transacaoPendenteBtc) {
        try {
            $this->conexao->adapter->iniciar();


            try {
                $this->carregar($transacaoPendenteBtc, true, false, false);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("transacaoNaoLocalizadaSistema"));
            }

            if ($transacaoPendenteBtc->executada > 0) {
                throw new \Exception($this->idioma->getText("naoPossivelExcluirTransaConfirmada"));
            }

            $this->conexao->delete("hash = '{$transacaoPendenteBtc->hash}'");

            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
            $this->conexao->adapter->cancelar();
        }
    }
    
    
    public function getQuantidadePorStatus() {
        $query = "SELECT COUNT(*) AS qtd FROM transacoes_pendentes_btc WHERE executada = 0;";
        $result = $this->conexao->adapter->query($query)->execute();
        
        foreach ($result as $dados) {
            return $dados["qtd"];
        }
        return 0;
    }
    
    public function getUltimaTransacao($idMoeda = 0) {
        $sWhere = ($idMoeda > 0 ? " AND id_moeda = {$idMoeda} " : "");
        $query = "SELECT * FROM transacoes_pendentes_btc WHERE executada = 1 {$sWhere}  ORDER BY data_cadastro DESC LIMIT 1";
        $result = $this->conexao->adapter->query($query)->execute();
        if (sizeof($result) > 0) {
            $d = $result->current();
            return new TransacaoPendenteBtc($d);
        }
        
        return null;
    }
    
}

?>
