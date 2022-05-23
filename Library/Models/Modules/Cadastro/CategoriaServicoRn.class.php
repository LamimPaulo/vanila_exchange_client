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
class CategoriaServicoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new CategoriaServico());
        } else {
            $this->conexao = new GenericModel($adapter, new CategoriaServico());
        }
    }
    
    public function salvar(CategoriaServico &$categoriaServico) {
        
        if (!\Utils\Geral::isCliente()) {
            throw new \Exception($this->idioma->getText("voceNaoTemPermirealOper"));
        }
        $cliente = \Utils\Geral::getCliente();
        
        if ($categoriaServico->id > 0) {
            $aux = new CategoriaServico(Array("id" => $categoriaServico->id));
            $this->conexao->carregar($aux);
            
            $categoriaServico->ativo = $aux->ativo;
            
            if ($aux->idCliente == $cliente->id) {
                $categoriaServico->idCliente = $cliente->id;
            } else {
                throw new \Exception($this->idioma->getText("voceNaoTemPermirealOper"));
            }
            
        } else {
            $categoriaServico->ativo = 1;
            $categoriaServico->idCliente = $cliente->id;
        } 
        
        if (empty($categoriaServico->descricao)) {
            throw new \Exception($this->idioma->getText("aDescDeveInformada"));
        }
        
        if (!$categoriaServico->idCliente > 0) {
            throw new \Exception($this->idioma->getText("oIdClienteDeveInformado"));
        }
        
        $this->conexao->salvar($categoriaServico);
        
    }
    
    
    public function alterarStatusAtivo(CategoriaServico &$categoriaServico) {
        try {
            $this->conexao->carregar($categoriaServico);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("caterogiaInvalida"));
        }
        
        $categoriaServico->ativo = ($categoriaServico->ativo > 0 ? 0 : 1);
        $this->conexao->update(Array("ativo" => $categoriaServico->ativo), Array("id" => $categoriaServico->id));
    }
    
    public function excluir(CategoriaServico &$categoriaServico) {
        try {
            $this->conexao->carregar($categoriaServico);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("caterogiaInvalida"));
        }
        
        $boletoClienteRn = new BoletoClienteRn();
        $boletos = $boletoClienteRn->conexao->listar("id_categoria_servico = {$categoriaServico->id}", NULL, null, 1);
        if (sizeof($boletos) > 0) {
            throw new \Exception($this->idioma->getText("aCatNaoPodeSerDeletada"));
        }
        $remessaDinheiroRn = new RemessaDinheiroRn();
        $remessas = $remessaDinheiroRn->conexao->listar("id_categoria_servico = {$categoriaServico->id}", NULL, null, 1);
        if (sizeof($remessas) > 0) {
            throw new \Exception($this->idioma->getText("caterogiaInvalida"));
        }
        
        $this->conexao->excluir($categoriaServico);
    }
    
}

?>