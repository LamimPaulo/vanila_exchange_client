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
class ComercioRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Comercio());
        } else {
            $this->conexao = new GenericModel($adapter, new Comercio());
        }
    }
    
    public function salvar(Comercio $comercio) {
        
        if ($comercio->id > 0) {
            
            $aux = new Comercio(Array("id" => $comercio->id));
            $this->conexao->carregar($aux);
            
            $comercio->ativo = ($aux->ativo);
            $comercio->idCliente = ($aux->idCliente);
            
        } else {
            $comercio->ativo = 1;
            
            $cliente = \Utils\Geral::getCliente();
            $comercio->idCliente = $cliente->id;
        }
        
        if (empty($comercio->bairro)) {
            //throw new \Exception("É necessário informar o bairro");
        }
        
        if (empty($comercio->cep)) {
            throw new \Exception($this->idioma->getText("necessarioCEP"));
        }
        
        if (empty($comercio->codigoCidade)) {
            throw new \Exception($this->idioma->getText("codigoCidadeInvalido"));
        }
        
        
        if (empty($comercio->coordenadas)) {
            throw new \Exception($this->idioma->getText("necessarioCoordenadas"));
        }
        
        
        if (empty($comercio->descricao)) {
            throw new \Exception($this->idioma->getText("necessarioDescricao"));
        }
        
        
        if (empty($comercio->endereco)) {
            throw new \Exception($this->idioma->getText("necessarioEnd"));
        }
        
        
        if (!$comercio->idCliente>0) {
            throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
        }
        
        
        if (empty($comercio->idSegmentoComercio)) {
            throw new \Exception($this->idioma->getText("necessarioSeguimentoComercio"));
        }
        
        
        if (empty($comercio->numero)) {
            throw new \Exception($this->idioma->getText("necessarioInformarNumero"));
        }
        
        unset($comercio->seguimentoComercial);
        unset($comercio->cliente);
        unset($comercio->cidade);
        $this->conexao->salvar($comercio);
    }
    
    public function filtrar($cliente = 0, $segmento = 0, $filtro = null) {
        
        $where = Array();
        
        if (!empty($filtro)) {
            $where[] = " (  "
                    . " ( LOWER(cc.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.descricao) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.cep) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.endereco) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.numero) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.bairro) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(cd.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(es.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(es.sigla) LIKE LOWER('%{$filtro}%') )  "
                    . "  ) ";
        }
        
        if ($cliente > 0) {
            $where[] = " c.id_cliente = {$cliente} ";
        }
        
        if ($segmento > 0) {
            $where[] = " c.id_segmento_comercio = {$segmento} ";
        }
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT c.*, cd.nome AS cidade, es.sigla AS estado, cc.nome AS cliente "
                . " FROM comercios c "
                . " INNER JOIN clientes AS cc ON (c.id_cliente = cc.id) "
                . " INNER JOIN cidades AS cd ON (c.codigo_cidade = cd.codigo) "
                . " INNER JOIN estados es ON (cd.id_estado = es.id) "
                . " {$whereString} "
                . " ORDER BY c.descricao ";
                
                
        $result = $this->conexao->executeSql($query);
        
        $lista = Array();
        foreach ($result as $dados) {
            $comercio = new Comercio($dados);
            $this->carregar($comercio, false, true, true, true);
            $lista[] = $comercio;
        }
        
        return $lista;
    }
    
    public function carregar(Comercio &$comercio, $carregar = true, $carregarCliente = true, $carregarCidade = true, $carregarSeguimentoComercio = true) {
        
        if ($carregar) {
            $this->conexao->carregar($comercio);
        }
        
        
        if ($carregarSeguimentoComercio && $comercio->idSegmentoComercio > 0) {
            $comercio->seguimentoComercial = new SeguimentoComercial(Array("id" => $comercio->idSegmentoComercio));
            $seguimentoComercialRn = new SeguimentoComercialRn();
            $seguimentoComercialRn->conexao->carregar($comercio->seguimentoComercial);
        }
        
        if ($carregarCliente && $comercio->idCliente > 0) {
            $comercio->cliente = new Cliente(Array("id" => $comercio->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($comercio->cliente);
        }
        
        if ($carregarCidade && !empty($comercio->codigoCidade)) {
            $comercio->cidade = new Cidade(Array("codigo" => $comercio->codigoCidade));
            $cidadeRN = new CidadeRn();
            $cidadeRN->carregar($comercio->cidade, true, true);
        }
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarCidade = true, $carregarSeguimentoComercio = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $comercio) {
            $this->carregar($comercio, false, $carregarCliente, $carregarCidade, $carregarSeguimentoComercio);
            $lista[] = $comercio;
        }
        return $lista;
    }
    
    
    public function alterarStatusAtivo(Comercio &$comercio) {
        try {
            $this->carregar($comercio, true, true, true, true);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("comercioNaoEncontrado"));
        }
        
        $comercio->ativo = ($comercio->ativo > 0 ? 0  : 1);
        $this->conexao->update(Array("ativo" => ($comercio->ativo > 0 ? "1" : "0")), Array("id" => $comercio->id));
    }
    
}

?>