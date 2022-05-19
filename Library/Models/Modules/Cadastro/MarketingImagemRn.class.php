<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class MarketingImagemRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new MarketingImagem());
        } else {
            $this->conexao = new GenericModel($adapter, new MarketingImagem());
        }
    }
    
    
    public function salvar(MarketingImagem &$marketingImagem) {
        
        if ($marketingImagem->id > 0) {
            $aux = new NotificacaoMoeda(Array("id" => $marketingImagem->id));
            $this->conexao->carregar($aux);
            $marketingImagem->id = $aux->id;
        }        
        
        if (empty($marketingImagem->nomePropaganda)) {
            throw new \Exception("O nome da propaganda deve ser informado.");
        }
        
        /*if (empty($marketingImagem->url)) {
            throw new \Exception("O arquivo deve ser carregado.");
        }  */
        
        if (!isset($marketingImagem->dataInicial->data) || $marketingImagem->dataInicial->data == null) {
            throw new \Exception("Data inicial inválida");
        }
        
        if (!isset($marketingImagem->dataFinal->data) || $marketingImagem->dataFinal->data == null) {
            throw new \Exception("Data final inválida");
        }
        
        if (empty($marketingImagem->idUsuario)) {
            throw new \Exception("Usuário inválido.");
        }
        
        if (empty($marketingImagem->ativo)) {
            throw new \Exception("Selecione se inicia ativado ou desativado.");
        }
        
        if (empty($marketingImagem->prioridade)) {
            throw new \Exception("Selecione uma prioridade.");
        }

        $this->conexao->salvar($marketingImagem);
    }
    
    
    public function alterarStatus(MarketingImagem &$marketingImagem) {
        try {
            $this->conexao->carregar($marketingImagem);
        } catch (\Exception $ex) {
            throw new \Exception("Propaganda não localizada.");
        }        
        //exit(print_r($marketingImagem));
        $marketingImagem->ativo = ($marketingImagem->ativo == \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO ? \Utils\Constantes::NOTIFICACAO_MOEDA_DESATIVADO : \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO);
        $this->conexao->update(Array("ativo" => $marketingImagem->ativo), Array("id" => $marketingImagem->id));
    }    
    
    public function excluir(MarketingImagem &$marketingImagem) {
        try {
            $this->conexao->carregar($marketingImagem);
        } catch (\Exception $ex) {
            throw new \Exception("Propaganda não localizada.");
        }       
       
        $this->conexao->delete(Array("id" => $marketingImagem->id));
    } 
    
    
}

?>