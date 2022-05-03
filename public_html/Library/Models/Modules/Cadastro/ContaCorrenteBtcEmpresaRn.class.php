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
class ContaCorrenteBtcEmpresaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaCorrenteBtcEmpresa());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaCorrenteBtcEmpresa());
        }
    }
    
    public function gerarContaCorrente(ContaCorrenteBtcEmpresa &$contaCorrenteBtcEmpresa, $token = null) {
    
        $novo = ($contaCorrenteBtcEmpresa->id <= 0);
        
            if ($contaCorrenteBtcEmpresa->id > 0) {
                $aux = new ContaCorrenteBtcEmpresa(Array("id" => $contaCorrenteBtcEmpresa->id));
                $this->conexao->carregar($aux);
                $contaCorrenteBtcEmpresa->dataCadastro = $aux->dataCadastro;
                $contaCorrenteBtcEmpresa->bloqueado = $aux->bloqueado;
                
                if ($contaCorrenteBtcEmpresa->bloqueado > 0) {
                    throw new \Exception("Você não pode alterar esse tipo de registro");
                }
                
            } else {
                $contaCorrenteBtcEmpresa->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                
                if (!is_numeric($contaCorrenteBtcEmpresa->bloqueado)) {
                    $contaCorrenteBtcEmpresa->bloqueado = 1;
                }
            }

            
            if (!is_numeric($contaCorrenteBtcEmpresa->airdrop)) {
                $contaCorrenteBtcEmpresa->airdrop = 0;
            }
            
            if (!$contaCorrenteBtcEmpresa->transferencia > 0) {
                $contaCorrenteBtcEmpresa->transferencia = 0;
            }
            
            if (!$contaCorrenteBtcEmpresa->idMoeda) {
                throw new \Exception("Moeda inválida");
            }
            
            if (empty($contaCorrenteBtcEmpresa->descricao)) {
                throw new \Exception("É necessário informar a descrição do lançamento");
            }

            if (!isset($contaCorrenteBtcEmpresa->data->data) || $contaCorrenteBtcEmpresa->data->data == null) {
                throw new \Exception("É necessário informar a data do lançamento");
            }
            
            if ($contaCorrenteBtcEmpresa->tipo != \Utils\Constantes::ENTRADA && $contaCorrenteBtcEmpresa->tipo != \Utils\Constantes::SAIDA) {
                throw new \Exception("Tipo de movimento inválido");
            }

            if (!$contaCorrenteBtcEmpresa->valor > 0) {
                throw new Exception("O valor precisa ser maior que zero");
            }

            
            unset($contaCorrenteBtcEmpresa->moeda);
            $this->conexao->salvar($contaCorrenteBtcEmpresa);
            if ($novo) {
                $descricao = "Cadastrou a conta corrente {$contaCorrenteBtcEmpresa->id}.";
            } else {
                $descricao = "Alterou a conta corrente {$contaCorrenteBtcEmpresa->id}.";
            }
            
            //$logContaCorrenteBtcEmpresaRn = new LogContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            //$logContaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa, $descricao, $token);
            
    }
    
    public function salvar(ContaCorrenteBtcEmpresa &$contaCorrenteBtcEmpresa, $token = null) {
        $novo = ($contaCorrenteBtcEmpresa->id <= 0);
        try {
            $this->conexao->adapter->iniciar();
            
            $this->gerarContaCorrente($contaCorrenteBtcEmpresa, $token);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
        
    }
    
    
    
    
    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = 'T', $filtro = null, $idMoeda = 2, $transferencia = "T", $limit = "T") {
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception("A data inicial deve ser informada");
        }
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception("A data final deve ser informada");
        }
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception("A data inicial não pode ser maior que a data final");
        }
        
        $where = Array();
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(c.descricao) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( CAST(c.id AS CHAR(200) ) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        if ($idMoeda > 0) {
            $where[] = " c.id_moeda = {$idMoeda} ";
        }
        
        $where[] = " c.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
    
        
        if ($tipo != "T") {
            $where[] = " c.tipo = '{$tipo}' ";
        }
        
        
        if ($transferencia != "T") {
            if($transferencia == "S") {
                $where[] = " c.transferencia = 1 ";
            } else {
                $where[] = " c.transferencia = 0 ";
            }
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : " ");
        $limit = ($limit != "T" ? " limit {$limit}" : "");
        $query = "SELECT c.*, c.tipo FROM conta_corrente_btc_empresa c {$where} ORDER BY data {$limit};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa($dados);
            $this->carregar($contaCorrenteBtcEmpresa, false, true);
            $lista[] = $contaCorrenteBtcEmpresa;
        }
        
        return $lista;
    }
    
    public function calcularSaldoConta($idMoeda = 2, $airdrop = false) {
        
        if (!$idMoeda > 0) {
            throw new \Exception("Moeda inválida");
        }
        
        $sWhere = "";
        if (!$airdrop) {
            $sWhere = " AND airdrop < 1 ";
        }
        
        $query = " SELECT SUM(valor) AS valor, tipo FROM conta_corrente_btc_empresa WHERE id_moeda = {$idMoeda} {$sWhere} GROUP BY tipo;";
        $entrada = 0;
        $saida = 0;
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                $entrada = $dados["valor"];
            } else {
                $saida = $dados["valor"];
            }
        }
        
        return ($entrada - $saida);
    }
    
    
    public function calcularSaldoContaAirdrop($idMoeda = 2) {
        
        if (!$idMoeda > 0) {
            throw new \Exception("Moeda inválida");
        }
        
        $query = " SELECT SUM(valor) AS valor, tipo FROM conta_corrente_btc_empresa WHERE id_moeda = {$idMoeda} AND airdrop = 1 GROUP BY tipo;";
        $entrada = 0;
        $saida = 0;
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            if ($dados["tipo"] == \Utils\Constantes::ENTRADA) {
                $entrada = $dados["valor"];
            } else {
                $saida = $dados["valor"];
            }
        }
        
        return ($entrada - $saida);
    }
    
    public function carregar(ContaCorrenteBtcEmpresa &$contaCorrenteBtcEmpresa, $carregar = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($contaCorrenteBtcEmpresa);
        }
        
        if ($carregarMoeda && $contaCorrenteBtcEmpresa->idMoeda > 0) {
            $contaCorrenteBtcEmpresa->moeda = new Moeda(Array("id" => $contaCorrenteBtcEmpresa->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($contaCorrenteBtcEmpresa->moeda);
        }
        
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $contaCorrenteBtcEmpresa) {
            $this->carregar($contaCorrenteBtcEmpresa, false, $carregarMoeda);
            $lista[] = $contaCorrenteBtcEmpresa;
        }
        return $contaCorrenteBtcEmpresa;
    }
    
    public function excluir(ContaCorrenteBtcEmpresa &$contaCorrenteBtcEmpresa) {
        try {
            $logContaCorrenteBtcEmpresaRn = new LogContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $logContaCorrenteBtcEmpresaRn->conexao->delete("id_conta_corrente_btc_empresa = {$contaCorrenteBtcEmpresa->id}");
            
            $this->conexao->excluir($contaCorrenteBtcEmpresa);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    

    public function transferir($enderecoBitcoin, $valor, $descricao, $idMoeda = 2) {
        try {
            $this->conexao->adapter->iniciar();
            $clienteRn = new ClienteRn($this->conexao->adapter);
            if (!$valor > 0) {
                throw new \Exception("O valor precisa ser maior que zero");
            }
            
            if (!$idMoeda > 0) {
                throw new \Exception("Moeda inválida");
            }
            
            $moeda = new Moeda(Array("id" => $idMoeda));
            try {
                $moedaRn = new MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida", 122);
            }

            if ($moeda->ativo < 1) {
                throw new \Exception("O comércio da moeda está suspenso", 130);
            }
            if ($moeda->statusMercado < 1) {
                throw new \Exception("O comércio da moeda está temporariamente suspenso", 123);
            }
            
            $carteiraRn = new CarteiraRn($this->conexao->adapter);
            $carteira = $carteiraRn->getByEndereco($enderecoBitcoin, 0);

            $cliente = null;
            if ($carteira != null) {
                $cliente = new Cliente(Array("id" => $carteira->idCliente));
                $clienteRn->conexao->carregar($cliente);
            } else {
                throw new \Exception("O endereço informado não pertence ao banco de endereços do sistema");
            }

            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->id = 0;
            $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresa->descricao = $descricao;
            $contaCorrenteBtcEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtcEmpresa->valor = $valor;
            $contaCorrenteBtcEmpresa->transferencia = 1;
            $contaCorrenteBtcEmpresa->idMoeda = $idMoeda;
            
            $this->salvar($contaCorrenteBtcEmpresa, null);

            
            $saldo = $this->calcularSaldoConta($idMoeda);
            //throw new \Exception("Em manutenção {$saldo}");
            if ($saldo < 0) {
                $this->excluir($contaCorrenteBtcEmpresa);
                throw new \Exception("Você não tem saldo suficiente para efetuar essa operação");
            }

            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->idCliente = $cliente->id;
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->descricao = "Depósito de {$moeda->nome}";
            $contaCorrenteBtc->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtc->valor = number_format($valor, $moeda->casasDecimais, ".", "");
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->transferencia = 1;
            $contaCorrenteBtc->idMoeda = $idMoeda;
            $contaCorrenteBtc->enderecoBitcoin = "";
            $contaCorrenteBtc->direcao = (\Utils\Constantes::TRANF_INTERNA);
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->autorizada = 1;

            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
            $contaCorrenteBtcRn->salvar($contaCorrenteBtc, null);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function calcularSaldoCurrencies() {
        
        $query = "select "
                . "m.simbolo, cc.tipo, SUM(cc.valor) AS valor, m.id "
                . "FROM conta_corrente_btc_empresa cc "
                . "INNER JOIN moedas m  ON (cc.id_moeda = m.id) "
                . "GROUP BY m.simbolo, cc.tipo, m.id";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $dados = Array();
        
        foreach ($result as $d) {
            if (!isset($dados[$d["simbolo"]])) {
                $dados[$d["simbolo"]] = Array(
                    "moeda" => $d["simbolo"],
                    "id" => $d["id"],
                    "entrada" => 0,
                    "saida" => 0,
                    "saldo" => 0
                );
            }
            
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $dados[$d["simbolo"]]["entrada"] = $d["valor"];
                $dados[$d["simbolo"]]["saldo"] = number_format(($dados[$d["simbolo"]]["entrada"] - $dados[$d["simbolo"]]["saida"]), 8, ".", "");
            } else {
                $dados[$d["simbolo"]]["saida"] = $d["valor"];
                $dados[$d["simbolo"]]["saldo"] = number_format(($dados[$d["simbolo"]]["entrada"] - $dados[$d["simbolo"]]["saida"]), 8, ".", "");
            }
        }
        
        return $dados;
    }
    
    
    
}

?>
