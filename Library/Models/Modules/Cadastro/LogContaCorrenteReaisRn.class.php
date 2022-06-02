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
class LogContaCorrenteReaisRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogContaCorrenteReais());
        } else {
            $this->conexao = new GenericModel($adapter, new LogContaCorrenteReais());
        }
    }
    
    
    public function salvar(ContaCorrenteReais &$contaCorrenteReais, $descricao, $token = null) {
        
        $usuario = \Utils\Geral::getLogado();
        $logContaCorrenteReais = new LogContaCorrenteReais();
        $logContaCorrenteReais->id = 0;
        $logContaCorrenteReais->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $logContaCorrenteReais->idContaCorrenteReais = $contaCorrenteReais->id;

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
            $logContaCorrenteReais->idUsuario = $usuario->id;
        } else {
            $logContaCorrenteReais->idCliente = $usuario->id;
        }

        $logContaCorrenteReais->descricao = $descricao;


        unset($logContaCorrenteReais->usuario);
        unset($logContaCorrenteReais->cliente);
        $this->conexao->salvar($logContaCorrenteReais);
    }
    
    public function carregar(LogContaCorrenteReais &$logContaCorrenteReais, $carregar = true, $carregarUsuario = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($logContaCorrenteReais);
        }
        
        if ($carregarUsuario && $logContaCorrenteReais->idUsuario > 0) {
            $logContaCorrenteReais->usuario = new Usuario(Array("id" => $logContaCorrenteReais->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($logContaCorrenteReais->usuario);
        }
        if ($carregarCliente && $logContaCorrenteReais->idCliente > 0) {
            $logContaCorrenteReais->cliente = new Cliente(Array("id" => $logContaCorrenteReais->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($logContaCorrenteReais->cliente);
        }
    }
    
    public function lista($where = null, $order = null, $offset = null, $limit = null, $carregarUsuario = true, $carregarCliente = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $logContaCorrenteReais) {
            $this->carregar($logContaCorrenteReais, false, $carregarUsuario, $carregarCliente);
            $lista[] = $logContaCorrenteReais;
        }
        return $logContaCorrenteReais;
    }
    
    public function excluir(LogContaCorrenteReais &$logContaCorrenteReais) {
        try {
            
            $this->conexao->excluir($logContaCorrenteReais);
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($logContaCorrenteReais));
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
                . " FROM log_conta_corrente_reais lcc "
                . " INNER JOIN conta_corrente_reais cc ON (cc.id = lcc.id_conta_corrente_reais) "
                . " {$where} "
                . " ORDER BY lcc.data DESC ";
        
                
        $lista = Array();
        $cacheUsuarios = Array();
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $logContaCorrenteReais = new LogContaCorrenteReais($dados);
            
            if (!isset($cacheUsuarios[$logContaCorrenteReais->idUsuario])) {
                $this->carregar($logContaCorrenteReais, false, true);
                $cacheUsuarios[$logContaCorrenteReais->idUsuario] = $logContaCorrenteReais->usuario;
            } else {
                $logContaCorrenteReais->usuario = $cacheUsuarios[$logContaCorrenteReais->idUsuario];
            }
            
            
            $lista[] = $logContaCorrenteReais;
        }
        
        return $lista;
    }
}

?>