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
class ContaBancariaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaBancaria());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaBancaria());
        }
    }
    
    public function salvar(ContaBancaria &$contaBancaria) {
        if ($contaBancaria->id > 0) {
            $aux = new ContaBancaria(Array("id" => $contaBancaria->id));
            $this->conexao->carregar($aux);
            
            $contaBancaria->idCliente = $aux->idCliente;
            $contaBancaria->ativo = $aux->ativo;
        } else {
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            
            $contaBancaria->dataCadastro = date("Y-m-d H:i:s");
            $contaBancaria->idCliente = $cliente->id;
            $contaBancaria->ativo = 1;            
            
            switch ($contaBancaria->documentoCliente) {
                case \Utils\Constantes::DOCUMENTO_CPF:
                    $contaBancaria->nomeCliente = $cliente->nome;
                    $contaBancaria->documentoCliente = $cliente->documento;                    
                    break;
                case \Utils\Constantes::DOCUMENTO_CNPJ:
                    
                    $consultaCnpjRn = new ConsultaCnpjRn();
                    $consulta = $consultaCnpjRn->getByCnpj($cliente->cnpj);
                    
                    $contaBancaria->nomeCliente = $consulta->nomeEmpresa;
                    $contaBancaria->documentoCliente = $cliente->cnpj; 
                    break;
                default:
                    throw new \Exception($this->idioma->getText("contaClienteDocumento"));
                    break;
            }
            
        }
        
        // if (empty($contaBancaria->agencia) || strlen($contaBancaria->agencia) < 4 || ($contaBancaria->agencia + 0 ) <= 0) {
        //     throw new \Exception($this->idioma->getText("necessarioInformarAgencia"));
        // }
                
        // if (empty($contaBancaria->conta) || strlen($contaBancaria->conta) < 4) {
        //     throw new \Exception($this->idioma->getText("necessarioInformarConta"));
        // }        
        
        // if ($contaBancaria->contaDigito == null) {
        //     throw new \Exception($this->idioma->getText("necessarioInformarContaDigito"));
        // }
        
        // if ($contaBancaria->nomeCliente == null) {
        //     throw new \Exception($this->idioma->getText("contaClienteNome"));
        // }
        
        if ($contaBancaria->documentoCliente == null) {
            throw new \Exception($this->idioma->getText("contaClienteDocumento"));
        }
        
        // if (!$contaBancaria->idBanco > 0) {
        //     throw new \Exception($this->idioma->getText("necessarioInformarBanco"));
        // }
        
        // $tiposConta = Array(
        //     \Utils\Constantes::CONTA_CORRENTE,
        //     \Utils\Constantes::CONTA_POUPANCA
        // );
        
        // if (!in_array($contaBancaria->tipoConta, $tiposConta)) {
        //     throw new \Exception($this->idioma->getText("tipoContaInvalido"));
        // }
        
        /*$contasBancarias = $this->listar(
                      " id_banco = '{$contaBancaria->idBanco}' AND "
                    . " agencia = '{$contaBancaria->agencia}' AND "
                    . " conta = '{$contaBancaria->conta}' AND "
                    . " tipo_conta = '{$contaBancaria->tipoConta}' AND "
                    . " conta_digito = '{$contaBancaria->contaDigito}' AND "
                    . " nome_cliente = '{$contaBancaria->nomeCliente}' AND "
                    . " documento_cliente = '{$contaBancaria->documentoCliente}' AND "
                    . " agencia_digito = '{$contaBancaria->agenciaDigito}'");
                    
        if(sizeof($contasBancarias) > 0){ 
            throw new \Exception($this->idioma->getText("bancoCadastrado"));
        }*/
        
        unset($contaBancaria->banco);
        $this->conexao->salvar($contaBancaria);
    }
    
    
    public function carregar(ContaBancaria &$contaBancaria, $carregar = true, $carregarBanco = true) {
        if ($carregar) {
            $this->conexao->carregar($contaBancaria);
        }
        
        if ($carregarBanco && $contaBancaria->idBanco > 0) {
            $bancoRn = new BancoRn();
            $contaBancaria->banco = new Banco(Array("id" => $contaBancaria->idBanco));
            $bancoRn->conexao->carregar($contaBancaria->banco);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarBanco = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $contaBancaria) {
            $this->carregar($contaBancaria, false, $carregarBanco);
            $lista[] = $contaBancaria;
        }
        return $lista;
    }
    
    public function getBancosEmUso() {
        $where = Array();
        $cliente = \Utils\Geral::getCliente();
        if ($cliente != null) { 
            $where[] = " cb.id_cliente = {$cliente->id} ";
        }
        
        $whereString = (sizeof($where) ? " WHERE " . implode( " AND ", $where) : "");
        
        $query = "SELECT DISTINCT(b.id), b.* FROM bancos b " 
                    . " INNER JOIN contas_bancarias cb ON (b.id = cb.id_banco) "
                    . " {$whereString} "
                    . " ORDER BY b.nome, b.codigo";
        //exit($query);                   
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $banco = new Banco($dados);
            $lista[] = $banco;
        }
        //exit(print_r($lista));
        return $lista;
    }
    
    
    public function alterarStatusAtivo(ContaBancaria &$contaBancaria) { 
        try {
            $this->conexao->carregar($contaBancaria);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("contaInvalida"));
        }
        
        $contaBancaria->ativo = ($contaBancaria->ativo > 0 ? 0 : 1);
        
        $this->conexao->update(Array("ativo" => $contaBancaria->ativo), Array("id" => $contaBancaria->id));
    }
}

?>