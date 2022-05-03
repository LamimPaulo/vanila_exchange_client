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
class LogContaCorrenteBtcEmpresaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogContaCorrenteBtcEmpresa());
        } else {
            $this->conexao = new GenericModel($adapter, new LogContaCorrenteBtcEmpresa());
        }
    }
    
    
    public function salvar(ContaCorrenteBtcEmpresa &$contaCorrenteBtcEmpresa, $descricao, $token = null) {
        $logContaCorrenteBtcEmpresa = new LogContaCorrenteBtcEmpresa();
        $logContaCorrenteBtcEmpresa->id = 0;
        $logContaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $logContaCorrenteBtcEmpresa->idContaCorrenteBtcEmpresa = $contaCorrenteBtcEmpresa->id;
        
        $usuario = \Utils\Geral::getLogado();
        if ($usuario == null) {
            if (empty($token)) {
                throw new \Exception("É necessário estar logado para efetuar a operação");
            } 
            
            $tokenApiRn = new TokenApiRn();
            $usuario = $tokenApiRn->getUserByToken($token);
            
            if ($usuario == null) {
                throw new \Exception("É necessário estar logado para efetuar a operação");
            }
            
        }
        
        $logContaCorrenteBtcEmpresa->descricao = $descricao;
        
        
        unset($logContaCorrenteBtcEmpresa->usuario);
        unset($logContaCorrenteBtcEmpresa->cliente);
        $this->conexao->salvar($logContaCorrenteBtcEmpresa);
        
    }
    
    public function carregar(LogContaCorrenteBtcEmpresa &$logContaCorrenteBtcEmpresa, $carregar = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($logContaCorrenteBtcEmpresa);
        }
        
        if ($carregarUsuario && $logContaCorrenteBtcEmpresa->idUsuario > 0) {
            $logContaCorrenteBtcEmpresa->usuario = new Usuario(Array("id" => $logContaCorrenteBtcEmpresa->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($logContaCorrenteBtcEmpresa->usuario);
        }
        if ($carregarCliente && $logContaCorrenteBtcEmpresa->idCliente > 0) {
            $logContaCorrenteBtcEmpresa->cliente = new Cliente(Array("id" => $logContaCorrenteBtcEmpresa->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($logContaCorrenteBtcEmpresa->cliente);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarUsuario = true, $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $logContaCorrenteBtcEmpresa) {
            $this->carregar($logContaCorrenteBtcEmpresa, false, $carregarUsuario, $carregarCliente);
            $lista[] = $logContaCorrenteBtcEmpresa;
        }
        return $lista;
    }
    
    public function excluir(LogContaCorrenteBtcEmpresa &$logContaCorrenteBtcEmpresa) {
        try {
            
            $this->conexao->excluir($logContaCorrenteBtcEmpresa);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($logContaCorrenteBtcEmpresa));
        }
    }
    
    
    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $filtro = null, $idUsuario = 0) {
        
        
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
        
        $where[] = " lcc.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        
        if (!empty($filtro)) {
            $where[] = " ( (LOWER(lcc.descricao) LIKE LOWER('%{$filtro}%')) OR (CAST(cc.id AS CHAR(200)) LIKE '%{$filtro}%') ) ";
        }
        
        if ($idUsuario > 0) {
            $where[] = " lcc.id_usuario = {$idUsuario} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT lcc.* "
                . " FROM log_conta_corrente_btc_empresa lcc "
                . " {$where} "
                . " ORDER BY lcc.data DESC ";
           
        $lista = Array();
        $cacheUsuarios = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $logContaCorrenteBtcEmpresa = new LogContaCorrenteBtcEmpresa($dados);
            
            if (!isset($cacheUsuarios[$logContaCorrenteBtcEmpresa->idUsuario])) {
                $this->carregar($logContaCorrenteBtcEmpresa, false, true);
                $cacheUsuarios[$logContaCorrenteBtcEmpresa->idUsuario] = $logContaCorrenteBtcEmpresa->usuario;
            } else {
                $logContaCorrenteBtcEmpresa->usuario = $cacheUsuarios[$logContaCorrenteBtcEmpresa->idUsuario];
            }
            
            
            $lista[] = $logContaCorrenteBtcEmpresa;
        }
        
        return $lista;
    }
}

?>