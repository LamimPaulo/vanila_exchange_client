<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class ClienteConvidadoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteConvidado());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteConvidado());
        }
    }
    
    public function salvar(ClienteConvidado &$clienteConvidado) {
        
        $clienteConvidado->id = 0;
        $clienteConvidado->cadastrou = 0;
        $clienteConvidado->comissao = 0;
        $clienteConvidado->dataConvite = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (empty($clienteConvidado->email)) {
            throw new \Exception($this->idioma->getText("oEmailDeveInformado"));
        }
        
        if (!$clienteConvidado->idCliente > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        
        $result = $this->conexao->listar("email = '{$clienteConvidado->email}' AND id_cliente  = {$clienteConvidado->idCliente}");
        if (sizeof($result) <= 0) {
            $clienteConvidado->qtdEnvios = 1;
            
            $this->conexao->salvar($clienteConvidado);
        } else {
            $clienteConvidado = $result->current();
            $this->conexao->update(Array("qtd_envios" => $clienteConvidado->qtdEnvios +1), Array("id" => $clienteConvidado->id));
        }
        
    }
    
    public function marcarCadastrado(Cliente $cliente, $email) {
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        if (empty($email)) {
            throw new \Exception($this->idioma->getText("oEmailDeveInformado"));
        }
        
        $this->conexao->update(Array("cadastrou" => 1), Array("id_cliente" => $cliente->id, "email" => $email));
        
    }
    
    
    public function setComissao(Cliente $cliente, $email, $comissao, $movimento) {
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        if (empty($email)) {
            throw new \Exception($this->idioma->getText("oEmailDeveInformado"));
        }
        
        if (empty($movimento)) {
            throw new \Exception($this->idioma->getText("movimentoInvalido"));
        }
        
        $this->conexao->update(Array("comissao" => number_format($comissao, 2, ".",  ""), "movimento" => $movimento), Array("id_cliente" => $cliente->id, "email" => $email));
        
    }
    
    
    public function get(Cliente $cliente, $email) {
        if (!$cliente->id > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        if (empty($email)) {
            throw new \Exception($this->idioma->getText("oEmailDeveInformado"));
        }
        
        $result = $this->conexao->listar("email = '{$email}' AND id_cliente  = {$cliente->id}");
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function getQuantidadeConvitesEnviados(Cliente $cliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null) {
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " cc.data_convite BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if ($cliente != null && $cliente->id > 0) { 
            $where[] = " cc.id_cliente = {$cliente->id} ";
        }
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode( " AND ", $where) : "");
        $query = " SELECT COUNT(*) AS qtd FROM clientes_convidados cc {$whereString};";
        $qtd = 0;
        $dados = $this->conexao->adapter->query($query)->execute();
        foreach ($dados as $d) {
            $qtd = $d["qtd"];
        }
        return $qtd;
    }
    
    public function filtrar(Cliente $cliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $filtro = null) {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " cc.data_convite BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if ($cliente != null && $cliente->id > 0) { 
            $where[] = " cc.id_cliente = {$cliente->id} ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( LOWER(cc.email) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode( " AND ", $where) : "");
        $query = " SELECT "
                . " DISTINCT(cc.id), cc.* "
                . " FROM clientes_convidados cc "
                . " LEFT JOIN clientes c ON (cc.email = c.email) "
                . " {$whereString}; ";
                
                
        $listar = Array();
        $dados = $this->conexao->adapter->query($query)->execute();
        foreach ($dados as $d) {
            $clienteConvite = new ClienteConvidado($d);
            $listar[] = $clienteConvite;
        }
        return $listar;
        
    }
    
    public function getComissaoClienteByMoeda(Cliente $cliente){
        
        $query = "SELECT 
                1 AS id_moeda,
                SUM(valor) AS valor
                FROM conta_corrente_reais c
                WHERE id_cliente = {$cliente->id} AND origem IN (3, 4, 5)AND data_cadastro BETWEEN  '2018-01-01 00:00:00' AND now() 

                UNION

                SELECT 
                c.id_moeda,
                SUM(valor) AS valor
                FROM conta_corrente_btc c
                WHERE id_cliente = {$cliente->id} AND origem IN (2)AND data_cadastro BETWEEN '2018-01-01 00:00:00' AND now()
                GROUP BY id_moeda;";
              
        
        $dados = $this->conexao->adapter->query($query)->execute();
        
        return $dados;
        
    }
    
}

?>