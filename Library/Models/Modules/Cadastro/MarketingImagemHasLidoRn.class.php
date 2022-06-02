<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class MarketingImagemHasLidoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new MarketingImagemHasLido());
        } else {
            $this->conexao = new GenericModel($adapter, new MarketingImagemHasLido());
        }
    }
    
    
    public function salvar(MarketingImagemHasLido &$marketingHasLido) {
        
        if ($marketingHasLido->id > 0) {
            $aux = new MarketingImagemHasLido(Array("id" => $marketingHasLido->id));
            $this->conexao->carregar($aux);            
            
        }
       
        if (empty($marketingHasLido->idNotificacao)) {
            throw new \Exception("Verifique o ID da notificação.");
        }
         
        if (empty($marketingHasLido->idCliente)) {
            throw new \Exception("Verifique o ID do cliente.");
        }
       
        $this->conexao->salvar($marketingHasLido);
    }
    
    public function imagensNaoVista(Cliente $cliente) {
        
        $query = "SELECT marketing_imagem.id, nome_propaganda, prioridade, url, id_cliente, qtd_max_visualizacao, intervalo
            FROM marketing_imagem 
            LEFT JOIN marketing_imagem_has_lido ON marketing_imagem.id = marketing_imagem_has_lido.id_notificacao AND id_cliente = {$cliente->id}
            WHERE  ativo = 1 AND id_cliente IS NULL AND date(data_final) >= curdate() ORDER BY prioridade DESC, data_inicial DESC;";
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
    
    public function somarQtdVisualizacao(String $id) {

        $query = "SELECT id_notificacao,  COUNT(id_notificacao) as total FROM marketing_imagem_has_lido WHERE id_notificacao = {$id};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
}

?>