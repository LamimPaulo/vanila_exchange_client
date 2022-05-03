<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class CofreRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
     private $idioma = null;
    
    public static $MOEDAS_ACEITAS = Array(
        2, 4, 7
    );
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Cofre());
        } else {
            $this->conexao = new GenericModel($adapter, new Cofre());
        }
    }
    
    public function salvar(Cofre &$cofre) {
        try {
            $this->conexao->adapter->iniciar();
            
            if (!($cofre->idCliente > 0)) {
                throw new \Exception($this->idioma->getText("clienteDeveInformado"));
            }
            $cliente = new Cliente(Array("id" => $cofre->idCliente));
            try {
                $clienteRn = new ClienteRn($this->conexao->adapter);
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente inválido");
            }
            
            if (!($cofre->idMoeda > 0)) {
                throw new \Exception($this->idioma->getText("moedaDeveInformada"));
            }
            
            if (!($cofre->contrato > 0)) {
                throw new \Exception($this->idioma->getText("contratoInvalido"));
            }
            
            if (!in_array($cofre->idMoeda, self::$MOEDAS_ACEITAS)) {
                throw new \Exception($this->idioma->getText("moedaDeveInformada"));
            }
            
            if ($cofre->volumeDepositado <= 0) {
                throw new \Exception($this->idioma->getText("volumeInvalido"));
            }
            
            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
            
            $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $cofre->idMoeda, false);
            
            if ($cofre->volumeDepositado > $saldo) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            $cofre->id = 0;
            $cofre->dataEntrada = new \Utils\Data(date("d/m/Y H:i:s"));
            $cofre->dataProvisaoSaque = null;
            $cofre->sacado = 0;
            $cofre->volumeCobradoTaxa = 0;
            $cofre->saqueSolicitado = 0;
            $cofre->dataSolicitacaoSaque = null;
           
            unset($cofre->cliente);
            unset($cofre->moeda);
            
            if (!in_array($cofre->idMoeda, self::$MOEDAS_ACEITAS)) {
                throw new \Exception($this->idioma->getText("moedaNaoAceita"));
            }
            
            $cofre->volumePagoRendimento = 0;
            
            $contrato = new InvestimentoContratos();
            $contratoRn = new InvestimentoContratosRn();
            $contrato->id = $cofre->contrato;
            $contratoRn->conexao->carregar($contrato);
            
            $cofre->taxa = $contrato->lucroNc;
            
            $cofre->dataExpiracaoContrato = new \Utils\Data(date("d/m/Y H:i:s"));            
            $cofre->dataExpiracaoContrato->somar(0, $contrato->tempoMeses);
            
            $this->conexao->salvar($cofre);
            
            $moeda = MoedaRn::get($cofre->idMoeda);
            
            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->descricao = "Investimento";
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->enderecoBitcoin = null;
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->hash = null;
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->origem = 3;
            $contaCorrenteBtc->idCliente = $cofre->idCliente;
            $contaCorrenteBtc->idMoeda = $cofre->idMoeda;
            $contaCorrenteBtc->orderBook  = 0;
            $contaCorrenteBtc->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->valor = number_format($cofre->volumeDepositado, $moeda->casasDecimais, ".", "");
            $contaCorrenteBtc->autorizada = 1;
            
            $contaCorrenteBtcRn->salvar($contaCorrenteBtc, null);
            
            $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
            $contaCorrenteEmpresaBtc->id = 0;
            $contaCorrenteEmpresaBtc->bloqueado = 1;
            $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteEmpresaBtc->descricao = "Investimento: Cliente {$cliente->nome}, registro {$cofre->id}";
            $contaCorrenteEmpresaBtc->idMoeda = $cofre->idMoeda;
            $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteEmpresaBtc->transferencia = 0;
            $contaCorrenteEmpresaBtc->valor = number_format($cofre->volumeDepositado, $moeda->casasDecimais, ".", "");;
            $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
            
            if (AMBIENTE == "producao") { 
                /*
                try {
                    $usuarioRenato = new \Models\Modules\Cadastro\Usuario(Array("id" => 1483022582));
                    $usuarioGerson = new \Models\Modules\Cadastro\Usuario(Array("id" => 1483023872));

                    $usuarioRn = new UsuarioRn();
                    $usuarioRn->conexao->carregar($usuarioGerson);
                    $usuarioRn->conexao->carregar($usuarioRenato);

                    $moedas = Array(2 => "BTC", 4=>"LTC", 7=>"DASH");
                    $msg = "Novo investimento em {$moedas[$cofre->idMoeda]}: {$cofre->volumeDepositado}";

                    $cel1 = str_replace(Array(" ", "-", "(", ")"), "", $usuarioRenato->celular);
                    $cel2 = str_replace(Array(" ", "-", "(", ")"), "", $usuarioGerson->celular);


                    $api = new \TWWSms\TWWLibrary(\TWWSms\Credenciais::getCredenciais());
                    $api->EnviaSMS("55{$cel1}", $msg);
                    $api->EnviaSMS("55{$cel2}", $msg);
                } catch (\Exception $ex) {

                }
                 * 
                 */
            }
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    public function carregar(Cofre &$cofre, $carregar = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($cofre);
        }
        
        if ($carregarCliente && $cofre->idCliente > 0) {
            $clienteRn = new ClienteRn();
            $cofre->cliente = new Cliente(Array("id" => $cofre->idCliente));
            $clienteRn->conexao->carregar($cofre->cliente);
        }
        
    }
    
    public function solicitarSaqueInvestimento(Cofre &$cofre) {
        try {
            $this->conexao->carregar($cofre);
        } catch (\Exception $ex) {
            throw new \Exception("Registro inválido");
        }
        
        if ($cofre->sacado > 0) {
            throw new \Exception("Investimento já foi solicitado");
        }
        if ($cofre->saqueSolicitado > 0) {
            throw new \Exception("O saque do investimento já foi solicitado");
        }
        $configuracao = ConfiguracaoRn::get();
        
        $dataProvisionamento = new \Utils\Data(date("d/m/Y H:i:s"));
        $dataProvisionamento->somar(0, 0, $configuracao->prazoDiasSaqueCofre);
        $this->conexao->update(
            Array(
                "saque_solicitado" => 1, 
                "data_solicitacao_saque" => date("Y-m-d H:i:s"), 
                "data_provisao_saque" => $dataProvisionamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
            ), 
            Array(
                "id" => $cofre->id
            )
        );
        
    }
    
    public function solicitarRetirada(Cliente $cliente, Moeda $moeda) {
        $saldoSacado = 0;
        try {
            
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida");
            }
            
            $this->conexao->adapter->iniciar();
            
            $configuracao = new Configuracao(Array("id"=>1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $dataProvisionamento = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataProvisionamento->somar(0, 0, $configuracao->prazoDiasSaqueCofre);
            
            $query = " SELECT * "
                    . " FROM cofre "
                    . " WHERE id_cliente = {$cliente->id} AND id_moeda = {$moeda->id} AND sacado = 0 AND saque_solicitado = 0"
                    . " ORDER BY data_entrada;";
                    
            $dados = $this->conexao->adapter->query($query)->execute();
            
            foreach ($dados as $d) {
                $saldoSacado += number_format(($d["volume_depositado"] - $d["volume_cobrado_taxa"]), $moeda->casasDecimais, ".", "");
                $this->conexao->update(
                        Array(
                            "saque_solicitado" => 1, 
                            "data_solicitacao_saque" => date("Y-m-d H:i:s"), 
                            "data_provisao_saque" => $dataProvisionamento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                        ), 
                        Array(
                            "id" => $d["id"]
                        )
                    );
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($ex);
        }
        return $saldoSacado;
    }
    
    
    public function retirar(Cliente $cliente, Moeda  $moeda) {
        $this->conexao->adapter->iniciar();
        try {
            
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida");
            }
            
            $saldo = 0;
            $dataAtual = date("Y-m-d");
            $query = " SELECT * FROM cofre "
            . " WHERE id_cliente = {$cliente->id} AND id_moeda = {$moeda->id} AND ((saque_solicitado = 1 AND data_provisao_saque <= '{$dataAtual} 00:00:00') OR data_expiracao_contrato <= '{$dataAtual} 23:59:59') AND sacado = 0 "
            . " ORDER BY data_entrada;";
            
            $result = $this->conexao->adapter->query($query)->execute();
            
            foreach ($result as $dados) {
                $saldo += number_format(($dados["volume_depositado"]), $moeda->casasDecimais, ".","");
                
                $this->conexao->update(
                        Array(
                            "data_saque" => date("Y-m-d H:i:s"),
                            "sacado" => 1
                        ), 
                        Array(
                            "id" => $dados["id"]
                        )
                    );
            }
            
            if ($saldo > 0) { 
                $contaCorrenteBtc = new ContaCorrenteBtc();
                $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->descricao = "Saque de Investimento";
                $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                $contaCorrenteBtc->enderecoBitcoin = null;
                $contaCorrenteBtc->executada = 1;
                $contaCorrenteBtc->hash = null;
                $contaCorrenteBtc->id = 0;
                $contaCorrenteBtc->origem = 3;
                $contaCorrenteBtc->idCliente = $cliente->id;
                $contaCorrenteBtc->idMoeda = $moeda->id;
                $contaCorrenteBtc->orderBook  = 0;
                $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteBtc->transferencia = 0;
                $contaCorrenteBtc->valor = number_format($saldo, $moeda->casasDecimais, ".", "");
                $contaCorrenteBtc->autorizada = 1;
                
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                $contaCorrenteBtcRn->salvar($contaCorrenteBtc);
                
                $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
                $contaCorrenteEmpresaBtc->id = 0;
                $contaCorrenteEmpresaBtc->bloqueado = 1;
                $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteEmpresaBtc->descricao = "Retirada de investimento: Cliente {$cliente->nome}, registro {$dados["id"]}";
                $contaCorrenteEmpresaBtc->idMoeda = $moeda->id;
                $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
                $contaCorrenteEmpresaBtc->transferencia = 0;
                $contaCorrenteEmpresaBtc->valor = $saldo;
                $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn();
                $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
                
            }
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($ex);
        }
    }
    
    public function getClientesComResgateSolicitado() {
        $dataAtual = date("Y-m-d");
        $query = " SELECT c.*, SUM(cf.volume_depositado) AS saldo, cf.id_moeda "
                . " FROM cofre cf "
                . " INNER JOIN clientes c ON (c.id= cf.id_cliente) "
                . " WHERE "
                . " (cf.data_provisao_saque <= '{$dataAtual} 23:59:59' OR cf.data_expiracao_contrato <= '{$dataAtual} 23:59:59') AND cf.sacado < 1"
                . " GROUP BY "
                . " cf.id,"
                . " cf.id_moeda "
                . " ORDER BY c.id ";
        
        $lista = Array();        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            $saldo = $dados["saldo"];
            $moeda = new Moeda(Array("id" => $dados["id_moeda"]));
            
            $lista[] = Array("cliente" => $cliente, "saldo" => number_format($saldo, 25, ".", ""), "moeda" => $moeda);
        }
        
        return $lista;
    }
    
    public function cobrarTaxaTodosOsClientes() {
        $dataAtual = date("Y-m-d");
        $query = " SELECT DISTINCT(c.id), c.*, cf.id_moeda FROM cofre cf INNER JOIN clientes c ON (cf.id_cliente = c.id) WHERE cf.sacado = 0 AND cf.saque_solicitado < 1 AND (data_ultima_cobranca_taxa IS NULL OR data_ultima_cobranca_taxa < '{$dataAtual} 00:00:00') ";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            
            $moeda = new Moeda(Array("id" => $dados["id_moeda"]));
            
            $this->cobrarTaxaCofre($cliente, $moeda);
        }
    }
    
    
    public function calcularSaldo(Cliente $cliente, Moeda $moeda) {
        
        try {
            $moedaRn = new MoedaRn();
            $moedaRn->carregar($moeda);
        } catch (\Exception $ex) {
            throw new \Exception("Moeda inválida");
        }
        
        $query = "SELECT SUM(volume_depositado) AS depositado, SUM(volume_cobrado_taxa) AS rendimento, SUM(volume_pago_rendimento) AS recebido FROM cofre WHERE id_moeda = {$moeda->id} AND id_cliente = {$cliente->id} AND sacado = 0";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $saldo = 0;
        $rendimento = 0;
        $recebido = 0;
        
        foreach ($result as $dados) {
            $saldo = $dados["depositado"];
            $rendimento = $dados["rendimento"];
            $recebido = $dados["recebido"];
        }
        return Array(
                "depositados" => number_format($saldo, $moeda->casasDecimais, ".", ""), 
                "rendimentos" => number_format($rendimento, $moeda->casasDecimais, ".", ""),
                "recebido" => number_format($recebido, $moeda->casasDecimais, ".", "")               
            );
    }
    
    public function cobrarTaxaCofre(Cliente $cliente, Moeda $moeda) {
        $this->conexao->adapter->iniciar();
        try {
            
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida");
            }
            
            $dataAtual = date("Y-m-d");
            $query = " SELECT * FROM cofre "
                    . " WHERE sacado = 0 AND saque_solicitado < 1 AND id_moeda = {$moeda->id} AND id_cliente = {$cliente->id} AND (data_ultima_cobranca_taxa IS NULL OR data_ultima_cobranca_taxa < '{$dataAtual} 00:00:00') "
                    . " ORDER BY data_entrada ";

            $result = $this->conexao->adapter->query($query)->execute();

            $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);

            foreach ($result as $dados) {
                $saldo = number_format($dados["volume_depositado"], 8, ".", "");
                
                if ($saldo > 0) {
                    
                    $pagar = true;
                    
                    $dataUltimaCobranca = new \Utils\Data(substr(($dados["data_ultima_cobranca_taxa"] != null ? $dados["data_ultima_cobranca_taxa"] : $dados["data_entrada"]), 0, 10) . " 00:00:00");
                    if ($dados["data_ultima_cobranca_taxa"] == null) {
                        $dataReferencia = new \Utils\Data(substr($dados["data_entrada"], 0, 10) . " 23:59:59");
                        $dataReferencia->somar(0, 0, 1, 0, 0, 1);
                        
                        if ($dataReferencia->menor(new \Utils\Data($dataAtual . " 00:00:00"))) {
                            $dataUltimaCobranca = new \Utils\Data(substr($dados["data_entrada"], 0, 10) . " 00:00:00");
                            $dataUltimaCobranca->somar(0, 0, 1);
                        } else {
                            $pagar = false;
                        }
                        
                    }
                    
                    if ($pagar) {
                        $dtAtual = new \Utils\Data($dataAtual . " 00:00:00");

                        $diasSemCobranca = $dtAtual->diferenca($dataUltimaCobranca);

                        $diasMes = date("t");
                        $taxa = $dados["taxa"];

                        $taxa = number_format(($taxa / $diasMes / 100), 10, ".", "");

                        $taxaCobrada = number_format($taxa * $saldo, $moeda->casasDecimais, ".", "");
                        

                        $taxaCobrada = number_format(($diasSemCobranca->days * $taxaCobrada), $moeda->casasDecimais, ".", "");

                        if ($taxaCobrada > 0) {
                            $volumeTaxaTotalCobrada = number_format(($dados["volume_cobrado_taxa"] + $taxaCobrada), $moeda->casasDecimais, ".", "");
                            $cofre = new Cofre(Array("id" => $dados["id"]));
                            $this->conexao->carregar($cofre);
                            $saldoNaoPago = number_format(($cofre->volumeCobradoTaxa - $cofre->volumePagoRendimento + $taxaCobrada), $moeda->casasDecimais, ".", "");

                            
                            $contaCorrenteBtc = new ContaCorrenteBtc();
                            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                            $contaCorrenteBtc->descricao = "Rendimento investimento {$dados["id"]} ";
                            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
                            $contaCorrenteBtc->enderecoBitcoin = "";
                            $contaCorrenteBtc->executada = 1;
                            $contaCorrenteBtc->hash = null;
                            $contaCorrenteBtc->id = 0;
                            $contaCorrenteBtc->origem = 3;
                            $contaCorrenteBtc->idCliente = $cliente->id;
                            $contaCorrenteBtc->idMoeda = $moeda->id;
                            $contaCorrenteBtc->orderBook  = 0;
                            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                            $contaCorrenteBtc->transferencia = 0;
                            $contaCorrenteBtc->valor = number_format($saldoNaoPago, $moeda->casasDecimais, ".", "");
                            $contaCorrenteBtc->autorizada = 1;

                            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                            $contaCorrenteBtcRn->salvar($contaCorrenteBtc);

                            $contaCorrenteEmpresaBtc = new ContaCorrenteBtcEmpresa();
                            $contaCorrenteEmpresaBtc->id = 0;
                            $contaCorrenteEmpresaBtc->bloqueado = 1;
                            $contaCorrenteEmpresaBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                            $contaCorrenteEmpresaBtc->descricao = "Rendimento de investimento: Cliente {$cliente->nome}, registro {$dados["id"]}";
                            $contaCorrenteEmpresaBtc->idMoeda = $moeda->id;
                            $contaCorrenteEmpresaBtc->tipo = \Utils\Constantes::SAIDA;
                            $contaCorrenteEmpresaBtc->transferencia = 0;
                            $contaCorrenteEmpresaBtc->valor = number_format($saldoNaoPago, $moeda->casasDecimais, ".", "");;
                            $contaCorrenteEmpresaBtcRn = new ContaCorrenteBtcEmpresaRn();
                            $contaCorrenteEmpresaBtcRn->salvar($contaCorrenteEmpresaBtc, NULL);
                            
                            $this->conexao->update(
                                    Array(
                                        "data_ultima_cobranca_taxa" => date("Y-m-d H:i:s"),
                                        "volume_cobrado_taxa" => $volumeTaxaTotalCobrada
                                    ), 
                                    Array(
                                        "id" => $dados["id"]
                                    )
                                );


                            $this->conexao->update(
                                    Array(
                                        "volume_pago_rendimento" => number_format(($cofre->volumePagoRendimento + $saldoNaoPago), $moeda->casasDecimais, ".", "")
                                    ), 
                                    Array(
                                        "id" => $dados["id"]
                                    )
                                );

                        }
                    }
                }

            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    public function filtrarSolicitacoesSaque(Cliente $cliente, Moeda $moeda) {
                
        $query = "SELECT "
                . "SUM(volume_depositado + volume_cobrado_taxa) AS saldo, "
                . "data_solicitacao_saque, "
                . "data_provisao_saque, "
                . "id_moeda "
                . "FROM cofre "
                . "WHERE id_cliente = {$cliente->id} AND saque_solicitado > 0 AND sacado < 1 "
                . " GROUP BY data_solicitacao_saque, data_provisao_saque, id_moeda"
                . " ORDER BY data_provisao_saque;";
                
        $result = $this->conexao->adapter->query($query)->execute();
        return $result;
        
    }
    
    public function somaSaldoInvestido() {
        $query = "SELECT "
                . "id_moeda, sum(volume_cobrado_taxa) + sum(volume_depositado) as volume_total "
                . "FROM cofre "
                . "WHERE id_moeda = 2 AND sacado = 0 OR id_moeda = 4 AND sacado = 0 OR id_moeda = 7 AND sacado = 0 "
                . "GROUP BY id_moeda; ";
                
        $result = $this->conexao->adapter->query($query)->execute();

        return $result;        
    }
    
    public function qtdInvestidoEmpresa() {
        $query = "SELECT "
                . "COUNT(*) as total, SUM(volume_depositado) as volumeDepositado "
                . "FROM cofre "
                . "WHERE id_moeda = 2 AND sacado = 0;";
                
        $result = $this->conexao->adapter->query($query)->execute();

        return $result;        
    }
    
    public function qtdRendimentosEmpresa() {
        $di = new \Utils\Data(date("d/m/Y H:i:s"));
        $di->subtrair(0, 12);
        $df = new \Utils\Data(date("d/m/Y H:i:s"));
        $totalRendimentos = 0;
        $query = "SELECT "
                . "SUM(volume_cobrado_taxa) as totalRendimentos "
                . "FROM cofre "
                . "WHERE id_moeda = 2 AND data_entrada BETWEEN '{$di->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$df->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}';";
                
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados){
            $totalRendimentos = $dados["totalRendimentos"];
        }
        return $totalRendimentos;        
    }
    
    public function volumeRendimentoDatas($idCliente, $dataInicial, $dataFinal){
        $cliente = "";
        $data = "";
        
        if($dataFinal != null && $dataFinal != null){
            $data = " AND data_entrada BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'";
        } else {
            $data = "";
        }
        
        if($idCliente != null){
            $cliente = " id_cliente = {$idCliente} AND ";
        } else {
            $cliente = "";
        }
        
        $query = "SELECT"
                . " YEAR(data_entrada) as ano, MONTH(data_entrada) as mes, SUM(volume_depositado) as volume, SUM(volume_cobrado_taxa) as rendimento"
                . " FROM cofre "
                . " WHERE {$cliente} id_moeda = 2 {$data} "
                . " GROUP BY ano, mes;";
                
                //exit($query);
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
       
}

?>