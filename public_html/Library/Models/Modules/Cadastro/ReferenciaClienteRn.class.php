<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade ReferenciaCliente
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ReferenciaClienteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ReferenciaCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new ReferenciaCliente());
        }
    }
    
    public function salvar(ReferenciaCliente &$referenciaCliente) {
        
        if (empty($referenciaCliente->referencia)) {
            throw new \Exception($this->idioma->getText("referenciaPrecisaInformada"));
        }
        
        if (!$referenciaCliente->idEstabelecimento > 0) {
            throw new \Exception($this->idioma->getText("necessariosInformarIdentEstbe"));
        }
        
        try {
            $estabelecimento = new Estabelecimento(Array("id" => $referenciaCliente->idEstabelecimento));
            $estabelecimentoRn = new EstabelecimentoRn();
            $estabelecimentoRn->conexao->carregar($estabelecimento);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("estabelecimentoInvalido"));
        }
        
        $result = $this->conexao->listar("referencia = '{$referenciaCliente->referencia}' AND id_estabelecimento = {$referenciaCliente->idEstabelecimento}");
        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("clienteJaCadastradoSistema"));
        }
        
        
        $referenciaCliente->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
        
        unset($referenciaCliente->estabelecimento);
        
        $this->conexao->salvar($referenciaCliente);
        
    }
    
    public function carregar(ReferenciaCliente &$referenciaCliente, $carregar = true, $carregarEstabelecimento = true) {
        if ($carregar) {
            $this->conexao->carregar($referenciaCliente);
        }
        
        if ($carregarEstabelecimento && $referenciaCliente->idEstabelecimento > 0) {
            $referenciaCliente->estabelecimento = new Estabelecimento(Array("id" => $referenciaCliente->idEstabelecimento));
            $estabelecimentoRn = new EstabelecimentoRn();
            $estabelecimentoRn->conexao->carregar($referenciaCliente->estabelecimento);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarEstabelecimento = true) {
        
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $referenciaCliente) {
            $this->carregar($referenciaCliente, false, $carregarEstabelecimento);
            $lista[] = $referenciaCliente;
        }
        return $lista;
    }
    
    
    public function getByReferencia($referencia, Estabelecimento $estabelecimento) {
        $result = $this->conexao->select(
                Array(
                    "referencia" => $referencia, 
                    "id_estabelecimento" => $estabelecimento->id
                )
            );
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function getOrCreate($referencia, $chaveEstabelecimento) {
        
        $estabelecimentoRn = new EstabelecimentoRn();
        $estabelecimento = $estabelecimentoRn->getByChave($chaveEstabelecimento);
        
        if ($estabelecimento == null) {
            throw new \Exception($this->idioma->getText("chaveEstabeleInvalida"));
        }
        
        if ($estabelecimento->ativo < 1) {
            throw new \Exception($this->idioma->getText("estabelecimentoDesativado"));
        }
        
        if (empty($referencia)) {
            throw new \Exception($this->idioma->getText("referenciaInvalida"));
        }
        
        $referenciaCliente = $this->getByReferencia($referencia, $estabelecimento);
        
        if ($referenciaCliente != null) {
            return $referenciaCliente;
        } else {
            $referenciaCliente = new ReferenciaCliente();
            $referenciaCliente->idEstabelecimento = $estabelecimento->id;
            $referenciaCliente->referencia = $referencia;
            
            $this->salvar($referenciaCliente);
            
            return $referenciaCliente;
        }
        
    }
    
    
    
    public function validar($referencia, $chaveEstabelecimento) {
        
        $estabelecimentoRn = new EstabelecimentoRn($this->conexao->adapter);
        $estabelecimento = $estabelecimentoRn->getByChave($chaveEstabelecimento);
        
        if ($estabelecimento == null) {
            throw new \Exception($this->idioma->getText("chaveEstabeleInvalida"));
        }
        
        if ($estabelecimento->ativo < 1) {
            throw new \Exception($this->idioma->getText("estabelecimentoDesativado"));
        }
        
        $clienteReferencia = $this->getByReferencia($referencia, $estabelecimento);
        
        if ($clienteReferencia == null) {
            throw new \Exception($this->idioma->getText("referenciaInvalida"));
        }
        
    }
    
    
    public function getQuantidadeReferencias(Estabelecimento $estabelecimento) {
        
        if (!$estabelecimento->id > 0) {
            throw new \Exception($this->idioma->getText("identEstabeleInvalida"));
        }
        
        $query = " SELECT COUNT(*) AS qtd FROM referencias_clientes rc WHERE rc.id_estabelecimento = {$estabelecimento->id} ";
        
        $result = $this->conexao->adapter->query($query)->execute();
        $qtd = 0;
        foreach ($result as $d) {
            $qtd = $d["qtd"];
        }
        return $qtd;
    }
    
    
}

?>