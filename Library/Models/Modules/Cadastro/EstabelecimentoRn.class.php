<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estabelecimento
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class EstabelecimentoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Estabelecimento());
        } else {
            $this->conexao = new GenericModel($adapter, new Estabelecimento());
        }
    }
    
    public function salvar(Estabelecimento &$estabelecimento) {
        
        if ($estabelecimento->id > 0) {
            $aux = new Estabelecimento(Array("id" => $estabelecimento->id));
            $this->conexao->carregar($aux);
            
            $estabelecimento->dataCriacao = $aux->dataCriacao;
            $estabelecimento->ativo = $aux->ativo;
            $estabelecimento->idCliente = $aux->idCliente;
            
            $gerarChave = true;
        
        } else {
            $logado = \Utils\Geral::getCliente();
            if ($logado == null) {
                throw new \Exception("Sessão inválida. Por favor realize login e tente novamente");
            }
            
            $estabelecimento->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $estabelecimento->ativo = 1;
            $estabelecimento->idCliente = $logado->id;
            
            $gerarChave = true;
        }
        
        if ($estabelecimento->tipoComissaoEstabelecimento == null) {
            $estabelecimento->tipoComissaoEstabelecimento = "p";
        }
        
        if (!is_numeric($estabelecimento->habilitarSaqueAutomatico)) {
            $estabelecimento->habilitarSaqueAutomatico = 0;
        }
        
        if (!is_numeric($estabelecimento->comissaoEstabelecimento)) {
            $estabelecimento->comissaoEstabelecimento = 0;
        }
        
        if (empty($estabelecimento->nome)) {
            throw new \Exception("É necessário cadastrar o nome do estabelecimento");
        }
        
        if (empty($estabelecimento->codigoCidade)) {
            throw new \Exception("É necessário informar a cidade");
        }
        
        unset($estabelecimento->cliente);
        unset($estabelecimento->cidade);
        
        
        
        $this->conexao->salvar($estabelecimento);
        if ($gerarChave) {
            $estabelecimento->chave = sha1("NC-ChavePdv-{$estabelecimento->id}-PdvChave-NC");
            $estabelecimento->chaveSandbox = sha1("SANDBOX-NC-ChavePdv-{$estabelecimento->id}-PdvChave-NC-SANDBOX");
            $this->conexao->update(
                    Array(
                        "chave" => $estabelecimento->chave,
                        "chave_sandbox" => $estabelecimento->chaveSandbox
                    ), 
                    Array(
                        "id" => $estabelecimento->id
                    )
                );
        }
        
    }
    
    public function filtrar($idCliente = null, $filtro = null) {
        
        $where = Array();
        if ($idCliente > 0) {
            $where[] = " id_cliente = {$idCliente} ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " (LOWER(nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " (LOWER(endereco) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(numero) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(complemento) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(bairro) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(cep) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(cnpj) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(cpf) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(telefone) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(telefone2) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(email) LIKE LOWER('%{$filtro}%')) "
                    . " ) ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT "
                . " * "
                . " FROM estabelecimentos "
                . " {$where} "
                . " ORDER BY nome; ";
                
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $estabelecimento = new Estabelecimento($dados);
            $this->carregar($estabelecimento, false, true, false);
            $lista[] = $estabelecimento;
        }
        return $lista;
    }
    
    public function carregar(Estabelecimento &$estabelecimento, $carregar = true, $carregarCidade = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($estabelecimento);
        } 
        
        if ($carregarCidade && !empty($estabelecimento->codigoCidade)) {
            $estabelecimento->cidade = new Cidade(Array("codigo" => $estabelecimento->codigoCidade));
            $cidadeRn = new CidadeRn();
            $cidadeRn->carregar($estabelecimento->cidade, true, true);
        }
        
        if ($carregarCliente && $estabelecimento->idCliente > 0) {
            $estabelecimento->cliente = new Cliente(Array("id" => $estabelecimento->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($estabelecimento->cliente);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarCidade = true, $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $estabelecimento) {
            $this->carregar($estabelecimento, false, $carregarCidade, $carregarCliente);
            $lista[] = $estabelecimento;
        }
        
        return $lista;
    }
    
    
    public function excluir(Estabelecimento &$estabelecimento) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            $pontoPdvRn = new PontoPdvRn();
            $pontos = $pontoPdvRn->conexao->listar("id_estabelecimento = {$estabelecimento->id}", null, null, 1);
            if (sizeof($pontos) > 0) {
                throw new \Exception("Você não pode excluir um estabelecimento com PDVs vinculados");
            }
            
            $referenciaClienteRn = new ReferenciaClienteRn();
            $clientes = $referenciaClienteRn->conexao->listar("id_estabelecimento = {$estabelecimento->id}", null, null, 1);
            if (sizeof($clientes) > 0) {
                throw new \Exception("Você não pode excluir um estabelecimento com carteiras remotas vinculadas");
            }
            
            $this->conexao->excluir($estabelecimento);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    
    public function alterarStatusAtivo(Estabelecimento &$estabelecimento) {
        
        try {
            $this->conexao->carregar($estabelecimento);
        } catch (\Exception $ex) {
            throw new \Exception("Estabelecimento não localizado no sistema ");
        }
        
        $estabelecimento->ativo = ($estabelecimento->ativo > 0 ? 0 : 1);
        
        $this->conexao->update(
                Array("ativo" => $estabelecimento->ativo), 
                Array("id" => $estabelecimento->id)
                );
        
    }
    
    
    public function getByChave($chave) {
        $result = $this->conexao->select("chave = '{$chave}' OR chave_sandbox = '{$chave}' ");
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    
    public function sacar(Estabelecimento $estabelecimento, $valor, Moeda $moeda) {
        
        try {
            $this->conexao->carregar($estabelecimento);
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
        
        if ($moeda == 2) {
            $taxaTransferenciaRede = $configuracao->taxaTransferenciaInternaBtc;
        } else {
            $taxaMoedaRn = new TaxaMoedaRn();
            $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);
            $taxaTransferenciaRede = $taxaMoeda->taxaRede;
        }
        
        
        if ($moeda->id == 2) {
            $taxaTransferenciaRemota = $cliente->taxaTransferenciaRemota;
            $taxaTransferenciaEstabelecimento = $estabelecimento->comissaoEstabelecimento;


            if ($cliente->tipoTaxaCarteiraRemota == "p") {
                $taxaTransferenciaRemota = number_format(($valor * $taxaTransferenciaRemota / 100), 8, ".", "");
            }

            if ($estabelecimento->tipoComissaoEstabelecimento == "p") {
                $taxaTransferenciaEstabelecimento = number_format(($valor * $taxaTransferenciaEstabelecimento / 100), 8, ".", "");
            }
        } else {
            $taxaTransferenciaRemota = 0;
            $taxaTransferenciaEstabelecimento = 0;
        }
        
        if ($valor <= ($taxaTransferenciaRede + $taxaTransferenciaRemota + $taxaTransferenciaEstabelecimento)) {
            throw new \Exception("O valor sacado é inferior ao valor dos encargos da transação.");
        }
        
        if ($cliente->statusResgatePdv < 1) {
            throw new \Exception("O resgate de carteiras remotas está temporariamente suspenso para sua conta");
        }
        
        if (!$moeda->id  > 0) {
            throw new \Exception("Moeda inválida");
        }
        
        try {
            $this->conexao->adapter->iniciar();
            
            $querySaldo = "SELECT SUM(cp.saldo_btc - cp.saldo_gasto_btc) AS saldo, cp.id,  cp.id_referencia_cliente "
                    . " FROM referencias_clientes rc "
                    . " INNER JOIN carteiras_pdv cp ON (rc.id = cp.id_referencia_cliente) "
                    . " WHERE "
                    . " rc.id_estabelecimento = {$estabelecimento->id} AND cp.id_moeda = {$moeda->id}";
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
            
            //$taxaCobrada = 0;
            foreach ($resultSaldo as $dadosSaldo) {
                $saldo += $dadosSaldo["saldo"]; // é feita a soma dos saldos
                $ids[] = $dadosSaldo["id"]; // guardo o id
                
                if ($dadosSaldo["saldo"] < $valorRestante) { // se o valor da carteira é inferior ao valor restante 
                    $valorRestante = number_format(($valorRestante - $dadosSaldo["saldo"]), 8, ".", "");
                    $v = 0;
                    $saldoSacado = $dadosSaldo["saldo"];
                    
                    //$taxaCobrada += $taxa;
                } else { // o valor contido na carteira é superior ao valor restante de saque
                    $v = number_format(($dadosSaldo["saldo"] - $valorRestante), 8, ".", "");
                    $saldoSacado = $valorRestante;
                    $valorRestante = 0;
                    
                    /*
                    if ($saldoSacado > $taxa) {
                        $taxaCobrada += number_format($taxa, $moeda->casasDecimais, ".", "");
                    }
                     * 
                     */
                }
                
                // armazeno a carteira de volta atualizada
                $wallets[] = Array(
                    "id" => $dadosSaldo["id"],
                    "id_referencia_cliente" => $dadosSaldo["id_referencia_cliente"],
                    "saldo" => number_format($v, $moeda->casasDecimais, ".", ""),
                    "saldoSacado" => number_format($saldoSacado, $moeda->casasDecimais, ".", "")
                );
            }
            
            //echo ("{$valorRestante} - {$saldo} - {$valor} <br><br>");
            
            if ($valorRestante > 0) {
                throw new \Exception("Saldo insuficiente");
            }
            
            // segunda verificação para se ter certeza que os registros do array ainda estão consistentes no banco de dados
            $queryVerificacaoSaldo = " SELECT SUM((saldo_btc - saldo_gasto_btc)) AS saldo FROM carteiras_pdv WHERE id IN (". implode(",", $ids)."); ";
            
            $resultVerificacaoSaldo = $this->conexao->adapter->query($queryVerificacaoSaldo)->execute();
            
            $saldoVerificacao = 0;
            if (sizeof($resultVerificacaoSaldo) > 0) {
                $dadosVerificacaoSaldo = $resultVerificacaoSaldo->current();
                $saldoVerificacao = (isset($dadosVerificacaoSaldo["saldo"]) ? $dadosVerificacaoSaldo["saldo"] : 0);
            }
            
            //exit("{$valor} > {$saldoVerificacao}");
            
            if ($valor > $saldoVerificacao) {
                throw new \Exception("Saldo insuficiente");
            }
            
            
            // atualização das carteiras
            
            $historicoTransacaoReferenciaRn = new HistoricoTransacaoReferenciaRn($this->conexao->adapter);
            
            foreach ($wallets as $carteira) {
                
                $vSacado = number_format($carteira["saldoSacado"], $moeda->casasDecimais, ".", "");
                
                $isSacado = ($carteira["saldoSacado"] > 0 ? "0" : "1");
                $queryUpdate = " UPDATE carteiras_pdv SET saldo_gasto_btc = saldo_gasto_btc + {$vSacado}, sacado = {$isSacado} WHERE id = {$carteira["id"]}; ";
                
                $this->conexao->adapter->query($queryUpdate)->execute();
                
                $historicoTransacaoReferencia = new HistoricoTransacaoReferencia();
                $historicoTransacaoReferencia->idReferenciaCliente = $carteira["id_referencia_cliente"];
                $historicoTransacaoReferencia->idCarteiraPdv = $carteira["id"];
                $historicoTransacaoReferencia->idMoeda = $moeda->id;
                $historicoTransacaoReferencia->tipo = \Utils\Constantes::SAQUE;
                $historicoTransacaoReferencia->valor = $carteira["saldoSacado"];
                $historicoTransacaoReferenciaRn->salvar($historicoTransacaoReferencia);
                
            }
            
            
            if ($valor > 0) {
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                $contaCorrenteBtc = new ContaCorrenteBtc();
                $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
                $contaCorrenteBtc->descricao = "Resgate de carteiras remotas";
                $contaCorrenteBtc->direcao = "I";
                $contaCorrenteBtc->enderecoBitcoin = null;
                $contaCorrenteBtc->hash = null;
                $contaCorrenteBtc->executada = 1;
                $contaCorrenteBtc->idMoeda = $moeda->id;
                $contaCorrenteBtc->origem = 12;
                $contaCorrenteBtc->idCliente = $cliente->id;
                $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
                $contaCorrenteBtc->transferencia = 0;
                $contaCorrenteBtc->valor = number_format(($valor), $moeda->casasDecimais, ".", "");
                $contaCorrenteBtc->valorTaxa =0;

                $contaCorrenteBtcRn->salvar($contaCorrenteBtc, null);
                
                $resgateEstabelecimento = new ResgateEstabelecimento();
                $resgateEstabelecimento->idContaCorrenteBtc = $contaCorrenteBtc->id;
                $resgateEstabelecimento->idEstabelecimento = $estabelecimento->id;
                $resgateEstabelecimento->idCliente = $cliente->id;
                $resgateEstabelecimento->idMoeda = $moeda->id;
                $resgateEstabelecimento->tipo = \Utils\Constantes::ENTRADA;
                $resgateEstabelecimentoRn = new ResgateEstabelecimentoRn($this->conexao->adapter);
                $resgateEstabelecimentoRn->salvar($resgateEstabelecimento);
                

                if ($estabelecimento->habilitarSaqueAutomatico && !(empty($estabelecimento->walletSaqueAutomatico))) {
                    
                    
                    $contaCorrenteBtcSaque = new ContaCorrenteBtc();
                    $contaCorrenteBtcSaque->data = new \Utils\Data(date("d/m/Y H:i:s"));
                    $contaCorrenteBtcSaque->descricao = "Saque automático estabelecimento {$estabelecimento->nome}";
                    $contaCorrenteBtcSaque->direcao = "E";
                    $contaCorrenteBtcSaque->enderecoBitcoin = $estabelecimento->walletSaqueAutomatico;
                    $contaCorrenteBtcSaque->hash = null;
                    $contaCorrenteBtcSaque->executada = 0;
                    $contaCorrenteBtcSaque->autorizada = 1;
                    $contaCorrenteBtcSaque->origem = 12;
                    $contaCorrenteBtcSaque->idMoeda = $moeda->id;
                    $contaCorrenteBtcSaque->idCliente = $cliente->id;
                    $contaCorrenteBtcSaque->tipo = \Utils\Constantes::SAIDA;
                    $contaCorrenteBtcSaque->transferencia = 1;
                    $contaCorrenteBtcSaque->valor = number_format(($valor - ($taxaTransferenciaRemota + $taxaTransferenciaEstabelecimento)), $moeda->casasDecimais, ".", "");
                    $contaCorrenteBtcSaque->valorTaxa = number_format($taxaTransferenciaRede, 8, ".", "");

                    $contaCorrenteBtcRn->salvar($contaCorrenteBtcSaque, null);
                    
                    $resgateEstabelecimentoSaque = new ResgateEstabelecimento();
                    $resgateEstabelecimentoSaque->idContaCorrenteBtc = $contaCorrenteBtcSaque->id;
                    $resgateEstabelecimentoSaque->idEstabelecimento = $estabelecimento->id;
                    $resgateEstabelecimentoSaque->idCliente = $cliente->id;
                    $resgateEstabelecimentoSaque->idMoeda = $moeda->id;
                    $resgateEstabelecimentoSaque->tipo = \Utils\Constantes::SAIDA;
                    $resgateEstabelecimentoRn->salvar($resgateEstabelecimentoSaque);
                    
                    
                    if ($taxaTransferenciaEstabelecimento > 0) {
                        $contaCorrenteBtcTaxaEstabelecimento = new ContaCorrenteBtc();
                        $contaCorrenteBtcTaxaEstabelecimento->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtcTaxaEstabelecimento->descricao = "Comissão de saque automatico estabelecimento {$estabelecimento->nome}";
                        $contaCorrenteBtcTaxaEstabelecimento->direcao = "I";
                        $contaCorrenteBtcTaxaEstabelecimento->enderecoBitcoin = "";
                        $contaCorrenteBtcTaxaEstabelecimento->hash = null;
                        $contaCorrenteBtcTaxaEstabelecimento->executada = 1;
                        $contaCorrenteBtcTaxaEstabelecimento->autorizada = 1;
                        $contaCorrenteBtcTaxaEstabelecimento->idMoeda = $moeda->id;
                        $contaCorrenteBtcTaxaEstabelecimento->idCliente = $cliente->id;
                        $contaCorrenteBtcTaxaEstabelecimento->tipo = \Utils\Constantes::SAIDA;
                        $contaCorrenteBtcTaxaEstabelecimento->transferencia = 0;
                        $contaCorrenteBtcTaxaEstabelecimento->valor = $taxaTransferenciaRemota;
                        $contaCorrenteBtcTaxaEstabelecimento->valorTaxa = 0;

                        $contaCorrenteBtcRn->salvar($contaCorrenteBtcTaxaEstabelecimento, null);
                    }
                    
                    
                    if ($taxaTransferenciaRemota > 0) {
                        $contaCorrenteBtcEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
                        $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
                        $contaCorrenteBtcEmpresa->id = 0;
                        $contaCorrenteBtcEmpresa->descricao = "Taxa de saque automático de estabelecimento: {$estabelecimento->nome}";
                        $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::ENTRADA;
                        $contaCorrenteBtcEmpresa->transferencia = 0;
                        $contaCorrenteBtcEmpresa->idMoeda = $moeda->id;
                        $contaCorrenteBtcEmpresa->airdrop = 0;
                        $contaCorrenteBtcEmpresa->valor= number_format(($taxaTransferenciaRemota + $taxaTransferenciaRede), $moeda->casasDecimais, ".", "");
                        $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
                        $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa);
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
    
    
    public function getClientesComEstabelecimento() {
        
        $query = " SELECT c.* "
                . " FROM clientes c "
                . " INNER JOIN estabelecimentos e ON (e.id_cliente = c.id) "
                . " ORDER BY nome;";
        
        $lista = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $cliente = new Cliente($dados);
            $lista[] = $cliente;
        }
        
        return $lista;
    }
}