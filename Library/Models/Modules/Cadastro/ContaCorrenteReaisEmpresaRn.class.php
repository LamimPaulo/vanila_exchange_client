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
class ContaCorrenteReaisEmpresaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaCorrenteReaisEmpresa());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaCorrenteReaisEmpresa());
        }
    }
    
    
    public function salvar(ContaCorrenteReaisEmpresa &$contaCorrenteReaisEmpresa, $token = null) {
        $novo = ($contaCorrenteReaisEmpresa->id <= 0);
        try {
            $this->conexao->adapter->iniciar();
            if ($contaCorrenteReaisEmpresa->id > 0) {
                $aux = new ContaCorrenteReaisEmpresa(Array("id" => $contaCorrenteReaisEmpresa->id));
                $this->conexao->carregar($aux);
                $contaCorrenteReaisEmpresa->dataCadastro = $aux->dataCadastro;
                $contaCorrenteReaisEmpresa->bloqueado = $aux->bloqueado;
                
                
                if ($contaCorrenteReaisEmpresa->bloqueado > 0) {
                    throw new \Exception("Você não pode alterar esse tipo de registro");
                }
            } else {
                $contaCorrenteReaisEmpresa->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                
                if (!is_numeric($contaCorrenteReaisEmpresa->bloqueado)) {
                    $contaCorrenteReaisEmpresa->bloqueado = 1;
                }
            }

            if (!$contaCorrenteReaisEmpresa->transferencia > 0) {
                $contaCorrenteReaisEmpresa->transferencia = 0;
            }
            
            
            if (empty($contaCorrenteReaisEmpresa->descricao)) {
                throw new \Exception("É necessário informar a descrição do lançamento");
            }

            if (!isset($contaCorrenteReaisEmpresa->data->data) || $contaCorrenteReaisEmpresa->data->data == null) {
                throw new \Exception("É necessário informar a data do lançamento");
            }

            if ($contaCorrenteReaisEmpresa->tipo != \Utils\Constantes::ENTRADA && $contaCorrenteReaisEmpresa->tipo != \Utils\Constantes::SAIDA) {
                throw new \Exception("Tipo de movimento inválido");
            }

            if (!$contaCorrenteReaisEmpresa->valor > 0) {
                throw new Exception("O valor precisa ser maior que zero");
            }

           
            $this->conexao->salvar($contaCorrenteReaisEmpresa);
            
            if ($novo) {
                $descricao = "Cadastrou a conta corrente {$contaCorrenteReaisEmpresa->id}.";
            } else {
                $descricao = "Alterou a conta corrente {$contaCorrenteReaisEmpresa->id}.";
            }
            
            if (\Utils\Geral::isLogado()) {
                $logContaCorrenteReaisEmpresaRn = new LogContaCorrenteReaisEmpresaRn($this->conexao->adapter);
                $logContaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa, $descricao, $token);
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
        
    }
    
    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = 'T', $filtro = null, $transferencia = "T", $limit = "T") {
        
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
        
        $where[] = " c.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
    
        if ($tipo != "T") {
            $where[] = " c.tipo = '{$tipo}' ";
        }
        
        
        if ($transferencia != "T") {
            if ($transferencia == "S") { 
                $where[] = " c.transferencia = 1 ";
            } else {
                $where[] = " c.transferencia = 0 ";
            }
        }
        
        
        if ($transferencia != "T") {
            if ($transferencia == "S") { 
                $where[] = " c.transferencia = 1 ";
            } else {
                $where[] = " c.transferencia = 0 ";
            }
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : " ");
        $limit = ($limit != "T" ? " LIMIT {$limit} " : "");
        
        $query = "SELECT c.*, c.tipo FROM conta_corrente_reais_empresa c {$where} ORDER BY data {$limit};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa($dados);
            $lista[] = $contaCorrenteReaisEmpresa;
        }
        
        return $lista;
    }
    
    
    public function calcularSaldoConta() {
        $query = " SELECT SUM(valor) AS valor, tipo FROM conta_corrente_reais_empresa GROUP BY tipo;";
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
    
    public function carregar(ContaCorrenteReaisEmpresa &$contaCorrenteReaisEmpresa, $carregar = true) {
        if ($carregar) {
            $this->conexao->carregar($contaCorrenteReaisEmpresa);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $contaCorrenteReaisEmpresa) {
            $this->carregar($contaCorrenteReaisEmpresa, false);
            $lista[] = $contaCorrenteReaisEmpresa;
        }
        return $contaCorrenteReaisEmpresa;
    }
    
    public function excluir(ContaCorrenteReaisEmpresa &$contaCorrenteReaisEmpresa) {
        try {
            
            $logContaCorrenteReaisEmpresaRn = new LogContaCorrenteReaisEmpresaRn($this->conexao->adapter);
            
            $logContaCorrenteReaisEmpresaRn->conexao->delete("id_conta_corrente_reais_empresa = {$contaCorrenteReaisEmpresa->id}");
            $this->conexao->excluir($contaCorrenteReaisEmpresa);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    public function transferir(Cliente $cliente, $valor, $descricao) {
        try {
            $this->conexao->adapter->iniciar();
            
            $clienteRn = new ClienteRn($this->conexao->adapter);
            try{
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("O cliente precisa ser identificado");
            }
            
            if (!$valor > 0) {
                throw new \Exception("O valor precisa ser maior que zero");
            }
            
            $contaCorrenteReaisEmpresa = new ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = $descricao;
            $contaCorrenteReaisEmpresa->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReaisEmpresa->valor = $valor;
            $contaCorrenteReaisEmpresa->transferencia = 1;
            
            $this->salvar($contaCorrenteReaisEmpresa);
            
            $saldo = $this->calcularSaldoConta();
            
            if ($saldo < 0) {
                $this->conexao->excluir($contaCorrenteReaisEmpresa);
                throw new \Exception("Você não tem saldo suficiente para efetuar essa operação");
            }
            
            $contaCorrenteReais = new ContaCorrenteReais();
            $contaCorrenteReais->id = 0;
            $contaCorrenteReais->idCliente = $cliente->id;
            $contaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReais->descricao = "Depósito de reais.";
            $contaCorrenteReais->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReais->valor = ($valor);
            $contaCorrenteReais->transferencia = 1;
            $contaCorrenteReais->idClienteDestino = null;
            $contaCorrenteReais->valorTaxa = 0;
            
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter);
            $contaCorrenteReaisRn->salvar($contaCorrenteReais);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function calcularSaldoSistema() {
        
        $query = "select cc.tipo, SUM(cc.valor) AS valor FROM conta_corrente_reais_empresa cc GROUP BY cc.tipo";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $entrada = 0;
        $saida = 0;
        
        foreach ($result as $d) {
            if ($d["tipo"] == \Utils\Constantes::ENTRADA) {
                $entrada = $d["valor"];
            } else {
                $saida = $d["valor"];
            }
        }
        
        return Array("moeda" => "real", "id" => "1", "entrada" => $entrada, "saida" => $saida, "saldo" => number_format(($entrada - $saida), 2, ".", ""));
    }
    
}

?>
