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
class LogContaCorrenteBtcRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados  $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogContaCorrenteBtc());
        } else {
            $this->conexao = new GenericModel($adapter, new LogContaCorrenteBtc());
        }
    }
    
    
    public function salvar(ContaCorrenteBtc &$contaCorrenteBtc, $descricao, $token = null) {
        $logContaCorrenteBtc = new LogContaCorrenteBtc();
        $logContaCorrenteBtc->id = 0;
        $logContaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $logContaCorrenteBtc->idContaCorrenteBtc = $contaCorrenteBtc->id;
        
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
        
        if ($usuario instanceof Usuario) {
            $logContaCorrenteBtc->idUsuario = $usuario->id;
        } else {
            $logContaCorrenteBtc->idCliente = $usuario->id;
        }
        
        $logContaCorrenteBtc->descricao = $descricao;
        
        
        unset($logContaCorrenteBtc->usuario);
        unset($logContaCorrenteBtc->cliente);
        $this->conexao->salvar($logContaCorrenteBtc);
        
    }
    
    public function carregar(LogContaCorrenteBtc &$logContaCorrenteBtc, $carregar = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($logContaCorrenteBtc);
        }
        
        if ($carregarUsuario && $logContaCorrenteBtc->idUsuario > 0) {
            $logContaCorrenteBtc->usuario = new Usuario(Array("id" => $logContaCorrenteBtc->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($logContaCorrenteBtc->usuario);
        }
        if ($carregarCliente && $logContaCorrenteBtc->idCliente > 0) {
            $logContaCorrenteBtc->cliente = new Cliente(Array("id" => $logContaCorrenteBtc->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($logContaCorrenteBtc->cliente);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarUsuario = true, $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $logContaCorrenteBtc) {
            $this->carregar($logContaCorrenteBtc, false, $carregarUsuario, $carregarCliente);
            $lista[] = $logContaCorrenteBtc;
        }
        return $logContaCorrenteBtc;
    }
    
    public function excluir(LogContaCorrenteBtc &$logContaCorrenteBtc) {
        try {
            
            $this->conexao->excluir($logContaCorrenteBtc);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($logContaCorrenteBtc));
        }
    }
    
    
    public function filtrar($idCliente, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $filtro = null, $idUsuario = 0) {
        
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception("A data inicial deve ser informada");
        }
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception("A data final deve ser informada");
        }
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception("A data inicial não pode ser maior que a data final");
        }
        
        if (!$idCliente > 0) {
            throw new \Exception("A identificação do cliente deve ser informada");
        }
        
        $where = Array();
        
        $where[] = " cc.id_cliente = {$idCliente} ";
        $where[] = " lcc.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        
        if (!empty($filtro)) {
            $where[] = " ( (LOWER(lcc.descricao) LIKE LOWER('%{$filtro}%')) OR (CAST(cc.id AS CHAR(200)) LIKE '%{$filtro}%') ) ";
        }
        
        if ($idUsuario > 0) {
            $where[] = " lcc.id_usuario = {$idUsuario} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT lcc.* "
                . " FROM log_conta_corrente_btc lcc "
                . " INNER JOIN conta_corrente_btc cc ON (cc.id = lcc.id_conta_corrente_btc) "
                . " {$where} "
                . " ORDER BY lcc.data DESC ";
           
        $lista = Array();
        $cacheUsuarios = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $logContaCorrenteBtc = new LogContaCorrenteBtc($dados);
            
            if (!isset($cacheUsuarios[$logContaCorrenteBtc->idUsuario])) {
                $this->carregar($logContaCorrenteBtc, false, true);
                $cacheUsuarios[$logContaCorrenteBtc->idUsuario] = $logContaCorrenteBtc->usuario;
            } else {
                $logContaCorrenteBtc->usuario = $cacheUsuarios[$logContaCorrenteBtc->idUsuario];
            }
            
            
            $lista[] = $logContaCorrenteBtc;
        }
        
        return $lista;
    }
}

?>