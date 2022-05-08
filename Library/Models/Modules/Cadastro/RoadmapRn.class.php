<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class RoadmapRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Roadmap());
        } else {
            $this->conexao = new GenericModel($adapter, new Roadmap());
        }
    }
    
    
    public function salvar(Roadmap &$roadmap) {
        
        if ($roadmap->id > 0) {
            $aux = new Roadmap(Array("id" => $roadmap->id));
            $this->conexao->carregar($aux);
            
            $roadmap->publicado = $aux->publicado;
            $roadmap->concluido = $aux->concluido;
            
            if (empty($roadmap->imagem)) {
                $roadmap->imagem = $aux->imagem;
            }
        } else {
            $roadmap->publicado = 0;
            $roadmap->concluido = 0;
            
            if (empty($roadmap->imagem)) {
                throw new \Exception("A Imagem deve ser informado");
            } 
        }
        
        
        if (empty($roadmap->titulo)) {
            throw new \Exception("O título deve ser informado");
        }
        
        if (empty($roadmap->texto)) {
            throw new \Exception("O texto deve ser informado");
        }
        
        if ($roadmap->posicao <= 0) {
            throw new \Exception("A posição deve ser informada");
        }
        
        if (!isset($roadmap->data->data) || $roadmap->data->data == null) {
            throw new \Exception("Data inválida");
        }
        
        $this->conexao->salvar($roadmap);
    }
    
    public function excluir(Roadmap &$roadmap) {
        
        try {
            $this->conexao->carregar($roadmap);
        } catch (Exception $ex) {
            throw new \Exception("Roadmap não encontrado");
        }
        
        $this->conexao->excluir($roadmap);
    }
    
    public function alterarStatusPublicado(Roadmap &$roadmap) {
        try {
            $this->conexao->carregar($roadmap);
        } catch (\Exception $ex) {
            throw new \Exception("Roadmap não localizado");
        }
        
        $roadmap->publicado = ($roadmap->publicado > 0 ? 0 : 1);
        $this->conexao->update(Array("publicado" => $roadmap->publicado), Array("id" => $roadmap->id));
    }
    
    
    public function alterarStatusConcluido(Roadmap &$roadmap) {
        try {
            $this->conexao->carregar($roadmap);
        } catch (\Exception $ex) {
            throw new \Exception("Roadmap não localizado");
        }
        
        $roadmap->concluido = ($roadmap->concluido > 0 ? 0 : 1);
        $this->conexao->update(Array("concluido" => $roadmap->concluido), Array("id" => $roadmap->id));
    }
    
}

?>