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
class LogContaCorrenteReaisEmpresaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogContaCorrenteReaisEmpresa());
        } else {
            $this->conexao = new GenericModel($adapter, new LogContaCorrenteReaisEmpresa());
        }
    }
    
    
    public function salvar(ContaCorrenteReaisEmpresa &$contaCorrenteReaisEmpresa, $descricao, $token = null) {
        $logContaCorrenteReaisEmpresa = new LogContaCorrenteReaisEmpresa();
        $logContaCorrenteReaisEmpresa->id = 0;
        $logContaCorrenteReaisEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $logContaCorrenteReaisEmpresa->idContaCorrenteReaisEmpresa = $contaCorrenteReaisEmpresa->id;
        
        $usuario = \Utils\Geral::getLogado();
        if ($usuario == null) {
            if (empty($token)) {
                throw new \Exception("É necessário estar logado para efetuar a operação");
            } 
            
            $tokenApiRn = new TokenApiRn($this->conexao->adapter);
            $usuario = $tokenApiRn->getUserByToken($token);
            
            if ($usuario == null) {
                throw new \Exception("É necessário estar logado para efetuar a operação");
            }
            
        }
        
        if ($usuario instanceof Usuario) {
            $logContaCorrenteReaisEmpresa->idUsuario = $usuario->id;
        } else {
            $logContaCorrenteReaisEmpresa->idCliente = $usuario->id;
        }
        
        $logContaCorrenteReaisEmpresa->descricao = $descricao;
        
        
        unset($logContaCorrenteReaisEmpresa->usuario);
        unset($logContaCorrenteReaisEmpresa->cliente);
        $this->conexao->salvar($logContaCorrenteReaisEmpresa);
        
    }
    
    public function carregar(LogContaCorrenteReaisEmpresa &$logContaCorrenteReaisEmpresa, $carregar = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($logContaCorrenteReaisEmpresa);
        }
        
        if ($carregarUsuario && $logContaCorrenteReaisEmpresa->idUsuario > 0) {
            $logContaCorrenteReaisEmpresa->usuario = new Usuario(Array("id" => $logContaCorrenteReaisEmpresa->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($logContaCorrenteReaisEmpresa->usuario);
        }
        if ($carregarCliente && $logContaCorrenteReaisEmpresa->idCliente > 0) {
            $logContaCorrenteReaisEmpresa->cliente = new Cliente(Array("id" => $logContaCorrenteReaisEmpresa->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($logContaCorrenteReaisEmpresa->cliente);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarUsuario = true, $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $logContaCorrenteReaisEmpresa) {
            $this->carregar($logContaCorrenteReaisEmpresa, false, $carregarUsuario, $carregarCliente);
            $lista[] = $logContaCorrenteReaisEmpresa;
        }
        return $lista;
    }
    
    public function excluir(LogContaCorrenteReaisEmpresa &$logContaCorrenteReaisEmpresa) {
        try {
            
            $this->conexao->excluir($logContaCorrenteReaisEmpresa);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($logContaCorrenteReaisEmpresa));
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
                . " FROM log_conta_corrente_reais_empresa lcc "
                . " {$where} "
                . " ORDER BY lcc.data DESC ";
        
                
        $lista = Array();
        $cacheUsuarios = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $logContaCorrenteReaisEmpresa = new LogContaCorrenteReaisEmpresa($dados);
            
            if (!isset($cacheUsuarios[$logContaCorrenteReaisEmpresa->idUsuario])) {
                $this->carregar($logContaCorrenteReaisEmpresa, false, true);
                $cacheUsuarios[$logContaCorrenteReaisEmpresa->idUsuario] = $logContaCorrenteReaisEmpresa->usuario;
            } else {
                $logContaCorrenteReaisEmpresa->usuario = $cacheUsuarios[$logContaCorrenteReaisEmpresa->idUsuario];
            }
            
            
            $lista[] = $logContaCorrenteReaisEmpresa;
        }
        
        return $lista;
    }
}

?>