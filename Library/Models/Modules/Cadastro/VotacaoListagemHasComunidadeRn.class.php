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
class VotacaoListagemHasComunidadeRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    /**
     *
     * @var \Utils\PropertiesUtils 
     */
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new VotacaoListagemHasComunidade());
        } else {
            $this->conexao = new GenericModel($adapter, new VotacaoListagemHasComunidade());
        }
    }
    
    public function salvar(VotacaoListagemHasComunidade $votacaoListagemHasComunidade) {
        
        if (!($votacaoListagemHasComunidade->idComunidade > 0)) {
            throw new \Exception($this->idioma->getText("votacaoListagemHasComunidadeRn1"));
        }
        
        if (!($votacaoListagemHasComunidade->idVotacaoListagem > 0)) {
            throw new \Exception($this->idioma->getText("votacaoListagemHasComunidadeRn2"));
        }
        
        if (!is_numeric($votacaoListagemHasComunidade->membros)) {
            $votacaoListagemHasComunidade->membros = 0;
        }
        
        if (empty($votacaoListagemHasComunidade->link)) {
            $comunidade = new Comunidade(Array("id" => $votacaoListagemHasComunidade->idComunidade));
            $comunidadeRn = new ComunidadeRn();
            $comunidadeRn->conexao->carregar($comunidade);
            
            throw new \Exception($this->idioma->getText("votacaoListagemHasComunidadeRn3") . " {$comunidade->nome}");
        }
        
        
        unset($votacaoListagemHasComunidade->comunidade);
        $this->conexao->salvar($votacaoListagemHasComunidade);
    }
    
    public function carregar(VotacaoListagemHasComunidade &$votacaoListagemHasComunidade, $carregar = true, $carregarComunidade = true) {
        
        if ($carregar) {
            $this->conexao->carregar($votacaoListagemHasComunidade);
        }
        
        if ($carregarComunidade && $votacaoListagemHasComunidade->idComunidade > 0) {
            $votacaoListagemHasComunidade->comunidade = new Comunidade(Array("id" => $votacaoListagemHasComunidade->idComunidade));
            $comunidadeRn = new ComunidadeRn();
            $comunidadeRn->conexao->carregar($votacaoListagemHasComunidade->comunidade);
        }
        
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarComunidade = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        
        foreach ($result as $votacaoListagemHasComunidade) {
            $this->carregar($votacaoListagemHasComunidade, false, $carregarComunidade);
            $lista[] = $votacaoListagemHasComunidade;
        }
        
        return $lista;
    }
    
}

?>