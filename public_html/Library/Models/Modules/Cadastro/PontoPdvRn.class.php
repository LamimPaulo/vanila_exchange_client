<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estabelecimento
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class PontoPdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PontoPdv());
        } else {
            $this->conexao = new GenericModel($adapter, new PontoPdv());
        }
    }
    
    
    public function salvar(PontoPdv &$pontoPdv) {
        
        if ($pontoPdv->id > 0) {
            
            $aux = new PontoPdv(Array("id" => $pontoPdv->id));
            $this->conexao->carregar($aux);
            
            $pontoPdv->dataCriacao = $aux->dataCriacao;
            $pontoPdv->ativo = $aux->ativo;
            
        } else {
            $pontoPdv->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $pontoPdv->ativo = 1;
        }
        
        
        if ($pontoPdv->tipoComissaoPdv == null) {
            $pontoPdv->tipoComissaoPdv = "p";
        }
        
        if (!is_numeric($pontoPdv->habilitarSaqueAutomatico)) {
            $pontoPdv->habilitarSaqueAutomatico = 0;
        }
        
        if (!is_numeric($pontoPdv->comissaoPdv)) {
            $pontoPdv->comissaoPdv = 0;
        }
        
        if (empty($pontoPdv->descricao)) {
            throw new \Exception("É necessário informar uma descrição para o ponto");
        }
        
        if (!$pontoPdv->idEstabelecimento > 0) {
            throw new \Exception("É necessário informar a identificação do estabelecimento");
        }
        
        unset($pontoPdv->estabelecimento);
        $this->conexao->salvar($pontoPdv);
        
    }
    
    
    public function carregar(PontoPdv &$pontoPdv, $carregar = true, $carregarEstabelecimento = true) {
        if ($carregar) {
            $this->conexao->carregar($pontoPdv);
        }
        
        if ($carregarEstabelecimento && $pontoPdv->idEstabelecimento > 0) {
            $pontoPdv->estabelecimento = new Estabelecimento(Array("id" => $pontoPdv->idEstabelecimento));
            $estabelecimentoRn = new EstabelecimentoRn($this->conexao->adapter);
            $estabelecimentoRn->carregar($pontoPdv->estabelecimento, true, false, true);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarEstabelecimento = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $pontoPdv) {
            $this->carregar($pontoPdv, false, $carregarEstabelecimento);
            $lista[] = $pontoPdv;
        }
        return $lista;
    }
    
    public function filtrar($idEstabelecimento = null, $filtro = null) {
        
        
            
        $where = Array();
        if ($idEstabelecimento > 0) {
            $where[] = " p.id_estabelecimento = {$idEstabelecimento} ";
        }
        $cliente = \Utils\Geral::getCliente();
        if ($cliente != null) {
            $where[] = " c.id = {$cliente->id} ";
        }
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " (LOWER(p.descricao) LIKE LOWER('%{$filtro}%') ) OR "
                    . " (LOWER(e.nome) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) "
                    . " ) ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT "
                . " p.* "
                . " FROM pontos_pdv p "
                . " INNER JOIN estabelecimentos e ON (p.id_estabelecimento = e.id) "
                . " INNER JOIN clientes c ON (e.id_cliente = c.id) "
                . " {$where} "
                . " ORDER BY p.descricao; ";
                
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $pontoPdv = new PontoPdv($dados);
            $this->conexao->carregar($pontoPdv);
            $lista[] = $pontoPdv;
        }
        return $lista;
    }
    
    
    public function excluir(PontoPdv &$pontoPdv) {
        try {
            
            $this->conexao->adapter->iniciar();
            
            $invoicePdvRn = new InvoicePdvRn();
            $invoices = $invoicePdvRn->conexao->listar("id_ponto_pdv = {$pontoPdv->id}", null, null, 1);
            if (sizeof($invoices) > 0) {
                throw new \Exception("Você não pode excluir o PDV pois existem invoices relacionadas ao mesmo");
            }
            
            $chavePdvRn = new ChavePdvRn();
            $chavePdvRn->conexao->delete("id_ponto_pdv = {$pontoPdv->id}");
            $this->conexao->excluir($pontoPdv);
            
            $this->conexao->adapter->finalizar();
        } catch (Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function alterarStatusAtivo(PontoPdv &$pontoPdv) {
        
        try {
            $this->conexao->carregar($pontoPdv);
        } catch (\Exception $ex) {
            throw new \Exception("Ponto não localizado no sistema ");
        }
        
        $pontoPdv->ativo = ($pontoPdv->ativo > 0 ? 0 : 1);
        
        $this->conexao->update(
                Array("ativo" => $pontoPdv->ativo), 
                Array("id" => $pontoPdv->id)
                );
        
    }
    
    
    public function getByChave($chave, $validar = false) {
        if (empty($chave) && $validar) {
            throw new \Exception("Chave de PDV inválida");
        }
        
        $chavePdvRn = new ChavePdvRn();
        
        $result = $chavePdvRn->conexao->listar("chave = '{$chave}' OR chave_homologacao = '{$chave}' ");
        if (sizeof($result) > 0) {
            $chavePdv = $result->current();
        } else {
            $chavePdv = null;
        }
        
        if ($chavePdv != null) {
            
            if ($validar) {
                if ($chavePdv->ativo < 1) {
                    throw new \Exception("Chave de PDV inválida");
                }
            }
            $pontoPdv = new PontoPdv(Array("id" => $chavePdv->idPontoPdv));
            $this->carregar($pontoPdv, true, true);
            
        } else {
            $pontoPdv = null;
        }
        
        $homologacao =  ($chavePdv->chaveHomologacao == $chave);
        
        return Array("ponto"=> $pontoPdv, "homologacao" => $homologacao);
    }
}
?>