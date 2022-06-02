<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ObservacaoClienteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
     private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ObservacaoCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new ObservacaoCliente());
        }
    }
    
    public function salvar(ObservacaoCliente &$observacaoCliente) {
        
        $usuario = \Utils\Geral::getLogado();
        
        $observacaoCliente->id = 0;
        if ($observacaoCliente->idCliente < 1) {
            throw new \Exception($this->idioma->getText("necessarioInformCliente"));
        }
        
        if ($usuario instanceof Usuario) {
            $observacaoCliente->idUsuario = $usuario->id;
        } else {
            $observacaoCliente->idUsuario = 1483296812;
        }
        
        $observacaoCliente->data = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (empty($observacaoCliente->observacoes)) {
            throw new \Exception($this->idioma->getText("conteudoObsevacoes"));
        }
        
        $observacaoCliente->observacoes = \Utils\Criptografia::encriptyPostId($observacaoCliente->observacoes);
        
        unset($observacaoCliente->cliente);
        unset($observacaoCliente->usuario);
        $this->conexao->salvar($observacaoCliente);
        
    }
    
    
    public function carregar(ObservacaoCliente &$observacaoCliente, $carregar = true, $carregarCliente = true, $carregarUsuario = true) {
        if ($carregar) {
            $this->conexao->carregar($observacaoCliente);
        }
        
        if ($carregarCliente && $observacaoCliente->idCliente > 0) {
            $observacaoCliente->cliente = new Cliente(Array("id" => $observacaoCliente->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($observacaoCliente->cliente);
        }
        
        if ($carregarUsuario && $observacaoCliente->idUsuario > 0) {
            $observacaoCliente->usuario = new Usuario(Array("id" => $observacaoCliente->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($observacaoCliente->usuario);
        }
        
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarUsuario = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $observacaoCliente) {
            $this->carregar($observacaoCliente, false, $carregarCliente, $carregarUsuario);
            $lista[] = $observacaoCliente;
        }
        
        return $lista;
    }
    
}

?>